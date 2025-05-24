<?php

namespace App\Infrastructure\Repositories\TwitchAPI;

use App\Domain\Interfaces\TwitchAPI\TwitchStreamRepositoryInterface;
use App\Domain\Models\EnrichedStream;
use App\Domain\Models\Stream;

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
        if ($httpCode !== 200 || empty($response)) {
            return [
                'httpCode' => $httpCode,
                'streams' => null,
            ];
        }

        $data = json_decode($response, true);
        $streams = [];

        if (isset($data['data'])) {
            foreach ($data['data'] as $item) {
                if ($item['type'] === 'live') {
                    $streams[] = new Stream([
                        'title' => $item['title'],
                        'user_name' => $item['user_name'],
                    ]);
                }
            }
        }

        return [
            'httpCode' => $httpCode,
            'streams' => $streams,
        ];
    }

    public function getEnrichedStreamsFromTwitch(): array
    {
        $url = "https://api.twitch.tv/helix/streams?first=100";
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

        if ($httpCode !== 200 || empty($response)) {
            return [
                'httpCode' => $httpCode,
                'streams' => null,
            ];
        }

        $data = json_decode($response, true);
        $enrichedStreams = [];

        if (isset($data['data'])) {
            foreach ($data['data'] as $item) {
                if ($item['type'] === 'live') {
                    $enrichedStreams[] = new EnrichedStream([
                        'stream_id'         => $item['id'],
                        'user_id'           => $item['user_id'],
                        'user_name'         => $item['user_name'],
                        'viewer_count'      => (int)$item['viewer_count'],
                        'title'             => $item['title'],
                        'user_display_name' => $item['user_name'], // assuming it's the same
                        'profile_image_url' => $item['thumbnail_url'], // may need enrichment
                    ]);
                }
            }
        }

        return [
            'httpCode' => $httpCode,
            'streams' => $enrichedStreams,
        ];
    }
}
