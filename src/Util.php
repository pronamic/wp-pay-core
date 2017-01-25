<?php

/**
 * Title: WordPress utility class
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.3.9
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Util {
	/**
	 * Remote get body
	 *
	 * @param string $url
	 * @param int $required_response_code
	 */
	public static function remote_get_body( $url, $required_response_code = 200, array $args = array() ) {
		$return = false;

		$result = wp_remote_request( $url, $args );

		if ( is_wp_error( $result ) ) {
			$return = $result;
		} else {
			/*
			 * The response code is cast to a integer since WordPress 4.1, therefor we can't use
			 * strict comparison on the required response code.
			 *
			 * @see https://github.com/WordPress/WordPress/blob/4.1/wp-includes/class-http.php#L528-L529
			 * @see https://github.com/WordPress/WordPress/blob/4.0/wp-includes/class-http.php#L527
			 */
			if ( wp_remote_retrieve_response_code( $result ) == $required_response_code ) { // WPCS: loose comparison ok.
				$return = wp_remote_retrieve_body( $result );
			} else {
				$return = new WP_Error(
					'wrong_response_code',
					sprintf(
						__( 'The response code (<code>%1$s<code>) was incorrect, required response code <code>%2$s</code>.', 'pronamic_ideal' ),
						wp_remote_retrieve_response_code( $result ),
						$required_response_code
					)
				);
			}
		}

		return $return;
	}

	//////////////////////////////////////////////////

	/**
	 * SimpleXML load string
	 *
	 * @param string $string
	 * @return SimpleXMLElement || WP_Error
	 */
	public static function simplexml_load_string( $string ) {
		$result = false;

		// Suppress all XML errors
		$use_errors = libxml_use_internal_errors( true );

		// Load
		$xml = simplexml_load_string( $string );

		if ( false !== $xml ) {
			$result = $xml;
		} else {
			$error = new WP_Error( 'simplexml_load_error', __( 'Could not load the XML string.', 'pronamic_ideal' ) );

			foreach ( libxml_get_errors() as $e ) {
				$error->add( 'libxml_error', $e->message, $e );
			}

			libxml_clear_errors();

			$result = $error;
		}

		// Set back to previous value
		libxml_use_internal_errors( $use_errors );

		return $result;
	}

	//////////////////////////////////////////////////

	/**
	 * Amount to cents
	 *
	 * @param float $price
	 * @return int
	 */
	public static function amount_to_cents( $price ) {
		return round( $price * 100 );
	}

	/**
	 * Cents to amount
	 *
	 * @param int $cents
	 * @return float
	 */
	public static function cents_to_amount( $cents ) {
		return $cents / 100;
	}

	/**
	 * String to amount (user input string)
	 *
	 * @version 1.3.1
	 * @since 1.3.0
	 * @param string $amount
	 * @return float
	 */
	public static function string_to_amount( $amount ) {
		// Remove thousands seperators
		$decimal_sep   = get_option( 'pronamic_pay_decimal_sep' );

		// Seperators
		$seperators = array( $decimal_sep, '.', ',' );
		$seperators = array_unique( array_filter( $seperators ) );

		// Check
		foreach ( array( -3, -2 ) as $i ) {
			$test = substr( $amount, $i, 1 );

			if ( in_array( $test, $seperators, true ) ) {
				$decimal_sep = $test;

				break;
			}
		}

		// Split
		$position = strrpos( $amount, $decimal_sep );

		if ( false !== $position ) {
			$full = substr( $amount, 0, $position );
			$half = substr( $amount, $position + 1 );

			$full = filter_var( $full, FILTER_SANITIZE_NUMBER_INT );
			$half = filter_var( $half, FILTER_SANITIZE_NUMBER_INT );

			$amount = $full . '.' . $half;
		} else {
			$amount = filter_var( $amount, FILTER_SANITIZE_NUMBER_INT );
		}

		// Filter
		$amount = filter_var( $amount, FILTER_VALIDATE_FLOAT );

		return $amount;
	}

	//////////////////////////////////////////////////

	/**
	 * Convert boolean to an numceric boolean
	 *
	 * @see https://github.com/eet-nu/buckaroo-ideal/blob/master/lib/buckaroo-ideal/request.rb#L136
	 * @param boolean $boolean
	 * @return int
	 */
	public static function to_numeric_boolean( $boolean ) {
		return $boolean ? 1 : 0;
	}

	//////////////////////////////////////////////////

	/**
	 * Convert boolean to an string boolean
	 *
	 * @see https://github.com/eet-nu/buckaroo-ideal/blob/master/lib/buckaroo-ideal/request.rb#L136
	 * @param boolean $boolean
	 * @return int
	 */
	public static function to_string_boolean( $boolean ) {
		return $boolean ? 'true' : 'false';
	}

	//////////////////////////////////////////////////

	public static function format_date( $format, DateTime $date = null ) {
		$result = null;

		if ( null !== $date ) {
			$result = $date->format( $format );
		}

		return $result;
	}

	/**
	 * Convert the specified period to a single char notation.
	 *
	 * @since 1.3.9
	 * @param string $period
	 * @return string
	 */
	public static function to_period( $period ) {
		if ( false !== strpos( $period, 'day' ) ) {
			return 'D';
		}

		if ( false !== strpos( $period, 'week' ) ) {
			return 'W';
		}

		if ( false !== strpos( $period, 'month' ) ) {
			return 'M';
		}

		if ( false !== strpos( $period, 'year' ) ) {
			return 'Y';
		}

		return $period;
	}

	//////////////////////////////////////////////////

	/**
	 * Build URL with the specified parameters
	 *
	 * @param string $url
	 * @param array $parameters
	 * @return string
	 */
	public static function build_url( $url, array $parameters ) {
		return $url . '?' . _http_build_query( $parameters, null, '&' );
	}

	//////////////////////////////////////////////////

	/**
	 * Convert input fields array to HTML.
	 *
	 * @param array $fields
	 * @return string
	 */
	public static function input_fields_html( array $fields ) {
		$html = '';

		foreach ( $fields as $field ) {
			if ( ! isset( $field['type'] ) ) {
				continue;
			}

			switch ( $field['type'] ) {
				case 'select':
					$html .= sprintf(
						'<label for="%s">%s</label> ',
						esc_attr( $field['id'] ),
						$field['label']
					);

					$html .= sprintf(
						'<select id="%s" name="%s">%s</select>',
						esc_attr( $field['id'] ),
						esc_attr( $field['name'] ),
						Pronamic_WP_HTML_Helper::select_options_grouped( $field['choices'] )
					);

					break;
			}
		}

		return $html;
	}
}
