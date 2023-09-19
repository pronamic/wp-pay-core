<?php
/**
 * Payment Merge Tags Controller
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways
 */

namespace Pronamic\WordPress\Pay\Payments;

use Pronamic\WordPress\Pay\MergeTags\MergeTagsController;
use Pronamic\WordPress\Pay\MergeTags\MergeTag;

/**
 * Payment Merge Tags Controller class
 */
class PaymentMergeTagsController extends MergeTagsController {
	/**
	 * Payment.
	 * 
	 * @var Payment
	 */
	private $payment;

	/**
	 * Construct payment merge tags controllers.
	 * 
	 * @param Payment $payment Payment.
	 */
	public function __construct( Payment $payment ) {
		parent::__construct( 'payment' );

		$this->payment = $payment;

		$this->add_merge_tag(
			new MergeTag(
				'payment_id',
				\__( 'Payment ID', 'pronamic_ideal' ),
				function () use ( $payment ) {
					return $payment->get_id();
				}
			)
		);

		$this->add_merge_tag(
			new MergeTag(
				'order_id',
				\__( 'Order ID', 'pronamic_ideal' ),
				function () use ( $payment ) {
					return $payment->get_order_id();
				}
			)
		);

		$this->add_merge_tag(
			new MergeTag(
				'payment_lines_names',
				\__( 'Payment Lines Names', 'pronamic_ideal' ),
				function () use ( $payment ) {
					$names = [];

					$lines = $payment->get_lines();

					if ( null !== $lines ) {
						$names = array_map(
							function ( \Pronamic\WordPress\Pay\Payments\PaymentLine $line ) {
								return (string) $line->get_name();
							},
							$lines->get_array()
						);
					}

					return implode( ', ', $names );
				}
			)
		);
	}
}
