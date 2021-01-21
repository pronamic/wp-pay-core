<?php
/**
 * Customer helper test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay;

/**
 * Customer helper test
 *
 * @author  Remco Tolsma
 * @version 2.6.0
 * @since   2.6.0
 */
class CustomerHelperTest extends \PHPUnit\Framework\TestCase {
	/**
	 * Test customer from array.
	 */
	public function test_customer_from_array() {
		$customer = CustomerHelper::from_array( array() );

		$this->assertNull( $customer );

		$customer = CustomerHelper::from_array(
			array(
				'email'   => '',
				'phone'   => '',
				'user_id' => '',
			)
		);

		$this->assertNull( $customer );

		$customer = CustomerHelper::from_array(
			array(
				'email'   => 'john@example.com',
				'phone'   => '',
				'user_id' => '',
			)
		);

		$this->assertEquals( 'john@example.com', $customer->get_email() );
		$this->assertNull( $customer->get_phone() );
		$this->assertNull( $customer->get_user_id() );
	}
}
