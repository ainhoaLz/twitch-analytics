<?php

namespace App\Infrastructure\Repositories\TwitchAPI;

use App\Domain\Interfaces\TwitchAPI\TwichUserRepositoryInterface;

class TwitchUserRepository implements TwichUserRepositoryInterface
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
    public function getUser(mixed $streamerId): array
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
        return [
            'httpCode' => $httpCode,
            'user' => $apiResponse
        ];
    }
    public function getMultipleUsers(array $streamerIds): array
    {
        $url = "https://api.twitch.tv/helix/users?id=" . implode('&id=', $streamerIds);
        $headers = [
            "Authorization: Bearer $this->accessToken",
            "Client-Id: $this->clientId",
            "Content-type: application/json"
        ];
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
}
