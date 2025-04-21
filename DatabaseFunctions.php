<?php

use PDO;
use PDOException;

function connectToDB()
{
    $host = "c724r43q8jp5nk.cluster-czz5s0kz4scl.eu-west-1.rds.amazonaws.com";
    $database = "d4nk1746t10jil";
    $user = "uk0klbg1uescn";
    $password = "p4408a7b7ac517dbb23e0ff811487d6f0c68576d627ea50312b0c9835cd3e78b5";
    $port = 5432;

    try {
        $dsn = "pgsql:host=$host;port=$port;dbname=$database";
        return new PDO($dsn, $user, $password);
    } catch (PDOException $e) {
        echo $e->getMessage();
        return null;
    }
}

function updateDDBB($videos)
{
    $con = connectToDB();
    $deleteQuery = "TRUNCATE TABLE public.top_games";

    try {
        $con->exec($deleteQuery);
        insertTopGamesToDDBB($con, $videos);
    } catch (PDOException $e) {
        echo "Error al insertar datos: " . $e->getMessage();
    } finally {
        $con = null;
    }
}

function insertTopGamesToDDBB($con, $videos)
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

    $stmt = $con->prepare($insertQuery);

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
