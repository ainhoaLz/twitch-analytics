<?php

namespace App\Domain\Interfaces\TwitchAPI;

interface TwichUserRepositoryInterface
{
    public function getUser(mixed $streamerId): array;
    public function getMultipleUsers(array $streamerIds): array;
}
