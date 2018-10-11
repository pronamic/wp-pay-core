<?php
/**
 * Payment line helper test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Payments;

use Pronamic\WordPress\Money\Money;
use WP_UnitTestCase;

/**
 * Payment line helper test
 *
 * @author Remco Tolsma
 * @version 1.0
 */
class PaymentLineHelperTest extends WP_UnitTestCase {
	/**
	 * Test unit price including tax.
	 */
	public function test_complement_unit_price_including_tax() {
		$line = new PaymentLine();

		$line->set_tax_percentage( 21 );
		$line->set_unit_price_excluding_tax( new Money( 100, 'EUR' ) );

		PaymentLineHelper::complement_payment_line( $line );

		$this->assertEquals( 121, $line->get_unit_price_including_tax()->get_amount() );
	}

	/**
	 * Test unit price excluding tax.
	 */
	public function test_complement_unit_price_excluding_tax() {
		$line = new PaymentLine();

		$line->set_tax_percentage( 21 );
		$line->set_unit_price_including_tax( new Money( 121, 'EUR' ) );

		PaymentLineHelper::complement_payment_line( $line );

		$this->assertEquals( 100, $line->get_unit_price_excluding_tax()->get_amount() );
	}

	/**
	 * Test total amount including tax.
	 */
	public function test_complement_total_amount_including_tax() {
		$line = new PaymentLine();

		$line->set_tax_percentage( 21 );
		$line->set_total_amount_excluding_tax( new Money( 1000, 'EUR' ) );

		PaymentLineHelper::complement_payment_line( $line );

		$this->assertEquals( 1210, $line->get_total_amount_including_tax()->get_amount() );
	}

	/**
	 * Test unit price excluding tax.
	 */
	public function test_complement_total_amount_excluding_tax() {
		$line = new PaymentLine();

		$line->set_tax_percentage( 21 );
		$line->set_total_amount_including_tax( new Money( 1210, 'EUR' ) );

		PaymentLineHelper::complement_payment_line( $line );

		$this->assertEquals( 1000, $line->get_total_amount_excluding_tax()->get_amount() );
	}
}
