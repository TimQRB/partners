<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Model\Partnership;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Http\Status;
use Yiisoft\Router\HydratorAttribute\RouteArgument;

/**
 * Публичный read-only API для встраивания главной на внешний сайт (CORS).
 */
final class PartnershipApiController
{
    public function __construct(
        private ConnectionInterface $db,
        private ResponseFactoryInterface $responseFactory,
        private StreamFactoryInterface $streamFactory,
        private string $publicBaseUrl,
    ) {}

    public function list(ServerRequestInterface $request): ResponseInterface
    {
        $query = $request->getQueryParams();
        $search = isset($query['q']) ? trim((string) $query['q']) : null;
        $rows = Partnership::findAllPublished($this->db, $search);

        $payload = ['items' => array_map(fn (array $row) => $this->shapeCard($row), $rows)];

        return $this->json($payload);
    }

    public function view(#[RouteArgument('id')] string $id): ResponseInterface
    {
        $card = Partnership::findById($this->db, (int) $id);
        if ($card === null || empty($card['published'])) {
            return $this->json(['error' => 'Not found'], Status::NOT_FOUND);
        }

        return $this->json(['item' => $this->shapeCard($card, true)]);
    }

    public function preflight(): ResponseInterface
    {
        return $this->responseFactory->createResponse(Status::NO_CONTENT);
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function shapeCard(array $row, bool $full = false): array
    {
        $base = $this->publicBaseUrl;
        $basename = '';
        if (!empty($row['file_path'])) {
            $basename = basename(str_replace('\\', '/', (string) $row['file_path']));
        }

        $out = [
            'id' => (int) ($row['id'] ?? 0),
            'org_name' => (string) ($row['org_name'] ?? ''),
            'org_name_en' => (string) ($row['org_name_en'] ?? ''),
            'org_name_kz' => (string) ($row['org_name_kz'] ?? ''),
            'description' => (string) ($row['description'] ?? ''),
            'description_en' => (string) ($row['description_en'] ?? ''),
            'description_kz' => (string) ($row['description_kz'] ?? ''),
            'created_at' => isset($row['created_at']) ? (string) $row['created_at'] : '',
            'image_url' => $base !== '' && $basename !== ''
                ? $base . '/serve/partnership?f=' . rawurlencode($basename)
                : null,
            'card_url' => $base !== '' ? $base . '/card/' . (int) ($row['id'] ?? 0) : null,
        ];

        if ($full) {
            $out += [
                'org_type' => (string) ($row['org_type'] ?? ''),
                'country' => (string) ($row['country'] ?? ''),
                'city' => (string) ($row['city'] ?? ''),
                'website' => (string) ($row['website'] ?? ''),
                'contact_name' => (string) ($row['contact_name'] ?? ''),
                'contact_position' => (string) ($row['contact_position'] ?? ''),
                'contact_email' => (string) ($row['contact_email'] ?? ''),
                'contact_phone' => (string) ($row['contact_phone'] ?? ''),
                'contact_method' => (string) ($row['contact_method'] ?? ''),
                'cooperation_directions' => (string) ($row['cooperation_directions'] ?? ''),
                'activity_areas' => (string) ($row['activity_areas'] ?? ''),
                'interaction_format' => (string) ($row['interaction_format'] ?? ''),
                'subtasks' => Partnership::decodeJson(isset($row['subtasks']) ? (string) $row['subtasks'] : null),
                'subtasks_en' => Partnership::decodeJson(isset($row['subtasks_en']) ? (string) $row['subtasks_en'] : null),
                'subtasks_kz' => Partnership::decodeJson(isset($row['subtasks_kz']) ? (string) $row['subtasks_kz'] : null),
                'goals' => Partnership::decodeJson(isset($row['goals']) ? (string) $row['goals'] : null),
                'goals_en' => Partnership::decodeJson(isset($row['goals_en']) ? (string) $row['goals_en'] : null),
                'goals_kz' => Partnership::decodeJson(isset($row['goals_kz']) ? (string) $row['goals_kz'] : null),
                'events' => Partnership::decodeJson(isset($row['events']) ? (string) $row['events'] : null),
                'materials' => Partnership::decodeJson(isset($row['materials']) ? (string) $row['materials'] : null),
                'description_images' => Partnership::decodeJson(isset($row['description_images']) ? (string) $row['description_images'] : null),
            ];
        }

        return $out;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function json(array $data, int $status = Status::OK): ResponseInterface
    {
        $json = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $stream = $this->streamFactory->createStream($json);

        return $this->responseFactory->createResponse($status)
            ->withBody($stream)
            ->withHeader('Content-Type', 'application/json; charset=UTF-8');
    }
}
