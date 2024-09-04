<?php
/**
 * Subscription mandate.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

use Pronamic\WordPress\Pay\Cards;
use Pronamic\WordPress\Pay\Core\PaymentMethods;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $subscription ) ) {
	return;
}

if ( ! isset( $gateway ) ) {
	return;
}

if ( ! class_exists( '\Pronamic\WordPress\Mollie\Client' ) ) {
	return;
}

$mollie_customer_id = $subscription->get_meta( 'mollie_customer_id' );

if ( empty( $mollie_customer_id ) ) {
	include \get_404_template();

	exit;
}

$api_key = \get_post_meta( $subscription->config_id, '_pronamic_gateway_mollie_api_key', true );

$client = new \Pronamic\WordPress\Mollie\Client( $api_key );

/**
 * Mandates.
 *
 * @link https://docs.mollie.com/reference/v2/mandates-api/list-mandates
 */
$mollie_customer_mandates = [];

// phpcs:disable Generic.CodeAnalysis.EmptyStatement.DetectedCatch

try {
	$response = $client->get_mandates( $mollie_customer_id );

	if (
		property_exists( $response, '_embedded' )
			&&
		property_exists( $response->_embedded, 'mandates' )
	) {
		$mollie_customer_mandates = $response->_embedded->mandates;
	}
} catch ( \Exception $exception ) {
	/**
	 * Nothing to do.
	 *
	 * Retrieval of customer mandates could fail for example when the configuration
	 * has changed and the customer is invalid now. We cannot retrieve mandates, but
	 * it should still be possible to add a new payment method to the subscription.
	 */
}

// phpcs:enable Generic.CodeAnalysis.EmptyStatement.DetectedCatch

$subscription_mandate_id = $subscription->get_meta( 'mollie_mandate_id' );

// Set current subscription mandate as first item.
$current_mandate = wp_list_filter( $mollie_customer_mandates, [ 'id' => $subscription_mandate_id ] );

if ( is_array( $current_mandate ) ) {
	unset( $mollie_customer_mandates[ key( $current_mandate ) ] );

	$mollie_customer_mandates = array_merge( $current_mandate, $mollie_customer_mandates );
}

?>
<!DOCTYPE html>

