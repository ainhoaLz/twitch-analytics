<?php

namespace App\Domain\Models;

class User
{
    private string $userId;
    private string $login;
    private string $displayName;
    private string $type;
    private string $broadcasterType;
    private string $description;
    private string $profileImageUrl;
    private string $offlineImageUrl;
    private int $viewCount;
    private string $createdAt;

    public function __construct(array $userAttributes)
    {
        $this->userId = $userAttributes['userId'];
        $this->login = $userAttributes['login'];
        $this->displayName = $userAttributes['displayName'];
        $this->type = $userAttributes['type'];
        $this->broadcasterType = $userAttributes['broadcasterType'];
        $this->description = $userAttributes['description'];
        $this->profileImageUrl = $userAttributes['profileImageUrl'];
        $this->offlineImageUrl = $userAttributes['offlineImageUrl'];
        $this->viewCount = $userAttributes['viewCount'];
        $this->createdAt = $userAttributes['createdAt'];
    }

    public function getId(): string
    {
        return $this->userId;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getBroadcasterType(): string
    {
        return $this->broadcasterType;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getProfileImageUrl(): string
    {
        return $this->profileImageUrl;
    }

    public function getOfflineImageUrl(): string
    {
        return $this->offlineImageUrl;
    }

    public function getViewCount(): int
    {
        return $this->viewCount;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->userId,
            'login' => $this->login,
            'display_name' => $this->displayName,
            'type' => $this->type,
            'broadcaster_type' => $this->broadcasterType,
            'description' => $this->description,
            'profile_image_url' => $this->profileImageUrl,
            'offline_image_url' => $this->offlineImageUrl,
            'view_count' => $this->viewCount,
            'created_at' => $this->createdAt,
        ];
    }
}
