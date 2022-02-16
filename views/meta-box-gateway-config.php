<?php
/**
 * Meta Box Gateway Config
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 * @var \Pronamic\WordPress\Pay\Plugin $plugin Plugin.
 * @var \WP_Post                       $post   Post.
 */

use Pronamic\WordPress\Pay\Util;

$integrations = iterator_to_array( $plugin->gateway_integrations );

usort(
	$integrations,
	function( $integration_a, $integration_b ) {
		return strcasecmp( $integration_a->get_name(), $integration_b->get_name() );
	}
);

// Sections.
$config_id = $post->ID;

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
						if ( null !== $integration->get_product_url() ) {
							$links[] = sprintf(
								'<a href="%s" target="_blank" title="%s">%2$s</a>',
								$integration->get_product_url(),
								__( 'Product information', 'pronamic_ideal' )
							);
						}

						// Manual URL.
						$manual_url = $integration->get_manual_url();

						if ( null !== $manual_url ) {
							$links[] = sprintf(
								'<a href="%s" target="_blank" title="%s">%s</a>',
								\esc_url( $plugin->tracking_module->get_tracking_url( $manual_url ) ),
								\esc_attr__( 'Manual', 'pronamic_ideal' ),
								\esc_html__( 'Manual', 'pronamic_ideal' )
							);
						}

						$description = implode( ' | ', $links );

						printf(
							'<option data-gateway-description="%s" data-pronamic-pay-settings="%s" value="%s" %s class="%s">%s</option>',
							esc_attr( $description ),
							esc_attr( (string) wp_json_encode( $integration->get_settings() ) ),
							esc_attr( (string) $integration_id ),
							selected( $gateway_id, $integration_id, false ),
							esc_attr( implode( ' ', $classes ) ),
							esc_attr( (string) $name )
						);
					}

					?>
				</select>

				<p id="pronamic-pay-gateway-description"></p>
			</td>
		</tr>
	</table>
</div>

<div id="pronamic-pay-gateway-settings" style="padding-top: 5px;">

	<?php require __DIR__ . '/meta-box-gateway-settings.php'; ?>

</div>
