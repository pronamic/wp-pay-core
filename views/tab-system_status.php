<?php
/**
 * Tab System Status
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\DateTime\DateTimeZone;

?>
<table class="pronamic-pay-table pronamic-pay-status-table widefat">
	<thead>
		<tr>
			<th colspan="3"><?php esc_html_e( 'License', 'pronamic_ideal' ); ?></th>
		</tr>
	</thead>

	<tbody>
		<tr>
			<th scope="row">
				<?php esc_html_e( 'License Status', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php

				$license_status = get_option( 'pronamic_pay_license_status' );

				switch ( $license_status ) {
					case 'valid':
						esc_html_e( 'Valid', 'pronamic_ideal' );

						break;
					case 'invalid':
						esc_html_e( 'Invalid', 'pronamic_ideal' );

						break;
					case 'site_inactive':
						esc_html_e( 'Site Inactive', 'pronamic_ideal' );

						break;
					default:
						if ( \is_string( $license_status ) ) {
							echo \esc_html( $license_status );
						}

						break;
				}

				?>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<?php esc_html_e( 'Next License Check', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php

				$timestamp = wp_next_scheduled( 'pronamic_pay_license_check' );

				if ( false !== $timestamp ) {
					$date = new DateTime( '@' . $timestamp, new DateTimeZone( 'UTC' ) );

					echo esc_html( $date->format_i18n() );
				} else {
					esc_html_e( 'Not scheduled', 'pronamic_ideal' );
				}

				?>
			</td>
		</tr>
	</tbody>
</table>

<table class="pronamic-pay-table pronamic-pay-status-table widefat striped">
	<thead>
		<tr>
			<th colspan="3"><?php esc_html_e( 'WordPress Environment', 'pronamic_ideal' ); ?></th>
		</tr>
	</thead>

	<tbody>
		<tr>
			<th scope="row">
				<?php esc_html_e( 'Site URL', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php echo esc_html( site_url() ); ?>
			</td>
			<td>
				✓
			</td>
		</tr>
		<tr>
			<th scope="row">
				<?php esc_html_e( 'Home URL', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php echo esc_html( home_url() ); ?>
			</td>
			<td>
				✓
			</td>
		</tr>
		<tr>
			<th scope="row">
				<?php \esc_html_e( 'PHP Version', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php echo \esc_html( (string) \phpversion() ); ?>
			</td>
			<td>
				<?php

				$php_version = \phpversion();

				if ( false !== $php_version ) {
					if ( \version_compare( $php_version, '5.2', '>' ) ) {
						echo '✓';
					} else {
						esc_html_e( 'Pronamic Pay requires PHP 5.2 or above.', 'pronamic_ideal' );
					}
				}

				?>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<?php esc_html_e( 'MySQL Version', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php

				global $wpdb;

				echo esc_html( $wpdb->db_version() );

				?>
			</td>
			<td>
				<?php

				if ( version_compare( $wpdb->db_version(), '5', '>' ) ) {
					echo '✓';
				} else {
					esc_html_e( 'Pronamic Pay requires MySQL 5 or above.', 'pronamic_ideal' );
				}

				?>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<?php esc_html_e( 'WordPress Version', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php echo esc_html( get_bloginfo( 'version' ) ); ?>
			</td>
			<td>
				<?php

				if ( version_compare( get_bloginfo( 'version' ), '3.2', '>' ) ) {
					echo '✓';
				} else {
					esc_html_e( 'Pronamic Pay requires WordPress 3.2 or above.', 'pronamic_ideal' );
				}

				?>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<?php esc_html_e( 'WP Memory Limit', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php

				$memory = pronamic_pay_let_to_num( WP_MEMORY_LIMIT );

				$memory_formatted = size_format( $memory );

				if ( false === $memory_formatted ) {
					echo \esc_html( (string) $memory );
				}

				if ( false !== $memory_formatted ) {
					echo \esc_html( $memory_formatted );
				}

				?>
			</td>
			<td>
				<?php

				if ( $memory >= 67108864 ) { // 64 MB
					echo '✓';
				} else {
					echo wp_kses(
						sprintf(
							/* translators: %s: WordPress Codex link */
							__( 'We recommend setting memory to at least 64MB. See: <a href="%s" target="_blank">Increasing memory allocated to PHP</a>', 'pronamic_ideal' ),
							esc_attr( 'http://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP' )
						),
						array(
							'a' => array(
								'href'   => true,
								'target' => true,
							),
						)
					);
				}

				?>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<?php esc_html_e( 'Character Set', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php bloginfo( 'charset' ); ?>
			</td>
			<td>
				<?php

				// @link http://codex.wordpress.org/Function_Reference/bloginfo#Show_Character_Set
				if ( 0 === strcasecmp( get_bloginfo( 'charset' ), 'UTF-8' ) ) {
					echo '✓';
				} else {
					esc_html_e( 'Pronamic Pay advices to set the character encoding to UTF-8.', 'pronamic_ideal' );
				}

				?>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<?php esc_html_e( 'Time (UTC)', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php echo esc_html( gmdate( __( 'Y/m/d g:i:s A', 'pronamic_ideal' ) ) ); ?>
			</td>
			<td>
				✓
			</td>
		</tr>
		<tr>
			<th scope="row">
				<?php esc_html_e( 'cURL', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php

				if ( \function_exists( 'curl_version' ) ) {
					/**
					 * Using cURL functions is highly discouraged within VIP context.
					 * We only use this cURL function for on the system status page.
					 */
					$curl_version = curl_version();

					if ( false === $curl_version ) {
						\esc_html_e( 'Unable to get the current cURL version.', 'pronamic_ideal' );
					}

					if ( \is_array( $curl_version ) && \array_key_exists( 'version', $curl_version ) ) {
						echo \esc_html( $curl_version['version'] );
					}
				}

				?>
			</td>
			<td>
				✓
			</td>
		</tr>
		<tr>
			<th scope="row">
				<?php esc_html_e( 'OpenSSL', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php

				if ( \defined( 'OPENSSL_VERSION_TEXT' ) ) {
					echo \esc_html( OPENSSL_VERSION_TEXT );
				}

				// @link https://www.openssl.org/docs/crypto/OPENSSL_VERSION_NUMBER.html
				$version_current  = (string) OPENSSL_VERSION_NUMBER;
				$version_required = (string) 0x000908000;

				?>
			</td>
			<td>
				<?php

				if ( \version_compare( $version_current, $version_required, '>' ) ) {
					echo '✓';
				} else {
					esc_html_e( 'Pronamic Pay requires OpenSSL 0.9.8 or above.', 'pronamic_ideal' );
				}

				?>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<?php esc_html_e( 'Registered Hashing Algorithms', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php

				$algorithms = hash_algos();

				echo esc_html( implode( ', ', $algorithms ) );

				?>
			</td>
			<td>
				<?php

				if ( in_array( 'sha1', $algorithms, true ) ) {
					echo '✓';
				} else {
					esc_html_e( 'Pronamic Pay requires the "sha1" hashing algorithm.', 'pronamic_ideal' );
				}

				?>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<?php esc_html_e( 'Travis CI build status', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php

				$url = add_query_arg( 'branch', pronamic_pay_plugin()->get_version(), 'https://travis-ci.org/pronamic/wp-pronamic-ideal.png' );

				?>
				<a href="https://travis-ci.org/pronamic/wp-pronamic-ideal">
					<img src="<?php echo \esc_url( $url ); ?>" alt="" />
				</a>
			</td>
			<td>

			</td>
		</tr>
	</tbody>
</table>
