<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Throwable;

class UserService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function register(array|null $data): array
    {
        try {
            if (empty($data['email'])) {
                return respondWithError(400, "The email is mandatory");
            }

            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return respondWithError(400, "The email must be a valid email address");
            }

            $apiKey = bin2hex(random_bytes(16));

            if ($this->userRepository->existsByEmail($data['email'])) {
                $this->userRepository->updateApiKeyByEmail($data['email'], $apiKey);
                return $this->respondWithApiKey($apiKey);
            }

            $userId = $this->userRepository->getNextUserId();
            $createdAt = date("Y-m-d H:i:s");
            $this->userRepository->insertNewUser($data['email'], $apiKey, $userId, $createdAt);

            return $this->respondWithApiKey($apiKey);
        } catch (Throwable $e) {
            return respondWithError(500, "Internal server error.");
        }
    }

    private function respondWithApiKey(string $apiKey): array
    {
        http_response_code(200);
        return ["api_key" => $apiKey];
    }
}
