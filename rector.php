<?php

declare( strict_types=1 );

use Rector\CodeQuality\Rector\ClassMethod\ReturnTypeFromStrictScalarReturnExprRector;
use Rector\Config\RectorConfig;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNativeCallRector;

return static function ( RectorConfig $rectorConfig ): void {
	$rectorConfig->paths(
		[
			__DIR__ . '/src',
		]
	);

	// define sets of rules
	$rectorConfig->sets(
		[
			LevelSetList::UP_TO_PHP_74,
		]
	);

	$rectorConfig->rules( [
		ReturnTypeFromStrictNativeCallRector::class,
		ReturnTypeFromStrictScalarReturnExprRector::class,
		AddVoidReturnTypeWhereNoReturnRector::class,
	] );

	$rectorConfig->skip( [
		AddLiteralSeparatorToNumberRector::class,
		ClosureToArrowFunctionRector::class
	] );

	$rectorConfig->importNames();
	$rectorConfig->importShortClasses( false );
	$rectorConfig->phpstanConfig( __DIR__ . '/phpstan.neon.dist' );
	$rectorConfig->indent( "\t", 1 );
};
