<?php

namespace App\Application\Services;

use App\Domain\Exceptions\ServerException;
use App\Domain\Exceptions\UnauthorizedException;
use App\Domain\Exceptions\UnknownException;
use App\Domain\Exceptions\NotFoundException;
use App\Domain\Interfaces\Database\TwitchTokenRepositoryInterface;
use App\Domain\Interfaces\TwitchAPI\TwichUserRepositoryInterface;
use Exception;

class TwitchUserService
{
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
        $data = json_decode($apiResponse, true);
        if (empty($data['data'])) {
            throw new NotFoundException("User not found.");
        }
        if ($httpCode == 200) {
            return $data['data'][0];
        }
        if ($httpCode == 401) {
            throw new UnauthorizedException("Unauthorized. Twitch access token is invalid or has expired.");
        }
        if ($httpCode == 404) {
            throw new NotFoundException("User not found.");
        }
        if ($httpCode == 500) {
            throw new ServerException("Internal server error.");
        }
        throw new UnknownException("Unknown error.", $httpCode);
    }
}
