<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\User;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Session\SessionInterface;

final class AuthService
{
    private const SESSION_USER_ID = 'user_id';
    private const SESSION_USERNAME = 'username';
    private const SESSION_EMAIL = 'email';

    public function __construct(
        private SessionInterface $session,
        private ConnectionInterface $db,
    ) {}

    public function login(string $username, string $password): bool
    {
        $user = User::findByUsername($this->db, $username);
        if ($user === null) {
            return false;
        }
        $hash = $user['password'] ?? '';
        if (!password_verify($password, $hash)) {
            return false;
        }
        $this->session->set(self::SESSION_USER_ID, (int) $user['id']);
        $this->session->set(self::SESSION_USERNAME, $user['username']);
        $this->session->set(self::SESSION_EMAIL, $user['email'] ?? '');
        return true;
    }

    public function logout(): void
    {
        $this->session->remove(self::SESSION_USER_ID);
        $this->session->remove(self::SESSION_USERNAME);
        $this->session->remove(self::SESSION_EMAIL);
    }

    public function isGuest(): bool
    {
        return $this->session->get(self::SESSION_USER_ID) === null;
    }

    public function getId(): ?int
    {
        $id = $this->session->get(self::SESSION_USER_ID);
        return $id !== null ? (int) $id : null;
    }

    public function getUsername(): ?string
    {
        $v = $this->session->get(self::SESSION_USERNAME);
        return $v !== null ? (string) $v : null;
    }
}
