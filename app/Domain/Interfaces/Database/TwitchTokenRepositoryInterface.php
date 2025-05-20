<?php

namespace App\Domain\Interfaces\Database;

interface TwitchTokenRepositoryInterface
{
    public function getStoredToken(): ?array;

    public function storeToken(string $accessToken, int $expiresIn): void;

    public function isTokenValidInDB(string $token): bool;
}
