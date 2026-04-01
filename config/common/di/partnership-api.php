<?php

declare(strict_types=1);

use App\Controller\Api\PartnershipApiController;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Definitions\Reference;

/** @var array $params */

return [
    PartnershipApiController::class => [
        '__construct()' => [
            Reference::to(ConnectionInterface::class),
            Reference::to(ResponseFactoryInterface::class),
            Reference::to(StreamFactoryInterface::class),
            $params['api.publicBaseUrl'] ?? '',
        ],
    ],
];
