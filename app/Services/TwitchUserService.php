<?php

namespace App\Services;

use Exception;

class TwitchUserService
{
    protected string $accessToken;
    protected string $clientId;

    public function __construct(string $accessToken, string $clientId)
    {
        $this->accessToken = $accessToken;
        $this->clientId = $clientId;
    }

    /**
     * @throws Exception
     */
    public function getUser(int|null $streamerId): array
    {
        $url = "https://api.twitch.tv/helix/users?id=$streamerId";
        $curlh = curl_init();
        $headers = [
            "Authorization: Bearer $this->accessToken",
            "Client-Id: $this->clientId",
            "Content-type: application/json"
        ];
        curl_setopt($curlh, CURLOPT_URL, $url);
        curl_setopt($curlh, CURLOPT_HTTPGET, true);
        curl_setopt($curlh, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlh, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curlh, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlh, CURLOPT_SSL_VERIFYHOST, false);
        $apiResponse = curl_exec($curlh);
        if (curl_errno($curlh)) {
            echo json_encode(["error" => "Error en la solicitud curl: " . curl_error($curlh)]);
            curl_close($curlh);
            exit;
        }

        $httpCode = curl_getinfo($curlh, CURLINFO_HTTP_CODE);
        curl_close($curlh);
        http_response_code($httpCode);
        if ($httpCode == 200) {
            $data = json_decode($apiResponse, true);
            return $data['data'][0];
        }
        if ($httpCode == 400) {
            return respondWithError(400, "Invalid or missing 'id' parameter.");
        }
        if ($httpCode == 401) {
            return respondWithError(401, "Unauthorized. Twitch access token is invalid or has expired.");
        }
        if ($httpCode == 404) {
            return respondWithError(404, "User not found.");
        }
        if ($httpCode == 500) {
            return respondWithError(500, "Internal server error.");
        }
        return respondWithError($httpCode, "Error: $httpCode");
    }
}
