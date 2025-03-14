/**
 * Clipboard feature.
 * 
 * Please note: this file deliberately does not have `clipboard.js` in its name, as this would
 * cause the WordPress Plugin Review Team to think that https://clipboardjs.com/ is included with
 * the plugin.
 * 
 * @link https://github.com/pronamic/wp-pay-core/issues/209
 * 
 * @link https://github.com/WordPress/WordPress/blob/68e3310c024d7fceb84a5028e955ad163de6bd45/wp-includes/js/plupload/handlers.js#L364-L393
 * @link https://translate.wordpress.org/projects/wp/dev/nl/default/?filters%5Bstatus%5D=either&filters%5Boriginal_id%5D=10763746&filters%5Btranslation_id%5D=91929960
 * @link https://translate.wordpress.org/projects/wp/dev/nl/default/?filters%5Bstatus%5D=either&filters%5Boriginal_id%5D=6831324&filters%5Btranslation_id%5D=58732256
 */
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
