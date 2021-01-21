<?php
/**
 * Meta box gateway config payment methods.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

use Pronamic\WordPress\Pay\Core\PaymentMethods;

$columns = array(
	'payment_method' => __( 'Payment method', 'pronamic_ideal' ),
	'active'         => __( 'Active', 'pronamic_ideal' ),
);

$integration = pronamic_pay_plugin()->gateway_integrations->get_integration( $gateway_id );

if ( $integration->supports( 'recurring' ) ) :
	$columns['recurring'] = __( 'Recurring', 'pronamic_ideal' );
endif;

?>
<table class="form-table widefat pronamic-pay-payment-methods">
	<thead>
		<tr>
			<?php foreach ( $columns as $column ) : ?>

				<th><?php echo esc_html( $column ); ?></th>

			<?php endforeach; ?>
		</tr>
	</thead>

	<tbody>
		<?php

		foreach ( $payment_methods as $method ) {
			?>

			<tr>

				<?php

				foreach ( $columns as $key => $column ) :
					$value = '';

					switch ( $key ) :
						case 'payment_method':
							$value = $method->name;

							break;
						case 'active':
							$icon = 'question-mark';

							if ( $supports_methods_request ) {
								$icon = ( $method->available ? 'completed' : 'cancelled' );
							}

							$value = sprintf( '<span class="pronamic-pay-icon pronamic-pay-icon-%s"></span>', esc_attr( $icon ) );

							break;
						case 'recurring':
							$icon = 'cancelled';

							if ( PaymentMethods::is_recurring_method( $method->id ) ) :
								$icon = 'completed';
							endif;

							$value = sprintf( '<span class="pronamic-pay-icon pronamic-pay-icon-%s"></span>', esc_attr( $icon ) );

							break;
					endswitch;

					printf(
						'<td>%s</td>',
						wp_kses(
							$value,
							array( 'span' => array( 'class' => array() ) )
						)
					);

				endforeach;

				?>

			</tr>

			<?php

		}

		?>
	</tbody>

</table>
