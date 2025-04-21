
<?php
function getStreams($accessToken, $clientId)
{
    $url = "https://api.twitch.tv/helix/streams";
    $curlh = curl_init($url);
    $headers = ["Authorization: Bearer $accessToken", "Client-Id: $clientId", "Content-type: application/json"];

    curl_setopt($curlh, CURLOPT_HTTPGET, true);
    curl_setopt($curlh, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curlh, CURLOPT_HTTPHEADER, $headers);

    curl_setopt($curlh, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curlh, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($curlh);

    $httpCode = curl_getinfo($curlh, CURLINFO_HTTP_CODE);
    if ($httpCode == 200) {
        http_response_code(200);
        $data = json_decode($response, true);
        if (isset($data["data"])) {
            $resp = [];
            foreach ($data["data"] as $stream) {
                if ($stream["type"] == "live") {
                    $resp[] = [
                        "title" => $stream["title"],
                        "user_name" => $stream["user_name"],
                    ];
                }
            }
            echo json_encode($resp);
        }
        return;
    }
    if ($httpCode == 401) {
        http_response_code(401);
        echo json_encode(["error" => "Unauthorized. Twitch access token is invalid or has expired."]);
        return;
    }
    if ($httpCode == 500) {
        http_response_code(500);
        echo json_encode(["error" => "Internal server error."]);
        return;
    }

    curl_close($curlh);
}
