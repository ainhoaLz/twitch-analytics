<?php
function getUsers($accessToken, $clientId, $streamerId){
    $url = "https://api.twitch.tv/helix/users?id=$streamerId";
    $ch = curl_init();
    $headers = ["Authorization: Bearer $accessToken", "Client-Id: $clientId", "Content-type: application/json"];

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo json_encode(["error" => "Error en la solicitud curl: " . curl_error($ch)]);
        curl_close($ch);
        exit;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    switch ($httpCode) {
        case 200:
            $data = json_decode($response, true);
            $user = $data['data'][0];
            echo json_encode($user);
            break;
        case 400:
            echo json_encode(["error" => "Invalid or missing 'id' parameter."]);
            break;
        case 401:
            echo json_encode(["error" => "Unauthorized. Twitch access token is invalid or has expired."]);
            break;
        case 404:
            echo json_encode(["error" => "User not found."]);
            break;
        case 500:
            echo json_encode(["error" => "Internal server error."]);
            break;
    }
    curl_close($ch);
}
?>