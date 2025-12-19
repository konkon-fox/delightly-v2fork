<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__)
    ->exclude([
        'vendor',
        'node_modules',
        '.vscode',
    ])
    ->name('*.php');

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        // 基本
        '@PSR12' => true,

        // インデント・改行
        'indentation_type' => true,
        'line_ending' => true,

        // 配列
        'array_syntax' => ['syntax' => 'short'],
        'trailing_comma_in_multiline' => [
            'elements' => ['arrays'],
        ],

        // use / import
        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
        ],
        'no_unused_imports' => true,

        // スペース
        'binary_operator_spaces' => [
            'default' => 'single_space',
        ],

        // 制御構文
        'control_structure_braces' => true,
        'control_structure_continuation_position' => true,

        // 文字列
        'single_quote' => true,

        // セミコロン
        'semicolon_after_instruction' => true,

        // risky
        'strict_comparison' => true,
        'strict_param' => true,
    ])
    ->setFinder($finder);
