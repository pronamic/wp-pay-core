addEventListener( 'DOMContentLoaded', function () {
	const elements = document.querySelectorAll( '.pronamic-pay-clipboard' );

	elements.forEach( function ( element ) {
		var clipboard = new ClipboardJS( element ),
			successTimeout;

		clipboard.on( 'success', function( event ) {
			var triggerElement = jQuery( event.trigger ),
				successElement = jQuery( '.success', triggerElement.closest( '.pronamic-pay-action-link-clipboard' ) );

			event.clearSelection();

			clearTimeout( successTimeout );

			successElement.removeClass( 'hidden' );

			successTimeout = setTimeout( function() {
				successElement.addClass( 'hidden' );
			}, 3000 );
		} );
	} );
} );
