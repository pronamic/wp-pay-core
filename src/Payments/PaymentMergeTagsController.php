<?php
/**
 * Payment Merge Tags Controller
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
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
	 * Construct payment merge tags controllers.
	 *
	 * @param Payment $payment Payment.
	 */
	public function __construct( Payment $payment ) {
		$this->add_merge_tag(
			new MergeTag(
				'payment_id',
				function () use ( $payment ) {
					return $payment->get_id();
				}
			)
		);

		$this->add_merge_tag(
			new MergeTag(
				'order_id',
				function () use ( $payment ) {
					return $payment->get_order_id();
				}
			)
		);

		$this->add_merge_tag(
			new MergeTag(
				'payment_lines_name',
				function () use ( $payment ) {
					$lines = $payment->get_lines();

					if ( null === $lines ) {
						return '';
					}

					return $lines->get_name();
				}
			)
		);
	}
}
