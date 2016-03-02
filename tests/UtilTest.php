<?php

/**
 * Title: WordPress pay util test
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.3.1
 * @since 1.1.0
 */
class Pronamic_WP_Pay_UtilTest extends WP_UnitTestCase {
	/**
	 * Test string to amount.
	 *
	 * @see https://github.com/pronamic/wp-pronamic-ideal/blob/3.7.3/classes/Pronamic/WP/Pay/Settings.php#L71-L91
	 * @dataProvider string_to_amount_provider
	 */
	public function test_string_to_amount( $thousands_sep, $decimal_sep, $string, $expected ) {
		update_option( 'pronamic_pay_thousands_sep', $thousands_sep );
		update_option( 'pronamic_pay_decimal_sep', $decimal_sep );

		$amount = Pronamic_WP_Pay_Util::string_to_amount( $string );

		$this->assertEquals( $expected, $amount );
	}

	/**
	 * String to amount provider.
	 *
	 * @return array
	 */
	public function string_to_amount_provider() {
		return array(
			// '', '.'
			array( '', '.', '1', 1 ),
			array( '', '.', '2,5', 2.5 ),
			array( '', '.', '2,50', 2.5 ),
			array( '', '.', '1250,00', 1250 ),
			array( '', '.', '1250,75', 1250.75 ),
			array( '', '.', '1250.75', 1250.75 ),
			array( '', '.', '1.250,00', 1250 ),
			array( '', '.', '2.500,75', 2500.75 ),
			// '.', ','
			array( '.', ',', '1', 1 ),
			array( '.', ',', '2,5', 2.5 ),
			array( '.', ',', '2,50', 2.5 ),
			array( '.', ',', '1250,00', 1250 ),
			array( '.', ',', '2500,75', 2500.75 ),
			array( '.', ',', '1.250,00', 1250 ),
			array( '.', ',', '2.500,75', 2500.75 ),
			array( '.', ',', '2.500,750', 2500.75 ),
			array( '.', ',', '1.234.567.890', 1234567890 ),
			// ',', '.'
			array( ',', '.', '1', 1 ),
			array( ',', '.', '2.5', 2.5 ),
			array( ',', '.', '2.50', 2.5 ),
			array( ',', '.', '1250.00', 1250 ),
			array( ',', '.', '1250.75', 1250.75 ),
			array( ',', '.', '1,250.00', 1250 ),
			array( ',', '.', '2,500.75', 2500.75 ),
			array( ',', '.', '2,500.', 2500 ),
			// ' ', '.'
			array( ' ', '.', '2 500.75', 2500.75 ),
			// 't', '.'
			array( 't', '.', '2t500.75', 2500.75 ),
			array( 't', '.', '2t500.7', 2500.7 ),
			// 't', '-'
			array( 't', '-', '2t500-75', 2500.75 ),
			array( 't', '-', '2t500-7', 2500.7 ),
			// 't', ' '
			array( 't', ' ', '2t500 75', 2500.75 ),
			array( 't', ' ', '2t500 7', 2500.7 ),
			// ' ', 'd'
			array( ' ', 'd', '2 500d75', 2500.75 ),
			array( ' ', 'd', '2 500d7', 2500.7 ),
			// ' ', 'd'
			array( ' ', 'd', '-2 500d75', -2500.75 ),
			array( ' ', 'd', '-2 500d7', -2500.7 ),
			array( '', '', '123456789', 123456789 ),
			array( false, false, '123 456 789', 123456789 ),
		);
	}
}
