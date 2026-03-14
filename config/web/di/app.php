<?php

declare(strict_types=1);

use App\Service\AuthService;
use Yiisoft\Translator\MessageFormatterInterface;
use Yiisoft\Translator\NullMessageFormatter;

return [
    MessageFormatterInterface::class => NullMessageFormatter::class,
    AuthService::class => [
        'class' => AuthService::class,
    ],
];
