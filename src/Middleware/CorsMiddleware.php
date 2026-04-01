<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Http\Status;

/**
 * Отдаёт CORS-заголовки только для origin из списка CORS_ALLOWED_ORIGINS (через запятую).
 * Без переменной окружения cross-origin не разрешается.
 */
final class CorsMiddleware implements MiddlewareInterface
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $origin = $request->getHeaderLine('Origin');
        $allowedOrigin = $this->matchAllowedOrigin($origin);

        if ($request->getMethod() === 'OPTIONS' && $allowedOrigin !== null) {
            $response = $this->responseFactory->createResponse(Status::NO_CONTENT);
            return $this->withCors($response, $allowedOrigin, $request);
        }

        $response = $handler->handle($request);
        if ($allowedOrigin !== null) {
            return $this->withCors($response, $allowedOrigin, $request);
        }

        return $response;
    }

    /**
     * @return list<non-empty-string>
     */
    private function parseAllowedOrigins(): array
    {
        $raw = getenv('CORS_ALLOWED_ORIGINS');
        if ($raw === false || $raw === '') {
            $raw = $_ENV['CORS_ALLOWED_ORIGINS'] ?? '';
        }
        if ($raw === '') {
            return [];
        }
        $parts = preg_split('/\s*,\s*/', (string) $raw, -1, PREG_SPLIT_NO_EMPTY);
        if (!is_array($parts)) {
            return [];
        }
        $out = [];
        foreach ($parts as $part) {
            $part = trim((string) $part);
            if ($part !== '' && str_starts_with($part, 'http')) {
                $out[] = $part;
            }
        }

        return $out;
    }

    private function matchAllowedOrigin(string $origin): ?string
    {
        if ($origin === '') {
            return null;
        }
        foreach ($this->parseAllowedOrigins() as $allowed) {
            if ($origin === $allowed) {
                return $origin;
            }
        }

        return null;
    }

    private function withCors(
        ResponseInterface $response,
        string $reflectOrigin,
        ServerRequestInterface $request,
    ): ResponseInterface {
        $requestedHeaders = $request->getHeaderLine('Access-Control-Request-Headers');
        $allowHeaders = $requestedHeaders !== ''
            ? $requestedHeaders
            : 'Content-Type, Accept, Authorization';

        return $response
            ->withHeader('Access-Control-Allow-Origin', $reflectOrigin)
            ->withHeader('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', $allowHeaders)
            ->withHeader('Access-Control-Max-Age', '86400');
    }
}
