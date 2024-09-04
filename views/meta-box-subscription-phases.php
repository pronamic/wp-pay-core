<?php
/**
 * Meta Box Subscription Phases
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

use Pronamic\WordPress\Pay\Util;
use Pronamic\WordPress\Pay\Subscriptions\SubscriptionPhase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<?php if ( empty( $phases ) ) : ?>

	<?php esc_html_e( 'No phases found.', 'pronamic_ideal' ); ?>

<?php else : ?>

	<?php

	$has_trial     = false;
	$has_alignment = false;

	foreach ( $phases as $phase ) {
		if ( $phase->is_trial() ) {
			$has_trial = true;
		}

		if ( $phase->is_alignment() || $phase->is_prorated() ) {
			$has_alignment = true;
		}
	}

	?>

	<div class="pronamic-pay-table-responsive">
		<table class="pronamic-pay-table widefat">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Amount', 'pronamic_ideal' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Recurrence', 'pronamic_ideal' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Start Date', 'pronamic_ideal' ); ?></th>
					<th scope="col"><?php esc_html_e( 'End Date', 'pronamic_ideal' ); ?></th>

					<?php if ( $has_trial ) : ?>

						<th scope="col"><?php esc_html_e( 'Trial', 'pronamic_ideal' ); ?></th>

					<?php endif; ?>

					<?php if ( $has_alignment ) : ?>

						<th scope="col"><?php esc_html_e( 'Aligned', 'pronamic_ideal' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Prorated', 'pronamic_ideal' ); ?></th>

					<?php endif; ?>
				</tr>
			</thead>

			<tbody>

				<?php
				/**
				 * Subscription phase.
				 *
				 * @var SubscriptionPhase $phase
				 */
				foreach ( $phases as $phase ) :
					?>

					<tr>
						<td>
							<?php echo esc_html( $phase->get_amount()->format_i18n() ); ?>
						</td>
						<td>
							<?php

							$total_periods = $phase->get_total_periods();

							if ( null === $total_periods ) {
								// Unlimited.
								echo esc_html( strval( Util::format_recurrences( $phase->get_interval() ) ) );
							}

							if ( 1 === $total_periods ) {
								// No recurrence.
								echo '—';
							}

							if ( $total_periods > 1 ) {
								// Fixed number of recurrences.
								printf(
									'%s (%s)',
									esc_html( strval( Util::format_recurrences( $phase->get_interval() ) ) ),
									esc_html( strval( Util::format_frequency( $total_periods ) ) )
								);
							}

							?>
						</td>
						<td>
							<?php

							$start_date = $phase->get_start_date();

							echo esc_html( ( new \Pronamic\WordPress\DateTime\DateTime( '@' . $start_date->getTimestamp() ) )->format_i18n() );

							?>
						</td>
						<td>
							<?php

							$end_date = $phase->get_end_date();

							echo esc_html( null === $end_date ? '∞' : ( new \Pronamic\WordPress\DateTime\DateTime( '@' . $end_date->getTimestamp() ) )->format_i18n() );

							?>
						</td>

						<?php if ( $has_trial ) : ?>

							<td>
								<?php

								echo esc_html( $phase->is_trial() ? __( 'Yes', 'pronamic_ideal' ) : __( 'No', 'pronamic_ideal' ) );

								?>
							</td>

						<?php endif; ?>

						<?php if ( $has_alignment ) : ?>

							<td>
								<?php

								echo esc_html( $phase->is_alignment() ? __( 'Yes', 'pronamic_ideal' ) : __( 'No', 'pronamic_ideal' ) );

								?>
							</td>
							<td>
								<?php

								echo esc_html( $phase->is_prorated() ? __( 'Yes', 'pronamic_ideal' ) : __( 'No', 'pronamic_ideal' ) );

								?>
							</td>

						<?php endif; ?>
					</tr>

				<?php endforeach; ?>

			</tbody>
		</table>
	</div>

<?php endif; ?>
