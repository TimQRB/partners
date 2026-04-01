<?php

declare(strict_types=1);

use Yiisoft\Db\Cache\SchemaCache;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Mysql\Connection;
use Yiisoft\Db\Mysql\Driver;
use Yiisoft\Definitions\Reference;

/** @var array $params */

$host = $params['db.host'] ?? '127.0.0.1';
$port = $params['db.port'] ?? '3306';
$dbname = $params['db.name'] ?? 'yii1_db';
$username = $params['db.user'] ?? 'root';
$password = $params['db.password'] ?? '';
$charset = $params['db.charset'] ?? 'utf8mb4';
$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

$tablePrefix = $params['db.tablePrefix'] ?? 'tbl_';

return [
    ConnectionInterface::class => [
        'class' => Connection::class,
        '__construct()' => [
            'driver' => new Driver($dsn, $username, $password, []),
            'schemaCache' => Reference::to(SchemaCache::class),
        ],
        'setTablePrefix()' => [$tablePrefix],
    ],
];
