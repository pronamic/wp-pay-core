<?php
/**
 * Meta Box Gateway Settings
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

use Pronamic\WordPress\Pay\Admin\AdminGatewayPostType;
use Pronamic\WordPress\Pay\Util;
use Pronamic\WordPress\Pay\Webhooks\WebhookManager;
use Pronamic\WordPress\Pay\Webhooks\WebhookRequestInfo;

$integration = $this->plugin->gateway_integrations->get_integration( $gateway_id );

if ( null === $integration ) {
	return;
}

$fields = $integration->get_settings_fields();

$sections = array(
	'general'         => (object) array(
		'title'  => __( 'General', 'pronamic_ideal' ),
		'fields' => array(),
	),
	'advanced'        => (object) array(
		'title'  => __( 'Advanced', 'pronamic_ideal' ),
		'fields' => array(),
	),
	'feedback'        => (object) array(
		'title'  => __( 'Feedback', 'pronamic_ideal' ),
		'fields' => array(),
	),
	'payment_methods' => (object) array(
		'title'  => __( 'Payment Methods', 'pronamic_ideal' ),
		'fields' => array(
			array(
				'section'  => 'payment_methods',
				'title'    => __( 'Supported Payment Methods', 'pronamic_ideal' ),
				'type'     => 'html',
				'callback' => function() use ( $gateway ) {
					AdminGatewayPostType::settings_payment_methods( $gateway );
				},
			),
		),
	),
);

// Feedback.
if ( $integration->supports( 'webhook' ) ) {
	$fields[] = array(
		'section'  => 'feedback',
		'title'    => __( 'Webhook Status', 'pronamic_ideal' ),
		'type'     => 'description',
		'callback' => function() use ( $gateway, $gateway_id, $config_id ) {
			AdminGatewayPostType::settings_webhook_log( $gateway, $gateway_id, $config_id );
		},
	);
}

// Check if webhook configuration is needed.
if ( $integration->supports( 'webhook' ) && ! $integration->supports( 'webhook_no_config' ) ) {
	$webbhook_config_needed = true;

	$log = get_post_meta( $config_id, '_pronamic_gateway_webhook_log', true );

	if ( ! empty( $log ) ) {
		$log = json_decode( $log );

		$request_info = WebhookRequestInfo::from_json( $log );

		// Validate log request URL against current home URL.
		if ( WebhookManager::validate_request_url( $request_info ) ) {
			$webbhook_config_needed = false;
		}
	}

	if ( $webbhook_config_needed ) {
		$sections['feedback']->title = sprintf(
			'⚠️ %s',
			$sections['feedback']->title
		);

		$fields[] = array(
			'section' => 'general',
			'title'   => __( 'Transaction feedback', 'pronamic_ideal' ),
			'type'    => 'description',
			'html'    => sprintf(
				'⚠️ %s',
				__(
					'Processing gateway transaction feedback in the background requires additional configuration.',
					'pronamic_ideal'
				)
			),
		);
	}
}

// Sections.
foreach ( $fields as $field_id => $field ) {
	$section = 'general';

	if ( array_key_exists( 'section', $field ) ) {
		$section = $field['section'];
	}

	if ( ! array_key_exists( $section, $sections ) ) {
		$section = 'general';
	}

	$sections[ $section ]->fields[] = $field;
}

$sections = array_filter(
	$sections,
	function( $section ) {
		return ! empty( $section->fields );
	}
);

?>
<div class="pronamic-pay-tabs">
	<ul class="pronamic-pay-tabs-items">

		<?php foreach ( $sections as $section ) : ?>

			<li>
				<?php

				if ( isset( $section->icon ) ) {
					printf(
						'<span class="%s"></span>',
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						$section->icon
					);

					echo ' ';
				}

				echo esc_html( $section->title );

				?>
			</li>

		<?php endforeach; ?>

	</ul>

	<?php foreach ( $sections as $section ) : ?>

		<div class="pronamic-pay-tab">
			<div class="pronamic-pay-tab-block gateway-config-section-header">
				<h4 class="pronamic-pay-cloack"><?php echo esc_html( $section->title ); ?></h4>

				<?php if ( isset( $section->description ) ) : ?>

					<p>
						<?php

						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $section->description;

						?>
					</p>

				<?php endif; ?>
			</div>

			<table class="form-table">

				<?php

				foreach ( $section->fields as $field ) :

					$classes = array();

					if ( isset( $field['methods'] ) ) {
						foreach ( $field['methods'] as $method ) {
							$classes[] = 'setting-' . $method;
						}
					}

					if ( isset( $field['group'] ) ) {
						$classes[] = $field['group'];
					}

					if ( isset( $field['id'] ) ) {
						$field_id = $field['id'];
					} elseif ( isset( $field['meta_key'] ) ) {
						$field_id = $field['meta_key'];
					} else {
						$field_id = uniqid();
					}

					?>
					<tr class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">

						<?php if ( 'html' !== $field['type'] ) { ?>

						<th scope="row">
							<label for="<?php echo esc_attr( $field_id ); ?>">
								<?php echo esc_html( $field['title'] ); ?>
							</label>

							<?php

							if ( isset( $field['tooltip'] ) && ! empty( $field['tooltip'] ) ) {
								printf(
									'<span class="dashicons dashicons-editor-help pronamic-pay-tip" title="%s"></span>',
									esc_attr( $field['tooltip'] )
								);
							}

							?>
						</th>

						<?php } ?>

						<td 
						<?php
						if ( 'html' === $field['type'] ) :
							?>
							colspan="2"<?php endif; ?>>
							<?php

							$field = (array) $field;

							$attributes         = array();
							$attributes['id']   = $field_id;
							$attributes['name'] = $field_id;

							$classes = array();
							if ( isset( $field['classes'] ) ) {
								$classes = $field['classes'];
							}

							if ( isset( $field['readonly'] ) && $field['readonly'] ) {
								$attributes['readonly'] = 'readonly';

								$classes[] = 'readonly';
							}

							if ( isset( $field['size'] ) ) {
								$attributes['size'] = $field['size'];
							}

							if ( in_array( $field['type'], array( 'text', 'password', 'textarea', 'select' ), true ) ) {
								$classes[] = 'pronamic-pay-form-control';
							}

							if ( in_array( $field['type'], array( 'textarea' ), true ) ) {
								$classes[] = 'pronamic-pay-form-control-lg';
							}

							if ( ! empty( $classes ) ) {
								$attributes['class'] = implode( ' ', $classes );
							}

							$value = '';
							if ( isset( $field['meta_key'] ) ) {
								$attributes['name'] = $field['meta_key'];

								$value = get_post_meta( $config_id, $field['meta_key'], true );
							} elseif ( isset( $field['value'] ) ) {
								$value = $field['value'];
							}

							// Set default.
							if ( empty( $value ) && isset( $field['default'] ) ) {
								/**
								 * An empty value can also be an empty string, this
								 * should not always be overwritten with the default
								 * value. Therefor we check if there is anykind of
								 * meta.
								 *
								 * @link https://developer.wordpress.org/reference/functions/get_post_meta/
								 */
								$meta = get_post_meta( $config_id, $field['meta_key'], false );

								if ( empty( $meta ) ) {
									$value = $field['default'];
								}
							}

							switch ( $field['type'] ) {
								case 'text':
								case 'password':
									$attributes['type']  = $field['type'];
									$attributes['value'] = $value;

									printf(
										'<input %s />',
										// @codingStandardsIgnoreStart
										Util::array_to_html_attributes( $attributes )
										// @codingStandardsIgnoreEnd
									);

									break;
								case 'checkbox':
									$attributes['type']  = $field['type'];
									$attributes['value'] = '1';

									printf(
										'<input %s %s />',
										// @codingStandardsIgnoreStart
										Util::array_to_html_attributes( $attributes ),
										// @codingStandardsIgnoreEnd
										checked( $value, true, false )
									);

									printf( ' ' );

									printf(
										'<label for="%s">%s</label>',
										esc_attr( $attributes['id'] ),
										esc_html( $field['label'] )
									);

									break;
								case 'textarea':
									$attributes['rows'] = 4;
									$attributes['cols'] = 65;

									printf(
										'<textarea %s>%s</textarea>',
										// @codingStandardsIgnoreStart
										Util::array_to_html_attributes( $attributes ),
										// @codingStandardsIgnoreEnd
										esc_textarea( $value )
									);

									break;
								case 'file':
									$attributes['type'] = 'file';

									printf(
										'<input %s />',
										// @codingStandardsIgnoreStart
										Util::array_to_html_attributes( $attributes )
										// @codingStandardsIgnoreEnd
									);

									break;
								case 'select':
									printf(
										'<select %s>%s</select>',
										// @codingStandardsIgnoreStart
										Util::array_to_html_attributes( $attributes ),
										Util::select_options_grouped( $field['options'], $value )
										// @codingStandardsIgnoreEnd
									);

									break;
								case 'optgroup':
									printf( '<fieldset>' );
									printf( '<legend class="screen-reader-text">%s</legend>', esc_html( $field['title'] ) );

									foreach ( $field['options'] as $key => $label ) {
										printf(
											'<label>%s %s</label><br />',
											sprintf(
												'<input type="radio" value="%s" name="%s" %s />',
												esc_attr( $key ),
												esc_attr( $attributes['name'] ),
												checked( $value, $key, false )
											),
											esc_html( $label )
										);
									}

									break;
							}

							if ( isset( $field['html'] ) ) {
								if ( 'description' !== $field['type'] && isset( $field['title'] ) && ! empty( $field['title'] ) ) {
									printf(
										'<strong>%s</strong><br>',
										esc_html( $field['title'] )
									);
								}

								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo $field['html'];
							}

							if ( isset( $field['description'] ) ) {
								printf(
									'<p class="pronamic-pay-description description">%s</p>',
									// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									$field['description']
								);
							}

							if ( isset( $field['callback'] ) ) {
								$callback = $field['callback'];

								call_user_func( $callback, $field );
							}

							?>
						</td>
					</tr>

				<?php endforeach; ?>

			</table>
		</div>

	<?php endforeach; ?>

</div>
