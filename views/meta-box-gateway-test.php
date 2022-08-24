<?php
/**
 * Meta Box Gateway Test
 *
 * @author Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license GPL-3.0-or-later
 * @package Pronamic\WordPress\Pay
 * @var \WP_Post $post WordPress post.
 */

use Pronamic\WordPress\Money\Currency;
use Pronamic\WordPress\Money\Currencies;
use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Plugin;

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

?>
<table class="form-table">
	<tr>
		<th scope="row">
			<label for="pronamic-pay-test-payment-methods">
				<?php esc_html_e( 'Payment Method', 'pronamic_ideal' ); ?>
			</label>
		</th>
		<td>
			<select id="pronamic-pay-test-payment-methods" name="pronamic_pay_test_payment_method" required>
				<option value=""><?php esc_html_e( '— Choose payment method —', 'pronamic_ideal' ); ?></option>

				<?php

				foreach ( $gateway->get_payment_methods() as $payment_method ) {
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

	<?php foreach ( $gateway->get_payment_methods() as $payment_method ) : ?>

		<?php foreach ( $payment_method->get_fields() as $field ) : ?>

			<tr class="pronamic-pay-cloack pronamic-pay-test-payment-method <?php echo esc_attr( $payment_method->get_id() ); ?>">
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
			<?php esc_html_e( 'Amount', 'pronamic_ideal' ); ?>
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

			<input name="test_amount" id="test_amount" class="regular-text code pronamic-pay-form-control" value="" type="number" step="any" size="6" autocomplete="off" />
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

				<script type="text/javascript">
					jQuery( document ).ready( function( $ ) {
						$( '#pronamic-pay-test-subscription' ).change( function() {
							$( '.pronamic-pay-test-subscription' ).toggle( $( this ).prop( 'checked' ) );
						} );
					} );
				</script>
			</td>
		</tr>
		<tr class="pronamic-pay-cloack pronamic-pay-test-subscription">
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
		<tr class="pronamic-pay-cloack pronamic-pay-test-subscription">
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
		<tr class="pronamic-pay-cloack pronamic-pay-test-subscription">
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

<script type="text/javascript">
	jQuery( document ).ready( function( $ ) {
		// Interval label.
		function set_interval_label() {
			var text = $( '#pronamic_pay_test_repeat_frequency :selected' ).data( 'interval-suffix' );

			$( '#pronamic_pay_test_repeat_interval_suffix' ).text( text );
		}

		$( '#pronamic_pay_test_repeat_frequency' ).change( function() { set_interval_label(); } );

		set_interval_label();

		// Ends on value.
		$( 'label[for^="pronamic_pay_ends_"] input' ).focus( function () {
			var radio_id = $( this ).parents( 'label' ).attr( 'for' );

			$( '#' + radio_id ).prop( 'checked', true );
		} );
	} );
</script>
