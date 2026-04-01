<?php

declare(strict_types=1);

use App\Shared\ApplicationParams;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Assets\AssetManager;
use Yiisoft\Definitions\Reference;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Yii\View\Renderer\CsrfViewInjection;

$dbHost = getenv('DB_HOST') ?: '';
$dbPort = getenv('DB_PORT') ?: '';
$dbName = getenv('DB_NAME') ?: '';
$dbUser = getenv('DB_USER') ?: '';
$dbPassword = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '';

$publicUrl = getenv('APP_PUBLIC_URL') ?: '';
if ($publicUrl === '' && isset($_ENV['APP_PUBLIC_URL'])) {
    $publicUrl = (string) $_ENV['APP_PUBLIC_URL'];
}

return [
    'db.host' => $dbHost !== '' ? $dbHost : '127.0.0.1',
    'db.port' => $dbPort !== '' ? $dbPort : '3306',
    'db.name' => $dbName !== '' ? $dbName : 'yii1_db',
    'db.user' => $dbUser !== '' ? $dbUser : 'root',
    'db.password' => (string) $dbPassword,
    'db.charset' => 'utf8mb4',
    'db.tablePrefix' => 'tbl_',

    /** Публичный URL этого приложения (https://твой-домен), без слэша в конце — для ссылок и картинок в JSON API */
    'api.publicBaseUrl' => rtrim($publicUrl, '/'),

    'application' => require __DIR__ . '/application.php',

    'yiisoft/aliases' => [
        'aliases' => require __DIR__ . '/aliases.php',
    ],

    'yiisoft/view' => [
        'basePath' => null,
        'parameters' => [
            'assetManager' => Reference::to(AssetManager::class),
            'applicationParams' => Reference::to(ApplicationParams::class),
            'aliases' => Reference::to(Aliases::class),
            'urlGenerator' => Reference::to(UrlGeneratorInterface::class),
            'currentRoute' => Reference::to(CurrentRoute::class),
        ],
    ],

    'yiisoft/yii-view-renderer' => [
        'viewPath' => '@views',
        'layout' => 'layout/main',
        'injections' => [
            Reference::to(CsrfViewInjection::class),
        ],
    ],
];
