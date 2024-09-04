<?php
/**
 * Meta Box Subscription Update
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

use Pronamic\WordPress\Html\Element;
use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Pay\Payments\PaymentStatus;
use Pronamic\WordPress\Pay\Subscriptions\SubscriptionPostType;
use Pronamic\WordPress\Pay\Subscriptions\SubscriptionStatus;
use Pronamic\WordPress\Pay\Util;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $post ) ) {
	return;
}

$subscription = \get_pronamic_subscription( (int) get_the_ID() );

if ( null === $subscription ) {
	return;
}

$states = [
	SubscriptionStatus::OPEN      => _x( 'Pending', 'Subscription status', 'pronamic_ideal' ),
	SubscriptionStatus::CANCELLED => _x( 'Cancelled', 'Subscription status', 'pronamic_ideal' ),
	SubscriptionStatus::EXPIRED   => _x( 'Expired', 'Subscription status', 'pronamic_ideal' ),
	SubscriptionStatus::FAILURE   => _x( 'Failed', 'Subscription status', 'pronamic_ideal' ),
	SubscriptionStatus::ON_HOLD   => _x( 'On Hold', 'Subscription status', 'pronamic_ideal' ),
	SubscriptionStatus::ACTIVE    => _x( 'Active', 'Subscription status', 'pronamic_ideal' ),
	SubscriptionStatus::COMPLETED => _x( 'Completed', 'Subscription status', 'pronamic_ideal' ),
	// Map payment status `Success` for backwards compatibility.
	PaymentStatus::SUCCESS        => _x( 'Active', 'Subscription status', 'pronamic_ideal' ),
];

ksort( $states );

$states_options = [
	SubscriptionStatus::ACTIVE,
	SubscriptionStatus::CANCELLED,
	SubscriptionStatus::ON_HOLD,
];

// WordPress by default doesn't allow `post_author` values of `0`, that's why we use a dash (`-`).
// @link https://github.com/WordPress/WordPress/blob/4.9.5/wp-admin/includes/post.php#L56-L64.
$post_author = get_post_field( 'post_author' );
$post_author = empty( $post_author ) ? '-' : $post_author;


?>
<input type="hidden" name="post_author_override" value="<?php echo esc_attr( $post_author ); ?>" />

<div class="pronamic-pay-inner">
	<div class="pronamic-pay-minor-actions">
		<div class="misc-pub-section misc-pub-post-status">
			<?php echo esc_html( __( 'Status:', 'pronamic_ideal' ) ); ?>

			<?php

			$status_object = get_post_status_object( $post->post_status );

			$status_label = isset( $status_object, $status_object->label ) ? $status_object->label : '—';

			?>

			<span id="pronamic-pay-post-status-display"><?php echo esc_html( $status_label ); ?></span>

			<?php if ( 'subscr_completed' !== $post->post_status ) : ?>

				<a href="#pronamic-pay-post-status" class="edit-pronamic-pay-post-status hide-if-no-js" role="button">
					<span aria-hidden="true"><?php esc_html_e( 'Edit', 'pronamic_ideal' ); ?></span>
					<span class="screen-reader-text"><?php esc_html_e( 'Edit status', 'pronamic_ideal' ); ?></span>
				</a>

				<div id="pronamic-pay-post-status-input" class="hide-if-js">
					<label for="pronamic-pay-post-status" class="screen-reader-text"><?php esc_html_e( 'Set status', 'pronamic_ideal' ); ?></label>
					<select id="pronamic-pay-post-status" name="pronamic_subscription_status">
						<?php

						foreach ( $states as $subscription_status => $label ) {
							if ( ! in_array( $subscription_status, $states_options, true ) && $subscription_status !== $subscription->get_status() ) {
								continue;
							}

							printf(
								'<option value="%s" %s>%s</option>',
								esc_attr( $subscription_status ),
								selected( $subscription_status, $subscription->get_status(), false ),
								esc_html( $label )
							);
						}

						?>
					</select>

					<a href="#pronamic-pay-post-status" class="save-pronamic-pay-post-status hide-if-no-js button"><?php esc_html_e( 'OK', 'pronamic_ideal' ); ?></a>
					<a href="#pronamic-pay-post-status" class="cancel-pronamic-pay-post-status hide-if-no-js button-cancel"><?php esc_html_e( 'Cancel', 'pronamic_ideal' ); ?></a>
				</div>

				<?php if ( null !== $subscription && in_array( $subscription->get_status(), [ SubscriptionStatus::FAILURE, SubscriptionStatus::ON_HOLD ], true ) ) : ?>

					<div id="pronamic-pay-post-status-notice" class="notice inline">
						<p>
							<?php

							echo \wp_kses_post(
								\sprintf(
									'%s <a href="#pronamic_subscription_notes">%s</a>',
									\__( 'Recurring payments will not be created until manual reactivation of this subscription.', 'pronamic_ideal' ),
									\__( 'See subscription and payment notes for details about status changes.', 'pronamic_ideal' )
								)
							);

							?>
						</p>
					</div>

				<?php endif; ?>

			<?php endif; ?>
		</div>

		<div class="misc-pub-section curtime">
			<?php

			$next_payment_date = $subscription->get_next_payment_date();

			?>

			<span id="timestamp">
				<?php echo esc_html( __( 'Next payment:', 'pronamic_ideal' ) ); ?>
			</span>

			<span id="pronamic-pay-next-payment-date-display"><?php echo esc_html( null === $next_payment_date ? '—' : $next_payment_date->format_i18n( \__( 'D j M Y', 'pronamic_ideal' ) ) ); ?></span>

			<?php if ( 'woocommerce' !== $subscription->get_source() ) : ?>

				<a href="#pronamic-pay-next-payment-date" class="edit-pronamic-pay-next-payment-date hide-if-no-js" role="button">
					<span aria-hidden="true"><?php esc_html_e( 'Edit', 'pronamic_ideal' ); ?></span>
					<span class="screen-reader-text"><?php esc_html_e( 'Edit next payment date', 'pronamic_ideal' ); ?></span>
				</a>

			<?php endif; ?>

			<div id="pronamic-pay-next-payment-date-input" class="hide-if-js">
				<input type="hidden" name="hidden_pronamic_pay_next_payment_date" id="hidden_pronamic_pay_next_payment_date" value="<?php echo \esc_attr( null === $next_payment_date ? '' : $next_payment_date->format( 'Y-m-d' ) ); ?>" />
				<label for="pronamic-pay-next-payment-date" class="screen-reader-text"><?php esc_html_e( 'Set date', 'pronamic_ideal' ); ?></label>

				<?php

				$element = new Element(
					'input',
					[
						'id'       => 'pronamic-pay-next-payment-date',
						'name'     => 'pronamic_subscription_next_payment_date',
						'type'     => 'date',
						'value'    => null === $next_payment_date ? '' : $next_payment_date->format( 'Y-m-d' ),
						'data-min' => ( new DateTimeImmutable( 'tomorrow' ) )->format( 'Y-m-d' ),
					]
				);

				$element->output();

				?>

				<a href="#pronamic-pay-next-payment-date" class="save-pronamic-pay-next-payment-date hide-if-no-js button"><?php esc_html_e( 'OK', 'pronamic_ideal' ); ?></a>
				<a href="#pronamic-pay-next-payment-date" class="cancel-pronamic-pay-next-payment-date hide-if-no-js button-cancel"><?php esc_html_e( 'Cancel', 'pronamic_ideal' ); ?></a>
			</div>

			<?php

			$today = new DateTimeImmutable( 'today midnight', new DateTimeZone( Plugin::TIMEZONE ) );

			if ( SubscriptionStatus::ACTIVE === $subscription->get_status() && null !== $next_payment_date && $next_payment_date < $today ) :
				?>

				<div id="pronamic-pay-next-payment-date-error" class="error inline">
					<p><?php echo esc_html( __( 'Set the next payment date to a future date to continue payments for this subscription.', 'pronamic_ideal' ) ); ?></p>
				</div>

			<?php endif; ?>

			<div id="pronamic-pay-next-payment-date-min-error" class="hidden error inline">
				<p><?php echo esc_html( __( 'Please select a future date.', 'pronamic_ideal' ) ); ?></p>
			</div>

			<div id="pronamic-pay-next-payment-date-notice" class="hidden notice inline">
				<p>
					<?php

					\printf(
						/* translators: %s subscription source description */
						\esc_html( \__( 'Editing the next payment date does not affect the current status or validity of %s.', 'pronamic_ideal' ) ),
						\wp_kses(
							$subscription->get_source_text(),
							[
								'a' => [
									'href' => true,
								],
							]
						)
					);

					?>
				</p>
			</div>
		</div>
	</div>
</div>

<div class="pronamic-pay-major-actions">
	<div class="pronamic-pay-action">
		<?php

		wp_nonce_field( 'pronamic_subscription_update', 'pronamic_subscription_nonce' );

		printf(
			'<input type="hidden" name="pronamic_subscription_id" value="%s" />',
			esc_attr( (string) $subscription->get_id() )
		);

		submit_button(
			__( 'Update', 'pronamic_ideal' ),
			'primary',
			'pronamic_subscription_update',
			false
		);

		?>
	</div>

	<div class="clear"></div>
</div>
