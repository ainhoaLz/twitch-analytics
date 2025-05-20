<?php

namespace App\Infrastructure\Repositories\Database;

use App\Domain\Interfaces\Database\TwitchTokenRepositoryInterface;
use PDO;

class TwitchTokenRepository implements TwitchTokenRepositoryInterface
{
    private ?PDO $connection;

    public function __construct(?PDO $connection)
    {
        $this->connection = $connection;
    }

    public function getStoredToken(): ?array
    {
        $query = $this->connection->prepare("SELECT token, expires_at FROM twitch_tokens");
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function storeToken(string $accessToken, int $expiresIn): void
    {
        $expiresAt = date("Y-m-d H:i:s", time() + $expiresIn);
        $insertQuery = $this->connection->prepare("INSERT INTO twitch_tokens (token, expires_at) VALUES (?, ?)");
        $insertQuery->execute([$accessToken, $expiresAt]);
    }

    public function isTokenValidInDB(string $token): bool
    {
        $query = $this->connection->prepare("SELECT id FROM tokens WHERE token = ? AND expires_at > NOW()");
        $query->execute([$token]);
        return (bool) $query->fetchColumn();
    }
}
