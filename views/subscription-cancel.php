<?php
/**
 * Subscription cancel.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

use Pronamic\WordPress\Pay\Subscriptions\SubscriptionStatus;

if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! isset( $subscription ) ) {
	return;
}

$phase = $subscription->get_current_phase();

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
			<div class="pronamic-pay-redirect-container">
				<h1><?php esc_html_e( 'Subscription Cancellation', 'pronamic_ideal' ); ?></h1>

				<div class="pp-page-section-container">
					<div class="pp-page-section-wrapper alignleft">

						<?php

						// Subscription details.
						require __DIR__ . '/subscription-details.php';

						?>

						<?php if ( SubscriptionStatus::ACTIVE !== $subscription->get_status() ) : ?>

							<p>
								<?php esc_html_e( 'The subscription can not be canceled as it is not active anymore.', 'pronamic_ideal' ); ?>
							</p>

						<?php endif; ?>
					</div>

					<?php if ( SubscriptionStatus::ACTIVE === $subscription->get_status() ) : ?>

						<div class="pp-page-section-wrapper">

							<p>
								<?php esc_html_e( 'Are you sure you want to cancel the subscription?', 'pronamic_ideal' ); ?>
							</p>

							<form id="pronamic_ideal_form" name="pronamic_ideal_form" method="post">
								<input type="submit" value="<?php esc_attr_e( 'Cancel subscription', 'pronamic_ideal' ); ?>"/>
							</form>

						</div>

					<?php endif; ?>
				</div>
			</div>
		</div>
	</body>
</html>
