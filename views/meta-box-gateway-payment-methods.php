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
			<th><?php esc_html_e( 'Supported', 'pronamic_ideal' ); ?></th>
			<th><?php esc_html_e( 'Active', 'pronamic_ideal' ); ?></th>
		</tr>
	</thead>

	<tbody>
		<?php

		foreach ( $payment_methods as $method ) {
			$class = $method->id;
			$icon  = 'cancelled';

			if ( $method->available ) {
				$class .= ' available';
				$icon   = 'completed';
			}

			printf(
				'<tr class="%1$s"><td>%2$s</td><td>%3$s</td><td>%4$s</td></tr>',
				esc_attr( $class ),
				esc_html( $method->name ),
				'<span class="pronamic-pay-icon pronamic-pay-icon-completed"></span>',
				sprintf( '<span class="pronamic-pay-icon pronamic-pay-icon-%s"></span>', esc_attr( $icon ) )
			);
		}

		?>
	</tbody>

</table>
