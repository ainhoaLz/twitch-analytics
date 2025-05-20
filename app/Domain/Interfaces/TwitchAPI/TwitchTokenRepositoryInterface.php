<?php

namespace App\Domain\Interfaces\TwitchAPI;

interface TwitchTokenRepositoryInterface
{
    public function getNewToken(string $clientId, string $clientSecret): ?string;
}
