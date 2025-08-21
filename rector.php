<?php
/**
 * Rector
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2025 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;

return RectorConfig::configure()
	->withPaths(
		[
			__DIR__ . '/includes',
			__DIR__ . '/src',
			__DIR__ . '/tests',
			__DIR__ . '/views',
		]
	)
	->withPhpSets()
	->withSkip(
		[
			ClassPropertyAssignToConstructorPromotionRector::class,
		]
	)
	->withTypeCoverageLevel( 0 );
