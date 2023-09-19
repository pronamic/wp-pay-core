<?php
/**
 * Merge Tags Controller
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways
 */

namespace Pronamic\WordPress\Pay\MergeTags;

/**
 * Merge Tags Controller class
 */
class MergeTagsController {
	/**
	 * Slug of this controller.
	 * 
	 * @var string
	 */
	private $slug;

	/**
	 * Merge tags.
	 *
	 * @var MergeTag[]
	 */
	private $merge_tags = [];

	/**
	 * Construct merge tags controller.
	 * 
	 * @param string $slug Slug.
	 */
	public function __construct( $slug ) {
		$this->slug = $slug;
	}

	/**
	 * Add merge tag.
	 * 
	 * @param MergeTag $merge_tag Merge tag.
	 */
	public function add_merge_tag( MergeTag $merge_tag ) {
		$this->merge_tags[] = $merge_tag;
	}

	/**
	 * Get merge tags.
	 * 
	 * @return MergeTag[]
	 */
	private function get_merge_tags() {
		$merge_tags = $this->merge_tags;
		$controller = $this;

		/**
		 * Filter merge tags.
		 *
		 * @param MergeTag[]          $merge_tags Merge tags.
		 * @param MergeTagsController $controller Merge tags controller.
		 */
		$merge_tags = \apply_filters(
			'pronamic_pay_merge_tags',
			$merge_tags,
			$controller
		);

		return $merge_tags;
	}

	/**
	 * Format string.
	 * 
	 * @param string $value
	 */
	public function format_string( $value ) {
		$replace_pairs = [];

		foreach ( $this->get_merge_tags() as $merge_tag ) {
			$replace_pairs['{' . $merge_tag->get_slug() . '}'] = $merge_tag->resolve();
		}

		$value = \strtr( $value, $replace_pairs );

		return $value;
	}
}
