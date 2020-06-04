<?php
/**
 * VAT Number validator
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

/**
 * VAT Number validator
 *
 * @author  Remco Tolsma
 * @version 2.2.6
 * @since   1.4.0
 */
class VatNumberValidator {
	/**
	 * API URL
	 */
	const API_URL = 'http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';

	/**
	 * Validate VAT number.
	 *
	 * @param VatNumber $vat_number
	 * @return VatNumberValidity
	 */
	public static function validate( VatNumber $vat_number ) {
		$valid = false;

		$request_date = new \DateTimeImmutable();

		$validity = new VatNumberValidity( $vat_number, $request_date, $valid );
		$validity->set_service( VatNumberValidationService::VIES );

		try {
			// Client
			$client = new \SoapClient( self::API_URL );

			// Parameters
			$parameters = array(
				'countryCode' => $vat_number->get_2_digit_prefix(),
				'vatNumber'   => $vat_number->normalized_without_prefix(),
			);

			// Response
			$response = $client->checkVat( $parameters );

			$validity->set_valid( $response->valid );
			$validity->set_name( $response->name );
			$validity->set_address( $response->address );
		} catch ( \Exception $exception ) {
			$validity->error = $exception->getMessage();
		}

		return $validity;
	}
}
