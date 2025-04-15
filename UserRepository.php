<?php

function getUsers($accessToken, $clientId, $streamerId)
{
    $url = "https://api.twitch.tv/helix/users?id=$streamerId";
    $curlh = curl_init();
    $headers = ["Authorization: Bearer $accessToken", "Client-Id: $clientId", "Content-type: application/json"];
    curl_setopt($curlh, CURLOPT_URL, $url);
    curl_setopt($curlh, CURLOPT_HTTPGET, true);
    curl_setopt($curlh, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curlh, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curlh, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curlh, CURLOPT_SSL_VERIFYHOST, false);
    $response = curl_exec($curlh);
    if (curl_errno($curlh)) {
        echo json_encode(["error" => "Error en la solicitud curl: " . curl_error($curlh)]);
        curl_close($curlh);
        exit;
    }

    $httpCode = curl_getinfo($curlh, CURLINFO_HTTP_CODE);
    curl_close($curlh);
    http_response_code($httpCode);
    if ($httpCode == 200) {
        $data = json_decode($response, true);
        $user = $data['data'][0];
        echo json_encode($user);
        return;
    }
    if ($httpCode == 400) {
        echo json_encode(["error" => "Invalid or missing 'id' parameter."]);
        return;
    }
    if ($httpCode == 401) {
        echo json_encode(["error" => "Unauthorized. Twitch access token is invalid or has expired."]);
        return;
    }
    if ($httpCode == 404) {
        echo json_encode(["error" => "User not found."]);
        return;
    }
    if ($httpCode == 500) {
        echo json_encode(["error" => "Internal server error."]);
    }
}
