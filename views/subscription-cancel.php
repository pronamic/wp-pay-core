<?php
/**
 * Subscription cancel.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

use Pronamic\WordPress\Pay\Subscriptions\SubscriptionStatus;
use Pronamic\WordPress\Pay\Util;

$phase = $subscription->get_current_phase();

$expiry_date = $subscription->get_expiry_date();

?>
<!DOCTYPE html>

<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>" />

		<title><?php esc_html_e( 'Subscription Cancellation', 'pronamic_ideal' ); ?></title>

		<?php wp_print_styles( 'pronamic-pay-redirect' ); ?>
	</head>

	<body>
		<div class="pronamic-pay-redirect-page">
			<div class="pronamic-pay-redirect-container alignleft">
				<form id="pronamic_ideal_form" name="pronamic_ideal_form" method="post">
					<h1><?php esc_html_e( 'Subscription Cancellation', 'pronamic_ideal' ); ?></h1>

					<?php if ( null !== $expiry_date && SubscriptionStatus::CANCELLED !== $subscription->get_status() ) : ?>

						<p>
							<?php

							echo esc_html(
								sprintf(
									/* translators: %s: expiry date */
									__( 'The subscription expires at %s.', 'pronamic_ideal' ),
									$expiry_date->format_i18n()
								)
							);

							?>
						</p>

					<?php endif; ?>

					<hr />

					<?php if ( null !== $phase ) : ?>

						<dl>
							<dt>
								<?php esc_html_e( 'Subscription Length:', 'pronamic_ideal' ); ?>
							</dt>
							<dd>
								<?php echo esc_html( strval( Util::format_date_interval( $phase->get_interval() ) ) ); ?>
							</dd>

							<dt>
								<?php esc_html_e( 'Amount:', 'pronamic_ideal' ); ?>
							</dt>
							<dd>
								<?php echo esc_html( $phase->get_amount()->format_i18n() ); ?>
							</dd>
						</dl>

					<?php endif; ?>

					<?php if ( SubscriptionStatus::CANCELLED === $subscription->get_status() ) : ?>

						<p>
							<?php \esc_html_e( 'The subscription has already been canceled.', 'pronamic_ideal' ); ?>
						</p>

					<?php else : ?>

						<p>
							<?php \esc_html_e( 'Are you sure you want to cancel the subscription?', 'pronamic_ideal' ); ?>
						</p>

						<input type="submit" value="<?php esc_html_e( 'Cancel', 'pronamic_ideal' ); ?>"/>

					<?php endif; ?>
				</form>
			</div>
		</div>
	</body>
</html>
