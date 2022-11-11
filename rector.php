<?php
/**
 * Rector config.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 * @link      https://getrector.org
 */

declare( strict_types=1 );

use Rector\CodeQuality\Rector\ClassMethod\ReturnTypeFromStrictScalarReturnExprRector;
use Rector\Config\RectorConfig;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNativeCallRector;

return static function( RectorConfig $config ): void {
	$config->paths(
		[
			__DIR__ . '/src',
		]
	);

	$config->sets(
		[
			LevelSetList::UP_TO_PHP_74,
		]
	);

	$config->rules(
		[
			ReturnTypeFromStrictNativeCallRector::class,
			ReturnTypeFromStrictScalarReturnExprRector::class,
			AddVoidReturnTypeWhereNoReturnRector::class,
		]
	);

	$config->skip(
		[
			AddLiteralSeparatorToNumberRector::class,
			ClosureToArrowFunctionRector::class,
		]
	);

	$config->importNames();
	$config->importShortClasses( false );
	$config->phpstanConfig( __DIR__ . '/phpstan.neon.dist' );
	$config->indent( "\t", 1 );
};
