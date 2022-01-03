<?php
/**
 * Cards
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

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
		$this->cards = array(
			// Cards.
			array(
				'bic'   => null,
				'brand' => 'american-express',
				'title' => __( 'American Express', 'pronamic_ideal' ),
			),
			array(
				'bic'   => null,
				'brand' => 'carta-si',
				'title' => __( 'Carta Si', 'pronamic_ideal' ),
			),
			array(
				'bic'   => null,
				'brand' => 'carte-bleue',
				'title' => __( 'Carte Bleue', 'pronamic_ideal' ),
			),
			array(
				'bic'   => null,
				'brand' => 'dankort',
				'title' => __( 'Dankort', 'pronamic_ideal' ),
			),
			array(
				'bic'   => null,
				'brand' => 'diners-club',
				'title' => __( 'Diners Club', 'pronamic_ideal' ),
			),
			array(
				'bic'   => null,
				'brand' => 'discover',
				'title' => __( 'Discover', 'pronamic_ideal' ),
			),
			array(
				'bic'   => null,
				'brand' => 'jcb',
				'title' => __( 'JCB', 'pronamic_ideal' ),
			),
			array(
				'bic'   => null,
				'brand' => 'maestro',
				'title' => __( 'Maestro', 'pronamic_ideal' ),
			),
			array(
				'bic'   => null,
				'brand' => 'mastercard',
				'title' => __( 'Mastercard', 'pronamic_ideal' ),
			),
			array(
				'bic'   => null,
				'brand' => 'unionpay',
				'title' => __( 'UnionPay', 'pronamic_ideal' ),
			),
			array(
				'bic'   => null,
				'brand' => 'visa',
				'title' => __( 'Visa', 'pronamic_ideal' ),
			),

			// Banks.
			array(
				'bic'   => 'abna',
				'brand' => 'abn-amro',
				'title' => __( 'ABN Amro', 'pronamic_ideal' ),
			),
			array(
				'bic'   => 'asnb',
				'brand' => 'asn-bank',
				'title' => __( 'ASN Bank', 'pronamic_ideal' ),
			),
			array(
				'bic'   => 'bunq',
				'brand' => 'bunq',
				'title' => __( 'bunq', 'pronamic_ideal' ),
			),
			array(
				'bic'   => 'hand',
				'brand' => 'handelsbanken',
				'title' => __( 'Handelsbanken', 'pronamic_ideal' ),
			),
			array(
				'bic'   => 'ingb',
				'brand' => 'ing',
				'title' => __( 'ING Bank', 'pronamic_ideal' ),
			),
			array(
				'bic'   => 'knab',
				'brand' => 'knab',
				'title' => __( 'Knab', 'pronamic_ideal' ),
			),
			array(
				'bic'   => 'moyo',
				'brand' => 'moneyou',
				'title' => __( 'Moneyou', 'pronamic_ideal' ),
			),
			array(
				'bic'   => 'rabo',
				'brand' => 'rabobank',
				'title' => __( 'Rabobank', 'pronamic_ideal' ),
			),
			array(
				'bic'   => 'rbrb',
				'brand' => 'regiobank',
				'title' => __( 'RegioBank', 'pronamic_ideal' ),
			),
			array(
				'bic'   => 'snsb',
				'brand' => 'sns',
				'title' => __( 'SNS Bank', 'pronamic_ideal' ),
			),
			array(
				'bic'   => 'trio',
				'brand' => 'triodos-bank',
				'title' => __( 'Triodos Bank', 'pronamic_ideal' ),
			),
			array(
				'bic'   => 'fvlb',
				'brand' => 'van-lanschot',
				'title' => __( 'Van Lanschot', 'pronamic_ideal' ),
			),
		);
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
			array(
				'bic'   => $bic_or_brand,
				'brand' => $bic_or_brand,
			),
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
		return sprintf(
			'https://cdn.wp-pay.org/jsdelivr.net/npm/@wp-pay/logos@1.6.6/dist/cards/%1$s/card-%1$s-logo-_x80.svg',
			$brand
		);
	}
}
