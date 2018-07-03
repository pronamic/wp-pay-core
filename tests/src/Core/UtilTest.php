<?php
/**
 * Util test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Core;

/**
 * Title: WordPress pay util test
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 2.0.0
 * @since 1.1.0
 */
class UtilTest extends \WP_UnitTestCase {
	/**
	 * Test string to amount.
	 *
	 * @link https://github.com/pronamic/wp-pronamic-ideal/blob/3.7.3/classes/Pronamic/WP/Pay/Settings.php#L71-L91
	 * @link https://github.com/WordPress/WordPress/blob/4.9.6/wp-includes/class-wp-locale.php
	 * @link https://github.com/WordPress/WordPress/blob/4.9.6/wp-includes/functions.php#L206-L237
	 *
	 * @dataProvider string_to_amount_provider
	 *
	 * @param string $thousands_sep Thousands seperator.
	 * @param string $decimal_sep   Decimal seperator.
	 * @param string $string        String value to convert.
	 * @param float  $expected      Expected float value.
	 */
	public function test_string_to_amount( $thousands_sep, $decimal_sep, $string, $expected ) {
		global $wp_locale;

		$wp_locale->number_format['thousands_sep'] = $thousands_sep;
		$wp_locale->number_format['decimal_point'] = $decimal_sep;

		$amount = Util::string_to_amount( $string );

		$this->assertEquals( $expected, $amount );
	}

	/**
	 * String to amount provider.
	 *
	 * @return array
	 */
	public function string_to_amount_provider() {
		return array(
			// Thousands seperator is '' and decimal seperator is '.'.
			array( '', '.', '1', 1 ),
			array( '', '.', '2,5', 2.5 ),
			array( '', '.', '2,50', 2.5 ),
			array( '', '.', '1250,00', 1250 ),
			array( '', '.', '1250,75', 1250.75 ),
			array( '', '.', '1250.75', 1250.75 ),
			array( '', '.', '1.250,00', 1250 ),
			array( '', '.', '2.500,75', 2500.75 ),
			// Thousands seperator is '.' and decimal seperator is ','.
			array( '.', ',', '1', 1 ),
			array( '.', ',', '2,5', 2.5 ),
			array( '.', ',', '2,50', 2.5 ),
			array( '.', ',', '1250,00', 1250 ),
			array( '.', ',', '2500,75', 2500.75 ),
			array( '.', ',', '1.250,00', 1250 ),
			array( '.', ',', '2.500,75', 2500.75 ),
			array( '.', ',', '2.500,750', 2500.75 ),
			array( '.', ',', '1.234.567.890', 1234567890 ),
			// Thousands seperator is ',' and decimal seperator is '.'.
			array( ',', '.', '1', 1 ),
			array( ',', '.', '2.5', 2.5 ),
			array( ',', '.', '2.50', 2.5 ),
			array( ',', '.', '1250.00', 1250 ),
			array( ',', '.', '1250.75', 1250.75 ),
			array( ',', '.', '1,250.00', 1250 ),
			array( ',', '.', '2,500.75', 2500.75 ),
			array( ',', '.', '2,500.', 2500 ),
			// Thousands seperator is ' ' and decimal seperator is '.'.
			array( ' ', '.', '2 500.75', 2500.75 ),
			// Thousands seperator is 't' and decimal seperator is '.'.
			array( 't', '.', '2t500.75', 2500.75 ),
			array( 't', '.', '2t500.7', 2500.7 ),
			// Thousands seperator is 't' and decimal seperator is '-'.
			array( 't', '-', '2t500-75', 2500.75 ),
			array( 't', '-', '2t500-7', 2500.7 ),
			// Thousands seperator is 't' and decimal seperator is ' '.
			array( 't', ' ', '2t500 75', 2500.75 ),
			array( 't', ' ', '2t500 7', 2500.7 ),
			// Thousands seperator is ' ' and decimal seperator is 'd'.
			array( ' ', 'd', '2 500d75', 2500.75 ),
			array( ' ', 'd', '2 500d7', 2500.7 ),
			array( ' ', 'd', '-2 500d75', -2500.75 ),
			array( ' ', 'd', '-2 500d7', -2500.7 ),
			// Other.
			array( '', '', '123456789', 123456789 ),
			array( false, false, '123 456 789', 123456789 ),
		);
	}

	/**
	 * Test method exists.
	 *
	 * @dataProvider status_matrix_provider
	 *
	 * @param string $class    Class name to check.
	 * @param string $method   Method name to check.
	 * @param bool   $expected Expected result.
	 */
	public function test_class_method_exists( $class, $method, $expected ) {
		$exists = Util::class_method_exists( $class, $method );

		$this->assertEquals( $expected, $exists );
	}

	/**
	 * Status matrix provider.
	 *
	 * @return array
	 */
	public function status_matrix_provider() {
		return array(
			array( __NAMESPACE__ . '\Util', 'class_method_exists', true ),
			array( __NAMESPACE__ . '\Server', 'get', true ),
			array( 'ClassDoesNotExist', 'method_does_not_exist', false ),
			array( '', '', false ),
			array( null, null, false ),
		);
	}
}
