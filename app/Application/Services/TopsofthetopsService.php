<?php

namespace App\Application\Services;

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
            http_response_code(200);
            return $twitchResponse[0];
        }
        if ($httpCode == 400) {
            return respondWithError(400, "Bad Request. Invalid or missing parameters.");
        }
        if ($httpCode == 404) {
            return respondWithError(404, "Not Found. No data available.");
        }
        if ($httpCode == 500) {
            return respondWithError(500, "Internal server error.");
        }
        return respondWithError($httpCode, "Error: $httpCode");
    }

    private function callTwitchAPI(): array
    {
        //primero hay que obtener los top 3 juegos más visualizados
        $apiResponse = $this->twitchRepository->getTop3Games();
        $response = $apiResponse['response'];
        $httpCode = $apiResponse['httpCode'];

        if ($httpCode != 200) {
            return [null, $httpCode];
        }

        $topThreeGames = $this->parseTop3Games($response);

        $result = [];
        //Para cada juego obtener:
        //-top40 videos más vistos
        foreach ($topThreeGames as $game) {
            $gameId = $game['game_id'];
            $gameName = $game['game_name'];

            $apiResponse = $this->twitchRepository->getTop40Videos($gameId);
            $response = $apiResponse['response'];
            $httpCode = $apiResponse['httpCode'];
            if ($httpCode != 200) {
                return [null, $httpCode];
            }
            $data = json_decode($response, true);
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

            $topVideo = $videos[0];
            $topUser = $topVideo['user_name'];

            //contamos cuantos videos de este usuario hay en el top40
            //sumamos todas las vistas de este usuario
            $totalVideos = 0;
            $totalViews = 0;
            foreach ($videos as $video) {
                if ($video['user_name'] == $topUser) { //si el usuario de ese video es el mismo
                    $totalVideos++;
                    $totalViews += $video['views'];
                }
            }

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
        $topGamesUpdater = new TopGamesRepository(new DatabaseConnection());
        $topGamesUpdater->update($result);
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
}
