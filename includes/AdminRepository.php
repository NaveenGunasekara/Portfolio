<?php
/**
 * Admin authentication data access.
 */
declare(strict_types=1);

final class AdminRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, username, password_hash FROM admins WHERE username = :u LIMIT 1');
        $stmt->execute(['u' => $username]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function verify(string $username, string $password): ?array
    {
        $admin = $this->findByUsername($username);
        if (!$admin) {
            return null;
        }
        if (!password_verify($password, $admin['password_hash'])) {
            return null;
        }
        return $admin;
    }
}
