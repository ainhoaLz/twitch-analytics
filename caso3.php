
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




?>
