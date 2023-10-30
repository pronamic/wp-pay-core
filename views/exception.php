<?php
/**
 * Exception
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 * @var       \Exception $exception Exception.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="error">
	<dl>
		<dt><?php esc_html_e( 'Message', 'pronamic_ideal' ); ?></dt>
		<dd><?php echo esc_html( $exception->getMessage() ); ?></dd>

		<?php if ( 0 !== $exception->getCode() ) : ?>

			<dt><?php esc_html_e( 'Code', 'pronamic_ideal' ); ?></dt>
			<dd><?php echo esc_html( $exception->getCode() ); ?></dd>

		<?php endif; ?>

		<?php if ( pronamic_pay_plugin()->is_debug_mode() && current_user_can( 'manage_options' ) ) : ?>

			<dt><?php esc_html_e( 'Trace', 'pronamic_ideal' ); ?></dt>
			<dd>
				<?php

				echo '<pre>';
				echo esc_html( $exception->getTraceAsString() );
				echo '</pre>';

				?>
			</dd>

		<?php endif; ?>
	</dl>
</div>

<?php

$exception = $exception->getPrevious();

if ( null !== $exception ) {
	require __FILE__;
}
