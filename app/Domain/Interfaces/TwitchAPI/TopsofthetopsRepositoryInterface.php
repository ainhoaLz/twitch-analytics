<?php

namespace App\Domain\Interfaces\TwitchAPI;

interface TopsofthetopsRepositoryInterface
{
    public function getTop40Videos(string $gameId): array;

    public function getTop3Games(): array;
}
