<?php

namespace App\Services;

use DateTime;
use PDO;

class TopsofthetopsService
{
    private ?PDO $connection;
    private DatabaseConnection $dbConnection;
    public function __construct(DatabaseConnection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
        $this->connection = $this->dbConnection->connect();
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function getTopsofthetops(string $accessToken, string $clientId, int $since): array
    {
        $checkQuery = $this->connection->query("SELECT updated_at from top_games");
        $row = $checkQuery->fetch();
        if (!$row) {
            return $this->respondWithTwitchAPIData($accessToken, $clientId);
        }

        $updated_at = new DateTime($row['updated_at']);
        $now = new DateTime();
        $lastUpdated = $now->getTimestamp() - $updated_at->getTimestamp();
        if ($lastUpdated > $since) { //php convierte el $since a un int al compararlo con un entero
            return $this->respondWithTwitchAPIData($accessToken, $clientId);
        }
        return $this->respondWithDatabaseData($this->connection);
    }

    private function respondWithDatabaseData(?PDO $con): array
    {
        $sqlQuery = "
        SELECT
            game_id,
            game_name,
            user_name,
            total_videos,
            total_views,
            most_viewed_title,
            most_viewed_views,
            most_viewed_duration,
            most_viewed_created_at
        from top_games
    ";
        $topGamesQuery = $con->query($sqlQuery);
        $result = $topGamesQuery->fetchAll(PDO::FETCH_ASSOC);
        http_response_code(200);
        return $result;
    }

    private function respondWithTwitchAPIData(string $accessToken, string $clientId): array
    {
        $twitchResponse = $this->callTwitchAPI($accessToken, $clientId);
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

    private function callTwitchAPI(string $accessToken, string $clientId): array
    {
        //primero hay que obtener los top 3 juegos más visualizados
        $topThreeGames = $this->getTop3Games($accessToken, $clientId);
        if ($topThreeGames[1] != 200) {
            return [null, $topThreeGames[1]];
        }
        $result = [];
        //Para cada juego obtener:
        //-top40 videos más vistos
        foreach ($topThreeGames[0] as $game) {
            $gameId = $game['game_id'];
            $gameName = $game['game_name'];
            $videos = $this->getTop40Videos($accessToken, $clientId, $gameId);
            if ($videos[1] != 200) {
                return [null, $videos[1]];
            }
            $topVideo = $videos[0];
            $topUser = $topVideo[0]['user_name'];

            //contamos cuantos videos de este usuario hay en el top40
            //sumamos todas las vistas de este usuario
            $totalVideos = 0;
            $totalViews = 0;
            foreach ($videos[0] as $video) {
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
                "most_viewed_title" => $topVideo[0]["title"],
                "most_viewed_views" => $topVideo[0]["views"],
                "most_viewed_duration" => $topVideo[0]["duration"],
                "most_viewed_created_at" => $topVideo[0]["created_at"]
            ];
        }
        $topGamesUpdater = new TopGamesUpdater(new DatabaseConnection());
        $topGamesUpdater->update($result);
        return [$result, 200];
    }

    private function getTop40Videos(string $accessToken, string $clientId, string $gameId): array
    {
        $url = "https://api.twitch.tv/helix/videos?game_id=" . $gameId . "&sort=views&first=40";
        $curlh = curl_init($url);
        $headers = ["Authorization: Bearer $accessToken", "Client-Id: $clientId", "Content-type: application/json"];

        curl_setopt($curlh, CURLOPT_HTTPGET, true);
        curl_setopt($curlh, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlh, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($curlh, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlh, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($curlh);
        $httpCode = curl_getinfo($curlh, CURLINFO_HTTP_CODE);
        curl_close($curlh);
        if ($httpCode == 200) {
            $data = json_decode($response, true);
            if (isset($data["data"])) {
                $resp = [];
                foreach ($data["data"] as $video) {
                    $resp[] = [
                        "user_name" => $video["user_name"],
                        "title" => $video["title"],
                        "views" => $video["view_count"],
                        "duration" => $video["duration"],
                        "created_at" => $video["created_at"]
                    ];
                }
                curl_close($curlh);
                return [$resp , $httpCode];
            }
        }
        return [null, $httpCode];
    }

    private function getTop3Games(string $accessToken, string $clientId): array
    {
        $url = "https://api.twitch.tv/helix/games/top?first=3";
        $curl_h = curl_init($url);
        $headers = ["Authorization: Bearer $accessToken", "Client-Id: $clientId", "Content-type: application/json"];

        curl_setopt($curl_h, CURLOPT_HTTPGET, true);
        curl_setopt($curl_h, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_h, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($curl_h, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl_h, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($curl_h);
        $httpCode = curl_getinfo($curl_h, CURLINFO_HTTP_CODE);

        if ($httpCode == 200) {
            $data = json_decode($response, true);
            if (isset($data["data"])) {
                $topGamesResponse = [];
                foreach ($data["data"] as $game) {
                    $topGamesResponse[] = [
                        "game_id" => $game["id"],
                        "game_name" => $game["name"]
                    ];
                }
                curl_close($curl_h);
                return [$topGamesResponse , $httpCode];
            }
        }
        curl_close($curl_h);
        return [null, $httpCode];
    }
}
