
<?php
$accessToken = "c9v255gz2xi5xij9293ei6vaob999x";
$clientId = "myqofuuwfbtja3s178j5x7osa78xfh";
$limit = 3;

function getStreams($accessToken, $clientId){
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
            return $resp;
        }

    }
    curl_close($ch);
    return null;

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
    }
    curl_close($ch);
    //agregamos los usuarios a los streams
    foreach ($returnStream as $index => $s) {
        $userId = $s["user_id"];
        if (isset($users[$userId])) {
            $returnStream[$index]["user_display_name"] = $users[$userId]["user_display_name"];
            $returnStream[$index]["profile_image_url"] = $users[$userId]["profile_image_url"];
        }
    }
    return $returnStream;


}

function my_sort($a, $b){
    if($a["viewer_count"] == $b["viewer_count"]){
        return 0;
    }
    return ($a["viewer_count"] > $b["viewer_count"]) ? -1 : 1;
    //si a es mayor que b devuelve -1 para que a aparezca antes en el array
    //si a es menor que b devuelve 1 para que b aparezca antes en e array
}

$streams = getStreams($accessToken, $clientId);
usort($streams, "my_sort");
$limitedStreams = array_slice($streams, 0, $limit);
$resp = addUserInfo($limitedStreams, $accessToken, $clientId);
echo json_encode($resp);
?>
