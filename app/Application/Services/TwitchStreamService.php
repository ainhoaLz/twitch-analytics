<?php

namespace App\Application\Services;

use App\Domain\Interfaces\TwitchAPI\TwitchStreamRepositoryInterface;
use Exception;

class TwitchStreamService
{
    private TwitchStreamRepositoryInterface $streamRepository;

    public function __construct(TwitchStreamRepositoryInterface $streamRepository)
    {
        $this->streamRepository = $streamRepository;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getStreams(): array
    {
        $apiResponse = $this->streamRepository->getStreamsFromTwitch();
        $httpCode = $apiResponse['httpCode'];
        $streams = $apiResponse['streams'];

        if ($httpCode === 200) {
            http_response_code(200);
            $data = json_decode($streams, true);
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
