<?php

declare(strict_types=1);

namespace App\Controller;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Yiisoft\Router\HydratorAttribute\RouteArgument;

final class LogoController
{
    private const ALLOWED = ['znak_white.png', 'logo_white.png'];

    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private StreamFactoryInterface $streamFactory,
    ) {}

    public function file(#[RouteArgument('name')] string $name): ResponseInterface
    {
        if (!in_array($name, self::ALLOWED, true)) {
            return $this->responseFactory->createResponse(404);
        }
        $path = dirname(__DIR__, 2) . '/public/uploads/' . $name;
        if (!is_file($path) || !is_readable($path)) {
            return $this->responseFactory->createResponse(404);
        }
        $stream = $this->streamFactory->createStreamFromFile($path, 'r');
        return $this->responseFactory->createResponse(200)
            ->withHeader('Content-Type', 'image/png')
            ->withHeader('Cache-Control', 'public, max-age=86400')
            ->withBody($stream);
    }
}
