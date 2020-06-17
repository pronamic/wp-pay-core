<?php
/**
 * Subscription mandate.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

global $wpdb;

$subscription_id = $subscription->get_id();

$mollie_customer_id = \get_post_meta( $subscription_id, '_pronamic_subscription_mollie_customer_id', true );

if ( empty( $mollie_customer_id ) ) {
	include \get_404_template();

	exit;
}

$api_key = \get_post_meta( $subscription->config_id, '_pronamic_gateway_mollie_api_key', true );

$client = new \Pronamic\WordPress\Pay\Gateways\Mollie\Client( $api_key );

/**
 * Customer.
 *
 * @link https://docs.mollie.com/reference/v2/customers-api/get-customer
 */
$mollie_customer = $client->get_customer( $mollie_customer_id );

/**
 * Mandates.
 *
 * @link https://docs.mollie.com/reference/v2/mandates-api/list-mandates
 */
$response = $client->get_mandates( $mollie_customer_id );

$mollie_customer_mandates = $response->_embedded->mandates;

$subscription_mandate_id = $subscription->get_meta( 'mollie_mandate_id' );

?>
<!DOCTYPE html>

<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>" />

		<title><?php esc_html_e( 'Subscription Mandate', 'pronamic_ideal' ); ?></title>

		<?php wp_print_styles( 'pronamic-pay-redirect' ); ?>

		<style type="text/css">
			.pronamic-pay-table {
				border-collapse: collapse;
			}

			.pronamic-pay-table th,
			.pronamic-pay-table td {
				border-bottom: 1px solid #c3c3c3;

				padding: 5px;
			}
		</style>
	</head>

	<body>
		<div class="pronamic-pay-redirect-page" style="max-width: 100%;">
			<div class="pronamic-pay-redirect-container alignleft">

				<h3><?php \esc_html_e( 'Mandates', 'pronamic_ideal' ); ?></h3>

				<form method="post">

					<table class="pronamic-pay-table">
						<thead>
							<tr>
								<th></th>
								<th><?php \esc_html_e( 'ID', 'pronamic_ideal' ); ?></th>
								<th><?php \esc_html_e( 'Mode', 'pronamic_ideal' ); ?></th>
								<th><?php \esc_html_e( 'Status', 'pronamic_ideal' ); ?></th>
								<th><?php \esc_html_e( 'Method', 'pronamic_ideal' ); ?></th>
								<th><?php \esc_html_e( 'Details', 'pronamic_ideal' ); ?></th>
								<th><?php \esc_html_e( 'Mandate Reference', 'pronamic_ideal' ); ?></th>
								<th><?php \esc_html_e( 'Signature Date', 'pronamic_ideal' ); ?></th>
								<th><?php \esc_html_e( 'Created On', 'pronamic_ideal' ); ?></th>
							</tr>
						</thead>

						<tbody>

							<?php if ( empty( $mollie_customer_mandates ) ) : ?>

								<tr>
									<td colspan="4"><?php esc_html_e( 'No mandates found.', 'pronamic_ideal' ); ?></td>
								</tr>

							<?php else : ?>

								<?php foreach ( $mollie_customer_mandates as $mandate ) : ?>

									<tr>
										<td>
											<input type="radio" id="mandate-<?php echo \esc_attr( $mandate->id ); ?>" name="pronamic_pay_subscription_mandate" value="<?php echo \esc_attr( $mandate->id ); ?>" <?php echo checked( $mandate->id, $subscription_mandate_id ); ?>>
										</td>
										<td>
											<label for="mandate-<?php echo \esc_attr( $mandate->id ); ?>">
												<code><?php echo \esc_html( $mandate->id ); ?></code>
											</label>
										</td>
										<td>
											<?php

											switch ( $mandate->mode ) {
												case 'test':
													\esc_html_e( 'Test', 'pronamic_ideal' );

													break;
												case 'live':
													\esc_html_e( 'Live', 'pronamic_ideal' );

													break;
												default:
													echo \esc_html( $mandate->mode );

													break;
											}

											?>
										</td>
										<td>
											<?php

											switch ( $mandate->status ) {
												case 'pending':
													\esc_html_e( 'Pending', 'pronamic_ideal' );

													break;
												case 'valid':
													\esc_html_e( 'Valid', 'pronamic_ideal' );

													break;
												default:
													echo \esc_html( $mandate->status );

													break;
											}

											?>
										</td>
										<td>
											<?php

											switch ( $mandate->method ) {
												case 'creditcard':
													\esc_html_e( 'Credit Card', 'pronamic_ideal' );

													break;
												case 'directdebit':
													\esc_html_e( 'Direct Debit', 'pronamic_ideal' );

													break;
												default:
													echo \esc_html( $mandate->method );

													break;
											}

											?>
										</td>
										<td>
											<?php

											switch ( $mandate->method ) {
												case 'creditcard':
													?>
													<dl style="margin: 0;">

														<?php if ( ! empty( $mandate->details->cardHolder ) ) : ?>

															<dt><?php \esc_html_e( 'Card Holder', 'pronamic_ideal' ); ?></dt>
															<dd>
																<?php echo \esc_html( $mandate->details->cardHolder ); ?>
															</dd>

														<?php endif; ?>

														<?php if ( ! empty( $mandate->details->cardNumber ) ) : ?>

															<dt><?php \esc_html_e( 'Card Number', 'pronamic_ideal' ); ?></dt>
															<dd>
																<?php echo \esc_html( $mandate->details->cardNumber ); ?>
															</dd>

														<?php endif; ?>

														<?php if ( ! empty( $mandate->details->cardLabel ) ) : ?>

															<dt><?php \esc_html_e( 'Card Label', 'pronamic_ideal' ); ?></dt>
															<dd>
																<?php echo \esc_html( $mandate->details->cardLabel ); ?>
															</dd>

														<?php endif; ?>

														<?php if ( ! empty( $mandate->details->cardFingerprint ) ) : ?>

															<dt><?php \esc_html_e( 'Card Fingerprint', 'pronamic_ideal' ); ?></dt>
															<dd>
																<?php echo \esc_html( $mandate->details->cardFingerprint ); ?>
															</dd>

														<?php endif; ?>

														<?php if ( ! empty( $mandate->details->cardExpiryDate ) ) : ?>

															<dt><?php \esc_html_e( 'Card Expiry Date', 'pronamic_ideal' ); ?></dt>
															<dd>
																<?php echo \esc_html( $mandate->details->cardExpiryDate ); ?>
															</dd>

														<?php endif; ?>
													</dl>
													<?php

													break;
												case 'directdebit':
													?>
													<dl style="margin: 0;">

														<?php if ( ! empty( $mandate->details->consumerName ) ) : ?>

															<dt><?php \esc_html_e( 'Consumer Name', 'pronamic_ideal' ); ?></dt>
															<dd>
																<?php echo \esc_html( $mandate->details->consumerName ); ?>
															</dd>

														<?php endif; ?>

														<?php if ( ! empty( $mandate->details->consumerAccount ) ) : ?>

															<dt><?php \esc_html_e( 'Consumer Account', 'pronamic_ideal' ); ?></dt>
															<dd>
																<?php echo \esc_html( $mandate->details->consumerAccount ); ?>
															</dd>

														<?php endif; ?>

														<?php if ( ! empty( $mandate->details->consumerBic ) ) : ?>

															<dt><?php \esc_html_e( 'Consumer BIC', 'pronamic_ideal' ); ?></dt>
															<dd>
																<?php echo \esc_html( $mandate->details->consumerBic ); ?>
															</dd>

														<?php endif; ?>
													</dl>
													<?php

													break;
												default:
													?>
													<pre><?php echo \esc_html( \wp_json_encode( $mandate->details, \JSON_PRETTY_PRINT ) ); ?></pre>
													<?php

													break;
											}

											?>
										</td>
										<td>
											<?php

											// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Mollie.
											echo \esc_html( $mandate->mandateReference );

											?>
										</td>
										<td>
											<?php

											// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Mollie.
											$signature_date = new \DateTime( $mandate->signatureDate, new \DateTimeZone( 'UTC' ) );

											$signature_date->setTimezone( \Pronamic\WordPress\DateTime\DateTimeZone::get_default() );

											echo \esc_html( $signature_date->format( 'd-m-Y' ) );

											?>
										</td>
										<td>
											<?php

											// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Mollie.
											$created_on = new \DateTime( $mandate->createdAt, new \DateTimeZone( 'UTC' ) );

											$created_on->setTimezone( \Pronamic\WordPress\DateTime\DateTimeZone::get_default() );

											echo \esc_html( $created_on->format( 'd-m-Y H:i:s' ) );

											?>
										</td>
									</tr>

								<?php endforeach; ?>

							<?php endif; ?>

							<tr>
								<td>
									<input type="radio" id="mandate-new" name="pronamic_pay_subscription_mandate" value="">
								</td>
								<td colspan="8">
									<label for="mandate-new">
										<?php esc_html_e( 'New mandate' ); ?>
									</label>
								</td>
							</tr>

						</tbody>
					</table>

					<?php wp_nonce_field( 'pronamic_pay_update_subscription_mandate', 'pronamic_pay_nonce' ); ?>

					<p>
						<input type="submit" value="<?php esc_attr_e( 'Submit', 'pronamic_ideal' ); ?>">
					</p>
				</form>

			</div>
		</div>
	</body>
</html>
