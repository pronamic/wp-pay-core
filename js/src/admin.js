/* global tippy */
( function( $ ) {
	/**
	 * Pronamic iDEAL config prototype
	 */
	var PronamicPayGatewayConfigEditor = function( element ) {
		var obj = this;
		var $element = $( element );

		// Elements
		var elements = {};
		elements.variantId          = $element.find( '#pronamic_gateway_id' );
		elements.extraSettings      = $element.find( 'div.extra-settings' );
		elements.sectionHeaders     = $element.find( '.gateway-config-section-header' );
		elements.tabs               = $element.find( '.pronamic-pay-tabs' );
		elements.tabItems           = $element.find( 'ul.pronamic-pay-tabs-items' );

		/**
		 * Update config fields
		 */
		this.updateFields = function() {
			// Find selected variant
			obj.selectedVariant = elements.variantId.find( 'option:selected' );

			obj.settings = obj.selectedVariant.data( 'pronamic-pay-settings' );

			// Hide all settings
			$element.find( '.extra-settings' ).hide();

			// Show settings for variant
			obj.settingElements = [];

			if ( $.isArray( obj.settings ) ) {
				$.each( obj.settings, function( index, value ) {
					$element.find( '.setting-' + value ).show();
				} );
			}

			$element.find( '.setting-' + obj.selectedVariant.val() ).show();

			// Set name of first tab item to name of selected provider
			var providerName = obj.selectedVariant.text().split( ' - ' )[0].replace( / \(.*\)/, '' );

			elements.tabItems.find( ':visible' ).first().text( providerName ).click();

			$( '#pronamic-pay-gateway-description').html( obj.selectedVariant.attr( 'data-gateway-description' ) );
		};

		// Update row background color
		this.updateRowBackgroundColor = function() {
			// Set background color of visible even rows
			var rows = elements.extraSettings.find( '.form-table tr' );

			rows.removeClass( 'even' );
			rows.filter( ':visible:even' ).addClass( 'even' );
		};

		/**
		 * Tabs
		 */
		this.initTabs = function() {
			$.each(elements.sectionHeaders, function ( i, elm ) {
				var item = $( elm );
				var title = item.find( 'h4' ).text();
				var settingsClasses = item.parents( 'div' )[0].className;

				elements.tabItems.append(
					$( '<li>' + title + '</li>' ).addClass( settingsClasses ).removeClass( 'pronamic-pay-tab' )
				);
			} );

			// Move tab items list after 'Mode' setting
			elements.tabItems.next().after( elements.tabItems );

			elements.tabItems.find( 'li' ).click( obj.showTabSettings );
		};

		this.showTabSettings = function() {
			var tabItem = $( this );

			// Show tab
			elements.extraSettings.hide().eq( tabItem.index() ).show();
		};

		/**
		 * Function calls
		 */
		var update_meta_boxes = function () {
			$.ajax( {
				url: pronamicPayGatewayAdmin.rest_url,
				method: 'GET',
				beforeSend: function ( xhr ) {
					xhr.setRequestHeader( 'X-WP-Nonce', pronamicPayGatewayAdmin.nonce );
				},
				data: {
					'gateway_id': $( '#pronamic_gateway_id' ).val(),
					'gateway_mode': $( '#pronamic_ideal_mode' ).val()
				}
			} ).done( function ( response ) {
				$( '#pronamic-pay-gateway-settings' ).html( response.meta_boxes.settings );

				$( '#pronamic-pay-gateway-settings .pronamic-pay-tabs' ).pronamicPayTabs();

				// Tooltip
				$( '#pronamic-pay-gateway-settings .pronamic-pay-tip' ).each( function () {
					tippy( this, {
						content: $( this ).attr( 'title' ),
						arrow: true,
						theme: 'pronamic-pay'
					} );
				} );
			} );
		};

		$( '#pronamic_gateway_id' ).change( update_meta_boxes );
		$( '#pronamic_ideal_mode' ).change( update_meta_boxes );

		obj.initTabs();

		obj.updateFields();

		elements.variantId.change( obj.updateFields );
	};

	/**
	 * jQuery plugin - Pronamic iDEAL config editor
	 */
	$.fn.pronamicPayGatewayConfigEditor = function() {
		return this.each( function() {
			var $this = $( this );

			if ( $this.data( 'pronamic-pay-gateway-config-editor' ) ) {
				return;
			}

			var editor = new PronamicPayGatewayConfigEditor( this );

			$this.data( 'pronamic-pay-gateway-config-editor', editor );
		} );
	};

	/**
	 * Pronamic Pay Tabs
	 */
	var PronamicPayTabs = function( element ) {
		var obj = this;
		var $element = $( element );

		// Elements
		var elements = {};
		elements.tabItems = $element.find( 'ul.pronamic-pay-tabs-items' );
		elements.tabs     = $element.find( '.pronamic-pay-tab' );
		elements.tabItems = $element.find( 'ul.pronamic-pay-tabs-items' );

		// Update row background color
		this.updateRowBackgroundColor = function() {
			// Set background color of visible even rows
			var rows = elements.tabs.find( '.form-table tr' );

			rows.removeClass( 'even' );
			rows.filter( ':visible:even' ).addClass( 'even' );
		};

		/**
		 * Tabs
		 */
		this.showTab = function( ) {
			var tabItem = $( this );

			elements.tabItems.find( 'li' ).removeClass( 'active' );

			tabItem.addClass( 'active' );

			// Show tab
			elements.tabs.hide().eq( tabItem.index() ).show();

			obj.updateRowBackgroundColor();

			obj.visibleTabItems = elements.tabItems.find( 'li:visible' );

			obj.activeTabItem = tabItem;
		};

		this.responsiveTabs = function() {
			if ( $( window ).width() > 960 ) {
				elements.tabs.hide();

				if ( obj.activeTabItem ) {
					// Activate last active tab
					obj.activeTabItem.click();
				} else {
					// Make first tab active
					elements.tabItems.find( 'li:visible' ).first().click();
				}
			} else {
				if ( ! obj.visibleTabItems ) {
					return;
				}

				elements.tabs.hide();

				$.each( obj.visibleTabItems, function( index, tabItem ) {
					elements.tabs.eq( $( tabItem ).index() ).show();
				} );
			}
		};

		/**
		 * Function calls
		 */
		elements.tabItems.find( 'li' ).click( obj.showTab );

		// Make first tab active
		elements.tabItems.find( 'li:visible' ).first().click();

		$( window ).resize( obj.responsiveTabs );
	};

	/**
	 * jQuery plugin - Pronamic Pay Tabs
	 */
	$.fn.pronamicPayTabs = function() {
		return this.each( function() {
			var $this = $( this );

			if ( $this.data( 'pronamic-pay-tabs' ) ) {
				return;
			}

			var tabs = new PronamicPayTabs( this );

			$this.data( 'pronamic-pay-tabs', tabs );
		} );
	};

	/**
	 * Pronamic Pay post status.
	 */
	var PronamicPayPostStatus = function( element ) {
		var $element = $( element );

		// Post Status edit click.
		$element.siblings( 'a.edit-pronamic-pay-post-status' ).on( 'click', function( event ) {
			if ( $element.is( ':hidden' ) ) {
				$element.slideDown( 'fast', function() { $element.find( 'select' ).trigger( 'focus' ); } );

				$( this ).hide();
			}

			event.preventDefault();
		} );

		// Save post status changes and hide options.
		$element.find( '.save-pronamic-pay-post-status' ).on( 'click', function( event ) {
			$element.slideUp( 'fast' ).siblings( 'a.edit-pronamic-pay-post-status' ).show().trigger( 'focus' );

			$( '#pronamic-pay-status-display' ).text( $( '#pronamic-pay-post-status option:selected' ).text() );

			event.preventDefault();
		} );

		// Cancel post status editing and hide options.
		$element.find( '.cancel-pronamic-pay-post-status' ).on( 'click', function( event ) {
			$element.slideUp( 'fast' ).siblings( 'a.edit-pronamic-pay-post-status' ).show().trigger( 'focus' );

			$( '#pronamic-pay-post-status' ).val( $( '#hidden_pronamic_pay_post_status' ).val() );

			$( '#pronamic-pay-status-display' ).text( $( '#pronamic-pay-post-status option:selected' ).text() );

			event.preventDefault();
		} );
	};

	/**
	 * jQuery plugin - Pronamic Pay post status
	 */
	$.fn.pronamicPayPostStatus = function() {
		return this.each( function() {
			var $this = $( this );

			if ( $this.data( 'pronamic-pay-post-status' ) ) {
				return;
			}

			var postStatus = new PronamicPayPostStatus( this );

			$this.data( 'pronamic-pay-post-status', postStatus );
		} );
	};

	/**
	 * Pronamic pay gateway test
	 */
	var PronamicPayGatewayTest = function( element ) {
		var obj = this;
		var $element = $( element );

		// Elements
		var elements = {};
		elements.paymentMethods = $element.find( 'select[name="pronamic_pay_test_payment_method"]' );

		/**
		 * Update input visibility
		 */
		this.updateInputVisibility = function() {
			var method = elements.paymentMethods.val();

			if ( '' !== method ) {
				$element.find( '.pronamic-pay-test-payment-method' ).hide().filter( '.' + method ).show();
			}

			// Hide subscription options for unsupported payment methods.
			if ( 1 === elements.paymentMethods.find( 'option:selected' ).data( 'is-recurring' ) ) {
				$( '#pronamic-pay-test-subscription' ).parents( 'tr' ).show();
			} else {
				$( '#pronamic-pay-test-subscription' ).parents( 'tr' ).hide();
				$( '#pronamic-pay-test-subscription' ).prop( 'checked', false ).trigger( 'change' );
			}
		};

		// Function calls
		obj.updateInputVisibility();

		elements.paymentMethods.change( obj.updateInputVisibility );

		$element.on( 'keydown', 'input, select', function( e ) {
			if ( 13 === e.keyCode) {
				e.preventDefault();

				$element.find('input[name="test_pay_gateway"]').click();
			}
		});
	};

	/**
	 * jQuery plugin - Pronamic pay gateway test
	 */
	$.fn.pronamicPayGatewayTest = function() {
		return this.each( function() {
			var $this = $( this );

			if ( $this.data( 'pronamic-pay-gateway-test' ) ) {
				return;
			}

			var gatewayTest = new PronamicPayGatewayTest( this );

			$this.data( 'pronamic-pay-gateway-test', gatewayTest );
		} );
	};

	/**
	 * Pronamic iDEAL pay form options
	 */
	var PronamicPayFormOptions = function( element ) {
		var obj = this;
		var $element = $( element );

		// Elements
		var elements = {};
		elements.amountMethod = $element.find( 'select[name="_pronamic_payment_form_amount_method"]' );

		/**
		 * Update amounts visibility
		 */
		this.updateAmountsVisibility = function() {
			var method = elements.amountMethod.val();

			if ( method === 'choices_only' || method === 'choices_and_input' ) {
				$element.find('input[name="_pronamic_payment_form_amount_choices\[\]"]').closest('div').show();
			} else {
				$element.find('input[name="_pronamic_payment_form_amount_choices\[\]"]').closest('div').hide();
			}
		};

		/**
		 * Maybe add an empty amount field
		 */
		this.maybeAddAmountChoice = function() {
			elements.amountChoices = $element.find( 'input[name="_pronamic_payment_form_amount_choices\[\]"]' );
			var emptyChoices       = elements.amountChoices.filter( function() { return this.value === ''; } );

			if ( emptyChoices.length === 0 ) {
				var lastChoice = elements.amountChoices.last().closest( 'div' );
				var newChoice  = lastChoice.clone();
				var choiceId   = '_pronamic_payment_form_amount_choice_' + elements.amountChoices.length;

				newChoice.find( 'input' ).attr( 'id', choiceId ).val( '' );
				newChoice.find( 'label' ).attr( 'for', choiceId );

				lastChoice.after( newChoice );
			}
		};

		// Function calls
		obj.updateAmountsVisibility();

		elements.amountMethod.change( obj.updateAmountsVisibility );

		$element.on( 'keyup', 'input[name="_pronamic_payment_form_amount_choices\[\]"]', function() {
			obj.maybeAddAmountChoice();
		});
	};

	/**
	 * jQuery plugin - Pronamic iDEAL form options
	 */
	$.fn.pronamicPayFormOptions = function() {
		return this.each( function() {
			var $this = $( this );

			if ( $this.data( 'pronamic-pay-forms-options' ) ) {
				return;
			}

			var formOptions = new PronamicPayFormOptions( this );

			$this.data( 'pronamic-pay-form-options', formOptions );
		} );
	};

	/**
	 * Ready
	 */
	$( document ).ready( function() {
		$( '#pronamic-pay-gateway-config-editor' ).pronamicPayGatewayConfigEditor();
		$( '#pronamic_payment_form_options').pronamicPayFormOptions();
		$( '#pronamic-pay-post-status-select' ).pronamicPayPostStatus();
		$( '#pronamic_gateway_test').pronamicPayGatewayTest();
		$( '.pronamic-pay-tabs' ).pronamicPayTabs();

		// Tooltip
		$( '.pronamic-pay-tip' ).each( function() {
			tippy( this, {
				content: $( this ).attr( 'title' ),
				arrow: true,
				theme: 'pronamic-pay'
			} );
		} );
	} );
} )( jQuery );
