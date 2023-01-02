<?php
/**
 * Meta Box Form Options
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

use Pronamic\WordPress\Number\Number;
use Pronamic\WordPress\Money\Money;

if ( ! isset( $post ) ) {
	return;
}

wp_nonce_field( 'pronamic_pay_save_form_options', 'pronamic_pay_nonce' );

?>
<table class="form-table">
	<tr>
		<th scope="row">
			<label for="_pronamic_payment_form_config_id">
				<?php esc_html_e( 'Gateway', 'pronamic_ideal' ); ?>
			</label>
		</th>
		<td>
			<?php

			$config_id = get_post_meta( $post->ID, '_pronamic_payment_form_config_id', true );

			\Pronamic\WordPress\Pay\Admin\AdminModule::dropdown_configs(
				[
					'name'     => '_pronamic_payment_form_config_id',
					'selected' => $config_id,
				]
			);

			?>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="_pronamic_payment_form_button_text">
				<?php esc_html_e( 'Button Text', 'pronamic_ideal' ); ?>
			</label>
		</th>
		<td>
			<?php $button_text = get_post_meta( $post->ID, '_pronamic_payment_form_button_text', true ); ?>

			<input class="regular-text" type="text" name="_pronamic_payment_form_button_text" value="<?php echo esc_attr( $button_text ); ?>" placeholder="<?php esc_attr_e( 'Pay Now', 'pronamic_ideal' ); ?>" />
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="_pronamic_payment_form_amount_method">
				<?php esc_html_e( 'Amount', 'pronamic_ideal' ); ?>
			</label>
		</th>
		<td>
			<select name="_pronamic_payment_form_amount_method">
				<?php

				$amount_method = get_post_meta( $post->ID, '_pronamic_payment_form_amount_method', true );

				$options = [
					\Pronamic\WordPress\Pay\Forms\FormPostType::AMOUNT_METHOD_INPUT_ONLY        => __( 'Show as input field', 'pronamic_ideal' ),
					\Pronamic\WordPress\Pay\Forms\FormPostType::AMOUNT_METHOD_CHOICES_ONLY      => __( 'Show as choices', 'pronamic_ideal' ),
					\Pronamic\WordPress\Pay\Forms\FormPostType::AMOUNT_METHOD_CHOICES_AND_INPUT => __( 'Show as choices with input field', 'pronamic_ideal' ),
				];

				foreach ( $options as $value => $name ) {
					printf(
						'<option value="%s" %s>%s</option>',
						esc_attr( $value ),
						selected( $value, $amount_method, false ),
						esc_html( $name )
					);
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<th scope="row"></th>
		<td>
			<?php

			$choices = get_post_meta( $post->ID, '_pronamic_payment_form_amount_choices', true );

			$items = [];

			if ( \is_array( $choices ) ) {
				foreach ( $choices as $value ) {
					$items[] = Number::from_mixed( $value )->divide( Number::from_int( 100 ) );
				}
			}

			// Add empty input field.
			$items[] = '';

			foreach ( $items as $i => $value ) {
				printf(
					'<div>
						<label for="_pronamic_payment_form_amount_choice_%d">
							€ <input id="_pronamic_payment_form_amount_choice_%d" type="number" step="any" name="_pronamic_payment_form_amount_choices[]" value="%s" />
						</label>
					</div>',
					esc_attr( (string) $i ),
					esc_attr( (string) $i ),
					esc_attr( $value )
				);
			}
			?>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="_pronamic_payment_form_description">
				<?php esc_html_e( 'Transaction description', 'pronamic_ideal' ); ?>
			</label>
		</th>
		<td>
			<?php $description = get_post_meta( $post->ID, '_pronamic_payment_form_description', true ); ?>

			<input class="regular-text" type="text" name="_pronamic_payment_form_description" value="<?php echo esc_attr( $description ); ?>" />
		</td>
	</tr>
</table>
