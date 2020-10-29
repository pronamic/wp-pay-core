<?php
/**
 * Meta Box Subscription Phases
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

use Pronamic\WordPress\Pay\Util;
use Pronamic\WordPress\Pay\Subscriptions\SubscriptionPhase;

?>

<?php if ( empty( $phases ) ) : ?>

	<?php esc_html_e( 'No phases found.', 'pronamic_ideal' ); ?>

<?php else : ?>

	<table class="pronamic-pay-table widefat">
		<thead>
			<tr>
				<th scope="col">
					<span class="pronamic-pay-tip pronamic-pay-icon pronamic-pay-status" title="<?php esc_attr_e( 'Status', 'pronamic_ideal' ); ?>"><?php esc_html_e( 'Status', 'pronamic_ideal' ); ?></span>
				</th>
				<th scope="col"><?php esc_html_e( 'Amount', 'pronamic_ideal' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Recurrence', 'pronamic_ideal' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Start Date', 'pronamic_ideal' ); ?></th>
				<th scope="col"><?php esc_html_e( 'End Date', 'pronamic_ideal' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Next Date', 'pronamic_ideal' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Periods created', 'pronamic_ideal' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Trial', 'pronamic_ideal' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Aligned', 'pronamic_ideal' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Prorated', 'pronamic_ideal' ); ?></th>
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
					<td></td>
					<td>
						<?php echo esc_html( $phase->get_amount()->format_i18n() ); ?>
					</td>
					<td>
						<?php

						$total_periods = $phase->get_total_periods();

						if ( 1 === $total_periods ) :
							// No recurrence.
							echo '—';

						elseif ( null === $total_periods ) :
							// Unlimited.
							echo esc_html( strval( Util::format_recurrences( $phase->get_interval() ) ) );

						else :
							// Fixed number of recurrences.
							printf(
								'%s (%s)',
								esc_html( strval( Util::format_recurrences( $phase->get_interval() ) ) ),
								esc_html( strval( Util::format_frequency( $total_periods ) ) )
							);

						endif;

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
					<td>
						<?php

						$next_date = $phase->get_next_date();

						echo esc_html( null === $next_date ? '—' : ( new \Pronamic\WordPress\DateTime\DateTime( '@' . $next_date->getTimestamp() ) )->format_i18n() );

						?>
					</td>
					<td>
						<?php

						echo esc_html( $phase->get_periods_created() );

						?>
					</td>
					<td>
						<?php

						echo esc_html( $phase->is_trial() ? __( 'Yes', 'pronamic_ideal' ) : __( 'No', 'pronamic_ideal' ) );

						?>
					</td>
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
				</tr>

			<?php endforeach; ?>

		</tbody>
	</table>

<?php endif; ?>
