<?php

namespace App\Services;

use App\Helpers\ResponseHelper;
use App\Repositories\TokenRepository;
use PDO;
use Symfony\Component\VarDumper\Cloner\Data;
use Throwable;

class TokenService
{
    private ?PDO $connection;
    private DatabaseConnection $dbConnection;
    private TokenRepository $tokenRepository;

    public function __construct(DatabaseConnection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
        $this->connection = $this->dbConnection->connect();
        $this->tokenRepository = new TokenRepository($this->connection);
    }

    public function generateToken(array|null $data): array
    {
        try {
            $userId = $this->getUserId($data["email"], $data["api_key"]);
            if (empty($userId)) {
                return respondWithError(401, "Unauthorized. API access token is invalid.");
            }

            $token = bin2hex(random_bytes(32));
            $tokenId = $this->tokenRepository->getNewTokenId();

            $existingTokenId = $this->tokenRepository->checkTokenExistence($userId);
            if (!empty($existingTokenId)) {
                $this->tokenRepository->deleteExistingToken($existingTokenId);
            }

            $this->tokenRepository->insertNewToken($tokenId, $userId, $token);

            http_response_code(200);
            return ["token" => $token];
        } catch (Throwable $e) {
            http_response_code(500);
            return respondWithError(500, "Internal server error.");
        }
    }

    private function getUserId(string $email, string $apiKey): ?int
    {
        $stmt = $this->connection->prepare("SELECT id FROM users WHERE email = ? AND api_key = ?");
        $stmt->execute([$email, $apiKey]);
        return $stmt->fetchColumn() ?: null;
    }
}
