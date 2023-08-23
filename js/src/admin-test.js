jQuery( document ).ready( function( $ ) {
	// Interval label.
	function set_interval_label() {
		var text = $( '#pronamic_pay_test_repeat_frequency :selected' ).data( 'interval-suffix' );

		$( '#pronamic_pay_test_repeat_interval_suffix' ).text( text );
	}

	$( '#pronamic_pay_test_repeat_frequency' ).change( function() { set_interval_label(); } );

	set_interval_label();

	// Ends on value.
	$( 'label[for^="pronamic_pay_ends_"] input' ).focus( function () {
		var radio_id = $( this ).parents( 'label' ).attr( 'for' );

		$( '#' + radio_id ).prop( 'checked', true );
	} );
} );
