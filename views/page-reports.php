<?php
/**
 * Page Reports
 *
 * @author Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license GPL-3.0-or-later
 * @package Pronamic\WordPress\Pay
 * @var \Pronamic\WordPress\Pay\Admin\AdminReports $admin_reports Admin reports.
 */

use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Pay\Util;

?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<hr class="wp-header-end">

	<div id="poststuff">
		<div class="postbox">
			<div class="pronamic-pay-chart-filter">

			</div>

			<div class="inside pronamic-pay-chart-with-sidebar">
				<div class="pronamic-pay-chart-sidebar">
					<ul class="pronamic-pay-chart-legend">

						<?php foreach ( $admin_reports->get_reports() as $i => $serie ) : ?>

                            <?php // phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Flot data object. ?>

							<li class="<?php echo esc_attr( $serie->class ); ?>" data-pronamic-pay-highlight-serie="<?php echo esc_attr( $i ); ?>">
								<?php

								echo '<strong>';

								$legend_value = \property_exists( $serie, 'legendValue' ) ? $serie->legendValue : '';

								if ( isset( $serie->tooltipFormatter ) && 'money' === $serie->tooltipFormatter ) {
									$money = new Money( $legend_value, 'EUR' );

									echo esc_html( $money->format_i18n() );
								} else {
									echo esc_html( $legend_value );
								}

								echo '</strong>';

								echo esc_html( $serie->label );

								?>
							</li>

							<?php // phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Flot data object. ?>

						<?php endforeach; ?>

					</ul>
				</div>

				<div id="chart1" style="height: 500px; width: 100%;"></div>
			</div>
		</div>
	</div>

	<?php require __DIR__ . '/pronamic.php'; ?>
</div>
