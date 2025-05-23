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

        switch ($httpCode) {
            case 200:
                if (empty($data['data'])) {
                    throw new NotFoundException("User not found.");
                }
                return $data['data'][0];

            case 401:
                throw new UnauthorizedException("Unauthorized. Twitch access token is invalid or has expired.");

            case 404:
                throw new NotFoundException("User not found.");

            case 500:
                throw new ServerException("Internal server error.");

            default:
                throw new UnknownException("Unknown error.", $httpCode);
        }
    }
}
