<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/backend')
    ->in(__DIR__ . '/common')
    ->in(__DIR__ . '/console')
    ->in(__DIR__ . '/environments')
    ->in(__DIR__ . '/frontend')
    ->exclude('config')
    ->exclude('mail')
    ->exclude('runtime')
    ->exclude('views')
    ->exclude('web')
    ->exclude('messages');


$config = new PhpCsFixer\Config();
return $config->setRules([
    '@PSR12' => true,
    'strict_param' => true,
    'declare_strict_types' => true,
    'no_unused_imports' => true,
    'ordered_imports' => [
        'sort_algorithm' => 'alpha',
        'imports_order' => ['class', 'function', 'const'],
    ],
    'static_lambda' => true,
    'global_namespace_import' => [
        'import_classes' => true,
        'import_constants' => true,
        'import_functions' => true,
    ],
])
    ->setFinder($finder);