<?php

namespace App\Infrastructure\Repositories\TwitchAPI;

use App\Domain\Interfaces\TwitchAPI\TopsofthetopsRepositoryInterface;

class TopsofthetopsRepository implements TopsofthetopsRepositoryInterface
{
    protected string $accessToken;
    protected string $clientId;

    public function __construct(string $accessToken, string $clientId)
    {
        $this->accessToken = $accessToken;
        $this->clientId = $clientId;
    }

    public function getTop40Videos(string $gameId): array
    {
        $url = "https://api.twitch.tv/helix/videos?game_id=" . $gameId . "&sort=views&first=40";
        $curlh = curl_init($url);
        $headers = [
            "Authorization: Bearer $this->accessToken",
            "Client-Id: $this->clientId",
            "Content-type: application/json"
        ];

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

    public function getTop3Games(): array
    {
        $url = "https://api.twitch.tv/helix/games/top?first=3";
        $curl_h = curl_init($url);
        $headers = [
            "Authorization: Bearer $this->accessToken",
            "Client-Id: $this->clientId",
            "Content-type: application/json"
        ];

        curl_setopt($curl_h, CURLOPT_HTTPGET, true);
        curl_setopt($curl_h, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_h, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($curl_h, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl_h, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($curl_h);
        $httpCode = curl_getinfo($curl_h, CURLINFO_HTTP_CODE);

        return [
            'response' => $response,
            'httpCode' => $httpCode
        ];
    }
}
