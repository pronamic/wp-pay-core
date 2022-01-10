<?php
/**
 * Forms template.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

global $pronamic_pay_errors;

use Pronamic\WordPress\Number\Number;
use Pronamic\WordPress\Money\Currency;
use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Forms\FormPostType;
use Pronamic\WordPress\Pay\Forms\FormsSource;
use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Pay\Util;

if ( ! isset( $settings ) ) {
	return;
}

$methods_with_choices = array(
	\Pronamic\WordPress\Pay\Forms\FormPostType::AMOUNT_METHOD_CHOICES_ONLY,
	\Pronamic\WordPress\Pay\Forms\FormPostType::AMOUNT_METHOD_CHOICES_AND_INPUT,
);

$gateway = Plugin::get_gateway( $settings['config_id'] );

$amount_value = '';

if ( filter_has_var( INPUT_GET, 'amount' ) ) {
	$amount_value = filter_input( INPUT_GET, 'amount', FILTER_SANITIZE_STRING );
}

if ( null === $gateway ) {
	return;
}

$currency = Currency::get_instance( 'EUR' );

?>
<div class="pronamic-pay-form-wrap">

	<?php if ( ! is_singular( 'pronamic_pay_form' ) && ! empty( $settings['title'] ) ) : ?>

		<h2 class="pronamic-pay-form-title"><?php echo esc_html( $settings['title'] ); ?></h2>

	<?php endif; ?>

	<form id="<?php echo esc_attr( $settings['html_id'] ); ?>" class="pronamic-pay-form" method="post">
		<?php if ( in_array( $settings['amount_method'], $methods_with_choices, true ) ) : ?>

		<fieldset>
			<legend><?php esc_html_e( 'Amount', 'pronamic_ideal' ); ?></legend>

		<?php endif; ?>

			<div class="pronamic-pay-amount pronamic-pay-form-row-wide">
				<?php if ( in_array( $settings['amount_method'], $methods_with_choices, true ) ) : ?>

						<?php foreach ( $settings['amounts'] as $amount ) : ?>

							<?php

							$input_id = 'pronamic-pay-amount-' . esc_attr( $amount );

							$money = new Money( $amount, $currency );

							?>

							<div>
								<input class="pronamic-pay-amount-input pronamic-pay-input" id="<?php echo esc_attr( $input_id ); ?>" name="pronamic_pay_amount" type="radio" required="required" value="<?php echo esc_attr( (string) $amount ); ?>" />

								<label for="<?php echo esc_attr( $input_id ); ?>">
									<span class="pronamic-pay-amount-value"><?php echo esc_html( $money->format_i18n() ); ?></span>
								</label>
							</div>

						<?php endforeach; ?>

						<?php if ( \Pronamic\WordPress\Pay\Forms\FormPostType::AMOUNT_METHOD_CHOICES_AND_INPUT === $settings['amount_method'] ) : ?>

							<div>
								<input class="pronamic-pay-amount-input pronamic-pay-input" id="pronamic-pay-amount-other" name="pronamic_pay_amount" type="radio" required="required" value="other" />

								<label for="pronamic-pay-amount-other">
									<span class="pronamic-pay-currency-symbol pronamic-pay-currency-position-before">€</span>
									<input class="pronamic-pay-amount-input pronamic-pay-input" id="pronamic-pay-amount" name="pronamic_pay_amount_other" type="number" step="any" autocomplete="off" value="<?php echo esc_attr( $amount_value ); ?>" />
								</label>
							</div>

						<?php endif; ?>

				<?php endif; ?>

				<?php if ( \Pronamic\WordPress\Pay\Forms\FormPostType::AMOUNT_METHOD_INPUT_ONLY === $settings['amount_method'] ) : ?>

					<span class="pronamic-pay-currency-symbol pronamic-pay-currency-position-before">€</span>
					<input class="pronamic-pay-amount-input pronamic-pay-input" id="pronamic-pay-amount" name="pronamic_pay_amount" type="number" step="any" autocomplete="off" value="<?php echo esc_attr( $amount_value ); ?>" />

				<?php endif; ?>
			</div>

		<?php if ( in_array( $settings['amount_method'], $methods_with_choices, true ) ) : ?>

		</fieldset>

		<?php endif; ?>

		<fieldset>
			<legend><?php esc_html_e( 'Personal Info', 'pronamic_ideal' ); ?></legend>

			<p class="pronamic-pay-form-row pronamic-pay-form-row-first">
				<label class="pronamic-pay-label" for="pronamic-pay-first-name">
					<?php esc_html_e( 'First Name', 'pronamic_ideal' ); ?> <span class="pronamic-pay-required-indicator">*</span>
				</label>

				<input class="pronamic-pay-input pronamic-pay-required" type="text" name="pronamic_pay_first_name" placeholder="<?php esc_attr_e( 'First Name', 'pronamic_ideal' ); ?>" id="pronamic-pay-first-name" required="required" value="" />
			</p>

			<p class="pronamic-pay-form-row pronamic-pay-form-row-last">
				<label class="pronamic-pay-label" for="pronamic-pay-last-name">
					<?php esc_html_e( 'Last Name', 'pronamic_ideal' ); ?>
				</label>

				<input class="pronamic-pay-input" type="text" name="pronamic_pay_last_name" id="pronamic-pay-last-name" placeholder="<?php esc_attr_e( 'Last Name', 'pronamic_ideal' ); ?>" value="" />
			</p>

			<p class="pronamic-pay-form-row pronamic-pay-form-row-wide">
				<label class="pronamic-pay-label" for="pronamic-pay-email">
					<?php esc_html_e( 'Email Address', 'pronamic_ideal' ); ?>
					<span class="pronamic-pay-required-indicator">*</span>
				</label>

				<input class="pronamic-pay-input required" type="email" name="pronamic_pay_email" placeholder="<?php esc_attr_e( 'Email Address', 'pronamic_ideal' ); ?>" id="pronamic-pay-email" required="required" value="" />
			</p>
		</fieldset>

		<?php

		if ( $gateway->payment_method_is_required() ) {

			$gateway->set_payment_method( PaymentMethods::IDEAL );

		}

		$fields = $gateway->get_input_fields();

		?>

		<?php if ( ! empty( $fields ) ) : ?>

			<fieldset>
				<legend><?php esc_html_e( 'Payment Info', 'pronamic_ideal' ); ?></legend>

				<?php foreach ( $fields as $i => $field ) : ?>

					<p class="pronamic-pay-form-row pronamic-pay-form-row-wide">
						<label class="pronamic-pay-label" for="<?php echo esc_attr( $field['id'] ); ?>">
							<?php echo esc_html( $field['label'] ); ?>
							<span class="pronamic-pay-required-indicator">*</span>
						</label>

						<?php if ( 'select' === $field['type'] ) : ?>

							<select id="<?php echo esc_attr( $field['id'] ); ?>" name="<?php echo esc_attr( $field['name'] ); ?>">
								<?php

								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo Util::select_options_grouped( $field['choices'] );

								?>
							</select>

						<?php endif; ?>
					</p>

				<?php endforeach; ?>

			</fieldset>

		<?php endif; ?>

		<?php if ( ! empty( $pronamic_pay_errors ) ) : ?>

			<div class="pronamic-pay-errors">

				<?php foreach ( $pronamic_pay_errors as $e ) : ?>

					<p class="pronamic-pay-error">
						<strong><?php esc_html_e( 'Error', 'pronamic_ideal' ); ?></strong>: <?php echo esc_html( $e ); ?>
					</p>

				<?php endforeach; ?>

			</div>

		<?php endif; ?>

		<div class="pronamic-pay-submit-button-wrap pronamic-pay-clearfix">
			<?php wp_nonce_field( 'pronamic_pay', 'pronamic_pay_nonce' ); ?>

			<?php

			$fields = array(
				'pronamic_pay_source'    => $settings['source'],
				'pronamic_pay_source_id' => $settings['source_id'],
			);

			// Add config ID when needed.
			if ( FormsSource::PAYMENT_FORM !== $settings['source'] ) {
				$fields['pronamic_pay_config_id'] = $settings['config_id'];
			}

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo Util::html_hidden_fields( $fields );

			?>

			<?php if ( FormPostType::AMOUNT_METHOD_INPUT_FIXED === $settings['amount_method'] ) : ?>

				<input type="hidden" name="pronamic_pay_amount" value="<?php echo esc_attr( array_shift( $settings['amounts'] ) ); ?>" />

			<?php endif; ?>

			<input type="submit" class="pronamic-pay-submit pronamic-pay-btn" id="pronamic-pay-purchase-button" name="pronamic_pay" value="<?php echo esc_attr( $settings['button_text'] ); ?>" />
		</div>
	</form>
</div>
