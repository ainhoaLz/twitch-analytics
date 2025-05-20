<?php

namespace App\Infrastructure\Repositories\Database;

use App\Domain\Interfaces\Database\TopGamesRepositoryInterface;
use App\Infrastructure\DatabaseConnection;
use PDO;
use PDOException;

class TopGamesRepository implements TopGamesRepositoryInterface
{
    protected PDO $connection;
    private DatabaseConnection $dbConnection;

    public function __construct(DatabaseConnection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
        $this->connection = $this->dbConnection->connect();
    }

    public function truncate(): void
    {
        $deleteQuery = "TRUNCATE TABLE public.top_games";

        try {
            $this->connection->exec($deleteQuery);
        } catch (PDOException $e) {
            echo "Error al truncar la tabla: " . $e->getMessage();
        }
    }

    public function update(array $videos): void
    {
        try {
            $this->truncate();
            $this->insertTopGames($videos);
        } catch (PDOException $e) {
            echo "Error al actualizar datos: " . $e->getMessage();
        }
    }

    private function insertTopGames(array $videos): void
    {
        $insertQuery = "INSERT INTO public.top_games (
                            game_id, game_name, user_name, total_videos, total_views,
                            most_viewed_title, most_viewed_views, most_viewed_duration,
                            most_viewed_created_at, updated_at
                        ) VALUES (
                            :game_id, :game_name, :user_name, :total_videos, :total_views,
                            :most_viewed_title, :most_viewed_views, :most_viewed_duration,
                            :most_viewed_created_at, NOW()
                        )";

        $stmt = $this->connection->prepare($insertQuery);

        foreach ($videos as $video) {
            $stmt->execute([
                ':game_id' => $video['game_id'],
                ':game_name' => $video['game_name'],
                ':user_name' => $video['user_name'],
                ':total_videos' => $video['total_videos'],
                ':total_views' => $video['total_views'],
                ':most_viewed_title' => $video['most_viewed_title'],
                ':most_viewed_views' => $video['most_viewed_views'],
                ':most_viewed_duration' => $video['most_viewed_duration'],
                ':most_viewed_created_at' => $video['most_viewed_created_at']
            ]);
        }
    }

    public function getUpdatedAt(): array
    {
        $checkQuery = $this->connection->query("SELECT updated_at from top_games");
        return $checkQuery->fetch();
    }

    public function getTopGames(): array
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
        $topGamesQuery = $this->connection->query($sqlQuery);
        return $topGamesQuery->fetchAll(PDO::FETCH_ASSOC);
    }
}
