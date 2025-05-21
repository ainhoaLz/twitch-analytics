<?php

namespace App\Application\Services;

use App\Domain\Interfaces\Database\TwitchTokenRepositoryInterface;
use DateTime;
use Illuminate\Http\Request;
use Throwable;

class TwitchTokenService
{
    private TwitchTokenRepositoryInterface $dbRepository;
    private \App\Domain\Interfaces\TwitchAPI\TwitchTokenRepositoryInterface $apiRepository;

    public function __construct(
        TwitchTokenRepositoryInterface $twitchTokenRepo,
        \App\Domain\Interfaces\TwitchAPI\TwitchTokenRepositoryInterface $apiRepository
    ) {
        $this->dbRepository = $twitchTokenRepo;
        $this->apiRepository = $apiRepository;
    }

    public function getToken(string $clientId, string $clientSecret): ?string
    {
        try {
            $storedToken = $this->dbRepository->getStoredToken();
            if ($storedToken && !$this->isTokenExpired(new DateTime($storedToken['expires_at']))) {
                return $storedToken['token'];
            }

            return $this->apiRepository->getNewToken($clientId, $clientSecret);
        } catch (Throwable $e) {
            return null;
        }
    }

    private function isTokenExpired(DateTime $expiresAt): bool
    {
        $now = new DateTime();
        return $now >= $expiresAt;
    }

    public function isTokenValid(Request $request): bool
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return false;
        }

        $token = substr($authHeader, 7);
        return $this->dbRepository->isTokenValidInDB($token);
    }

}
