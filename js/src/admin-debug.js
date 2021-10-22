/* global pronamicPayAdminDebugScheduler */
jQuery( document ).ready( function( $ ) {
	const $scheduled = $( '.pronamic-pay-debug-progress-count-scheduled' );
	const $total     = $( '.pronamic-pay-debug-progress-count-total' );
	const $btnStart  = $( '#pronamic-pay-debug-scheduler-start' );
	const $bar       = $( '.pronamic-pay-debug__bar' );
	const $barStatus = $( '.pronamic-pay-debug__bar__status' );
	let isPaused     = false;
	let currentProcessUrl;
	let numTotalPages;

	// Before XHR send.
	const beforeSend = function ( xhr ) {
		xhr.setRequestHeader( 'X-WP-Nonce', pronamicPayAdminDebugScheduler.nonce );
	};

	// Load total item count.
	$.ajax( {
		url: pronamicPayAdminDebugScheduler.count_url,
		method: 'GET',
		beforeSend: beforeSend
	} ).done( function ( response ) {
		let { count, num_pages } = response.data;

		if ( count !== 'undefined' ) {
			$total.html( count );

			if ( count > 0 ) {
				$btnStart.removeAttr( 'disabled' );
			}

			if ( num_pages ) {
				numTotalPages = num_pages;
			}
		}
	} );

	const processUrl = function ( url ) {
		currentProcessUrl = url;

		// Check if paused.
		if ( isPaused ) {
			return;
		}

		// Send request.
		$.ajax( {
			url: url,
			method: 'GET',
			beforeSend: beforeSend
		} ).done( function ( response ) {
			let { number_scheduled } = response.data;
			let { _links } = response;

			// Update progress.
			if ( number_scheduled ) {
				let total_scheduled = parseInt( $scheduled.html(), 10 ) + number_scheduled;

				$scheduled.html( total_scheduled );

				// Update bar.
				let total = parseInt( $total.html(), 10 );
				let progress = Math.ceil( ( total_scheduled / total ) * 100 );

				$bar.css( 'width', progress + '%' );
				$barStatus.html( progress + ' %' );
			}

			// Start next processing.
			if ( _links.next ) {
				processUrl( _links.next[0].href );
			}

			// Check finished.
			if ( ! _links.next && _links.scheduler ) {
				$btnStart.hide();

				$( '#pronamic-pay-debug-scheduler-pending' ).attr( 'href', _links.scheduler[0].href ).show();
			}
		} );
	};

	// Start/pause/resume button.
	$btnStart.on( 'click', function() {
		// Start.
		if ( ! $bar.hasClass( 'wp-ui-highlight' ) ) {
			$bar.addClass( 'wp-ui-highlight' );

			$btnStart.html( pronamicPayAdminDebugScheduler.labelPause );
			$btnStart.removeClass( 'button-primary' );

			processUrl( pronamicPayAdminDebugScheduler.schedule_url + '?page=' + numTotalPages );

			return;
		}

		// Pause.
		if ( ! isPaused ) {
			isPaused = true;

			$btnStart.html( pronamicPayAdminDebugScheduler.labelResume );

			return;
		}

		// Resume.
		if ( isPaused ) {
			isPaused = false;

			$btnStart.html( pronamicPayAdminDebugScheduler.labelPause );

			processUrl( currentProcessUrl );

			return;
		}
	} );
} );
