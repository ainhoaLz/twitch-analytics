<?php

namespace App\Infrastructure\Repositories\TwitchAPI;

use App\Domain\Exceptions\NotFoundException;
use App\Domain\Interfaces\TwitchAPI\TwichUserRepositoryInterface;
use App\Domain\Models\User;

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

        $user = null;

        if ($httpCode === 200) {
            $body = json_decode($apiResponse, true);
            if (empty($body['data'][0])) {
                return [
                    'httpCode' => $httpCode,
                    'user'     => null
                ];
            }
            $raw = $body['data'][0];
            $attrs = [
                'userId'          => $raw['id'],
                'login'           => $raw['login'],
                'displayName'     => $raw['display_name'],
                'type'            => $raw['type'],
                'broadcasterType' => $raw['broadcaster_type'],
                'description'     => $raw['description'],
                'profileImageUrl' => $raw['profile_image_url'],
                'offlineImageUrl' => $raw['offline_image_url'],
                'viewCount'       => (int)$raw['view_count'],
                'createdAt'       => $raw['created_at'],
            ];
            $user = new User($attrs);
        }
        return [
            'httpCode' => $httpCode,
            'user'     => $user,
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
