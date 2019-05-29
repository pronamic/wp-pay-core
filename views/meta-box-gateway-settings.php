<?php

use Pronamic\WordPress\Pay\Util;

global $gateway_integrations;

$gateway_integrations = $this->plugin->gateway_integrations;

add_filter( 'pronamic_pay_gateway_settings', function( $classes ) {
	global $gateway_integrations;

	foreach ( $gateway_integrations as $integration ) {
		$class = $integration->get_settings_class();

		if ( null === $class ) {
			continue;
		}

		if ( is_array( $class ) ) {
			foreach ( $class as $c ) {
				$classes[ $c ] = $c;
			}
		} else {
			$classes[ $class ] = $class;
		}
	}

	return $classes;
} );

$gateway_settings = new Pronamic\WordPress\Pay\Admin\GatewaySettings();

if ( ! array_key_exists( $gateway_id, $gateway_integrations ) ) {
	return;
}

$sections    = $gateway_settings->get_sections();
$fields      = $gateway_settings->get_fields();
$integration = $gateway_integrations[ $gateway_id ];
$settings    = $integration->get_settings();

$gateway_sections = array();
$gateway_fields   = array();

foreach ( $sections as $id => $section ) {
	if ( array_key_exists( 'methods', $section ) ) {
		$methods = $section['methods'];

		$intersect = array_intersect( $methods, $settings );

		if ( ! empty( $intersect ) ) {
			$section['fields'] = array();

			$gateway_sections[ $id ] = (object) $section;
		}
	}
}

foreach ( $fields as $id => $field ) {
	if ( ! array_key_exists( 'section', $field ) ) {
		continue;
	}

	$section_id = $field['section'];

	if ( ! array_key_exists( $section_id, $gateway_sections ) ) {
		continue;
	}

	$section = $gateway_sections[ $section_id ];

	$section->fields[] = $field;
}

if ( empty( $gateway_sections ) ) {
	return;
}

?>
<div class="pronamic-pay-tabs">
	<ul class="pronamic-pay-tabs-items">
		
		<?php foreach ( $gateway_sections as $section ) : ?>

			<li>
				<?php echo esc_html( $section->title ); ?>
			</li>

		<?php endforeach; ?>

	</ul>

	<?php foreach ( $gateway_sections as $section ) : ?>

		<div class="pronamic-pay-tab">
			<div class="pronamic-pay-tab-block gateway-config-section-header">
				<h4 class="pronamic-pay-cloack"><?php echo esc_html( $section->title ); ?></h4>

				<?php if ( isset( $section->description ) ) : ?>

					<p>
						<?php

						echo $section->description; // WPCS: XSS ok.

						?>
					</p>

				<?php endif; ?>
			</div>

			<table class="form-table">

				<?php foreach ( $section->fields as $field ) :

					$classes = array();
					if ( isset( $field['methods'] ) ) {
						// $classes[] = 'pronamic-pay-cloack';
						// $classes[] = 'extra-settings';

						foreach ( $field['methods'] as $method ) {
							$classes[] = 'setting-' . $method;
						}
					}

					if ( isset( $field['group'] ) ) {
						$classes[] = $field['group'];
					}

					if ( isset( $field['id'] ) ) {
						$id = $field['id'];
					} elseif ( isset( $field['meta_key'] ) ) {
						$id = $field['meta_key'];
					} else {
						$id = uniqid();
					}

					?>
					<tr class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">

						<?php if ( 'html' !== $field['type'] ) { ?>

						<th scope="row">
							<label for="<?php echo esc_attr( $id ); ?>">
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

						<td <?php if ( 'html' === $field['type'] ) : ?>colspan="2"<?php endif; ?>>
						<td>
							<?php

							$field = (array) $field;

							$attributes         = array();
							$attributes['id']   = $id;
							$attributes['name'] = $id;

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
								$value = $field['default'];
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

								echo $field['html']; // WPCS: XSS ok.
							}

							if ( isset( $field['description'] ) ) {
								printf( // WPCS: XSS ok.
									'<p class="pronamic-pay-description description">%s</p>',
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
