<?php
/**
 * Meta box gateway config payment methods.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

?>
<table class="form-table widefat pronamic-pay-payment-methods">
	<thead>
		<tr>
			<th><?php esc_html_e( 'Payment method', 'pronamic_ideal' ); ?></th>
			<th><?php esc_html_e( 'Active', 'pronamic_ideal' ); ?></th>
		</tr>
	</thead>

	<tbody>
		<?php

		$supports_methods_request = ( null !== $gateway->get_transient_available_payment_methods() );

		foreach ( $payment_methods as $method ) {
			$class = $method->id;
			$icon  = 'question-mark';


			printf(
				'<tr class="%1$s"><td>%2$s</td><td>%3$s</td></tr>',
				esc_attr( $class ),
				esc_html( $method->name ),
				sprintf( '<span class="pronamic-pay-icon pronamic-pay-icon-%s"></span>', esc_attr( $icon ) )
			);
		}

		?>
	</tbody>

</table>
