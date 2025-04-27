<?php

namespace App\Repositories;

use PDO;

class TokenRepository
{
    private ?PDO $connection;

    public function __construct(?PDO $connection)
    {
        $this->connection = $connection;
    }

    public function insertNewToken(int $tokenId, int $userId, string $token): bool
    {
        $sql = "
            INSERT INTO tokens (id, user_id, token, created_at, expires_at)
            VALUES (?, ?, ?, NOW(), NOW() + INTERVAL '3 days')
        ";
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute([$tokenId, $userId, $token]);
    }

    public function getNewTokenId(): ?int
    {
        $stmt = $this->connection->query("SELECT COALESCE(max(id),0) + 1 AS new_id FROM tokens");
        return $stmt->fetchColumn() ?: null;
    }

    public function checkTokenExistence(int $userId): ?int
    {
        $stmt = $this->connection->prepare("SELECT id FROM tokens WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn() ?: null;
    }

    public function deleteExistingToken(int $tokenId): bool
    {
        $stmt = $this->connection->prepare("DELETE FROM tokens WHERE id = ?");
        return $stmt->execute([$tokenId]);
    }
}
