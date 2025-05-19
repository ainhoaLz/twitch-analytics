<?php

namespace App\Services;

class TwitchEnrichedStreamService
{
    public function getEnrichedStreams($accessToken, $clientId, $limit): array
    {
        $streams = $this->getStreams($accessToken, $clientId);
        $resp = $this->addDataToResponse($streams, $limit, $clientId, $accessToken);
        return $this->fetchResponse($resp);
    }

    private function getStreams($accessToken, $clientId): array
    {
        $url = "https://api.twitch.tv/helix/streams";
        $headers = [
            "Authorization: Bearer $accessToken",
            "Client-Id: $clientId",
            "Content-type: application/json"
        ];
        $data = $this->getStreamsFromTwitch($url, $headers);

        if ($data['httpCode'] == 200) {
            return $this->processStreamData($data['response'], $data['httpCode']);
        }
        return [null, $data['httpCode']];
    }

    private function getStreamsFromTwitch($url, $headers): array
    {
        $curlh = curl_init($url);
        curl_setopt($curlh, CURLOPT_HTTPGET, true);
        curl_setopt($curlh, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlh, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curlh, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlh, CURLOPT_SSL_VERIFYHOST, false);
        $response = curl_exec($curlh);
        $httpCode = curl_getinfo($curlh, CURLINFO_HTTP_CODE);
        curl_close($curlh);

        return [
            'response' => $response,
            'httpCode' => $httpCode
        ];
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

    private function addDataToResponse($streams, $limit, $clientId, $accessToken)
    {
        if ($streams[1] != 200) {
            return $streams;
        }
        usort($streams[0], [$this, "ordenar"]);

        if (!$this->isLimitValid($limit, count($streams[0]))) {
            return [400, null];
        }

        $limitedStreams = array_slice($streams[0], 0, $limit);

        return $this->addUserInfo($limitedStreams, $accessToken, $clientId);
    }

    private function isLimitValid($limit, $count): bool
    {
        return ($limit <= $count);
    }

    private function addUserInfo($stream, $accessToken, $clientId): array
    {
        $returnStream = $stream;
        $userIds = array_column($stream, "user_id");
        $url = "https://api.twitch.tv/helix/users?id=" . implode('&id=', $userIds);
        $headers = [
            "Authorization: Bearer $accessToken",
            "Client-Id: $clientId",
            "Content-type: application/json"
        ];
        $data = $this->getUsersFromTwitch($url, $headers);

        if ($data['httpCode'] != 200) {
            return [$data['httpCode'], null];
        }
        $users = $this->fetchUserData($data['response']);
        $this->addUserToStreams($returnStream, $users);

        return [$data['httpCode'], $returnStream];
    }

    private function getUsersFromTwitch($url, $headers): array
    {
        $curlh = curl_init();
        curl_setopt($curlh, CURLOPT_URL, $url);
        curl_setopt($curlh, CURLOPT_HTTPGET, true);
        curl_setopt($curlh, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlh, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curlh, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlh, CURLOPT_SSL_VERIFYHOST, false);
        $response = curl_exec($curlh);
        $httpCode = curl_getinfo($curlh, CURLINFO_HTTP_CODE);
        curl_close($curlh);

        return [
            'response' => $response,
            'httpCode' => $httpCode
        ];
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
