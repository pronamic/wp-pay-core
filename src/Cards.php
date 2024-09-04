<?php
/**
 * Cards
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use Pronamic\WpPayLogos\ImageService;

/**
 * Cards
 *
 * @author  Reüel van der Steege
 * @version 2.7.1
 * @since   2.4.0
 */
class Cards {
	/**
	 * Cards.
	 *
	 * @var array
	 */
	private $cards;

	/**
	 * Cards constructor.
	 */
	public function __construct() {
		$this->register_cards();
	}

	/**
	 * Register cards.
	 *
	 * @return void
	 */
	private function register_cards() {
		$this->cards = [
			// Cards.
			[
				'bic'   => null,
				'brand' => 'american-express',
				'title' => __( 'American Express', 'pronamic_ideal' ),
			],
			[
				'bic'   => null,
				'brand' => 'carta-si',
				'title' => __( 'Carta Si', 'pronamic_ideal' ),
			],
			[
				'bic'   => null,
				'brand' => 'carte-bleue',
				'title' => __( 'Carte Bleue', 'pronamic_ideal' ),
			],
			[
				'bic'   => null,
				'brand' => 'dankort',
				'title' => __( 'Dankort', 'pronamic_ideal' ),
			],
			[
				'bic'   => null,
				'brand' => 'diners-club',
				'title' => __( 'Diners Club', 'pronamic_ideal' ),
			],
			[
				'bic'   => null,
				'brand' => 'discover',
				'title' => __( 'Discover', 'pronamic_ideal' ),
			],
			[
				'bic'   => null,
				'brand' => 'jcb',
				'title' => __( 'JCB', 'pronamic_ideal' ),
			],
			[
				'bic'   => null,
				'brand' => 'maestro',
				'title' => __( 'Maestro', 'pronamic_ideal' ),
			],
			[
				'bic'   => null,
				'brand' => 'mastercard',
				'title' => __( 'Mastercard', 'pronamic_ideal' ),
			],
			[
				'bic'   => null,
				'brand' => 'unionpay',
				'title' => __( 'UnionPay', 'pronamic_ideal' ),
			],
			[
				'bic'   => null,
				'brand' => 'visa',
				'title' => __( 'Visa', 'pronamic_ideal' ),
			],

			// Banks.
			[
				'bic'   => 'abna',
				'brand' => 'abn-amro',
				'title' => __( 'ABN Amro', 'pronamic_ideal' ),
			],
			[
				'bic'   => 'asnb',
				'brand' => 'asn-bank',
				'title' => __( 'ASN Bank', 'pronamic_ideal' ),
			],
			[
				'bic'   => 'bunq',
				'brand' => 'bunq',
				'title' => __( 'bunq', 'pronamic_ideal' ),
			],
			[
				'bic'   => 'hand',
				'brand' => 'handelsbanken',
				'title' => __( 'Handelsbanken', 'pronamic_ideal' ),
			],
			[
				'bic'   => 'ingb',
				'brand' => 'ing',
				'title' => __( 'ING Bank', 'pronamic_ideal' ),
			],
			[
				'bic'   => 'knab',
				'brand' => 'knab',
				'title' => __( 'Knab', 'pronamic_ideal' ),
			],
			[
				'bic'   => 'moyo',
				'brand' => 'moneyou',
				'title' => __( 'Moneyou', 'pronamic_ideal' ),
			],
			[
				'bic'   => 'rabo',
				'brand' => 'rabobank',
				'title' => __( 'Rabobank', 'pronamic_ideal' ),
			],
			[
				'bic'   => 'rbrb',
				'brand' => 'regiobank',
				'title' => __( 'RegioBank', 'pronamic_ideal' ),
			],
			[
				'bic'   => 'snsb',
				'brand' => 'sns',
				'title' => __( 'SNS Bank', 'pronamic_ideal' ),
			],
			[
				'bic'   => 'trio',
				'brand' => 'triodos-bank',
				'title' => __( 'Triodos Bank', 'pronamic_ideal' ),
			],
			[
				'bic'   => 'fvlb',
				'brand' => 'van-lanschot',
				'title' => __( 'Van Lanschot', 'pronamic_ideal' ),
			],
		];
	}

	/**
	 * Get card.
	 *
	 * @param string $bic_or_brand 4-letter ISO 9362 Bank Identifier Code (BIC) or brand name.
	 * @return array|null
	 */
	public function get_card( $bic_or_brand ) {
		// Use lowercase BIC or brand without spaces.
		$bic_or_brand = \strtolower( $bic_or_brand );

		$bic_or_brand = \str_replace( ' ', '-', $bic_or_brand );

		// Try to find card.
		$cards = \wp_list_filter(
			$this->cards,
			[
				'bic'   => $bic_or_brand,
				'brand' => $bic_or_brand,
			],
			'OR'
		);

		$card = \array_shift( $cards );

		// Return card details.
		if ( ! empty( $card ) ) {
			return $card;
		}

		// No matching card.
		return null;
	}

	/**
	 * Get card logo URL.
	 *
	 * @param string $brand Brand.
	 *
	 * @return string|null
	 */
	public function get_card_logo_url( $brand ) {
		$image_service = new ImageService();

		$path = 'cards/' . $brand . '/card-' . $brand . '-logo-_x80.svg';

		$path = $image_service->get_path( $path );

		if ( ! \is_readable( $path ) ) {
			return null;
		}

		return \plugins_url( \basename( $path ), $path );
	}
}
