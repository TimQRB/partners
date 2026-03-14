<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Partnership;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Router\HydratorAttribute\RouteArgument;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class LandingController
{
    public function __construct(
        private WebViewRenderer $view,
        private ConnectionInterface $db,
    ) {}

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $query = $request->getQueryParams();
        $search = isset($query['q']) ? trim((string) $query['q']) : null;
        $cards = Partnership::findAllPublished($this->db, $search);
        return $this->view->render('landing/index', ['cards' => $cards, 'search' => $search ?? '']);
    }

    public function view(#[RouteArgument('id')] string $id): ResponseInterface
    {
        $card = Partnership::findById($this->db, (int) $id);
        if ($card === null || empty($card['published'])) {
            throw new \RuntimeException('Not found', 404);
        }
        return $this->view->render('landing/card', ['card' => $card]);
    }
}
