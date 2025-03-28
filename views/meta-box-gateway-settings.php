<?php
/**
 * Meta Box Gateway Settings
 *
 * @author Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license GPL-3.0-or-later
 * @package Pronamic\WordPress\Pay
 * @var \Pronamic\WordPress\Pay\Plugin       $plugin     Plugin.
 * @var string                               $gateway_id Gateway ID.
 * @var int                                  $config_id  Configuration ID.
 * @var \Pronamic\WordPress\Pay\Core\Gateway $gateway    Gateway.
 */

use Pronamic\WordPress\Html\Element;
use Pronamic\WordPress\Pay\Admin\AdminGatewayPostType;
use Pronamic\WordPress\Pay\Util;
use Pronamic\WordPress\Pay\Webhooks\WebhookRequestInfo;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$integration = $plugin->gateway_integrations->get_integration( $gateway_id );

if ( null === $integration ) {
	return;
}

$fields = $integration->get_settings_fields();

$sections = [
	'general'         => (object) [
		'title'  => __( 'General', 'pronamic_ideal' ),
		'fields' => [],
	],
	'advanced'        => (object) [
		'title'  => __( 'Advanced', 'pronamic_ideal' ),
		'fields' => [],
	],
	'feedback'        => (object) [
		'title'  => __( 'Feedback', 'pronamic_ideal' ),
		'fields' => [],
	],
	'payment_methods' => (object) [
		'title'  => __( 'Payment Methods', 'pronamic_ideal' ),
		'fields' => [
			[
				'section'  => 'payment_methods',
				'title'    => __( 'Supported Payment Methods', 'pronamic_ideal' ),
				'type'     => 'custom',
				'callback' => function () use ( $gateway, $gateway_id ): void {
					AdminGatewayPostType::settings_payment_methods( $gateway, $gateway_id );
				},
			],
		],
	],
];

// Feedback.
if ( $integration->supports( 'webhook' ) ) {
	$fields[] = [
		'section'  => 'feedback',
		'title'    => __( 'Webhook Status', 'pronamic_ideal' ),
		'type'     => 'custom',
		'callback' => function () use ( $gateway, $gateway_id, $config_id ): void {
			AdminGatewayPostType::settings_webhook_log( $gateway, $gateway_id, $config_id );
		},
	];
}

