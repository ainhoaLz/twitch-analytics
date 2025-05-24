<?php

namespace App\Domain\Models;

class EnrichedStream
{
    private string $streamId;
    private string $userId;
    private string $userName;
    private int $viewerCount;
    private string $title;
    private string $userDisplayName;
    private string $profileImageUrl;

    public function __construct(array $attributes)
    {
        $this->streamId         = $attributes['stream_id'];
        $this->userId           = $attributes['user_id'];
        $this->userName         = $attributes['user_name'];
        $this->viewerCount      = $attributes['viewer_count'];
        $this->title            = $attributes['title'];
        $this->userDisplayName  = $attributes['user_display_name'];
        $this->profileImageUrl  = $attributes['profile_image_url'];
    }

    public function getStreamId(): string
    {
        return $this->streamId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getUserName(): string
    {
        return $this->userName;
    }

    public function getViewerCount(): int
    {
        return $this->viewerCount;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getUserDisplayName(): string
    {
        return $this->userDisplayName;
    }

    public function getProfileImageUrl(): string
    {
        return $this->profileImageUrl;
    }

    public function toArray(): array
    {
        return [
            'stream_id'          => $this->streamId,
            'user_id'            => $this->userId,
            'user_name'          => $this->userName,
            'viewer_count'       => $this->viewerCount,
            'title'              => $this->title,
            'user_display_name'  => $this->userDisplayName,
            'profile_image_url'  => $this->profileImageUrl,
        ];
    }
}
