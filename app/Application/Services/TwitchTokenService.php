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
        //        $headers = $request->headers->all(); no funciona ahora mismo
        $headers = getAllHeaders();
        if (!isset($headers['Authorization'])) {
            return false;
        }
        $authHeader = $headers['Authorization'];
        $token = str_replace('Bearer ', '', $authHeader);
        return $this->dbRepository->isTokenValidInDB($token);
    }
}
