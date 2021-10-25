<?php
/**
 * Page Debug
 *
 * @author Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license GPL-3.0-or-later
 * @package Pronamic\WordPress\Pay
 * @var \Pronamic\WordPress\Pay\Plugin $plugin Plugin.
 */

$tools_manager = $plugin->tools_manager;

$tools = $tools_manager->get_tools();

$tool = $tools_manager->get_tool( $tools_manager->get_current_action() );

if ( null !== $tool ) {
	require_once __DIR__ . '/page-debug-scheduler.php';

	return;
}

?>

<div class="wrap pronamic-pay-debug">
	<h1 class="wp-heading-inline"><?php echo \esc_html( \get_admin_page_title() ); ?></h1>

	<hr class="wp-header-end">

	<h2><?php \esc_html_e( 'Tools', 'pronamic_ideal' ); ?></h2>

	<table class="widefat">

		<?php foreach ( $tools as $tool ) : ?>

			<tr>
				<td>
					<strong><?php echo \esc_html( $tool->title ); ?></strong><br />
					<?php echo \esc_html( $tool->description ); ?>
				</td>
				<td>
					<?php

					$url = wp_nonce_url(
						\add_query_arg(
							array(
								'page'                => 'pronamic_pay_debug',
								'pronamic_pay_action' => $tool->action,
							),
							\admin_url( 'admin.php' )
						),
						$tool->action,
						'pronamic_pay_nonce'
					);

					\printf(
						'<a href="%s" class="button button-large">%s</a>',
						\esc_url( $url ),
						\esc_html( $tool->label )
					);

					?>
				</td>
			</tr>

		<?php endforeach; ?>

	</table>

	<?php require __DIR__ . '/pronamic.php'; ?>
</div>
