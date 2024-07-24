<?php
/**
 * Upgrades
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Upgrades
 */

namespace Pronamic\WordPress\Pay\Upgrades;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Upgrades
 *
 * @author     Remco Tolsma
 * @version    2.2.6
 * @since      2.2.6
 * @implements \IteratorAggregate<int, Upgrade>
 */
class Upgrades implements Countable, IteratorAggregate {
	/**
	 * Upgrades.
	 *
	 * @var array<Upgrade>
	 */
	private $upgrades;

	/**
	 * Executable.
	 *
	 * @var boolean
	 */
	private $executable;

	/**
	 * Construct.
	 */
	public function __construct() {
		$this->upgrades   = [];
		$this->executable = true;
	}

	/**
	 * Are executable.
	 *
	 * @return boolean True if upgrade are executable, false otherwise.
	 */
	public function are_executable() {
		return $this->executable;
	}

	/**
	 * Set the upgrades as executable or not.
	 *
	 * @param boolean $executable True if upgrades are executable, false otherwise.
	 * @return void
	 */
	public function set_executable( $executable ) {
		$this->executable = $executable;
	}

	/**
	 * Add upgrades.
	 *
	 * @param Upgrade $upgrade The upgrade to add.
	 * @return void
	 */
	public function add( Upgrade $upgrade ) {
		$this->upgrades[] = $upgrade;
	}

	/**
	 * Get iterator.
	 *
	 * @return Traversable
	 */
	public function getIterator(): Traversable {
		return new ArrayIterator( $this->upgrades );
	}

	/**
	 * Count upgrades.
	 *
	 * @return int
	 */
	public function count(): int {
		return count( $this->upgrades );
	}
}
