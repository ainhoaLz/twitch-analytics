<?php

namespace App\Application\Services;

use App\Domain\Interfaces\Database\TwitchTokenRepositoryInterface;
use App\Domain\Interfaces\TwitchAPI\TwichUserRepositoryInterface;
use Exception;

class TwitchUserService
{
    protected string $accessToken;
    protected string $clientId;
    private TwichUserRepositoryInterface $userRepository;

    public function __construct(TwichUserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @throws Exception
     */
    public function getUserById(mixed $streamerId): array
    {
        $userInfo = $this->userRepository->getUser($streamerId);
        $httpCode = $userInfo['httpCode'];
        $apiResponse = $userInfo['user'];
        if ($httpCode == 200) {
            $data = json_decode($apiResponse, true);
            return $data['data'][0];
        }
        if ($httpCode == 400) {
            return respondWithError(400, "Invalid or missing 'id' parameter.");
        }
        if ($httpCode == 401) {
            return respondWithError(401, "Unauthorized. Twitch access token is invalid or has expired.");
        }
        if ($httpCode == 404) {
            return respondWithError(404, "User not found.");
        }
        if ($httpCode == 500) {
            return respondWithError(500, "Internal server error.");
        }
        return respondWithError($httpCode, "Error: $httpCode");
    }
}
