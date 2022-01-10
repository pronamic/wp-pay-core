<?php
/**
 * Subscription renew.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

use Pronamic\WordPress\DateTime\DateTimeImmutable;
use Pronamic\WordPress\Pay\Subscriptions\SubscriptionPhase;
use Pronamic\WordPress\Pay\Subscriptions\SubscriptionStatus;

if ( ! isset( $subscription, $gateway ) ) {
	return;
}

$phase = $subscription->get_current_phase();

?>
<!DOCTYPE html>

<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>" />

		<title><?php esc_html_e( 'Subscription Renewal', 'pronamic_ideal' ); ?></title>

		<?php wp_print_styles( 'pronamic-pay-redirect' ); ?>
	</head>

	<body>
		<div class="pronamic-pay-redirect-page">
			<div class="pronamic-pay-redirect-container">
				<h1><?php esc_html_e( 'Subscription Renewal', 'pronamic_ideal' ); ?></h1>

				<div class="pp-page-section-container">
					<div class="pp-page-section-wrapper alignleft">
						<?php

						// Subscription details.
						require __DIR__ . '/subscription-details.php';

						// Determine next period.
						$phase = $subscription->get_current_phase();

						$now = new DateTimeImmutable();

						if (
								null !== $phase && $phase->get_next_date() < $now
									&&
								SubscriptionStatus::CANCELLED === $subscription->get_status() && 'gravityformsideal' === $subscription->get_source()
						) {
							$phase = new SubscriptionPhase( $subscription, $now, $phase->get_interval(), $phase->get_amount() );
						}

						$next_period = $phase->get_next_period();

						// Maybe use period from last failed payment.
						$renewal_period = $subscription->get_renewal_period();

						if ( null !== $renewal_period ) {
							$next_period = $renewal_period;
						}

						?>

						<?php if ( null === $next_period ) : ?>

							<p>
								<?php echo esc_html__( 'This subscription can not be renewed.', 'pronamic_ideal' ); ?>
							</p>

						<?php else : ?>

							<p>
								<?php

								printf(
									/* translators: %s: next period range */
									esc_html( __( 'Renew the subscription by paying for the period %s.', 'pronamic_ideal' ) ),
									esc_html( $next_period->human_readable_range( __( 'l j F Y', 'pronamic_ideal' ), _x( 'until', 'period seperator', 'pronamic_ideal' ) ) )
								);

								?>
							</p>

							<?php

							$html = $gateway->get_input_html();

							if ( ! empty( $html ) ) :

								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Complex input HTML.
								printf( '<p>%s</p>', $html );

							endif;

							?>

							<form id="pronamic_ideal_form" name="pronamic_ideal_form" method="post">
								<input type="submit" value="<?php esc_attr_e( 'Pay', 'pronamic_ideal' ); ?>"/>
							</form>

						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>
