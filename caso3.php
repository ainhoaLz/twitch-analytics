
<?php


function get_streams($accessToken, $clientId){
    $url = "https://api.twitch.tv/helix/streams";
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
            foreach ($data["data"] as $stream) {
                $resp[] = [
                    "stream_id" => $stream["id"],
                    "user_id" => $stream["user_id"],
                    "user_name" => $stream["user_name"],
                    "viewer_count" => $stream["viewer_count"],
                    "title" => $stream["title"],
                ];
            }
            curl_close($ch);
            return [$resp , $httpCode];
        }

    }
    curl_close($ch);
    return [null, $httpCode];

}

function addUserInfo($stream, $accessToken, $clientId){
    //creo un array con los ids de los usuarios
    $returnStream = $stream;
    $userIds = array_column($stream, "user_id");
    $url = "https://api.twitch.tv/helix/users?id=" . implode('&id=', $userIds);
    $ch = curl_init();
    $headers = ["Authorization: Bearer $accessToken", "Client-Id: $clientId", "Content-type: application/json"];

    curl_setopt($ch, CURLOPT_URL, $url);
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
            $users = [];
            foreach ($data["data"] as $user) {
                $users[$user["id"]] = [ //uso el id del usuario como clave del array
                    "user_display_name" => $user["display_name"],
                    "profile_image_url" => $user["profile_image_url"],
                ];
            }
        }
        //agregamos los usuarios a los streams
        foreach ($returnStream as $index => $s) {
            $userId = $s["user_id"];
            if (isset($users[$userId])) { //verifico si existe un usuario con ese userId en users
                //si existe añado dos claves al returnStream
                $returnStream[$index]["user_display_name"] = $users[$userId]["user_display_name"];
                $returnStream[$index]["profile_image_url"] = $users[$userId]["profile_image_url"];
            }
        }
        curl_close($ch);
        return [$httpCode, $returnStream];
    }
    curl_close($ch);
    return [$httpCode, null];

}

function my_sort($a, $b) {
    return $b["viewer_count"] - $a["viewer_count"];
}

function getEnrichedStreams($accessToken, $clientId, $limit){
    $streams = get_streams($accessToken, $clientId);
    if($streams[1] == 200){ //si se ha devuelto una respuesta 200 podemos seguir
        usort($streams[0], "my_sort");
        if(!ctype_digit($limit) || $limit <= 0 || $limit > count($streams[0])){
            http_response_code(400);
            echo json_encode(["error" => "Invalid 'limit' parameter."]);
            return;
        }
        $limitedStreams = array_slice($streams[0], 0, $limit);
        $resp = addUserInfo($limitedStreams, $accessToken, $clientId);
        switch($resp[0]){
            case 200:
                echo json_encode($resp);
                break;
            case 401:
                echo json_encode(["error" => "Unauthorized. Twitch access token is invalid or has expired."]);
                break;
            case 500:
                echo json_encode(["error" => "Internal server error."]);
                break;
        }
    }
}

?>
