<?php

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector;

return static function (RectorConfig $config): void {
    $config->paths([
        __DIR__ . '/src',
    ]);

    $config->rules([
        AddReturnTypeDeclarationRector::class,
        AddParamTypeDeclarationRector::class,
        TypedPropertyFromAssignsRector::class,
    ]);
};
