<?php
/**
 * Privacy manager
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use Exception;

/**
 * Class PrivacyManager
 *
 * @version 2.0.5
 */
class PrivacyManager {
	/**
	 * Exporters.
	 *
	 * @var array
	 */
	private $exporters = [];

	/**
	 * Erasers.
	 *
	 * @var array
	 */
	private $erasers = [];

	/**
	 * Privacy manager constructor.
	 */
	public function __construct() {
		// Filters.
		add_filter( 'wp_privacy_personal_data_exporters', [ $this, 'register_exporters' ], 10 );
		add_filter( 'wp_privacy_personal_data_erasers', [ $this, 'register_erasers' ], 10 );
		add_filter( 'wp_privacy_anonymize_data', [ $this, 'anonymize_custom_data_types' ], 10, 3 );
	}

	/**
	 * Register exporters.
	 *
	 * @param array $exporters Privacy exporters.
	 * @return array
	 */
	public function register_exporters( $exporters ) {
		$privacy_manager = $this;

		/**
		 * Register privacy exporters.
		 *
		 * @param PrivacyManager $privacy_manager Privacy manager.
		 */
		do_action( 'pronamic_pay_privacy_register_exporters', $privacy_manager );

		foreach ( $this->exporters as $id => $exporter ) {
			$exporters[ $id ] = $exporter;
		}

		return $exporters;
	}

	/**
	 * Register erasers.
	 *
	 * @param array $erasers Privacy erasers.
	 * @return array
	 */
	public function register_erasers( $erasers ) {
		$privacy_manager = $this;

		/**
		 * Register privacy erasers.
		 *
		 * @param PrivacyManager $privacy_manager Privacy manager.
		 */
		do_action( 'pronamic_pay_privacy_register_erasers', $privacy_manager );

		foreach ( $this->erasers as $id => $eraser ) {
			$erasers[ $id ] = $eraser;
		}

		return $erasers;
	}

	/**
	 * Add exporter.
	 *
	 * @param string $id       ID of the exporter.
	 * @param string $name     Exporter name.
	 * @param array  $callback Exporter callback.
	 * @return void
	 */
	public function add_exporter( $id, $name, $callback ) {
		$id = 'pronamic-pay-' . $id;

		$this->exporters[ $id ] = [
			'exporter_friendly_name' => $name,
			'callback'               => $callback,
		];
	}

	/**
	 * Add eraser.
	 *
	 * @param string $id       ID of the eraser.
	 * @param string $name     Eraser name.
	 * @param array  $callback Eraser callback.
	 * @return void
	 */
	public function add_eraser( $id, $name, $callback ) {
		$id = 'pronamic-pay-' . $id;

		$this->erasers[ $id ] = [
			'eraser_friendly_name' => $name,
			'callback'             => $callback,
		];
	}

	/**
	 * Export meta.
	 *
	 * @param string $meta_key     Meta key.
	 * @param array  $meta_options Registered meta options.
	 * @param array  $meta_values  Array with all post meta for item.
	 *
	 * @return array
	 */
	public function export_meta( $meta_key, $meta_options, $meta_values ) {
		// Label.
		$label = $meta_key;

		if ( isset( $meta_options['label'] ) ) {
			$label = $meta_options['label'];
		}

		// Meta value.
		$meta_value = $meta_values[ $meta_key ];

		if ( 1 === count( $meta_value ) ) {
			$meta_value = array_shift( $meta_value );
		} else {
			$meta_value = wp_json_encode( $meta_value );
		}

		// Return export data.
		return [
			'name'  => $label,
			'value' => $meta_value,
		];
	}

	/**
	 * Erase meta.
	 *
	 * @param int    $post_id  ID of the post.
	 * @param string $meta_key Meta key to erase.
	 * @param string $action   Action 'erase' or 'anonymize'.
	 * @return void
	 */
	public function erase_meta( $post_id, $meta_key, $action = 'erase' ) {
		switch ( $action ) {
			case 'erase':
				delete_post_meta( $post_id, $meta_key );

				break;
			case 'anonymize':
				$meta_value = get_post_meta( $post_id, $meta_key, true );

				// Mask email addresses.
				if ( false !== strpos( $meta_value, '@' ) ) {
					$meta_value = self::mask_email( $meta_value );
				}

				update_post_meta( $post_id, $meta_key, $meta_value );

				break;
		}
	}

	/**
	 * Mask email address.
	 *
	 * @param string $email Email address.
	 * @return string
	 */
	public static function mask_email( $email ) {
		// Is this an email address?
		if ( ! is_string( $email ) || false === strpos( $email, '@' ) ) {
			return $email;
		}

		$parts = explode( '@', $email );

		// Local part.
		$local = $parts[0];

		if ( strlen( $local ) > 2 ) {
			$local = sprintf(
				'%1$s%2$s%3$s',
				substr( $local, 0, 1 ),
				str_repeat( '*', ( strlen( $local ) - 2 ) ),
				substr( $local, - 1 )
			);
		}

		// Domain part.
		$domain_parts = explode( '.', $parts[1] );

		$domain = [];

		foreach ( $domain_parts as $part ) {
			if ( strlen( $part ) <= 2 ) {
				$domain[] = $part;

				continue;
			}

			$domain[] = sprintf(
				'%1$s%2$s%3$s',
				substr( $part, 0, 1 ),
				str_repeat( '*', ( strlen( $part ) - 2 ) ),
				substr( $part, - 1 )
			);
		}

		// Combine local and domain part.
		$email = sprintf(
			'%1$s@%2$s',
			$local,
			implode( '.', $domain )
		);

		return $email;
	}

	/**
	 * Anonymize data.
	 *
	 * @link https://github.com/WordPress/WordPress/blob/4.9.8/wp-includes/functions.php#L5932-L5978
	 *
	 * @param  string      $type The type of data to be anonymized.
	 * @param  string|null $data Optional The data to be anonymized.
	 * @return string|null The anonymous data for the requested type.
	 */
	public static function anonymize_data( $type, $data = null ) {
		if ( null === $data ) {
			return null;
		}

		return wp_privacy_anonymize_data( $type, $data );
	}

	/**
	 * Anonymize IPv4 or IPv6 address.
	 *
	 * @link https://github.com/WordPress/WordPress/blob/4.9.8/wp-includes/functions.php#L5862-L5930
	 *
	 * @param  string|null $ip_addr        The IPv4 or IPv6 address to be anonymized.
	 * @param  bool        $ipv6_fallback  Optional. Whether to return the original IPv6 address if the needed functions
	 *                                to anonymize it are not present. Default false, return `::` (unspecified address).
	 * @return string|null The anonymized IP address.
	 */
	public static function anonymize_ip( $ip_addr, $ipv6_fallback = false ) {
		if ( null === $ip_addr ) {
			return null;
		}

		return wp_privacy_anonymize_ip( $ip_addr, $ipv6_fallback );
	}

	/**
	 * Anonymize custom data types.
	 *
	 * @param string $anonymous Anonymized data.
	 * @param string $type      Type of the data.
	 * @param string $data      Original data.
	 *
	 * @return string Anonymized string.
	 *
	 * @throws Exception When error occurs anonymize phone.
	 */
	public static function anonymize_custom_data_types( $anonymous, $type, $data ) {
		switch ( $type ) {
			case 'email_mask':
				$anonymous = self::mask_email( $data );

				break;
			case 'phone':
				$anonymous = preg_replace( '/\d/u', '0', $data );

				if ( null === $anonymous ) {
					throw new Exception( 'Could not anonymize phone number.' );
				}

				break;
		}

		return $anonymous;
	}
}
