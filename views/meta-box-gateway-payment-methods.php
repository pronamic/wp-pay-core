<?php
/**
 * Meta box gateway config payment methods.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 * @var array<string, string> $columns                  Columns.
 * @var array                 $payment_methods          Payment methods.
 * @var bool                  $supports_methods_request Supports methods request.
 */

use Pronamic\WordPress\Pay\Core\PaymentMethods;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$show_recurring_column = false;

foreach ( $payment_methods as $payment_method ) {
	if ( $payment_method->supports( 'recurring' ) ) {
		$show_recurring_column = true;

		break;
	}
}

?>
<table class="form-table widefat pronamic-pay-payment-methods">
	<thead>
		<tr>
			<th scope="col"><?php esc_html_e( 'Payment Method', 'pronamic_ideal' ); ?></th>
			<th scope="col"><?php esc_html_e( 'Status', 'pronamic_ideal' ); ?></th>

			<?php if ( $show_recurring_column ) : ?>

				<th scope="col"><?php esc_html_e( 'Recurring', 'pronamic_ideal' ); ?></th>

			<?php endif; ?>
		</tr>
	</thead>

	<tbody>

		<?php foreach ( $payment_methods as $payment_method ) : ?>

			<tr>
				<td>
					<?php echo esc_html( $payment_method->get_name() ); ?>
				</td>
				<td>
					<?php

					$icon = 'question-mark';

					switch ( $payment_method->get_status() ) {
						case 'active':
							$icon = 'completed';
							break;
						case 'inactive':
							$icon = 'cancelled';
							break;
					}

					printf( '<span class="pronamic-pay-icon pronamic-pay-icon-%s"></span>', esc_attr( $icon ) );

					?>
				</td>

				<?php if ( $show_recurring_column ) : ?>

					<td>
						<?php

						$icon = 'cancelled';

						if ( $payment_method->supports( 'recurring' ) ) {
							$icon = 'completed';
						}

						printf( '<span class="pronamic-pay-icon pronamic-pay-icon-%s"></span>', esc_attr( $icon ) );

						?>
					</td>

				<?php endif; ?>

			</tr>

		<?php endforeach; ?>

	</tbody>
</table>
