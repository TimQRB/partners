<?php

declare(strict_types=1);

namespace App\Model;

use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Query\Query;

final class Partnership
{
    private const TABLE = '{{%partnership}}';

    public static function findAllPublished(ConnectionInterface $db, ?string $search = null): array
    {
        $q = (new Query($db))
            ->from(self::TABLE)
            ->where(['published' => 1])
            ->orderBy(['created_at' => SORT_DESC]);
        if ($search !== null && $search !== '') {
            $q->andWhere([
                'or',
                ['like', 'org_name', $search],
                ['like', 'org_name_en', $search],
            ]);
        }
        $rows = $q->all();
        return is_array($rows) ? $rows : [];
    }

    public static function findById(ConnectionInterface $db, int $id): ?array
    {
        $row = (new Query($db))->from(self::TABLE)->where(['id' => $id])->one();
        return $row === false ? null : $row;
    }

    public static function findAll(ConnectionInterface $db): array
    {
        $rows = (new Query($db))->from(self::TABLE)->orderBy(['created_at' => SORT_DESC])->all();
        return is_array($rows) ? $rows : [];
    }

    public static function decodeJson(?string $json): array
    {
        if ($json === null || $json === '') {
            return [];
        }
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    public static function encodeJson(array $data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public static function countAll(ConnectionInterface $db): int
    {
        return (int) (new Query($db))->from(self::TABLE)->count();
    }
}
