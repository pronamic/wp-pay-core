<?php
/**
 * XML Util
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core\XML
 */

namespace Pronamic\WordPress\Pay\Core\XML;

use DOMDocument;
use DOMNode;
use DOMText;

/**
 * Title: XML utility class
 * Description:
 * Copyright: 2005-2024 Pronamic
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 2.2.6
 * @since 1.2.1
 */
class Util {
	/**
	 * Create and add an element with the specified name and value to the specified parent.
	 *
	 * @param DOMDocument $document DOM document to add the specified node to.
	 * @param DOMNode     $node     DOM node to add a new element to.
	 * @param string      $name     Name of the new DOM element to add.
	 * @param string      $value    Value of the new DOM element to add.
	 *
	 * @return \DOMElement
	 */
	public static function add_element( DOMDocument $document, DOMNode $node, $name, $value = null ) {
		$element = $document->createElement( $name );

		if ( null !== $value ) {
			$element->appendChild( new DOMText( $value ) );
		}

		$node->appendChild( $element );

		return $element;
	}

	/**
	 * Add the specified elements to the parent node.
	 *
	 * @param DOMDocument $document DOM document to add the specified node to.
	 * @param DOMNode     $node     DOM node to add a new element to.
	 * @param array       $elements The elements (name => value pairs) to add.
	 * @return void
	 */
	public static function add_elements( DOMDocument $document, DOMNode $node, array $elements = [] ) {
		foreach ( $elements as $name => $value ) {
			$element = $document->createElement( $name );

			if ( null !== $value ) {
				$element->appendChild( new DOMText( $value ) );
			}

			$node->appendChild( $element );
		}
	}
}
