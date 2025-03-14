<?php
/**
 * Redirect via HTML
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 * @var       \Pronamic\WordPress\Pay\Payments\Payment $payment Payment.
 * @var       \Pronamic\WordPress\Pay\Core\Gateway     $this    Gateway.
 */

use Pronamic\WordPress\Html\Element;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<!DOCTYPE html>

<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>" />

		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<title><?php esc_html_e( 'Redirecting…', 'pronamic_ideal' ); ?></title>

		<?php wp_print_styles( 'pronamic-pay-redirect' ); ?>

		<?php

		/**
		 * Break out of iframe.
		 *
		 * @link https://github.com/pronamic/wp-pronamic-pay-give/issues/2
		 * @link https://github.com/pronamic/wp-pronamic-pay/commit/6936ec048c6778e688386d3c15f6a6c1cbaa8eb9
		 */
		$element = new Element(
			'script',
			[
				'type' => 'text/javascript',
			]
		);

		$element->children[] = 'if ( window.top.location !== window.location ) { window.top.location = window.location; }';

		$element->output();

		?>

	</head>

	<body>
		<div class="pronamic-pay-redirect-page">
			<div class="pronamic-pay-redirect-container">
				<h1><?php esc_html_e( 'Redirecting…', 'pronamic_ideal' ); ?></h1>

				<p>
					<?php esc_html_e( 'You will be automatically redirected to the online payment environment.', 'pronamic_ideal' ); ?>
				</p>

				<div class="pp-page-section-container">
					<div class="pp-page-section-wrapper">
						<p>
							<?php esc_html_e( 'Please click the button below if you are not automatically redirected.', 'pronamic_ideal' ); ?>
						</p>

						<?php $this->output_form( $payment ); ?>
					</div>
				</div>

				<div class="pp-page-section-container">
					<div class="pp-page-section-wrapper alignleft">
						<h2><?php esc_html_e( 'Payment', 'pronamic_ideal' ); ?></h2>

						<dl>
							<dt><?php esc_html_e( 'Date', 'pronamic_ideal' ); ?></dt>
							<dd><?php echo esc_html( $payment->get_date()->format_i18n() ); ?></dd>

							<?php $transaction_id = $payment->get_transaction_id(); ?>

							<?php if ( ! empty( $transaction_id ) ) : ?>

								<dt><?php esc_html_e( 'Transaction ID', 'pronamic_ideal' ); ?></dt>
								<dd><?php echo esc_html( $transaction_id ); ?></dd>

							<?php endif; ?>

							<dt><?php esc_html_e( 'Description', 'pronamic_ideal' ); ?></dt>
							<dd><?php echo esc_html( (string) $payment->get_description() ); ?></dd>

							<dt><?php esc_html_e( 'Amount', 'pronamic_ideal' ); ?></dt>
							<dd><?php echo esc_html( $payment->get_total_amount()->format_i18n() ); ?></dd>
						</dl>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>
