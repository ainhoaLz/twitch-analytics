
<?php
function getStreams($accessToken, $clientId) {
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
    switch ($httpCode) {
        case 200:
            $data = json_decode($response, true);
            if(isset($data["data"])){
                $resp = [];
                foreach ($data["data"] as $stream) {
                    if($stream["type"] == "live"){
                        $resp[] = [
                            "title" => $stream["title"],
                            "user_name" => $stream["user_name"],
                        ];
                    }
                }
                echo json_encode($resp);
            }
            break;
        case 401:
            echo json_encode(["error" => "Unauthorized. Twitch access token is invalid or has expired."]);
            break;
        case 500:
            echo json_encode(["error" => "Internal server error."]);
            break;
    }

    curl_close($ch);
}

?>
