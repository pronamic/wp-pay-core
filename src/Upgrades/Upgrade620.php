<?php
/**
 * Upgrade 6.2.0.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Upgrades;

/**
 * Upgrade 6.2.0.
 *
 * @author  Reüel van der Steege
 * @since   2.3.2
 * @version 2.3.2
 */
class Upgrade620 extends Upgrade {
	/**
	 * Construct 6.2.0 upgrade.
	 */
	public function __construct() {
		parent::__construct( '6.2.0' );
	}

	/**
	 * Execute.
	 *
	 * @return void
	 */
	public function execute() {
		$query = new \WP_Query(
			array(
				'post_type'      => 'pronamic_pay_subscr',
				'post_status'    => array(
					'subscr_active',
					'subscr_expired',
				),
				'posts_per_page' => -1,
				'no_found_rows'  => true,
				'meta_query'     => array(
					array(
						'key'     => '_pronamic_subscription_next_payment',
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => '_pronamic_subscription_end_date',
						'compare' => 'NOT EXISTS',
					),
				),
			)
		);

		if ( ! $query->have_posts() ) {
			return;
		}

		// Loop subscriptions.
		while ( $query->have_posts() ) {
			$query->the_post();

			// Update subscription.
			$subscription = get_pronamic_subscription( (int) get_the_ID() );

			if ( null === $subscription ) {
				continue;
			}

			$expiry_date = $subscription->get_expiry_date();

			if ( null === $expiry_date ) {
				continue;
			}

			$subscription->next_payment_date = $expiry_date;

			$subscription->save();

			if ( null === $subscription->next_payment_date ) {
				continue;
			}

			// Add note.
			$subscription->add_note(
				\sprintf(
					/* translators: %s: formatted next payment date */
					__( 'Missing subscription next payment date restored to %s.', 'pronamic_ideal' ),
					$subscription->next_payment_date->format_i18n()
				)
			);
		}

		\wp_reset_postdata();
	}
}
