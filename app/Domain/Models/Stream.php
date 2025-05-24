<?php

namespace App\Domain\Models;

class Stream
{
    private string $title;
    private string $userName;

    public function __construct(array $attributes)
    {
        $this->title = $attributes['title'];
        $this->userName = $attributes['user_name'];
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getUserName(): string
    {
        return $this->userName;
    }

    public function toArray(): array
    {
        return [
            'title'     => $this->title,
            'user_name' => $this->userName,
        ];
    }
}
