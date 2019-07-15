<?php
/**
 * Admin View: Notice - Update webhook URL
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

use Pronamic\WordPress\Pay\Admin\AdminGatewayPostType;

if ( ! defined( 'WPINC' ) ) {
	die;
}

// Get outdated webhook URLs config IDs.
$config_ids = get_transient( 'pronamic_outdated_webhook_urls' );

if ( ! is_array( $config_ids ) ) {
	return;
}

// Build gateways list.
$gateways = array();

foreach ( $config_ids as $config_id ) :
	if ( AdminGatewayPostType::POST_TYPE !== get_post_type( $config_id ) ) {
		continue;
	}

	$gateways[] = sprintf(
		'<a href="%1$s" title="%2$s">%2$s</a>',
		get_edit_post_link( $config_id ),
		get_the_title( $config_id )
	);

endforeach;

// Don't show notice if non of the gateways exists.
if ( empty( $gateways ) ) {
	// Delete transient.
	delete_transient( 'pronamic_outdated_webhook_urls' );

	return;
}

?>
<div class="error">
	<p>
		<strong><?php esc_html_e( 'Pronamic Pay', 'pronamic_ideal' ); ?></strong> —
		<?php

		$message = sprintf(
			/* translators: 1: configuration link(s) */
			_n(
				'The webhook URL to receive automatic payment status updates seems to have changed for the %1$s configuration. Please check your settings.',
				'The webhook URL to receive automatic payment status updates seems to have changed for the %1$s configurations. Please check your settings.',
				count( $config_ids ),
				'pronamic_ideal'
			),
			implode( ', ', $gateways ) // WPCS: xss ok.
		);

		echo wp_kses(
			$message,
			array(
				'a' => array(
					'href'  => true,
					'title' => true,
				),
			)
		);

		?>
	</p>
</div>
