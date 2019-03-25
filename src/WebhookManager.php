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
use Pronamic\WordPress\Pay\Core\Server;
use Pronamic\WordPress\Pay\Payments\Payment;
use stdClass;

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
		update_post_meta( $config_id, self::LOG_META_KEY, wp_json_encode( $object ) );
	}

	/**
	 * Callback for log settings field.
	 *
	 * @param array $settings_field Settings field.
	 *
	 * @return void
	 *
	 * @throws \Exception Throws Exception in case of an date error.
	 */
	public static function settings_status( array $settings_field ) {
		$config_id = get_the_ID();

		// Check gateway method with gateway being edited.
		if ( isset( $settings_field['methods'] ) ) {
			$gateway_id = get_post_meta( $config_id, '_pronamic_gateway_id', true );

			if ( ! in_array( $gateway_id, $settings_field['methods'], true ) ) {
				$config_id = null;
			}
		}

		// Add space between field HTML content and webhook status notice.
		if ( ! empty( $settings_field['html'] ) ) {
			echo ' ';
		}

		// Get log.
		$log = self::get_log( $config_id );

		if ( null === $log ) {
			if ( null !== $config_id ) {
				esc_html_e( 'No webhook request processed yet.', 'pronamic_ideal' );
			}

			return;
		}

		try {
			$date = new DateTime( $log->date, new DateTimeZone( 'UTC' ) );

			if ( isset( $log->payment_id ) ) {
				$settings_field['html'] = sprintf(
					/* translators: 1: formatted date, 2: payment edit url, 3: payment id */
					__(
						'Last webhook request processed on %1$s for <a href="%2$s" title="Payment %3$s">payment #%3$s</a>.',
						'pronamic_ideal'
					),
					$date->format_i18n( _x( 'l j F Y \a\t H:i', 'full datetime format', 'pronamic_ideal' ) ),
					get_edit_post_link( $log->payment_id ),
					$log->payment_id
				);
			} else {
				$settings_field['html'] = sprintf(
					/* translators: 1: formatted date */
					__( 'Last webhook request processed on %1$s.', 'pronamic_ideal' ),
					$date->format_i18n( _x( 'l j F Y \a\t H:i', 'full datetime format', 'pronamic_ideal' ) )
				);
			}
		} catch ( Exception $e ) {
			if ( isset( $log->payment_id ) ) {
				$settings_field['html'] = sprintf(
					/* translators: 1: payment edit url, 2: payment id */
					__(
						'Last webhook request processed for <a href="%1$s" title="Payment %2$s">payment #%2$s</a>.',
						'pronamic_ideal'
					),
					get_edit_post_link( $log->payment_id ),
					$log->payment_id
				);
			} else {
				$settings_field['html'] = __( 'Webhook request has been processed.', 'pronamic_ideal' );
			}
		}
	}

	/**
	 * Add icons to 'Transaction feedback' settings sections titles.
	 *
	 * @param array $sections Settings sections.
	 *
	 * @return array
	 */
	public function settings_section_feedback_icon( array $sections ) {
		$gateway_id = get_post_meta( get_the_ID(), '_pronamic_gateway_id', true );

		$feedback_title = __( 'Transaction feedback', 'pronamic_ideal' );

		foreach ( $sections as &$section ) {
			// Check section title.
			if ( ! isset( $section['title'] ) || $section['title'] !== $feedback_title ) {
				continue;
			}

			// Check section methods.
			$config_id = get_the_ID();

			if ( isset( $section['methods'] ) && ! in_array( $gateway_id, $section['methods'], true ) ) {
				$config_id = null;
			}

			// Additional configuration required?
			$requires_config = false;

			if ( isset( $section['requires_config'] ) && $section['requires_config'] ) {
				$requires_config = true;
			}

			// Prefix icon to section title.
			$icon = self::get_section_feedback_icon_html( $config_id, $requires_config );

			$section['title'] = sprintf(
				'%s %s',
				$icon,
				$section['title']
			);
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
		$gateway_id = get_post_meta( get_the_ID(), '_pronamic_gateway_id', true );

		$feedback_title = __( 'Transaction feedback', 'pronamic_ideal' );

		foreach ( $fields as &$field ) {
			// Check field title.
			if ( ! isset( $field['title'] ) || $field['title'] !== $feedback_title ) {
				continue;
			}

			// Check field methods.
			$config_id = get_the_ID();

			if ( isset( $field['methods'] ) && ! in_array( $gateway_id, $field['methods'], true ) ) {
				$config_id = null;
			}

			// Get log.
			$log = self::get_log( $config_id );

			if ( null === $log ) {
				continue;
			}

			// Additional configuration required?
			$requires_config = false;

			if ( isset( $field['requires_config'] ) && $field['requires_config'] ) {
				$requires_config = true;
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
						$date->format_i18n( _x( 'l j F Y \a\t H:i', 'full datetime format', 'pronamic_ideal' ) ),
						get_edit_post_link( $log->payment_id ),
						$log->payment_id
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
						get_edit_post_link( $log->payment_id ),
						$log->payment_id
					);
				} else {
					$field['html'] = __( 'Webhook request has been processed.', 'pronamic_ideal' );
				}
			}

			// Prefix icon to field title.
			$icon = self::get_field_feedback_icon_html( $config_id, $requires_config );

			$field['html'] = sprintf(
				'%s %s',
				$icon,
				$field['html']
			);
		}

		return $fields;
	}

	/**
	 * Get settings section title icon.
	 *
	 * @param int|string $config_id       Configuration ID.
	 * @param bool       $requires_config Whether or not transaction feedback requires additional configuration.
	 *
	 * @return null|string
	 */
	public static function get_section_feedback_icon_html( $config_id, $requires_config ) {
		return self::get_icon_html( $config_id, $requires_config, self::ICON_CLASS_WARNING );
	}

	/**
	 * Get settings field icon.
	 *
	 * @param int|string $config_id       Configuration ID.
	 * @param bool       $requires_config Whether or not transaction feedback requires additional configuration.
	 *
	 * @return null|string
	 */
	public static function get_field_feedback_icon_html( $config_id, $requires_config ) {
		return self::get_icon_html( $config_id, $requires_config, self::ICON_CLASS_OK );
	}

	/**
	 * Get icon.
	 *
	 * @param array       $type Settings section.
	 * @param null|string $type Restrict output of icon to a specific icon type.
	 *
	 * @return null|string
	 */
	public static function get_icon_html( $config_id, $requires_config, $type = null ) {
		$icon = self::ICON_CLASS_OK;

		if ( $requires_config ) {
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

		$scheme = is_ssl() ? 'https' : 'http';

		$site_url = home_url( '/', $scheme );

		if ( substr( $log->url, 0, strlen( $site_url ) ) !== $site_url ) {
			return false;
		}

		return true;
	}
}
