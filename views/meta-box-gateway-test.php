<?php
/**
 * Meta Box Gateway Test
 *
 * @author Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license GPL-3.0-or-later
 * @package Pronamic\WordPress\Pay
 * @var \WP_Post $post WordPress post.
 */

use Pronamic\WordPress\Money\Currencies;
use Pronamic\WordPress\Money\Currency;
use Pronamic\WordPress\Pay\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gateway = Plugin::get_gateway( $post->ID );

if ( null === $gateway ) {
	printf(
		'<em>%s</em>',
		esc_html( __( 'Please save the entered account details of your payment provider, to make a test payment.', 'pronamic_ideal' ) )
	);

	return;
}

wp_nonce_field( 'test_pay_gateway', 'pronamic_pay_test_nonce' );

$currency_default = Currency::get_instance( 'EUR' );

$payment_methods = $gateway->get_payment_methods(
	[
		'status' => [
			'',
			'active',
		],
	]
);

?>
<style>
	.pronamic-pay-lines {

	}

	.pronamic-pay-lines th {
		padding: 15px 10px;
	}
</style>

<table class="form-table">
	<tr>
		<th scope="row">
			<label for="pronamic-pay-test-payment-methods">
				<?php esc_html_e( 'Payment Method', 'pronamic_ideal' ); ?>
			</label>
		</th>
		<td>
			<select id="pronamic-pay-test-payment-methods" name="pronamic_pay_test_payment_method">
				<?php if ( count( $payment_methods ) > 1 ) : ?>

					<option value=""><?php esc_html_e( '— Choose payment method —', 'pronamic_ideal' ); ?></option>

				<?php endif; ?>

				<?php

				foreach ( $payment_methods as $payment_method ) {
					printf(
						'<option value="%s" data-is-recurring="%d">%s</option>',
						esc_attr( $payment_method->get_id() ),
						esc_attr( $payment_method->supports( 'recurring' ) ? '1' : ' 0' ),
						esc_html( $payment_method->get_name() )
					);
				}

				?>
			</select>
		</td>
	</tr>

	<?php foreach ( $payment_methods as $payment_method ) : ?>

		<?php foreach ( $payment_method->get_fields() as $field ) : ?>

			<tr class="pronamic-pay-cloak pronamic-pay-test-payment-method <?php echo esc_attr( $payment_method->get_id() ); ?>">
				<th scope="row">
					<?php echo esc_html( $field->get_label() ); ?>
				</th>
				<td>
					<?php

					try {
						$field->output();
					} catch ( \Exception $exception ) {
						echo '<em>';

						printf(
							/* translators: %s: Exception message. */
							esc_html__( 'This field could not be displayed due to the following error message: "%s".', 'pronamic_ideal' ),
							esc_html( $exception->getMessage() )
						);

						echo '</em>';

						?>
						<div class="error">
							<p>
								<?php

								echo wp_kses(
									sprintf(
										/* translators: 1: Field label, 2: Payment method name */
										__( '<strong>Pronamic Pay</strong> — An error occurred within the "%1$s" field of the "%2$s" payment method.', 'pronamic_ideal' ),
										esc_html( $field->get_label() ),
										esc_html( $payment_method->get_name() )
									),
									[
										'strong' => [],
									]
								);

								?>
							</p>

							<dl>
								<dt><?php esc_html_e( 'Message', 'pronamic_ideal' ); ?></dt>
								<dd><?php echo esc_html( $exception->getMessage() ); ?></dd>

								<?php if ( 0 !== $exception->getCode() ) : ?>

									<dt><?php esc_html_e( 'Code', 'pronamic_ideal' ); ?></dt>
									<dd><?php echo esc_html( $exception->getCode() ); ?></dd>

								<?php endif; ?>
							</dl>
						</div>
						<?php
					}

					?>
				</td>
			</tr>

		<?php endforeach; ?>

	<?php endforeach; ?>

	<tr>
		<th scope="row">
			<?php esc_html_e( 'Currency', 'pronamic_ideal' ); ?>
		</th>
		<td>
			<select name="test_currency_code">
				<?php

				foreach ( Currencies::get_currencies() as $currency ) {
					$label = $currency->get_alphabetic_code();

					$symbol = $currency->get_symbol();

					if ( null !== $symbol ) {
						$label = sprintf( '%s (%s)', $label, $symbol );
					}

					printf(
						'<option value="%s" %s>%s</option>',
						esc_attr( $currency->get_alphabetic_code() ),
						selected( $currency->get_alphabetic_code(), $currency_default->get_alphabetic_code(), false ),
						esc_html( $label )
					);
				}

				?>
			</select>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<?php esc_html_e( 'Lines', 'pronamic_ideal' ); ?>
		</th>
		<td style="padding: 0;">
			<table class="pronamic-pay-lines widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Name', 'pronamic_ideal' ); ?></th>
						<th><?php esc_html_e( 'Quantity', 'pronamic_ideal' ); ?></th>
						<th><?php esc_html_e( 'Unit price', 'pronamic_ideal' ); ?></th>
					</tr>
				</thead>

				<tbody>

					<?php

					$lines = [
						[
							'name'     => __( 'Test Product 1', 'pronamic_ideal' ),
							'quantity' => 1,
							'price'    => '60.00',
						],
						[
							'name'     => __( 'Test Product 2', 'pronamic_ideal' ),
							'quantity' => 2,
							'price'    => '30.00',
						],
						[
							'name'     => __( 'Test Product 3', 'pronamic_ideal' ),
							'quantity' => 3,
							'price'    => '20.00',
						],
					];

					foreach ( $lines as $key => $line ) :
						?>

						<tr>
							<?php

							$name = sprintf( 'lines[%s][%s]', $key, '%s' );

							?>
							<td>
								<input type="text" name="<?php echo \esc_attr( \sprintf( $name, 'name' ) ); ?>" value="<?php echo \esc_attr( $line['name'] ); ?>" class="pronamic-pay-form-control" />
							</td>
							<td>
								<input type="number" step="any" name="<?php echo \esc_attr( \sprintf( $name, 'quantity' ) ); ?>" value="<?php echo \esc_attr( (string) $line['quantity'] ); ?>" min="0" class="pronamic-pay-form-control" />
							</td>
							<td>
								<input type="number" name="<?php echo \esc_attr( \sprintf( $name, 'price' ) ); ?>" value="<?php echo \esc_attr( $line['price'] ); ?>" step="any" class="pronamic-pay-form-control" />
							</td>
						</tr>

					<?php endforeach; ?>

				</tbody>
			</table>
		</td>
	</tr>

	<tr>
		<td>

		</td>
		<td>
			<?php submit_button( __( 'Test', 'pronamic_ideal' ), 'secondary', 'test_pay_gateway', false ); ?>
		</td>
	</tr>

	<?php

	/**
	 * Print address fields.
	 *
	 * @param string $name_format Input name format.
	 * @param array  $values      Values to prefill the fields.
	 * @return void
	 */
	function print_adress_fields( $name_format, $values = [] ) {
		$fields = [
			'first_name'   => __( 'First name', 'pronamic_ideal' ),
			'last_name'    => __( 'Last name', 'pronamic_ideal' ),
			'company'      => __( 'Company', 'pronamic_ideal' ),
			'line_1'       => __( 'Address line 1', 'pronamic_ideal' ),
			'line_2'       => __( 'Address line 2', 'pronamic_ideal' ),
			'city'         => __( 'City', 'pronamic_ideal' ),
			'postal_code'  => __( 'Postcode / ZIP', 'pronamic_ideal' ),
			'country_code' => __( 'Country / Region', 'pronamic_ideal' ),
			'state'        => __( 'State / County', 'pronamic_ideal' ),
			'email'        => __( 'Email address', 'pronamic_ideal' ),
			'phone'        => __( 'Phone', 'pronamic_ideal' ),
		];

		?>
		<table class="form-table" style="margin-top: 0;">
			<?php

			foreach ( $fields as $key => $label ) {
				$name  = \sprintf( $name_format, $key );
				$id    = \sanitize_key( $name );
				$value = \array_key_exists( $key, $values ) ? $values[ $key ] : '';

				?>
				<tr>
					<th scope="row">
						<label for="<?php echo \esc_attr( $id ); ?>"><?php echo \esc_html( $label ); ?></label>
					</th>
					<td>
						<input id="<?php echo \esc_attr( $id ); ?>" name="<?php echo \esc_attr( $name ); ?>" value="<?php echo \esc_attr( $value ); ?>"  type="text" class="regular-text code pronamic-pay-form-control">
					</td>
				</tr>
				<?php
			}

			?>
		</table>

		<?php
	}

	$user = \wp_get_current_user();

	?>

	<tr>
		<th scope="row">
			<?php esc_html_e( 'Customer', 'pronamic_ideal' ); ?>
		</th>
		<td style="padding: 0;">
			<table class="form-table" style="margin-top: 0;">
				<?php

				$customer_fields = [
					'first_name' => [
						'label' => __( 'First name', 'pronamic_ideal' ),
						'value' => ( '' === $user->first_name ) ? 'John' : $user->first_name,
					],
					'last_name'  => [
						'label' => __( 'Last name', 'pronamic_ideal' ),
						'value' => ( '' === $user->last_name ) ? 'Doe' : $user->last_name,
					],
					'email'      => [
						'label' => __( 'Email address', 'pronamic_ideal' ),
						'value' => $user->user_email,
						'type'  => 'email',
					],
					'phone'      => [
						'label' => __( 'Phone', 'pronamic_ideal' ),
						'value' => '',
						'type'  => 'tel',
					],
				];

				foreach ( $customer_fields as $key => $field ) {
					$field_name  = \sprintf( 'customer[%s]', $key );
					$field_id    = \sanitize_key( $name );
					$field_value = $field['value'];
					$field_type  = $field['type'] ?? 'text';

					?>
					<tr>
						<th scope="row">
							<label for="<?php echo \esc_attr( $field_id ); ?>"><?php echo \esc_html( $field['label'] ); ?></label>
						</th>
						<td>
							<input id="<?php echo \esc_attr( $field_id ); ?>" name="<?php echo \esc_attr( $field_name ); ?>" value="<?php echo \esc_attr( $field_value ); ?>"  type="<?php echo \esc_attr( $field_type ); ?>" class="regular-text code pronamic-pay-form-control">
						</td>
					</tr>
					<?php
				}

				?>
			</table>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<?php esc_html_e( 'Billing', 'pronamic_ideal' ); ?>
		</th>
		<td style="padding: 0;">
			<?php

			print_adress_fields(
				'billing[%s]',
				[
					'first_name'   => ( '' === $user->first_name ) ? 'John' : $user->first_name,
					'last_name'    => ( '' === $user->last_name ) ? 'Doe' : $user->last_name,
					'company'      => 'Pronamic',
					'line_1'       => 'Billing Line 1',
					'postal_code'  => '1234 AB',
					'city'         => 'Billing City',
					'country_code' => 'NL',
					'email'        => $user->user_email,
				]
			);

			?>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<?php esc_html_e( 'Shipping', 'pronamic_ideal' ); ?>
		</th>
		<td style="padding: 0;">
			<?php

			print_adress_fields(
				'shipping[%s]',
				[
					'first_name'   => ( '' === $user->first_name ) ? 'Jane' : $user->first_name,
					'last_name'    => ( '' === $user->last_name ) ? 'Doe' : $user->last_name,
					'company'      => 'Pronamic',
					'line_1'       => 'Shipping Line 1',
					'postal_code'  => '5678 XY',
					'city'         => 'Shipping City',
					'country_code' => 'NL',
					'email'        => $user->user_email,
				]
			);

			?>
		</td>
	</tr>

	<?php if ( $gateway->supports( 'recurring' ) ) : ?>

		<?php

		$options = [
			''  => __( '— Select Repeat —', 'pronamic_ideal' ),
			'D' => __( 'Daily', 'pronamic_ideal' ),
			'W' => __( 'Weekly', 'pronamic_ideal' ),
			'M' => __( 'Monthly', 'pronamic_ideal' ),
			'Y' => __( 'Annually', 'pronamic_ideal' ),
		];

		$options_interval_suffix = [
			'D' => __( 'days', 'pronamic_ideal' ),
			'W' => __( 'weeks', 'pronamic_ideal' ),
			'M' => __( 'months', 'pronamic_ideal' ),
			'Y' => __( 'year', 'pronamic_ideal' ),
		];

		?>
		<tr>
			<th scope="row">
				<label for="pronamic-pay-test-subscription">
					<?php esc_html_e( 'Subscription', 'pronamic_ideal' ); ?>
				</label>
			</th>
			<td>
				<fieldset>
					<legend class="screen-reader-text"><span><?php esc_html_e( 'Test Subscription', 'pronamic_ideal' ); ?></span></legend>

					<label for="pronamic-pay-test-subscription">
						<input name="pronamic_pay_test_subscription" id="pronamic-pay-test-subscription" value="1" type="checkbox" />
						<?php esc_html_e( 'Start a subscription for this payment.', 'pronamic_ideal' ); ?>
					</label>
				</fieldset>

				<?php

				\wp_add_inline_script(
					'pronamic-pay-admin',
					"
					jQuery( document ).ready( function( $ ) {
						$( '#pronamic-pay-test-subscription' ).change( function() {
							$( '.pronamic-pay-test-subscription' ).toggle( $( this ).prop( 'checked' ) );
						} );
					} );
					"
				);

				?>
			</td>
		</tr>
		<tr class="pronamic-pay-cloak pronamic-pay-test-subscription">
			<th scope="row">
				<label for="pronamic_pay_test_repeat_frequency"><?php esc_html_e( 'Frequency', 'pronamic_ideal' ); ?></label>
			</th>
			<td>
				<select id="pronamic_pay_test_repeat_frequency" name="pronamic_pay_test_repeat_frequency">
					<?php

					foreach ( $options as $key => $label ) {
						$interval_suffix = '';

						if ( isset( $options_interval_suffix[ $key ] ) ) {
							$interval_suffix = $options_interval_suffix[ $key ];
						}

						printf(
							'<option value="%s" data-interval-suffix="%s">%s</option>',
							esc_attr( $key ),
							esc_attr( $interval_suffix ),
							esc_html( $label )
						);
					}

					?>
				</select>
			</td>
		</tr>
		<tr class="pronamic-pay-cloak pronamic-pay-test-subscription">
			<th scope="row">
				<label for="pronamic_pay_test_repeat_interval"><?php esc_html_e( 'Repeat every', 'pronamic_ideal' ); ?></label>
			</th>
			<td>
				<select id="pronamic_pay_test_repeat_interval" name="pronamic_pay_test_repeat_interval">
					<?php

					foreach ( range( 1, 30 ) as $value ) {
						printf(
							'<option value="%s">%s</option>',
							esc_attr( (string) $value ),
							esc_html( (string) $value )
						);
					}

					?>
				</select>

				<span id="pronamic_pay_test_repeat_interval_suffix"><?php esc_html_e( 'days/weeks/months/year', 'pronamic_ideal' ); ?></span>
			</td>
		</tr>
		<tr class="pronamic-pay-cloak pronamic-pay-test-subscription">
			<th scope="row">
				<?php esc_html_e( 'Ends On', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<div>
					<input type="radio" id="pronamic_pay_ends_never" name="pronamic_pay_ends_on" value="never" checked="checked" />

					<label for="pronamic_pay_ends_never">
						<?php esc_html_e( 'Never', 'pronamic_ideal' ); ?>
					</label>
				</div>
				<div>
					<input type="radio" id="pronamic_pay_ends_count" name="pronamic_pay_ends_on" value="count" />

					<label for="pronamic_pay_ends_count">
						<?php

						$allowed_html = [
							'input' => [
								'id'    => true,
								'name'  => true,
								'type'  => true,
								'value' => true,
								'size'  => true,
								'class' => true,
							],
						];

						echo wp_kses(
							sprintf(
								/* translators: %s: Input field for number times */
								__( 'After %s times', 'pronamic_ideal' ),
								sprintf( '<input type="number" name="pronamic_pay_ends_on_count" value="%s" min="1" />', esc_attr( '' ) )
							),
							$allowed_html
						);

						?>
					</label>
				</div>

				<div>
					<input type="radio" id="pronamic_pay_ends_date" name="pronamic_pay_ends_on" value="date" />

					<label for="pronamic_pay_ends_date">
						<?php

						echo wp_kses(
							sprintf(
								/* translators: %s: input HTML */
								__( 'On %s', 'pronamic_ideal' ),
								sprintf( '<input type="date" id="pronamic_pay_ends_on_date" name="pronamic_pay_ends_on_date" value="%s" />', esc_attr( '' ) )
							),
							$allowed_html
						);

						?>
					</label>
				</div>
			</td>
		</tr>

	<?php endif; ?>

	<tr>
		<td>

		</td>
		<td>
			<?php submit_button( __( 'Test', 'pronamic_ideal' ), 'secondary', 'test_pay_gateway', false ); ?>
		</td>
	</tr>

</table>

<?php wp_print_scripts( 'pronamic-pay-gateway-test' ); ?>
