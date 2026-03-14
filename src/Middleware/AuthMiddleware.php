<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Service\AuthService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Http\Status;
use Yiisoft\Router\UrlGeneratorInterface;

final class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private AuthService $auth,
        private ResponseFactoryInterface $responseFactory,
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->auth->isGuest()) {
            return $handler->handle($request);
        }
        $response = $this->responseFactory->createResponse(Status::FOUND);
        return $response->withHeader('Location', $this->urlGenerator->generate('admin/login'));
    }
}
