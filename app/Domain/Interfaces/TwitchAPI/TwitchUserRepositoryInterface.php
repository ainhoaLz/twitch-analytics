<?php

namespace App\Domain\Interfaces\TwitchAPI;

interface TwitchUserRepositoryInterface
{
    public function getUser(mixed $streamerId): array;
    public function getMultipleUsers(array $streamerIds): array;
}
