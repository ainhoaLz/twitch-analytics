<?php

namespace App\Application\Services;

use App\Domain\Exceptions\ServerException;
use App\Domain\Interfaces\Database\UserRepositoryInterface;
use Throwable;

class RegisterService
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function register(string $email): array
    {
        try {
            $apiKey = bin2hex(random_bytes(16));

            if ($this->userRepository->existsByEmail($email)) {
                $this->userRepository->updateApiKeyByEmail($email, $apiKey);
                return $this->respondWithApiKey($apiKey);
            }

            $userId = $this->userRepository->getNextUserId();
            $createdAt = date("Y-m-d H:i:s");
            $this->userRepository->insertNewUser($email, $apiKey, $userId, $createdAt);

            return $this->respondWithApiKey($apiKey);
        } catch (Throwable $e) {
            throw new ServerException("Internal server error.");
        }
    }

    private function respondWithApiKey(string $apiKey): array
    {
        http_response_code(200);
        return ["api_key" => $apiKey];
    }
}
