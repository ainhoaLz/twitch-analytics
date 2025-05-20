<?php

namespace App\Application\Services;

use App\Domain\Interfaces\TwitchAPI\TwichUserRepositoryInterface;
use App\Domain\Interfaces\TwitchAPI\TwitchStreamRepositoryInterface;

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
        $data = $this->streamRepository->getStreamsFromTwitch();

        if ($data['httpCode'] == 200) {
            return $this->processStreamData($data['streams'], $data['httpCode']);
        }
        return [null, $data['httpCode']];
    }

    private function processStreamData($response, $httpCode): array
    {
        $data = json_decode($response, true);
        if (isset($data["data"])) {
            $resp = [];
            foreach ($data["data"] as $stream) {
                $resp[] = [
                    "stream_id" => $stream["id"],
                    "user_id" => $stream["user_id"],
                    "user_name" => $stream["user_name"],
                    "viewer_count" => $stream["viewer_count"],
                    "title" => $stream["title"],
                ];
            }
            return [$resp, $httpCode];
        }
        return [null, $httpCode];
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
        $data = json_decode($response, true);
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
            return $response[1];
        }
        if ($response[0] == 400) {
            return respondWithError(400, "Invalid 'limit' parameter.");
        }
        if ($response[0] == 401) {
            return respondWithError(401, "Unauthorized. Twitch access token is invalid or has expired.");
        }
        if ($response[0] == 500) {
            return respondWithError(500, "Internal server error.");
        }
        return respondWithError($response[0], "Unknown error.");
    }

    private function ordenar($var1, $var2)
    {
        return $var2["viewer_count"] - $var1["viewer_count"];
    }
}
