<?php
/**
 * Admin View: Notice - License
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! isset( $data ) ) {
	return;
}

$class = ( 'valid' === $data->license ) ? 'updated' : 'error';

?>
<div class="<?php echo esc_attr( $class ); ?>">
	<p>
		<strong><?php esc_html_e( 'Pronamic Pay', 'pronamic_ideal' ); ?></strong> —
		<?php

		if ( 'valid' === $data->license ) {
			echo \esc_html(
				\sprintf(
					/* translators: %s: Pronamic Pay */
					\__( 'Thank you for activating your license and using the %s plugin.', 'pronamic_ideal' ),
					\__( 'Pronamic Pay', 'pronamic_ideal' )
				)
			);
		} elseif ( 'invalid' === $data->license && \property_exists( $data, 'activations_left' ) && 0 === $data->activations_left ) {
			echo \wp_kses(
				__( 'This license does not have any activations left. Maybe you have to deactivate your license on a local/staging server. This can be done on your <a href="https://www.pronamic.eu/" target="_blank">Pronamic.eu account</a>.', 'pronamic_ideal' ),
				array(
					'a' => array(
						'href'   => true,
						'target' => true,
					),
				)
			);
		} else {
			\esc_html_e( 'There was a problem activating your license key, please try again or contact support.', 'pronamic_ideal' );
		}

		?>
	</p>
</div>
