<?php

declare(strict_types=1);

namespace App\Controller;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class ServeController
{
    private const MIMES = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'svg' => 'image/svg+xml',
    ];

    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private StreamFactoryInterface $streamFactory,
    ) {}

    public function partnership(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        $name = isset($params['f']) ? trim((string) $params['f']) : '';
        $name = basename(str_replace('\\', '/', $name));
        if ($name === '' || str_contains($name, '..') || str_contains($name, '/')) {
            return $this->responseFactory->createResponse(404);
        }
        $dirs = [
            dirname(__DIR__, 2) . '/public/uploads/partnerships',
            dirname(__DIR__, 2) . '/Controller/public/uploads/partnerships',
        ];
        $realPath = null;
        $ext = null;
        foreach ($dirs as $dir) {
            $path = $dir . '/' . $name;
            if (!is_file($path) || !is_readable($path)) {
                continue;
            }
            $realDir = realpath($dir);
            $candidate = realpath($path);
            if ($candidate === false || $realDir === false || !str_starts_with($candidate, $realDir)) {
                continue;
            }
            $realPath = $candidate;
            $ext = strtolower(pathinfo($candidate, PATHINFO_EXTENSION));
            break;
        }
        if ($realPath === null) {
            return $this->responseFactory->createResponse(404);
        }
        $mime = self::MIMES[$ext ?? ''] ?? 'application/octet-stream';
        $stream = $this->streamFactory->createStreamFromFile($realPath, 'r');
        return $this->responseFactory->createResponse(200)
            ->withHeader('Content-Type', $mime)
            ->withHeader('Cache-Control', 'public, max-age=86400')
            ->withBody($stream);
    }
}
