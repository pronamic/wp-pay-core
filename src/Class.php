<?php

/**
 * Title: Class
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.1.0
 * @since 1.1.0
 */
class Pronamic_WP_Pay_Class {
	/**
	 * Method exists
	 *
	 * This helper function was created to fix an issue with `method_exists` calls
	 * and non existings classes.
	 *
	 * @param string $class
	 * @param string $method
	 * @return boolean
	 */
	public static function method_exists( $class, $method ) {
		return class_exists( $class ) && method_exists( $class, $method );
	}
}
