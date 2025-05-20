<?php

namespace App\Infrastructure\Repositories\TwitchAPI;

use App\Domain\Interfaces\TwitchAPI\TwitchTokenRepositoryInterface;

class TwitchTokenRepository implements TwitchTokenRepositoryInterface
{
    private \App\Domain\Interfaces\Database\TwitchTokenRepositoryInterface $dbRepository;

    public function __construct(\App\Domain\Interfaces\Database\TwitchTokenRepositoryInterface $dbRepository)
    {
        $this->dbRepository = $dbRepository;
    }
    public function getNewToken(string $clientId, string $clientSecret): ?string
    {
        $url = "https://id.twitch.tv/oauth2/token";
        $postData = [
            "client_id" => $clientId,
            "client_secret" => $clientSecret,
            "grant_type" => "client_credentials"
        ];

        $response = $this->getTokenFromTwitch($url, $postData);
        $result = json_decode($response, true);

        if (!isset($result['access_token'], $result['expires_in'])) {
            return null;
        }

        $this->dbRepository->storeToken($result['access_token'], $result['expires_in']);
        return $result["access_token"];
    }

    private function getTokenFromTwitch(string $url, array $postData): string
    {
        $curlh = curl_init();
        curl_setopt($curlh, CURLOPT_URL, $url);
        curl_setopt($curlh, CURLOPT_POST, true);
        curl_setopt($curlh, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($curlh, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlh, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($curlh);
        curl_close($curlh);

        return $response;
    }
}
