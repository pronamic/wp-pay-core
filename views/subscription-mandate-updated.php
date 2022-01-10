<?php
/**
 * Subscription renew failed.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

?>
<!DOCTYPE html>

<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>" />

		<title><?php esc_html_e( 'Subscription Mandate', 'pronamic_ideal' ); ?></title>

		<?php wp_print_styles( 'pronamic-pay-redirect' ); ?>
	</head>

	<body>
		<div class="pronamic-pay-redirect-page">
			<div class="pronamic-pay-redirect-container alignleft">
				<p>
					<?php esc_html_e( 'The subscription has been updated.', 'pronamic_ideal' ); ?>
				</p>

				<p>
					<a href="<?php echo esc_url( home_url() ); ?>">
						<?php esc_html_e( 'Return to home page', 'pronamic_ideal' ); ?>
					</a>
				</p>
			</div>
		</div>
	</body>
</html>
