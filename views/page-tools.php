<?php
/**
 * Page Tools
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

use Pronamic\WordPress\Pay\Plugin;

$nav_tabs = array(
	'system_status' => __( 'System Status', 'pronamic_ideal' ),
	'gateways'      => __( 'Payment Gateways', 'pronamic_ideal' ),
	'extensions'    => __( 'Extensions', 'pronamic_ideal' ),
);

$current_tab = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING );
$current_tab = empty( $current_tab ) ? key( $nav_tabs ) : $current_tab;

?>

<div class="wrap">
	<nav class="nav-tab-wrapper wp-clearfix" aria-label="<?php esc_attr_e( 'Secondary menu', 'pronamic_ideal' ); ?>">
		<?php

		foreach ( $nav_tabs as $tab_id => $tab_title ) {
			$classes = array( 'nav-tab' );

			if ( $current_tab === $tab_id ) {
				$classes[] = 'nav-tab-active';
			}

			$url = add_query_arg(
				array(
					'page' => 'pronamic_pay_tools',
					'tab'  => $tab_id,
				),
				admin_url( 'admin.php' )
			);

			printf(
				'<a class="nav-tab %s" href="%s">%s</a>',
				esc_attr( implode( ' ', $classes ) ),
				esc_attr( $url ),
				esc_html( $tab_title )
			);
		}

		?>
	</nav>

	<hr class="wp-header-end">

	<?php

	$file = __DIR__ . '/tab-' . $current_tab . '.php';

	if ( is_readable( $file ) ) {
		include $file;
	}

	?>

	<?php require __DIR__ . '/pronamic.php'; ?>
</div>
