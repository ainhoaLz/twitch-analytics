<?php

namespace App\Domain\Interfaces\TwitchAPI;

interface TwitchStreamRepositoryInterface
{
    public function getStreamsFromTwitch(): array;
    public function getEnrichedStreamsFromTwitch(): array;
}
