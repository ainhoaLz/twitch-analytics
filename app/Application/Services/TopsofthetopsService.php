<?php

namespace App\Application\Services;

use App\Domain\Exceptions\NotFoundException;
use App\Domain\Exceptions\ServerException;
use App\Domain\Exceptions\UnknownException;
use App\Domain\Interfaces\Database\TopGamesRepositoryInterface;
use App\Domain\Interfaces\TwitchAPI\TopsofthetopsRepositoryInterface;
use App\Infrastructure\DatabaseConnection;
use App\Infrastructure\Repositories\Database\TopGamesRepository;
use DateTime;

class TopsofthetopsService
{
    private TopGamesRepositoryInterface $dbTopGamesRepository;
    private TopsofthetopsRepositoryInterface $twitchRepository;
    public function __construct(
        TopGamesRepositoryInterface $dbTopGamesRepository,
        TopsofthetopsRepositoryInterface $twitchRepository
    ) {
        $this->dbTopGamesRepository = $dbTopGamesRepository;
        $this->twitchRepository = $twitchRepository;
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function getTopsofthetops(int $since): array
    {
        $row = $this->dbTopGamesRepository->getUpdatedAt();
        if (!$row) {
            return $this->respondWithTwitchAPIData();
        }

        $updated_at = new DateTime($row['updated_at']);
        $now = new DateTime();
        $lastUpdated = $now->getTimestamp() - $updated_at->getTimestamp();
        if ($lastUpdated > $since) {
            return $this->respondWithTwitchAPIData();
        }
        return $this->dbTopGamesRepository->getTopGames();
    }

    private function respondWithTwitchAPIData(): array
    {
        $twitchResponse = $this->callTwitchAPI();
        $httpCode = $twitchResponse[1];
        if ($httpCode == 200) {
            if (empty($twitchResponse[0])) {
                throw new NotFoundException("Not Found. No data available.");
            }
            http_response_code(200);
            return $twitchResponse[0];
        }
        if ($httpCode == 500) {
            throw new ServerException("Internal server error.");
        }
        throw new UnknownException("Unknown error.", $httpCode);
    }

    private function callTwitchAPI(): array
    {
        $apiResponse = $this->twitchRepository->getTop3Games();
        $response = $apiResponse['response'];
        $httpCode = $apiResponse['httpCode'];

        if ($httpCode != 200 || empty($response)) {
            return [null, $httpCode];
        }

        $topThreeGames = $this->parseTop3Games($response);

        $result = [];
        foreach ($topThreeGames as $game) {
            $gameId = $game['game_id'];
            $gameName = $game['game_name'];

            $apiResponse = $this->twitchRepository->getTop40Videos($gameId);
            $response = $apiResponse['response'];
            $httpCode = $apiResponse['httpCode'];
            if ($httpCode != 200 || empty($response)) {
                return [null, $httpCode];
            }
            $data = json_decode($response, true);
            $videos = $this->parseVideos($data);

            $topVideo = $videos[0];
            $topUser = $topVideo['user_name'];

            list($totalVideos, $totalViews) = $this->getTotalVideosAndViews($videos, $topUser);

            $result[] = [
                "game_id" => $gameId,
                "game_name" => $gameName,
                "user_name" => $topUser,
                "total_videos" => $totalVideos,
                "total_views" => $totalViews,
                "most_viewed_title" => $topVideo["title"],
                "most_viewed_views" => $topVideo["views"],
                "most_viewed_duration" => $topVideo["duration"],
                "most_viewed_created_at" => $topVideo["created_at"]
            ];
        }
        $this->dbTopGamesRepository->update($result);
        return [$result, 200];
    }

    private function parseTop3Games($response): array
    {
        $data = json_decode($response, true);
        if (isset($data["data"])) {
            $topThreeGames = [];
            foreach ($data["data"] as $game) {
                $topThreeGames[] = [
                    "game_id" => $game["id"],
                    "game_name" => $game["name"]
                ];
            }
        }
        return $topThreeGames;
    }

    /**
     * @param mixed $data
     * @return array
     */
    public function parseVideos(mixed $data): array
    {
        if (isset($data["data"])) {
            $videos = [];
            foreach ($data["data"] as $video) {
                $videos[] = [
                    "user_name" => $video["user_name"],
                    "title" => $video["title"],
                    "views" => $video["view_count"],
                    "duration" => $video["duration"],
                    "created_at" => $video["created_at"]
                ];
            }
        }
        return $videos;
    }

    /**
     * @param array $videos
     * @param mixed $topUser
     * @return array
     */
    public function getTotalVideosAndViews(array $videos, mixed $topUser): array
    {
        $totalVideos = 0;
        $totalViews = 0;
        foreach ($videos as $video) {
            if ($video['user_name'] == $topUser) {
                $totalVideos++;
                $totalViews += $video['views'];
            }
        }
        return array($totalVideos, $totalViews);
    }
}
