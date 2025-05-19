<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Interfaces\UserRepositoryInterface;
use App\Infrastructure\Database\DatabaseConnection;
use Exception;
use PDO;

class UserRepository implements UserRepositoryInterface
{
    private DatabaseConnection $dbConnection;
    private ?PDO $connection;

    public function __construct(DatabaseConnection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
        $this->connection = $this->dbConnection->connect();
    }

    public function existsByEmail(string $email): bool
    {
        $stmt = $this->connection->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() != null;
    }

    /**
     * @throws Exception
     */
    public function updateApiKeyByEmail(string $email, string $apiKey): void
    {
        $stmt = $this->connection->prepare("UPDATE users SET api_key = ? WHERE email = ?");
        if (!$stmt->execute([$apiKey, $email])) {
            throw new Exception("Error updating API key in database.");
        }
    }

    /**
     * @throws Exception
     */
    public function insertNewUser(string $email, string $apiKey, int $userId, string $createdAt): void
    {
        $stmt = $this->connection->prepare("INSERT INTO users (email, api_key, id, created_at) VALUES (?, ?, ?, ?)");
        if (!$stmt->execute([$email, $apiKey, $userId, $createdAt])) {
            throw new Exception("Error inserting user to database.");
        }
    }

    public function getNextUserId(): int
    {
        $stmt = $this->connection->query("SELECT COALESCE(MAX(id), 0) + 1 AS new_id FROM users");
        return (int) $stmt->fetchColumn();
    }

    public function getUserId(string $email, string $apiKey): ?int
    {
        $stmt = $this->connection->prepare("SELECT id FROM users WHERE email = ? AND api_key = ?");
        $stmt->execute([$email, $apiKey]);
        return $stmt->fetchColumn() ?: null;
    }
}
