<?php

namespace App\Domain\Interfaces;

interface TokenRepositoryInterface
{
    public function insertNewToken(int $tokenId, int $userId, string $token): bool;

    public function getNewTokenId(): ?int;

    public function checkTokenExistence(int $userId): ?int;

    public function deleteExistingToken(int $tokenId): bool;
}
