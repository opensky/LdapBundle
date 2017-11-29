<?php

return PhpCsFixer\Config::create()
    ->setRules([
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude(['bin', 'vendor'])
            ->in(__DIR__)
    )
;
