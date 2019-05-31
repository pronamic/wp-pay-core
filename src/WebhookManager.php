<?php
/**
 * Webhook manager
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use Exception;
use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\DateTime\DateTimeZone;
use Pronamic\WordPress\Pay\Admin\AdminNotices;
use Pronamic\WordPress\Pay\Core\Server;
use Pronamic\WordPress\Pay\Payments\Payment;
use stdClass;
use WP_Query;

/**
 * Webhook manager class
 *
 * @author  Reüel van der Steege
 * @version 2.1.6
 * @since   2.1.6
 */
class WebhookManager {
	/**
	 * Meta key for webhook log.
	 *
	 * @var string
	 */
	const LOG_META_KEY = '_pronamic_gateway_webhook_log';

	/**
	 * Transient option name for outdated webhook URLs.
	 *
	 * @var string
	 */
	const OUTDATED_WEBHOOK_URLS_OPTION = 'pronamic_outdated_webhook_urls';

	/**
	 * Icon 'OK' class.
	 *
	 * @var string
	 */
	const ICON_CLASS_OK = 'yes';

	/**
	 * Icon 'Warning' class.
	 *
	 * @var string
	 */
	const ICON_CLASS_WARNING = 'warning';

	/**
	 * Webhook constructor.
	 */
	public function __construct() {
		// Filter gateway settings.
		add_filter( 'pronamic_pay_gateway_sections', array( $this, 'settings_section_feedback_icon' ), PHP_INT_MAX );
		add_filter( 'pronamic_pay_gateway_fields', array( $this, 'settings_field_feedback_icon' ), PHP_INT_MAX );

		// Admin notices.
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

	/**
	 * Get log object.
	 *
	 * @param int $config_id Config ID.
	 *
	 * @return null|object
	 */
	public static function get_log( $config_id ) {
		if ( empty( $config_id ) ) {
			return null;
		}

		// Get log from gateway meta.
		$meta = get_post_meta( $config_id, self::LOG_META_KEY, true );

		if ( false === $meta ) {
			return null;
		}

		$object = json_decode( $meta );

		if ( is_object( $object ) ) {
			return $object;
		}

		return null;
	}

	/**
	 * Log payment.
	 *
	 * @param Payment $payment Payment.
	 *
	 * @return void
	 */
	public static function log_payment( Payment $payment ) {
		$config_id = $payment->get_config_id();

		$gateway = Plugin::get_gateway( $config_id );

		// Check if gateway exists.
		if ( null === $gateway ) {
			return;
		}

		// Object.
		$object             = new stdClass();
		$object->date       = gmdate( DateTime::MYSQL );
		$object->payment_id = $payment->get_id();
		$object->url        = sprintf(
			'%s://%s%s',
			( is_ssl() ? 'https' : 'http' ),
			Server::get( 'HTTP_HOST' ),
			Server::get( 'REQUEST_URI' )
		);

		// Update webhook log.
		update_post_meta( (int) $config_id, self::LOG_META_KEY, wp_json_encode( $object ) );

		// Delete outdated webhook URLs transient.
		delete_transient( self::OUTDATED_WEBHOOK_URLS_OPTION );
	}

	/**
	 * Get config ID for settings section or field.
	 *
	 * @param array $setting Settings section or field.
	 *
	 * @return int|null
	 */
	private static function get_setting_config_id( $setting ) {
		$config_id = get_the_ID();

		// Check valid ID.
		if ( false === $config_id ) {
			return null;
		}

		// Check gateway method with gateway being edited.
		if ( isset( $setting['methods'] ) ) {
			$gateway_id = get_post_meta( $config_id, '_pronamic_gateway_id', true );

			$gateway_ids = array(
				str_replace( '-', '_', $gateway_id ),
				str_replace( '_', '-', $gateway_id ),
			);

			$intersect = array_intersect( $gateway_ids, $setting['methods'] );

			if ( empty( $intersect ) ) {
				return null;
			}
		}

		return $config_id;
	}

	/**
	 * Check if manual webhook configuration is needed for the gateway.
	 *
	 * @param array $features Supported gateway features.
	 *
	 * @return bool
	 */
	private static function is_manual_config_required( $features ) {
		if ( is_array( $features ) ) {
			return in_array( 'webhook_manual_config', $features, true );
		}

		return false;
	}

	/**
	 * Callback for log settings field.
	 *
	 * @param array $field    Settings field.
	 * @param array $features Supported gateway features.
	 *
	 * @return void
	 */
	public static function settings_status( array $field, $features = array() ) {
		// Get log.
		$config_id = self::get_setting_config_id( $field );

		$log = self::get_log( $config_id );

		if ( null === $log ) {
			esc_html_e( 'No webhook request processed yet.', 'pronamic_ideal' );

			return;
		}

		// Prefix icon to field HTML.
		printf(
			'%s ',
			wp_kses(
				self::get_field_feedback_icon_html( $field, $features ),
				array(
					'span' => array(
						'class' => array(),
					),
				)
			)
		);

		try {
			$date = new DateTime( $log->date, new DateTimeZone( 'UTC' ) );

			if ( isset( $log->payment_id ) ) {
				printf(
					wp_kses(
						/* translators: 1: formatted date, 2: payment edit url, 3: payment id */
						__(
							'Last webhook request processed on %1$s for <a href="%2$s" title="Payment %3$s">payment #%3$s</a>.',
							'pronamic_ideal'
						),
						array(
							'a' => array(
								'href'  => array(),
								'title' => array(),
							),
						)
					),
					esc_html( $date->format_i18n( _x( 'l j F Y \a\t H:i', 'full datetime format', 'pronamic_ideal' ) ) ),
					esc_url( get_edit_post_link( $log->payment_id ) ),
					esc_html( $log->payment_id )
				);
			} else {
				printf(
					/* translators: 1: formatted date */
					esc_html( __( 'Last webhook request processed on %1$s.', 'pronamic_ideal' ) ),
					esc_html( $date->format_i18n( _x( 'l j F Y \a\t H:i', 'full datetime format', 'pronamic_ideal' ) ) )
				);
			}
		} catch ( Exception $e ) {
			if ( isset( $log->payment_id ) ) {
				printf(
					wp_kses(
						/* translators: 1: payment edit url, 2: payment id */
						__(
							'Last webhook request processed for <a href="%1$s" title="Payment %2$s">payment #%2$s</a>.',
							'pronamic_ideal'
						),
						array(
							'a' => array(
								'href'  => array(),
								'title' => array(),
							),
						)
					),
					esc_url( get_edit_post_link( $log->payment_id ) ),
					esc_html( $log->payment_id )
				);
			} else {
				esc_html_e( 'Webhook request has been processed.', 'pronamic_ideal' );
			}
		}
	}

	/**
	 * Add icons to transaction feedback settings sections titles.
	 *
	 * @param array $sections Settings sections.
	 *
	 * @return array
	 */
	public function settings_section_feedback_icon( array $sections ) {
		foreach ( $sections as $id => &$section ) {
			// Check section title.
			if ( '_feedback' !== substr( $id, -9 ) ) {
				continue;
			}

			// Prefix icon to section title.
			$features = array();

			if ( isset( $section['features'] ) ) {
				$features = $section['features'];
			}

			$icon = self::get_section_feedback_icon_html( $section, $features );

			if ( empty( $icon ) ) {
				continue;
			}

			$section['title'] = $icon . ' ' . $section['title'];
		}

		return $sections;
	}

	/**
	 * Prefix 'Transaction feedback' settings field HTML with checkmark icon if
	 * last webhook has been processed on the current site URL.
	 *
	 * @param array $fields Settings fields.
	 *
	 * @return array
	 */
	public function settings_field_feedback_icon( array $fields ) {
		$feedback_title = __( 'Transaction feedback', 'pronamic_ideal' );

		foreach ( $fields as &$field ) {
			// Check field title.
			if ( ! isset( $field['title'] ) || $field['title'] !== $feedback_title ) {
				continue;
			}

			// Get config ID for settings field.
			$config_id = self::get_setting_config_id( $field );

			// Get log.
			$log = self::get_log( $config_id );

			if ( null === $log ) {
				continue;
			}

			// Replace field HTML with details about last processed webhook request.
			try {
				$date = new DateTime( $log->date, new DateTimeZone( 'UTC' ) );

				if ( isset( $log->payment_id ) ) {
					$field['html'] = sprintf(
						/* translators: 1: formatted date, 2: payment edit url, 3: payment id */
						__(
							'Last webhook request processed on %1$s for <a href="%2$s" title="Payment %3$s">payment #%3$s</a>.',
							'pronamic_ideal'
						),
						esc_html( $date->format_i18n( _x( 'l j F Y \a\t H:i', 'full datetime format', 'pronamic_ideal' ) ) ),
						esc_url( get_edit_post_link( $log->payment_id ) ),
						esc_html( $log->payment_id )
					);
				} else {
					$field['html'] = sprintf(
						/* translators: 1: formatted date */
						__( 'Last webhook request processed on %1$s.', 'pronamic_ideal' ),
						$date->format_i18n( _x( 'l j F Y \a\t H:i', 'full datetime format', 'pronamic_ideal' ) )
					);
				}
			} catch ( Exception $e ) {
				if ( isset( $log->payment_id ) ) {
					$field['html'] = sprintf(
						/* translators: 1: payment edit url, 2: payment id */
						__(
							'Last webhook request processed for <a href="%1$s" title="Payment %2$s">payment #%2$s</a>.',
							'pronamic_ideal'
						),
						esc_url( get_edit_post_link( $log->payment_id ) ),
						esc_html( $log->payment_id )
					);
				} else {
					$field['html'] = esc_html__( 'Webhook request has been processed.', 'pronamic_ideal' );
				}
			}

			// Prefix icon to field HTML.
			$features = isset( $field['features'] ) ? $field['features'] : array();

			$icon = self::get_field_feedback_icon_html( $field, $features );

			if ( empty( $icon ) ) {
				continue;
			}

			$field['html'] = $icon . ' ' . $field['html'];
		}

		return $fields;
	}

	/**
	 * Get settings section title icon.
	 *
	 * @param array $section  Settings section.
	 * @param array $features Supported gateway features.
	 *
	 * @return null|string
	 */
	public static function get_section_feedback_icon_html( $section, $features ) {
		return self::get_icon_html( $section, $features, self::ICON_CLASS_WARNING );
	}

	/**
	 * Get settings field icon.
	 *
	 * @param array $field    Settings field.
	 * @param array $features Supported gateway features.
	 *
	 * @return null|string
	 */
	public static function get_field_feedback_icon_html( $field, $features ) {
		return self::get_icon_html( $field, $features, self::ICON_CLASS_OK );
	}

	/**
	 * Get icon HTML.
	 *
	 * @param array       $setting  Settings section or field.
	 * @param array       $features Supported gateway features.
	 * @param null|string $type     Restrict output of icon to a specific icon type.
	 *
	 * @return null|string
	 */
	public static function get_icon_html( $setting, $features, $type = null ) {
		$icon = self::ICON_CLASS_OK;

		if ( self::is_manual_config_required( $features ) ) {
			$config_id = self::get_setting_config_id( $setting );

			$log = self::get_log( $config_id );

			if ( null === $log || ! self::valid_log_url( $log ) ) {
				$icon = self::ICON_CLASS_WARNING;
			}
		}

		// Check restricted icon type to return icon for.
		if ( null !== $type && $icon !== $type ) {
			return null;
		}

		// Return HTML.
		$classes = array(
			'dashicons',
			'dashicons-' . $icon,
		);

		$html = sprintf(
			'<span class="%s"></span >',
			esc_html( implode( ' ', $classes ) )
		);

		return $html;
	}

	/**
	 * Validate log URL against current site URL.
	 *
	 * @param stdClass $log Log object.
	 *
	 * @return bool
	 */
	public static function valid_log_url( $log ) {
		if ( ! is_object( $log ) || ! isset( $log->url ) ) {
			return false;
		}

		// Check if current home URL is the same as in the logged URL.
		$site_url = home_url( '/' );

		if ( substr( $log->url, 0, strlen( $site_url ) ) !== $site_url ) {
			return false;
		}

		return true;
	}

	/**
	 * Admin notices.
	 */
	public function admin_notices() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$outdated_urls = get_transient( self::OUTDATED_WEBHOOK_URLS_OPTION );

		if ( false === $outdated_urls ) {
			$outdated_urls = array();

			// Get gateways for which a webhook log exists.
			$query = new WP_Query(
				array(
					'post_type'  => 'pronamic_gateway',
					'orderby'    => 'post_title',
					'order'      => 'ASC',
					'fields'     => 'ids',
					'nopaging'   => true,
					'meta_query' => array(
						array(
							'key' => '_pronamic_gateway_webhook_log',
						),
					),
				)
			);

			// Loop gateways.
			foreach ( $query->posts as $config_id ) {
				$log = self::get_log( $config_id );

				if ( self::valid_log_url( $log ) ) {
					continue;
				}

				$gateway = Plugin::get_gateway( $config_id );

				// Check if manual configuration is needed for webhook.
				if ( $gateway && ! $gateway->supports( 'webhook_manual_config' ) ) {
					continue;
				}

				$outdated_urls[] = $config_id;
			}

			if ( empty( $outdated_urls ) ) {
				$outdated_urls = true;
			}

			set_transient( self::OUTDATED_WEBHOOK_URLS_OPTION, $outdated_urls, HOUR_IN_SECONDS );
		}

		if ( ! empty( $outdated_urls ) ) {
			AdminNotices::add_notice( 'update_webhook_url' );
		}
	}
}
