<?php

use Rector\Config\RectorConfig;
use Rector\Php54\Rector\Array_\LongArrayToShortArrayRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;
use Rector\Visibility\Rector\ClassMethod\ExplicitPublicClassMethodRector;

return RectorConfig::configure()
    // register single rule
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withRules([
        LongArrayToShortArrayRector::class,
        ExplicitPublicClassMethodRector::class,
    ]);
    // here we can define, what prepared sets of rules will be applied
    // ->withPreparedSets(
    //     deadCode: true,
    //     codeQuality: true
    // );
