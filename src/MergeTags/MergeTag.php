<?php
/**
 * Merge Tag
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
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
	 * Resolver.
	 *
	 * @var callable
	 */
	private $resolver;

	/**
	 * Construct merge tag.
	 *
	 * @param string   $slug     Slug.
	 * @param callable $resolver Resolver.
	 */
	public function __construct( $slug, $resolver ) {
		$this->slug     = $slug;
		$this->resolver = $resolver;
	}

	/**
	 * Get slug.
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
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
