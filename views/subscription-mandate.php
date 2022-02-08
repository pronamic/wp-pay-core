<?php
/**
 * Subscription mandate.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

use Pronamic\WordPress\Pay\Cards;
use Pronamic\WordPress\Pay\Core\PaymentMethods;

if ( ! isset( $subscription ) ) {
	return;
}

if ( ! isset( $gateway ) ) {
	return;
}

if ( ! class_exists( '\Pronamic\WordPress\Pay\Gateways\Mollie\Client' ) ) {
	return;
}

$mollie_customer_id = $subscription->get_meta( 'mollie_customer_id' );

if ( empty( $mollie_customer_id ) ) {
	include \get_404_template();

	exit;
}

$api_key = \get_post_meta( $subscription->config_id, '_pronamic_gateway_mollie_api_key', true );

$client = new \Pronamic\WordPress\Pay\Gateways\Mollie\Client( $api_key );

/**
 * Mandates.
 *
 * @link https://docs.mollie.com/reference/v2/mandates-api/list-mandates
 */
$mollie_customer_mandates = array();

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

$subscription_mandate_id = $subscription->get_meta( 'mollie_mandate_id' );

// Set current subscription mandate as first item.
$current_mandate = wp_list_filter( $mollie_customer_mandates, array( 'id' => $subscription_mandate_id ) );

if ( is_array( $current_mandate ) ) {
	unset( $mollie_customer_mandates[ key( $current_mandate ) ] );

	$mollie_customer_mandates = array_merge( $current_mandate, $mollie_customer_mandates );
}

?>
<!DOCTYPE html>

<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>" />

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

										$card_name      = null;
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

										$classes = array( 'pp-card' );

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

									$recurring_methods = array_keys( PaymentMethods::get_recurring_methods() );

									$active_methods = $gateway->get_transient_available_payment_methods();

									if ( null !== $active_methods ) {
										foreach ( $active_methods as $method ) {
											if ( ! in_array( $method, $recurring_methods, true ) ) :
												continue;
											endif;

											$name = PaymentMethods::get_name( $method );
											$name = ( null === $name ) ? $method : $name;

											printf(
												'<option value="%s">%s</option>',
												esc_attr( $method ),
												esc_html( $name )
											);
										}
									}

									?>
								</select>
							</label>

							<?php

							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Complex input HTML.
							echo $gateway->get_input_html();

							?>

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

		<script type="text/javascript">
			jQuery( document ).ready( function () {
				var $slider = jQuery( '.pp-card-slider' ).slick( {
					dots: true,
					arrows: false,
					infinite: false,
					slidesToShow: 1,
					centerMode: true,
				} );

				$slider.find( '.slick-current input[type="radio"]' ).attr( 'checked', 'checked' );

				$slider.find( '.slick-slide' ).on( 'click', function () {
					var index = jQuery( this ).data( 'slick-index' );

					$slider.slick( 'slickGoTo', index );
				} );

				$slider.on( 'afterChange', function ( event, slick, currentSlide, nextSlide ) {
					$slider.find( 'input[type="radio"]' ).removeAttr( 'checked' );

					$slider.find( '.slick-slide' ).eq( currentSlide ).find( 'input[type="radio"]' ).attr( 'checked', 'checked' );
				} );
			} );
		</script>
	</body>
</html>
