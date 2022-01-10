<?php
/**
 * Meta box gateway config payment methods.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 * @var array<string, string> $columns                  Columns.
 * @var array                 $payment_methods          Payment methods.
 * @var bool                  $supports_methods_request Supports methods request.
 */

use Pronamic\WordPress\Pay\Core\PaymentMethods;

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

		<?php foreach ( $payment_methods as $method ) : ?>

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

		<?php endforeach; ?>

	</tbody>
</table>
