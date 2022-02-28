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

// Payment method selector.
$payment_methods = $gateway->get_payment_method_field_options( true );

$inputs = array();

try {
	foreach ( $payment_methods as $payment_method => $method_name ) {
		if ( ! \is_string( $payment_method ) ) {
			$payment_method = null;
		}

		$gateway->set_payment_method( $payment_method );

		// Payment method input HTML.
		$html = $gateway->get_input_html();

		if ( ! empty( $html ) ) {
			$inputs[ $payment_method ] = array(
				'label' => $method_name,
				'html'  => $html,
			);
		}
	}
} catch ( \Exception $exception ) {
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

$currency = Currency::get_instance( 'EUR' );

?>
<table class="form-table">
	<tr>
		<th scope="row">
			<label for="pronamic-pay-test-payment-methods">
				<?php esc_html_e( 'Payment Method', 'pronamic_ideal' ); ?>
			</label>
		</th>
		<td>
			<select id="pronamic-pay-test-payment-methods" name="pronamic_pay_test_payment_method">
				<?php

				foreach ( $payment_methods as $payment_method => $method_name ) {
					printf(
						'<option value="%s" data-is-recurring="%d">%s</option>',
						esc_attr( $payment_method ),
						esc_attr( PaymentMethods::is_recurring_method( $payment_method ) ? '1' : ' 0' ),
						esc_html( $method_name )
					);
				}

				?>
			</select>
		</td>
	</tr>

	<?php foreach ( $inputs as $method => $input ) : ?>

		<tr class="pronamic-pay-cloack pronamic-pay-test-payment-method <?php echo esc_attr( $method ); ?>">
			<th scope="row">
				<?php echo esc_html( $input['label'] ); ?>
			</th>
			<td>
				<?php

				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $input['html'];

				?>
			</td>
		</tr>

	<?php endforeach; ?>

	<tr>
		<th scope="row">
			<?php esc_html_e( 'Amount', 'pronamic_ideal' ); ?>
		</th>
		<td>
			<label for="test_amount"><?php echo \esc_html( (string) $currency->get_symbol() ); ?></label>

			<input name="test_amount" id="test_amount" class="regular-text code pronamic-pay-form-control" value="" type="number" step="any" size="6" autocomplete="off" />
		</td>
	</tr>

	<tr>
		<th scope="row">
			<?php esc_html_e( 'Phone Number', 'pronamic_ideal' ); ?>
		</th>
		<td>
			<input name="test_phone" id="test_phone" class="regular-text code pronamic-pay-form-control" value="" type="tel" />
		</td>
	</tr>

	<?php if ( $gateway->supports( 'recurring' ) ) : ?>

		<?php

		$options = array(
			''  => __( '— Select Repeat —', 'pronamic_ideal' ),
			'D' => __( 'Daily', 'pronamic_ideal' ),
			'W' => __( 'Weekly', 'pronamic_ideal' ),
			'M' => __( 'Monthly', 'pronamic_ideal' ),
			'Y' => __( 'Annually', 'pronamic_ideal' ),
		);

		$options_interval_suffix = array(
			'D' => __( 'days', 'pronamic_ideal' ),
			'W' => __( 'weeks', 'pronamic_ideal' ),
			'M' => __( 'months', 'pronamic_ideal' ),
			'Y' => __( 'year', 'pronamic_ideal' ),
		);

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

						$allowed_html = array(
							'input' => array(
								'id'    => true,
								'name'  => true,
								'type'  => true,
								'value' => true,
								'size'  => true,
								'class' => true,
							),
						);

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
