<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
	->withPaths(
		[
			__DIR__ . '/includes',
			__DIR__ . '/node_modules',
			__DIR__ . '/src',
			__DIR__ . '/tests',
			__DIR__ . '/views',
		]
	)
	// uncomment to reach your current PHP version
	// ->withPhpSets()
	->withTypeCoverageLevel( 0 );
