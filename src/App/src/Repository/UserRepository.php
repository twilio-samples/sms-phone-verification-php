<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

final class UserRepository
{
    private PDO $pdo;

    public function __construct(?string $dbPath = null)
    {
        $dbPath = $dbPath ?? __DIR__ . '/../../../../data/database.sqlite';
        $this->pdo = new PDO('sqlite:' . $dbPath);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->initDatabase();
    }

    private function initDatabase(): void
    {
        $this->pdo->exec('
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                phone_number TEXT NOT NULL,
                verified INTEGER DEFAULT 0
            )
        ');
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function create(string $username, string $password, string $phoneNumber): int
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare('INSERT INTO users (username, password, phone_number, verified) VALUES (?, ?, ?, 0)');
        $stmt->execute([$username, $hashedPassword, $phoneNumber]);
        return (int) $this->pdo->lastInsertId();
    }

    public function setVerified(int $userId): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET verified = 1 WHERE id = ?');
        $stmt->execute([$userId]);
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
