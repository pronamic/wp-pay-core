<?php
/**
 * Subscription renew.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

?>
<!DOCTYPE html>

<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>" />

		<title><?php esc_html_e( 'Subscription Renewal', 'pronamic_ideal' ); ?></title>

		<?php wp_print_styles( 'pronamic-pay-redirect' ); ?>
	</head>

	<body>
		<div class="pronamic-pay-redirect-page">
			<div class="pronamic-pay-redirect-container alignleft">
				<form id="pronamic_ideal_form" name="pronamic_ideal_form" method="post">
					<h1><?php esc_html_e( 'Subscription Renewal', 'pronamic_ideal' ); ?></h1>

					<p>
						<?php

						sprintf(
							/* translators: %s: expiry date */
							__( 'The subscription epxires at %s.', 'pronamic_ideal' ),
							$subscription->get_expiry_date()->format_i18n()
						);

						?>
					</p>

					<hr />

					<dl>
						<?php

						$interval = $subscription->get_interval();

						if ( null !== $interval ) :
							?>

							<dt>
								<?php esc_html_e( 'Subscription Length:', 'pronamic_ideal' ); ?>
							</dt>
							<dd>
								<?php

								switch ( $subscription->get_interval_period() ) {
									case 'D':
										echo esc_html(
											sprintf(
												/* translators: %s: interval */
												_n( '%s day', '%s days', $interval, 'pronamic_ideal' ),
												number_format_i18n( $interval )
											)
										);

										break;
									case 'W':
										echo esc_html(
											sprintf(
												/* translators: %s: interval */
												_n( '%s week', '%s weeks', $interval, 'pronamic_ideal' ),
												number_format_i18n( $interval )
											)
										);

										break;
									case 'M':
										echo esc_html(
											sprintf(
												/* translators: %s: interval */
												_n( '%s month', '%s months', $interval, 'pronamic_ideal' ),
												number_format_i18n( $interval )
											)
										);

										break;
									case 'Y':
										echo esc_html(
											sprintf(
												/* translators: %s: interval */
												_n( '%s year', '%s years', $interval, 'pronamic_ideal' ),
												number_format_i18n( $interval )
											)
										);

										break;
								}

								?>
							</dd>

						<?php endif; ?>

						<dt>
							<?php esc_html_e( 'Amount:', 'pronamic_ideal' ); ?>
						</dt>
						<dd>
							<?php echo esc_html( $subscription->get_total_amount()->format_i18n() ); ?>
						</dd>
					</dl>

					<?php

					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Complex input HTML.
					echo $gateway->get_input_html();

					?>
				</form>
			</div>
		</div>
	</body>
</html>
