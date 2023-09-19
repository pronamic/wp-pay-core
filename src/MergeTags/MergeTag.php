<?php
/**
 * Merge Tag
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways
 */

namespace Pronamic\WordPress\Pay\MergeTags;

/**
 * Merge Tag class
 */
class MergeTag {
	/**
	 * Slug of this merge tag.
	 * 
	 * @var string
	 */
	private $slug;

	/**
	 * Name of this merge tag.
	 * 
	 * @var string
	 */
	private $name;

	/**
	 * Resolver.
	 * 
	 * @var callback
	 */
	private $resolver;

	/**
	 * Construct merge tag.
	 * 
	 * @param string   $slug     Slug.
	 * @param string   $name     Name.
	 * @param callback $resolver Resolver.
	 */
	public function __construct( $slug, $name, $resolver ) {
		$this->slug     = $slug;
		$this->name     = $name;
		$this->resolver = $resolver;
	}

	/**
	 * Resolve.
	 * 
	 * @return string
	 */
	public function resolve() {
		return \call_user_func( $this->resolver );
	}
}
