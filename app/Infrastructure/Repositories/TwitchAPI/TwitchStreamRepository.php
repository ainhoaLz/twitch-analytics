<?php

namespace App\Infrastructure\Repositories\TwitchAPI;

use App\Domain\Interfaces\TwitchAPI\TwitchStreamRepositoryInterface;

class TwitchStreamRepository implements TwitchStreamRepositoryInterface
{
    protected string $accessToken;
    protected string $clientId;

    public function __construct(string $accessToken, string $clientId)
    {
        $this->accessToken = $accessToken;
        $this->clientId = $clientId;
    }
    public function getStreamsFromTwitch(): array
    {
        $url = "https://api.twitch.tv/helix/streams";
        $curlh = curl_init($url);
        $headers = [
            "Authorization: Bearer $this->accessToken",
            "Client-Id: $this->clientId",
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
        return [
            'httpCode' => $httpCode,
            'streams' => $response
        ];
    }
}
