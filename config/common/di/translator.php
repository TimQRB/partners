<?php

declare(strict_types=1);

use Yiisoft\Definitions\Reference;
use Yiisoft\Translator\CategorySource;
use Yiisoft\Translator\IdMessageReader;
use Yiisoft\Translator\SimpleMessageFormatter;
use Yiisoft\Translator\Translator;
use Yiisoft\Translator\TranslatorInterface;

/** @var array $params */

$t = $params['yiisoft/translator'] ?? [];
$locale = $t['locale'] ?? 'ru';
$fallbackLocale = $t['fallbackLocale'] ?? 'ru';
$defaultCategory = $t['defaultCategory'] ?? 'labels';

return [
    'translation.categorySource.labels' => [
        'class' => CategorySource::class,
        '__construct()' => [
            'name' => $defaultCategory,
            'reader' => new IdMessageReader(),
            'formatter' => new SimpleMessageFormatter(),
            'writer' => null,
        ],
        'tags' => ['translation.categorySource'],
    ],
    TranslatorInterface::class => [
        'class' => Translator::class,
        '__construct()' => [
            $locale,
            $fallbackLocale,
            $defaultCategory,
            null,
            null,
        ],
        'addCategorySources()' => [
            'categories' => Reference::to('tag@translation.categorySource'),
        ],
    ],
];
