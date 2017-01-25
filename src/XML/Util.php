<?php

/**
 * Title: XML utility class
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.2.1
 * @since 1.2.1
 */
class Pronamic_WP_Pay_XML_Util {
	/**
	 * Create and add an element with the specified name and value to the specified parent
	 *
	 * @param DOMDocument $document
	 * @param DOMNode $parent
	 * @param string $name
	 * @param string $value
	 */
	public static function add_element( DOMDocument $document, DOMNode $parent, $name, $value = null ) {
		$element = $document->createElement( $name );

		if ( null !== $value ) {
			$element->appendChild( new DOMText( $value ) );
		}

		$parent->appendChild( $element );

		return $element;
	}

	/**
	 * Add the specified elements to the parent node
	 *
	 * @param DOMDocument $document
	 * @param DOMNode $parent
	 * @param array $elements
	 */
	public static function add_elements( DOMDocument $document, DOMNode $parent, array $elements = array() ) {
		foreach ( $elements as $name => $value ) {
			$element = $document->createElement( $name );

			if ( null !== $value ) {
				$element->appendChild( new DOMText( $value ) );
			}

			$parent->appendChild( $element );
		}
	}
}
