<?php

use PhpCsFixer\Finder;
use PhpCsFixer\Config;

$finder = (new Finder())
    ->in([
        __DIR__ . "/src",
        __DIR__ . "/migrations",
        __DIR__ . "/tests"
    ]);
;

return (new Config())
    ->setRules([
        '@PHP83Migration' => true,
        '@PHP84Migration' => true,
        'declare_strict_types' => true,
        'nullable_type_declaration' => ['syntax' => 'union']
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true
    )
;