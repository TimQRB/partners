<?php

declare(strict_types=1);

namespace App\Model;

use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Query\Query;

final class User
{
    private const TABLE = '{{%user}}';

    public function __construct(
        private ConnectionInterface $db,
    ) {}

    public static function findByUsername(ConnectionInterface $db, string $username): ?array
    {
        $row = (new Query($db))
            ->from(self::TABLE)
            ->where(['username' => $username])
            ->one();
        return $row === false ? null : $row;
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public static function countAll(ConnectionInterface $db): int
    {
        return (int) (new Query($db))->from(self::TABLE)->count();
    }
}
