<?php

namespace App\Application\Services;

use App\Domain\Exceptions\ServerException;
use App\Domain\Exceptions\UnauthorizedException;
use App\Domain\Exceptions\UnknownException;
use App\Domain\Interfaces\TwitchAPI\TwichUserRepositoryInterface;
use App\Domain\Interfaces\TwitchAPI\TwitchStreamRepositoryInterface;
use App\Domain\Models\EnrichedStream;

class TwitchEnrichedStreamService
{
    private TwitchStreamRepositoryInterface $streamRepository;
    private TwichUserRepositoryInterface $userRepository;
    public function __construct(
        TwitchStreamRepositoryInterface $streamRepository,
        TwichUserRepositoryInterface $userRepository
    ) {
        $this->userRepository = $userRepository;
        $this->streamRepository = $streamRepository;
    }
    public function getEnrichedStreams($limit): array
    {
        $streams = $this->getStreams();
        $resp = $this->addDataToResponse($streams, $limit);
        return $this->fetchResponse($resp);
    }

    private function getStreams(): array
    {
        $data = $this->streamRepository->getEnrichedStreamsFromTwitch();

        if ($data['httpCode'] == 200) {
            return $this->processStreamData($data['streams'], $data['httpCode']);
        }
        return [null, $data['httpCode']];
    }

    private function processStreamData($streams, $httpCode): array
    {
        $resp = [];

        foreach ($streams as $stream) {
            $resp[] = [
                'stream_id' => $stream->getStreamId(),
                'user_id' => $stream->getUserId(),
                'user_name' => $stream->getUserName(),
                'viewer_count' => $stream->getViewerCount(),
                'title' => $stream->getTitle(),
            ];
        }

        return [$resp, $httpCode];
    }

    private function addDataToResponse($streams, $limit)
    {
        if ($streams[1] != 200) {
            return $streams;
        }
        usort($streams[0], [$this, "ordenar"]);

        if (!$this->isLimitValid($limit, count($streams[0]))) {
            return [400, null];
        }

        $limitedStreams = array_slice($streams[0], 0, $limit);

        return $this->addUserInfo($limitedStreams);
    }

    private function isLimitValid($limit, $count): bool
    {
        return ($limit <= $count);
    }

    private function addUserInfo($stream): array
    {
        $returnStream = $stream;
        $userIds = array_column($stream, "user_id");
        $data = $this->userRepository->getMultipleUsers($userIds);

        if ($data['httpCode'] != 200) {
            return [$data['httpCode'], null];
        }
        $users = $this->fetchUserData($data['response']);
        $this->addUserToStreams($returnStream, $users);

        return [$data['httpCode'], $returnStream];
    }

    private function fetchUserData($response): ?array
    {
        // Si $response ya es array, no hacer json_decode
        if (is_array($response)) {
            $data = $response;
        } else {
            $data = json_decode($response, true);
        }

        if (isset($data["data"])) {
            $users = [];
            foreach ($data["data"] as $user) {
                $users[$user["id"]] = [
                    "user_display_name" => $user["display_name"],
                    "profile_image_url" => $user["profile_image_url"],
                ];
            }
            return $users;
        }
        return null;
    }

    private function addUserToStreams(&$stream, $users): void
    {
        foreach ($stream as $index => $s) {
            $userId = $s["user_id"];
            if (isset($users[$userId])) {
                $stream[$index]["user_display_name"] = $users[$userId]["user_display_name"];
                $stream[$index]["profile_image_url"] = $users[$userId]["profile_image_url"];
            }
        }
    }

    private function fetchResponse($response): array
    {
        if ($response[0] == 200) {
            http_response_code(200);
            return array_map(fn ($streamData) => new EnrichedStream($streamData), $response[1]);
        }
        if ($response[0] == 401) {
            throw new UnauthorizedException("Unauthorized. Twitch access token is invalid or has expired.");
        }
        if ($response[0] == 500) {
            throw new ServerException("Internal server error.");
        }
        throw new UnknownException("Unknown error.", $response[0]);
    }

    private function ordenar($var1, $var2)
    {
        return $var2["viewer_count"] - $var1["viewer_count"];
    }
}
