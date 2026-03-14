<?php

declare(strict_types=1);

ini_set('max_execution_time', '300');

use App\Environment;
use Psr\Log\LogLevel;
use Yiisoft\ErrorHandler\ErrorHandler;
use Yiisoft\ErrorHandler\Renderer\HtmlRenderer;
use Yiisoft\Log\Logger;
use Yiisoft\Log\Target\File\FileTarget;
use Yiisoft\Yii\Runner\Http\HttpApplicationRunner;

$root = dirname(__DIR__);

error_reporting(E_ALL & ~E_DEPRECATED);

putenv('APP_ENV=dev');
$_ENV['APP_ENV'] = 'dev';
putenv('APP_DEBUG=1');
$_ENV['APP_DEBUG'] = '1';
putenv('APP_C3=false');
$_ENV['APP_C3'] = 'false';
require_once $root . '/src/autoload.php';

if (Environment::appC3()) {
    $c3 = $root . '/c3.php';
    if (file_exists($c3)) {
        require_once $c3;
    }
}

/**
 * @psalm-var string $_SERVER['REQUEST_URI']
 */
// PHP built-in server routing.
if (PHP_SAPI === 'cli-server') {
    // Serve static files as is.
    /** @var string $path */
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file(__DIR__ . $path)) {
        return false;
    }

    // Explicitly set for URLs with dot.
    $_SERVER['SCRIPT_NAME'] = '/index.php';
}

// Run HTTP application runner
$runner = new HttpApplicationRunner(
    rootPath: $root,
    debug: Environment::appDebug(),
    checkEvents: Environment::appDebug(),
    environment: Environment::appEnv(),
    temporaryErrorHandler: new ErrorHandler(
        new Logger(
            [
                (new FileTarget($root . '/runtime/logs/app-container-building.log'))->setLevels([
                    LogLevel::EMERGENCY,
                    LogLevel::ERROR,
                    LogLevel::WARNING,
                ]),
            ],
        ),
        new HtmlRenderer(),
    ),
);
$runner->run();
