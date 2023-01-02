<?php
/**
 * VAT Number validator
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\VatNumbers;

/**
 * VAT Number VIES validator
 *
 * @author  Remco Tolsma
 * @version 2.4.0
 * @since   1.4.0
 */
class VatNumberViesValidator {
	/**
	 * API URL
	 */
	const API_URL = 'http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';

	/**
	 * Validate VAT number.
	 *
	 * @param VatNumber $vat_number VAT number.
	 * @return VatNumberValidity
	 * @throws \Exception SOAP error.
	 */
	public static function validate( VatNumber $vat_number ) {
		// Client.
		$client = new \SoapClient( self::API_URL );

		// Parameters.
		$parameters = [
			'countryCode' => $vat_number->get_2_digit_prefix(),
			'vatNumber'   => $vat_number->normalized_without_prefix(),
		];

		// Response.
		$response = $client->checkVat( $parameters );

		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- VIES response object.
		$vat_number = VatNumber::from_prefix_and_number( $response->countryCode, $response->vatNumber );

		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- VIES response object.
		$request_date = new \DateTime( $response->requestDate );

		$validity = new VatNumberValidity( $vat_number, $request_date, $response->valid );

		$validity->set_name( $response->name );
		$validity->set_address( $response->address );
		$validity->set_service( VatNumberValidationService::VIES );

		return $validity;
	}
}
