<?php
/**
 * Tab Extensions
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

use Pronamic\WordPress\Pay\Plugin;

?><h2><?php esc_html_e( 'Supported extensions', 'pronamic_ideal' ); ?></h2>

<?php

$extensions_json_path = Plugin::$dirname . '/other/extensions.json';

if ( ! file_exists( $extensions_json_path ) ) {
	return;
}

$data = file_get_contents( $extensions_json_path, true );

if ( false === $data ) {
	return;
}

$extensions = json_decode( $data );

?>

<table class="wp-list-table widefat striped">
	<thead>
		<tr>
			<th scope="col">
				<?php esc_html_e( 'Name', 'pronamic_ideal' ); ?>
			</th>
			<th scope="col">
				<?php esc_html_e( 'Author', 'pronamic_ideal' ); ?>
			</th>
			<th scope="col">
				<?php esc_html_e( 'WordPress.org', 'pronamic_ideal' ); ?>
			</th>
			<th scope="col">
				<?php esc_html_e( 'Requires at least', 'pronamic_ideal' ); ?>
			</th>
		</tr>
	</thead>

	<tbody>

		<?php foreach ( $extensions as $extension ) : ?>

			<tr>
				<td>
					<a href="<?php echo \esc_url( $extension->url ); ?>" target="_blank">
						<?php echo esc_html( $extension->name ); ?>
					</a>
				</td>
				<td>
					<?php

					if ( isset( $extension->author, $extension->author_url ) ) {
						printf(
							'<a href="%s" target="_blank">%s</a>',
							esc_attr( $extension->author_url ),
							esc_html( $extension->author )
						);
					}

					?>
				</td>
				<td>
					<?php

					if ( isset( $extension->wp_org_url ) ) {
						printf(
							'<a href="%s" target="_blank">%s</a>',
							esc_attr( $extension->wp_org_url ),
							esc_html( $extension->wp_org_url )
						);
					}

					?>
				</td>
				<td>
					<?php

					if ( isset( $extension->requires_at_least ) ) {
						echo esc_html( $extension->requires_at_least );
					}

					?>
				</td>
			</tr>

		<?php endforeach; ?>

	</tbody>
</table>
