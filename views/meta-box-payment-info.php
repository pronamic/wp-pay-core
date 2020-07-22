<?php
/**
 * Meta Box Payment Info
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Gender;
use Pronamic\WordPress\Pay\Payments\PaymentStatus;
use Pronamic\WordPress\Pay\VatNumbers\VatNumberValidationService;

$payment_id = get_the_ID();

if ( empty( $payment_id ) ) {
	return;
}

$payments_post_type = \Pronamic\WordPress\Pay\Admin\AdminPaymentPostType::POST_TYPE;

$payment = get_pronamic_payment( $payment_id );

if ( null === $payment ) {
	return;
}

$purchase_id = get_post_meta( $payment_id, '_pronamic_payment_purchase_id', true );

?>
<table class="form-table">
	<tr>
		<th scope="row">
			<?php esc_html_e( 'Date', 'pronamic_ideal' ); ?>
		</th>
		<td>
			<?php echo esc_html( $payment->date->format_i18n() ); ?>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<?php esc_html_e( 'ID', 'pronamic_ideal' ); ?>
		</th>
		<td>
			<?php echo esc_html( $payment_id ); ?>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<?php esc_html_e( 'Order ID', 'pronamic_ideal' ); ?>
		</th>
		<td>
			<?php echo esc_html( get_post_meta( $payment_id, '_pronamic_payment_order_id', true ) ); ?>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<?php esc_html_e( 'Description', 'pronamic_ideal' ); ?>
		</th>
		<td>
			<?php echo esc_html( get_post_meta( $payment_id, '_pronamic_payment_description', true ) ); ?>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<?php esc_html_e( 'Amount', 'pronamic_ideal' ); ?>
		</th>
		<td>
			<?php

			echo esc_html( $payment->get_total_amount()->format_i18n() );

			?>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<?php esc_html_e( 'Transaction ID', 'pronamic_ideal' ); ?>
		</th>
		<td>
			<?php do_action( 'manage_' . $payments_post_type . '_posts_custom_column', 'pronamic_payment_transaction', $payment_id ); ?>
		</td>
	</tr>

	<?php if ( $purchase_id ) : ?>

		<tr>
			<th scope="row">
				<?php esc_html_e( 'Purchase ID', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php echo esc_html( $purchase_id ); ?>
			</td>
		</tr>

	<?php endif; ?>

	<tr>
		<th scope="row">
			<?php esc_html_e( 'Gateway', 'pronamic_ideal' ); ?>
		</th>
		<td>
			<?php edit_post_link( get_the_title( $payment->config_id ), '', '', $payment->config_id ); ?>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<?php esc_html_e( 'Payment Method', 'pronamic_ideal' ); ?>
		</th>
		<td>
			<?php

			$method = $payment->get_meta( 'method' );

			$name = PaymentMethods::get_name( $method );

			echo esc_html( $name );

			$issuer = $payment->get_meta( 'issuer' );

			if ( $issuer ) {
				echo esc_html( sprintf( ' (`%s`)', $issuer ) );
			}

			?>
		</td>
	</tr>

	<?php

	$bank_transfer_recipient = $payment->get_bank_transfer_recipient_details();

	?>

	<?php if ( null !== $bank_transfer_recipient ) : ?>

		<tr>
			<th scope="row">
				<?php esc_html_e( 'Bank Transfer Recipient', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php

				echo wp_kses(
					wpautop( $bank_transfer_recipient ),
					array(
						'p'  => array(),
						'br' => array(),
					)
				);

				?>
			</td>
		</tr>

	<?php endif; ?>

	<tr>
		<th scope="row">
			<?php esc_html_e( 'Action URL', 'pronamic_ideal' ); ?>
		</th>
		<td>
			<?php

			$url = get_post_meta( $payment_id, '_pronamic_payment_action_url', true );

			printf(
				'<a href="%s" target="_blank">%s</a>',
				esc_attr( $url ),
				esc_html( $url )
			);

			?>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<?php esc_html_e( 'Return URL', 'pronamic_ideal' ); ?>
		</th>
		<td>
			<?php

			$url = $payment->get_return_url();

			printf(
				'<a href="%s">%s</a>',
				esc_attr( $url ),
				esc_html( $url )
			);

			?>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<?php esc_html_e( 'Redirect URL', 'pronamic_ideal' ); ?>
		</th>
		<td>
			<?php

			$url = $payment->get_return_redirect_url();

			printf(
				'<a href="%s">%s</a>',
				esc_attr( $url ),
				esc_html( $url )
			);

			?>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<?php esc_html_e( 'Status', 'pronamic_ideal' ); ?>
		</th>
		<td>
			<?php

			$status_object = get_post_status_object( get_post_status( $payment_id ) );

			if ( isset( $status_object, $status_object->label ) ) {
				echo esc_html( $status_object->label );
			} else {
				echo '—';
			}

			// Failure reason.
			$failure_reason = $payment->get_failure_reason();

			if ( PaymentStatus::FAILURE === $payment->get_status() && null !== $failure_reason ) :

				printf(
					' — %s',
					esc_html( $failure_reason )
				);

			endif;

			?>
		</td>
	</tr>

	<?php

	$customer = $payment->get_customer();

	if ( null !== $customer ) :

		$text = \strval( $customer->get_name() );

		if ( empty( $text ) ) :

			$text = $customer->get_email();

		endif;

		if ( ! empty( $text ) ) :

			?>

			<tr>
				<th scope="row">
					<?php esc_html_e( 'Customer', 'pronamic_ideal' ); ?>
				</th>
				<td>
					<?php echo \esc_html( $text ); ?>
				</td>
			</tr>

			<?php
		endif;

		$company_name = $customer->get_company_name();

		if ( null !== $company_name ) :
			?>

			<tr>
				<th scope="row">
					<?php \esc_html_e( 'Company', 'pronamic_ideal' ); ?>
				</th>
				<td>
					<?php echo \esc_html( $company_name ); ?>
				</td>
			</tr>

			<?php
		endif;

		$vat_number = $customer->get_vat_number();

		if ( null !== $vat_number ) :
			$vat_number_validity = $vat_number->get_validity();

			?>

			<tr>
				<th scope="row">
					<?php esc_html_e( 'VAT Number', 'pronamic_ideal' ); ?>
				</th>
				<td>
					<?php echo \esc_html( $vat_number ); ?>
				</td>
			</tr>

			<?php if ( null !== $vat_number_validity ) : ?>

				<tr>
					<th scope="row">
						<?php esc_html_e( 'VAT Number Validity', 'pronamic_ideal' ); ?>
					</th>
					<td style="padding-top: 20px">
						<style type="text/css">
							.pronamic-pay-form-sub-table th,
							.pronamic-pay-form-sub-table td {
								padding: 0;
							}
						</style>

						<table class="pronamic-pay-form-sub-table">
							<tr>
								<th scope="row">
									<?php esc_html_e( 'VAT Number', 'pronamic_ideal' ); ?>
								</th>
								<td>
									<?php echo esc_html( $vat_number_validity->get_vat_number() ); ?>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<?php esc_html_e( 'Request Date', 'pronamic_ideal' ); ?>
								</th>
								<td>
									<?php echo esc_html( $vat_number_validity->get_request_date()->format( 'd-m-Y' ) ); ?>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<?php esc_html_e( 'Valid', 'pronamic_ideal' ); ?>
								</th>
								<td>
									<?php echo esc_html( $vat_number_validity->is_valid() ? __( 'Yes', 'pronamic_ideal' ) : __( 'No', 'pronamic_ideal' ) ); ?>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<?php esc_html_e( 'Name', 'pronamic_ideal' ); ?>
								</th>
								<td>
									<?php echo esc_html( $vat_number_validity->get_name() ); ?>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<?php esc_html_e( 'Address', 'pronamic_ideal' ); ?>
								</th>
								<td>
									<?php

									echo \wp_kses(
										\nl2br( $vat_number_validity->get_address() ),
										array(
											'br' => array(),
										)
									);

									?>
								</td>
							</tr>

							<?php

							$service = $vat_number_validity->get_service();

							if ( null !== $service ) :
								?>

								<tr>
									<th scope="row">
										<?php esc_html_e( 'Validation Service', 'pronamic_ideal' ); ?>
									</th>
									<td>
										<?php

										switch ( $service ) {
											case VatNumberValidationService::VIES:
												echo esc_html( __( 'VIES', 'pronamic_ideal' ) );

												break;
											default:
												echo \esc_html( $service );

												break;
										}

										?>
									</td>
								</tr>

							<?php endif; ?>

						</table>
					</td>
				</tr>

				<?php
			endif;

		endif;

		$birth_date = $customer->get_birth_date();

		if ( null !== $birth_date ) :
			?>

			<tr>
				<th scope="row">
					<?php esc_html_e( 'Date of birth', 'pronamic_ideal' ); ?>
				</th>
				<td>
					<?php

					echo esc_html( $birth_date->format_i18n( 'D j M Y' ) )

					?>
				</td>
			</tr>

		<?php endif; ?>

		<?php

		$gender = $customer->get_gender();

		if ( null !== $gender ) :
			?>

			<tr>
				<th scope="row">
					<?php esc_html_e( 'Gender', 'pronamic_ideal' ); ?>
				</th>
				<td>
					<?php

					switch ( $gender ) {
						case Gender::FEMALE:
							echo esc_html( __( 'Female', 'pronamic_ideal' ) );

							break;
						case Gender::MALE:
							echo esc_html( __( 'Male', 'pronamic_ideal' ) );

							break;
						case Gender::OTHER:
							echo esc_html( __( 'Other', 'pronamic_ideal' ) );

							break;
					}

					?>
				</td>
			</tr>

		<?php endif; ?>

		<?php

		$user_id = $customer->get_user_id();

		if ( null !== $user_id ) :
			?>

			<tr>
				<th scope="row">
					<?php esc_html_e( 'User', 'pronamic_ideal' ); ?>
				</th>
				<td>
					<?php

					$user_text = sprintf( '#%s', $user_id );

					// User display name.
					$user = new WP_User( $user_id );

					$display_name = $user->display_name;

					if ( ! empty( $display_name ) ) {
						$user_text .= sprintf( ' - %s', $display_name );
					}

					printf(
						'<a href="%s">%s</a>',
						esc_url( get_edit_user_link( $user_id ) ),
						esc_html( $user_text )
					);

					?>
				</td>
			</tr>

		<?php endif; ?>

	<?php endif; ?>

	<?php

	$consumer_bank_details = $payment->get_consumer_bank_details();

	if ( null !== $consumer_bank_details ) :

		$consumer_name = $consumer_bank_details->get_name();

		if ( null !== $consumer_name ) :

			?>

			<tr>
				<th scope="row">
					<?php esc_html_e( 'Account Holder', 'pronamic_ideal' ); ?>
				</th>
				<td>
					<?php echo esc_html( $consumer_name ); ?>
				</td>
			</tr>

			<?php

		endif;

		$account_holder_city = $consumer_bank_details->get_city();

		if ( null !== $account_holder_city ) :
			?>

			<tr>
				<th scope="row">
					<?php esc_html_e( 'Account Holder City', 'pronamic_ideal' ); ?>
				</th>
				<td>
					<?php echo esc_html( $account_holder_city ); ?>
				</td>
			</tr>

			<?php

		endif;

		$account_holder_country = $consumer_bank_details->get_country();

		if ( null !== $account_holder_country ) :
			?>

			<tr>
				<th scope="row">
					<?php esc_html_e( 'Account Holder Country', 'pronamic_ideal' ); ?>
				</th>
				<td>
					<?php echo esc_html( $account_holder_country ); ?>
				</td>
			</tr>

			<?php

		endif;

		$account_number = $consumer_bank_details->get_account_number();

		if ( null !== $account_number ) :
			?>

			<tr>
				<th scope="row">
					<?php esc_html_e( 'Account Number', 'pronamic_ideal' ); ?>
				</th>
				<td>
					<?php echo esc_html( $account_number ); ?>
				</td>
			</tr>

			<?php

		endif;

		$iban = $consumer_bank_details->get_iban();

		if ( null !== $iban ) :
			?>

			<tr>
				<th scope="row">
					<?php

					printf(
						'<abbr title="%s">%s</abbr>',
						esc_attr( _x( 'International Bank Account Number', 'IBAN abbreviation title', 'pronamic_ideal' ) ),
						esc_html__( 'IBAN', 'pronamic_ideal' )
					);

					?>
				</th>
				<td>
					<?php echo esc_html( $iban ); ?>
				</td>
			</tr>

			<?php

		endif;

		$bic = $consumer_bank_details->get_bic();

		if ( null !== $bic ) :
			?>

			<tr>
				<th scope="row">
					<?php

					printf(
						'<abbr title="%s">%s</abbr>',
						esc_attr( _x( 'Bank Identifier Code', 'BIC abbreviation title', 'pronamic_ideal' ) ),
						esc_html__( 'BIC', 'pronamic_ideal' )
					);

					?>
				</th>
				<td>
					<?php echo esc_html( $bic ); ?>
				</td>
			</tr>

			<?php

		endif;

	endif;

	?>

	<?php if ( null !== $payment->get_billing_address() ) : ?>

		<tr>
			<th scope="row">
				<?php esc_html_e( 'Billing Address', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php

				$address = $payment->get_billing_address();

				echo nl2br( esc_html( (string) $address ) );

				?>
			</td>
		</tr>

	<?php endif; ?>

	<?php if ( null !== $payment->get_shipping_address() ) : ?>

		<tr>
			<th scope="row">
				<?php esc_html_e( 'Shipping Address', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php

				$address = $payment->get_shipping_address();

				echo nl2br( esc_html( (string) $address ) );

				?>
			</td>
		</tr>

	<?php endif; ?>

	<tr>
		<th scope="row">
			<?php esc_html_e( 'Source', 'pronamic_ideal' ); ?>
		</th>
		<td>
			<?php

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $payment->get_source_text();

			?>
		</td>
	</tr>

	<?php

	$ga_tracked = $payment->get_ga_tracked();

	$ga_property_id = get_option( 'pronamic_pay_google_analytics_property' );

	?>

	<?php if ( $ga_tracked || ! empty( $ga_property_id ) ) : ?>

		<tr>
			<th scope="row">
				<?php esc_html_e( 'Google Analytics', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php

				if ( $ga_tracked ) :

					esc_html_e( 'Ecommerce conversion tracked', 'pronamic_ideal' );

				else :

					esc_html_e( 'Ecommerce conversion not tracked', 'pronamic_ideal' );

				endif;

				?>
			</td>
		</tr>

	<?php endif; ?>

	<?php if ( 's2member' === $payment->get_source() ) : ?>

		<tr>
			<th scope="row">
				<?php esc_html_e( 'Period', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php echo esc_html( get_post_meta( $payment->get_id(), '_pronamic_payment_s2member_period', true ) ); ?>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<?php esc_html_e( 'Level', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php echo esc_html( get_post_meta( $payment->get_id(), '_pronamic_payment_s2member_level', true ) ); ?>
			</td>
		</tr>

	<?php endif; ?>

	<?php if ( 'wp-e-commerce' === $payment->get_source() ) : ?>

		<tr>
			<th scope="row">
				<?php esc_html_e( 'Purchase ID', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php echo esc_html( get_post_meta( $payment->get_id(), '_pronamic_payment_wpsc_purchase_id', true ) ); ?>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<?php esc_html_e( 'Session ID', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php echo esc_html( get_post_meta( $payment->get_id(), '_pronamic_payment_wpsc_session_id', true ) ); ?>
			</td>
		</tr>

	<?php endif; ?>

	<?php if ( 'membership' === $payment->get_source() ) : ?>

		<tr>
			<th scope="row">
				<?php esc_html_e( 'User ID', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php echo esc_html( get_post_meta( $payment->get_id(), '_pronamic_payment_membership_user_id', true ) ); ?>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<?php esc_html_e( 'Subscription ID', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php echo esc_html( get_post_meta( $payment->get_id(), '_pronamic_payment_membership_subscription_id', true ) ); ?>
			</td>
		</tr>

	<?php endif; ?>

	<?php

	$ogone_alias = $payment->get_meta( 'ogone_alias' );

	?>

	<?php if ( ! empty( $ogone_alias ) ) : ?>

		<tr>
			<th scope="row">
				<?php esc_html_e( 'Ingenico Alias', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php echo esc_html( $ogone_alias ); ?>
			</td>
		</tr>

	<?php endif; ?>

	<?php if ( PRONAMIC_PAY_DEBUG ) : ?>

		<tr>
			<th scope="row">
				<?php esc_html_e( 'User Agent', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php echo esc_html( $payment->user_agent ); ?>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<?php esc_html_e( 'IP Address', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php echo esc_html( $payment->user_ip ); ?>
			</td>
		</tr>

		<?php if ( null !== $payment->get_version() ) : ?>

			<tr>
				<th scope="row">
					<?php esc_html_e( 'Version', 'pronamic_ideal' ); ?>
				</th>
				<td>
					<?php echo esc_html( $payment->get_version() ); ?>
				</td>
			</tr>

		<?php endif ?>

		<?php if ( null !== $payment->get_mode() ) : ?>

			<tr>
				<th scope="row">
					<?php esc_html_e( 'Mode', 'pronamic_ideal' ); ?>
				</th>
				<td>
					<?php

					switch ( $payment->get_mode() ) {
						case 'live':
							esc_html_e( 'Live', 'pronamic_ideal' );

							break;
						case 'test':
							esc_html_e( 'Test', 'pronamic_ideal' );

							break;
						default:
							echo esc_html( $payment->get_mode() );

							break;
					}

					?>
				</td>
			</tr>

		<?php endif ?>

		<tr>
			<th scope="row">
				<?php esc_html_e( 'REST API URL', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php

				/**
				 * REST API URL.
				 *
				 * @link https://developer.wordpress.org/rest-api/using-the-rest-api/authentication/#cookie-authentication
				 */
				$rest_api_url = rest_url( 'pronamic-pay/v1/payments/' . $payment_id );

				$rest_api_nonce_url = wp_nonce_url( $rest_api_url, 'wp_rest' );

				printf(
					'<a href="%s">%s</a>',
					esc_url( $rest_api_nonce_url ),
					esc_html( $rest_api_url )
				);

				?>
			</td>
		</tr>

	<?php endif; ?>
</table>
