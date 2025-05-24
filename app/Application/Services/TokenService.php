<?php

namespace App\Application\Services;

use App\Domain\Exceptions\ServerException;
use App\Domain\Exceptions\UnauthorizedException;
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
        $userId = $this->userRepository->getUserId($data["email"], $data["api_key"]);
        if (empty($userId)) {
            throw new UnauthorizedException("Unauthorized. API access token is invalid.");
        }

        try {
            $token = bin2hex(random_bytes(32));
            $tokenId = $this->tokenRepository->getNewTokenId();

            $existingTokenId = $this->tokenRepository->checkTokenExistence($userId);
            if (!empty($existingTokenId)) {
                $this->tokenRepository->deleteExistingToken($existingTokenId);
            }

            $this->tokenRepository->insertNewToken($tokenId, $userId, $token);

            return ["token" => $token];
        } catch (Throwable $e) {
            throw new ServerException("Internal server error.");
        }
    }
}
