
<?php


function getTopsofthetops($accessToken, $clientId, $since){
    //primero hay que obtener los top 3 juegos más visualizados
    $topGames = getTop3Games($accessToken, $clientId);
    if($topGames[1] == 200){ //si se ha devuelto una respuesta 200 podemos seguir
        $result = [];
        //Para cada juego obtener:
        //-top40 videos más vistos
        foreach($topGames[0] as $game){
            $gameId = $game['game_id'];
            $gameName = $game['game_name'];
            $videos = getTop40Videos($accessToken, $clientId, $gameId);

            if($videos[1] == 200){
                //el primer video de la lista es el más visto
                $topVideo = $videos[0];
                $topUser = $topVideo[0]['user_name'];

                //contamos cuantos videos de este usuario hay en el top40
                $totalVideos = 0;
                foreach($videos[0] as $video){
                    if($video['user_name'] == $topUser){ //si el usuario de ese video es el mismo
                        $totalVideos ++;
                    }
                }

                //sumamos todas las vistas de este usuario
                $totalViews = 0;
                foreach($videos[0] as $video){
                    if($video['user_name'] == $topUser){
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
        }
        echo json_encode($result);
    }


}

function getTop40Videos($accessToken, $clientId, $gameId){
    $url = "https://api.twitch.tv/helix/videos?game_id=".$gameId."&sort=views&first=40";
    $ch = curl_init($url);
    $headers = ["Authorization: Bearer $accessToken", "Client-Id: $clientId", "Content-type: application/json"];

    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if($httpCode == 200){
        $data = json_decode($response, true);
        if(isset($data["data"])){
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
            curl_close($ch);
            return [$resp , $httpCode];
        }
    }
    return [null, $httpCode];
}
function getTop3Games($accessToken, $clientId){
    $url = "https://api.twitch.tv/helix/games/top?first=3";
    $ch = curl_init($url);
    $headers = ["Authorization: Bearer $accessToken", "Client-Id: $clientId", "Content-type: application/json"];

    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if($httpCode == 200){
        $data = json_decode($response, true);
        if(isset($data["data"])){
            $resp = [];
            foreach ($data["data"] as $game) {
                $resp[] = [
                    "game_id" => $game["id"],
                    "game_name" => $game["name"]
                ];
            }
            curl_close($ch);
            return [$resp , $httpCode];
        }

    }
    curl_close($ch);
    return [null, $httpCode];
}

?>
