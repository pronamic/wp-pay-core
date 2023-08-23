jQuery( document ).ready( function () {
	var $slider = jQuery( '.pp-card-slider' ).slick( {
		dots: true,
		arrows: false,
		infinite: false,
		slidesToShow: 1,
		centerMode: true,
	} );

	$slider.find( '.slick-current input[type="radio"]' ).prop( 'checked', true );

	$slider.find( '.slick-slide' ).on( 'click', function () {
		var index = jQuery( this ).data( 'slick-index' );

		$slider.slick( 'slickGoTo', index );
	} );

	$slider.on( 'afterChange', function ( event, slick, currentSlide ) {
		$slider.find( 'input[type="radio"]' ).prop( 'checked', false );

		$slider.find( '.slick-slide' ).eq( currentSlide ).find( 'input[type="radio"]' ).prop( 'checked', true );
	} );
} );
