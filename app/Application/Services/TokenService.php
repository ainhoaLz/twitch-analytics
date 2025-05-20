<?php

namespace App\Application\Services;

use App\Domain\Interfaces\Database\TokenRepositoryInterface;
use App\Domain\Interfaces\Database\UserRepositoryInterface;
use App\Helpers\ResponseHelper;
use Throwable;

class TokenService
{
    private TokenRepositoryInterface $tokenRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(TokenRepositoryInterface $tokenRepository, UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->tokenRepository = $tokenRepository;
    }

    public function generateToken(array|null $data): array
    {
        try {
            $userId = $this->userRepository->getUserId($data["email"], $data["api_key"]);
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
}
