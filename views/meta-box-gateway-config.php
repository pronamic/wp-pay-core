<?php
/**
 * Meta Box Gateway Config
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

use Pronamic\WordPress\Pay\Util;

$integrations = iterator_to_array( $this->plugin->gateway_integrations );

usort(
	$integrations,
	function( $integration_a, $integration_b ) {
		return strcasecmp( $integration_a->get_name(), $integration_b->get_name() );
	}
);

// Sections.
$config_id = get_the_ID();

$gateway_id = get_post_meta( $config_id, '_pronamic_gateway_id', true );

?>
<div id="pronamic-pay-gateway-config-editor">
	<table class="form-table">
		<tr>
			<th scope="row">
				<label for="pronamic_gateway_id">
					<?php esc_html_e( 'Payment provider', 'pronamic_ideal' ); ?>
				</label>
			</th>
			<td>
				<select id="pronamic_gateway_id" name="_pronamic_gateway_id">
					<option value=""></option>

					<?php

					foreach ( $integrations as $integration ) {
						$integration_id = $integration->get_id();
						$name           = $integration->get_name();
						$classes        = array();
						$description    = '';
						$links          = array();

						if ( isset( $integration->deprecated ) && $integration->deprecated ) {
							$classes[] = 'deprecated';

							/* translators: %s: Integration name */
							$name = sprintf( __( '%s (obsoleted)', 'pronamic_ideal' ), $name );

							if ( $gateway_id !== $integration_id ) {
								continue;
							}
						}

						// Dashboard links.
						$dashboards = $integration->get_dashboard_url();

						if ( 1 === count( $dashboards ) ) {
							$links[] = sprintf(
								'<a href="%s" title="%s">%2$s</a>',
								esc_attr( $dashboards[0] ),
								__( 'Dashboard', 'pronamic_ideal' )
							);
						} elseif ( count( $dashboards ) > 1 ) {
							$dashboard_urls = array();

							foreach ( $dashboards as $dashboard_name => $dashboard_url ) {
								$dashboard_urls[] = sprintf(
									'<a href="%s" title="%s">%2$s</a>',
									esc_attr( $dashboard_url ),
									esc_html( ucfirst( $dashboard_name ) )
								);
							}

							$links[] = sprintf(
								'%s: %s',
								__( 'Dashboards', 'pronamic_ideal' ),
								strtolower( implode( ', ', $dashboard_urls ) )
							);
						}

						// Product link.
						if ( $integration->get_product_url() ) {
							$links[] = sprintf(
								'<a href="%s" target="_blank" title="%s">%2$s</a>',
								$integration->get_product_url(),
								__( 'Product information', 'pronamic_ideal' )
							);
						}

						$description = implode( ' | ', $links );

						printf(
							'<option data-gateway-description="%s" data-pronamic-pay-settings="%s" value="%s" %s class="%s">%s</option>',
							esc_attr( $description ),
							esc_attr( wp_json_encode( $integration->get_settings() ) ),
							esc_attr( $integration_id ),
							selected( $gateway_id, $integration_id, false ),
							esc_attr( implode( ' ', $classes ) ),
							esc_attr( $name )
						);
					}

					?>
				</select>

				<p id="pronamic-pay-gateway-description"></p>
			</td>
		</tr>
		<tr class="">
			<th scope="row">
				<label for="pronamic_ideal_mode">
					Modus
				</label>
			</th>
			<td>
				<?php

				$attributes = array(
					'id'    => 'pronamic_ideal_mode',
					'name'  => '_pronamic_gateway_mode',
					'class' => 'pronamic-pay-form-control',
				);

				$options = array(
					array(
						'options' => array(
							'test' => __( 'Test', 'pronamic_ideal' ),
							'live' => __( 'Live', 'pronamic_ideal' ),
						),
					),
				);

				$value = get_post_meta( get_the_ID(), '_pronamic_gateway_mode', true );

				printf(
					'<select %s>%s</select>',
					// @codingStandardsIgnoreStart
					Util::array_to_html_attributes( $attributes ),
					Util::select_options_grouped( $options, $value )
					// @codingStandardsIgnoreEnd
				);

				?>
			</td>
		</tr>
	</table>
</div>

<div id="pronamic-pay-gateway-settings" style="padding-top: 5px;">

	<?php require 'meta-box-gateway-settings.php'; ?>

</div>