// Check if webhook configuration is needed.
if ( $integration->supports( 'webhook' ) && ! $integration->supports( 'webhook_no_config' ) ) {
	$webhook_config_needed = true;

	$log = get_post_meta( $config_id, '_pronamic_gateway_webhook_log', true );

	if ( ! empty( $log ) ) {
		$log = json_decode( $log );

		$request_info = WebhookRequestInfo::from_json( $log );

		// Validate log request URL against current home URL.
		if ( str_starts_with( $request_info->get_request_url(), home_url( '/' ) ) ) {
			$webhook_config_needed = false;
		}
	}

	if ( $webhook_config_needed ) {
		$sections['feedback']->title = sprintf(
			'⚠️ %s',
			$sections['feedback']->title
		);

		$fields[] = [
			'section'  => 'general',
			'title'    => __( 'Transaction feedback', 'pronamic_ideal' ),
			'type'     => 'custom',
			'callback' => function (): void {
				printf(
					'⚠️ %s',
					esc_html__(
						'Processing gateway transaction feedback in the background requires additional configuration.',
						'pronamic_ideal'
					)
				);
			},
		];
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
	function ( $section ) {
		return ! empty( $section->fields );
	}
);

?>
<div class="pronamic-pay-tabs">
	<ul class="pronamic-pay-tabs-items">

		<?php foreach ( $sections as $section ) : ?>

			<li>
				<?php echo esc_html( $section->title ); ?>
			</li>

		<?php endforeach; ?>

	</ul>

	<?php foreach ( $sections as $section ) : ?>

		<div class="pronamic-pay-tab">
			<div class="pronamic-pay-tab-block gateway-config-section-header">
				<h4 class="pronamic-pay-cloak"><?php echo esc_html( $section->title ); ?></h4>
			</div>

			<table class="form-table">

				<?php

				foreach ( $section->fields as $field ) :

					$classes = [];

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
						<th scope="row">
							<?php

							if ( array_key_exists( 'title', $field ) ) {
								printf(
									'<label for="%s">%s</label>',
									esc_attr( $field_id ),
									esc_html( $field['title'] )
								);
							}

							if ( isset( $field['tooltip'] ) && ! empty( $field['tooltip'] ) ) {
								printf(
									'<span class="dashicons dashicons-editor-help pronamic-pay-tip" title="%s"></span>',
									esc_attr( $field['tooltip'] )
								);
							}

							?>
						</th>
						<td>
							<?php

							$field = (array) $field;

							$attributes         = [];
							$attributes['id']   = $field_id;
							$attributes['name'] = $field_id;

							$classes = [];
							if ( isset( $field['classes'] ) ) {
								$classes = $field['classes'];
							}

							if ( isset( $field['readonly'] ) && $field['readonly'] ) {
								$attributes['readonly'] = 'readonly';

								$classes[] = 'readonly';
							}

							if ( isset( $field['required'] ) && $field['required'] ) {
								$attributes['required'] = 'required';
							}

							if ( isset( $field['size'] ) ) {
								$attributes['size'] = $field['size'];
							}

							if ( in_array( $field['type'], [ 'text', 'password', 'textarea', 'select' ], true ) ) {
								$classes[] = 'pronamic-pay-form-control';
							}

							if ( in_array( $field['type'], [ 'textarea' ], true ) ) {
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
								$default = $field['default'];

								/**
								 * An empty value can also be an empty string, this
								 * should not always be overwritten with the default
								 * value. Therefore we check if there is any kind of
								 * meta.
								 *
								 * @link https://developer.wordpress.org/reference/functions/get_post_meta/
								 */
								$meta = get_post_meta( $config_id, $field['meta_key'], false );

								if ( empty( $meta ) ) {
									$value = \is_callable( $default ) ? call_user_func( $default, $config_id ) : $default;
								}
							}

							switch ( $field['type'] ) {
								case 'text':
								case 'password':
									$attributes['type']  = $field['type'];
									$attributes['value'] = $value;

									$element = new Element( 'input', $attributes );

									$element->output();

									break;
								case 'number':
									$attributes['type']  = $field['type'];
									$attributes['value'] = $value;

									if ( isset( $field['min'] ) ) {
										$attributes['min'] = $field['min'];
									}

									if ( isset( $field['max'] ) ) {
										$attributes['max'] = $field['max'];
									}

									$element = new Element( 'input', $attributes );

									$element->output();

									break;
								case 'checkbox':
									$attributes['type']  = $field['type'];
									$attributes['value'] = '1';

									/**
									 * Unchecked HTML checkboxes are not part of an HTML form POST request.
									 * Should the settings API delete settings that are not posted, or should
									 * it set the value to false? We simplify this by adding an hidden HTML
									 * input with `0` value. If the checkbox is checked it will post two
									 * values under the same name. PHP will work with the last occurrence.
									 *
									 * @link https://stackoverflow.com/questions/1746507/authoritative-position-of-duplicate-http-get-query-keys#8971514
									 */
									printf(
										'<input type="hidden" name="%s" value="0" />',
										\esc_attr( $field_id )
									);

									if ( true === (bool) $value ) {
										$attributes['checked'] = 'checked';
									}

									$element = new Element( 'input', $attributes );

									$element->output();

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

									$element = new Element( 'textarea', $attributes );

									$element->children[] = $value;

									$element->output();

									break;
								case 'file':
									$attributes['type'] = 'file';

									$element = new Element( 'input', $attributes );

									$element->output();

									break;
								case 'select':
									$element = new Element( 'select', $attributes );

									foreach ( $field['options'] as $key => $label ) {
										$option = new Element( 'option', [ 'value' => $key ] );

										$option->children[] = $label;

										if ( $value === (string) $key ) {
											$option->attributes['selected'] = 'selected';
										}

										$element->children[] = $option;
									}

									$element->output();

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

							if ( isset( $field['description'] ) ) {
								printf(
									'<p class="pronamic-pay-description description">%s</p>',
									\wp_kses(
										$field['description'],
										[
											'a'    => [
												'href'   => true,
												'target' => true,
											],
											'br'   => [],
											'code' => [],
										]
									)
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
