<?php

namespace App\Domain\Interfaces;

interface UserRepositoryInterface
{
    public function existsByEmail(string $email): bool;

    public function updateApiKeyByEmail(string $email, string $apiKey): void;

    public function insertNewUser(string $email, string $apiKey, int $userId, string $createdAt): void;

    public function getNextUserId(): int;
}
