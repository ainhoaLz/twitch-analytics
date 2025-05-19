<?php

namespace App\Application\Services;

use Exception;

class TwitchStreamService
{
    /**
     * @param string $accessToken
     * @param string $clientId
     * @return array
     * @throws Exception
     */
    public function getStreams(string $accessToken, string $clientId): array
    {
        $url = "https://api.twitch.tv/helix/streams";
        $curlh = curl_init($url);
        $headers = [
            "Authorization: Bearer $accessToken",
            "Client-Id: $clientId",
            "Content-Type: application/json"
        ];
        curl_setopt($curlh, CURLOPT_HTTPGET, true);
        curl_setopt($curlh, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlh, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curlh, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlh, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($curlh);
        $httpCode = curl_getinfo($curlh, CURLINFO_HTTP_CODE);
        curl_close($curlh);

        if ($httpCode === 200) {
            http_response_code(200);
            $data = json_decode($response, true);
            if (isset($data["data"])) {
                $resp = [];
                foreach ($data["data"] as $stream) {
                    if ($stream["type"] === "live") {
                        $resp[] = [
                            "title" => $stream["title"],
                            "user_name" => $stream["user_name"],
                        ];
                    }
                }
                return $resp;
            }
        }

        if ($httpCode === 401) {
            http_response_code(401);
            return ["error" => "Unauthorized. Twitch access token is invalid or has expired."];
        }

        if ($httpCode === 500) {
            http_response_code(500);
            return ["error" => "Internal server error."];
        }

        http_response_code($httpCode);
        return ["error" => "Unknown error."];
    }
}
