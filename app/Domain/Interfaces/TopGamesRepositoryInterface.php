<?php

namespace App\Domain\Interfaces;

interface TopGamesRepositoryInterface
{
    public function truncate(): void;
    public function update(array $videos): void;

    public function getUpdatedAt(): array;
    public function getTopGames(): array;
}