<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>" />

		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<title><?php esc_html_e( 'Change subscription payment method', 'pronamic_ideal' ); ?></title>

		<?php wp_print_styles( 'pronamic-pay-subscription-mandate' ); ?>
	</head>

	<body>
		<div class="pronamic-pay-redirect-page">
			<div class="pronamic-pay-redirect-container">

				<h1>
					<?php \esc_html_e( 'Change subscription payment method', 'pronamic_ideal' ); ?>
				</h1>

				<?php if ( count( $mollie_customer_mandates ) > 0 ) : ?>

					<p>
						<?php \esc_html_e( 'Select an existing payment method or add a new one.', 'pronamic_ideal' ); ?>
					</p>

					<div class="pp-card-slider-container">
						<div class="pp-card-slider-wrapper">
							<form method="post">
								<h2>
									<?php \esc_html_e( 'Select existing payment method', 'pronamic_ideal' ); ?>
								</h2>

								<div class="pp-card-slider alignleft">
									<?php

									$cards = new Cards();

									foreach ( $mollie_customer_mandates as $mandate ) :
										if ( 'valid' !== $mandate->status ) {
											continue;
										}

										$card_name      = '';
										$account_number = null;
										$account_label  = null;
										$bic_or_brand   = null;
										$logo_url       = null;

										switch ( $mandate->method ) {
											case 'creditcard':
												$card_name      = $mandate->details->cardHolder;
												$account_number = str_pad( $mandate->details->cardNumber, 16, '*', \STR_PAD_LEFT );
												$account_label  = _x( 'Card Number', 'Card selector', 'pronamic_ideal' );

												$bic_or_brand = $mandate->details->cardLabel;

												break;
											case 'directdebit':
												$card_name      = $mandate->details->consumerName;
												$account_number = $mandate->details->consumerAccount;
												$account_label  = _x( 'Account Number', 'Card selector', 'pronamic_ideal' );

												$bic_or_brand = substr( $mandate->details->consumerAccount, 4, 4 );

												break;
										}

										// Split account number in chunks.
										if ( null !== $account_number ) {
											$account_number = \chunk_split( $account_number, 4, ' ' );
										}

										$card_title = '';

										$classes = [ 'pp-card' ];

										$bg_color = 'purple';

										$card = $cards->get_card( $bic_or_brand );

										// Set card brand specific details.
										if ( null !== $card ) {
											$card_title = $card['title'];

											$classes[] = 'brand-' . $card['brand'];

											$logo_url = $cards->get_card_logo_url( $card['brand'] );

											$bg_color = 'transparent';
										}

										?>

										<div class="pp-card-container">
											<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" style="background: <?php echo \esc_attr( $bg_color ); ?>;">
												<div class="pp-card__background"></div>

												<div class="pp-card__content">
													<input class="pp-card__input" name="pronamic_pay_subscription_mandate" value="<?php echo esc_attr( $mandate->id ); ?>" type="radio" />

													<div class="pt-card__indicator"></div>

													<h3 class="pp-card__title"><?php echo esc_html( $card_title ); ?></h3>

													<figure class="pp-card__logo">
														<?php if ( null !== $logo_url ) : ?>

															<img class="pp-card__logo__img" src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $card_title ); ?>" />

														<?php endif; ?>
													</figure>

													<dl class="pp-card__name">
														<dt class="pp-card__label"><?php echo esc_html_x( 'Name', 'Card selector', 'pronamic_ideal' ); ?></dt>
														<dd class="pp-card__value"><?php echo esc_html( $card_name ); ?></dd>
													</dl>

													<dl class="pp-card__number">
														<dt class="pp-card__label"><?php echo esc_html( (string) $account_label ); ?></dt>
														<dd class="pp-card__value"><?php echo esc_html( (string) $account_number ); ?></dd>
													</dl>
												</div>
											</div>
										</div>

									<?php endforeach; ?>

								</div>

								<p>
									<?php wp_nonce_field( 'pronamic_pay_update_subscription_mandate', 'pronamic_pay_nonce' ); ?>

									<input type="submit" value="<?php esc_attr_e( 'Use selected payment method', 'pronamic_ideal' ); ?>" />
								</p>
							</form>
						</div>
					</div>

				<?php endif; ?>

				<div class="pp-new-payment-method-container">
					<div class="pp-new-payment-method-wrapper">
						<form method="post">
							<h2>
								<?php \esc_html_e( 'Add new payment method', 'pronamic_ideal' ); ?>
							</h2>

							<label>
								<p>
									<?php esc_html_e( 'Select payment method for verification payment.', 'pronamic_ideal' ); ?>
								</p>

								<select name="pronamic_pay_subscription_payment_method">
									<?php

									$payment_methods = $gateway->get_payment_methods(
										[
											'status'   => [ '', 'active' ],
											'supports' => 'recurring',
										]
									);

									/*
									 * Filter out payment methods with required fields,
									 * as these are not supported for now.
									 *
									 * @link https://github.com/pronamic/wp-pronamic-pay/issues/361
									 */
									$payment_methods = array_filter(
										$payment_methods->get_array(),
										function ( $payment_method ) {
											$required_fields = array_filter(
												$payment_method->get_fields(),
												function ( $field ) {
													return $field->is_required();
												}
											);

											return 0 === count( $required_fields );
										}
									);

									foreach ( $payment_methods as $payment_method ) {
										$payment_method_id = $payment_method->get_id();

										$name = $payment_method->get_name();
										$name = ( '' === $name ) ? $payment_method_id : $name;

										printf(
											'<option value="%s">%s</option>',
											esc_attr( $payment_method_id ),
											esc_html( $name )
										);
									}

									?>
								</select>
							</label>

							<p>
								<?php wp_nonce_field( 'pronamic_pay_update_subscription_mandate', 'pronamic_pay_nonce' ); ?>

								<input type="submit" value="<?php esc_attr_e( 'Pay', 'pronamic_ideal' ); ?>" />
							</p>
						</form>
					</div>
				</div>
			</div>
		</div>

		<?php wp_print_scripts( 'pronamic-pay-subscription-mandate' ); ?>
	</body>
</html>
