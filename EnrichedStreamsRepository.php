
<?php

function getEnrichedStreams($accessToken, $clientId, $limit)
{
    $streams = get_streams($accessToken, $clientId);
    $resp = addDataToResponse($streams, $limit, $clientId, $accessToken);
    fetchResponse($resp);
}

function get_streams($accessToken, $clientId)
{
    $url = "https://api.twitch.tv/helix/streams";
    $headers = ["Authorization: Bearer $accessToken", "Client-Id: $clientId", "Content-type: application/json"];
    $data = getStreamsFromTwitch($url, $headers);
    if ($data['httpCode'] == 200) {
        return processStreamData($data['response'], $data['httpCode']);
    }
    return [null, $data['httpCode']];
}

function getStreamsFromTwitch($url, $headers)
{
    $curlh = curl_init($url);


    curl_setopt($curlh, CURLOPT_HTTPGET, true);
    curl_setopt($curlh, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curlh, CURLOPT_HTTPHEADER, $headers);

    curl_setopt($curlh, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curlh, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($curlh);
    $httpCode = curl_getinfo($curlh, CURLINFO_HTTP_CODE);
    curl_close($curlh);

    return [
        'response' => $response,
        'httpCode' => $httpCode
    ];
}

function processStreamData($response, $httpCode)
{
    $data = json_decode($response, true);
    if (isset($data["data"])) {
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
        return [$resp , $httpCode];
    }
    return [null, $httpCode];
}

function addDataToResponse($streams, $limit, $clientId, $accessToken)
{
    if ($streams[1] != 200) {
        return $streams;
    }
    usort($streams[0], "ordenar");
    if (!isLimitValid($limit, count($streams[0]))) {
        return [400, null];
    }
    $limitedStreams = array_slice($streams[0], 0, $limit);
    return addUserInfo($limitedStreams, $accessToken, $clientId);
}
function isLimitValid($limit, $count)
{
    return (ctype_digit($limit) && $limit > 0 && $limit <= $count);
}

function addUserInfo($stream, $accessToken, $clientId)
{
    $returnStream = $stream;
    $userIds = array_column($stream, "user_id");
    $url = "https://api.twitch.tv/helix/users?id=" . implode('&id=', $userIds);
    $headers = ["Authorization: Bearer $accessToken", "Client-Id: $clientId", "Content-type: application/json"];
    $data = getUsersFromTwitch($url, $headers);

    if ($data['httpCode'] != 200) {
        return [$data['httpCode'], null];
    }
    $users = fetchUserData($data['response']);
    addUserToStreams($returnStream, $users);

    return [$data['httpCode'], $returnStream];
}

function getUsersFromTwitch($url, $headers)
{
    $curlh = curl_init();

    curl_setopt($curlh, CURLOPT_URL, $url);
    curl_setopt($curlh, CURLOPT_HTTPGET, true);
    curl_setopt($curlh, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curlh, CURLOPT_HTTPHEADER, $headers);

    curl_setopt($curlh, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curlh, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($curlh);
    $httpCode = curl_getinfo($curlh, CURLINFO_HTTP_CODE);
    curl_close($curlh);

    return [
        'response' => $response,
        'httpCode' => $httpCode
    ];
}
function fetchUserData($response)
{
    $data = json_decode($response, true);
    if (isset($data["data"])) {
        $users = [];
        foreach ($data["data"] as $user) {
            $users[$user["id"]] = [ //uso el id del usuario como clave del array
                "user_display_name" => $user["display_name"],
                "profile_image_url" => $user["profile_image_url"],
            ];
        }
        return $users;
    }
    return null;
}

function addUserToStreams($stream, $users)
{
    foreach ($stream as $index => $s) {
        $userId = $s["user_id"];
        if (isset($users[$userId])) { //verifico si existe un usuario con ese userId en users
            //si existe añado dos claves al returnStream
            $stream[$index]["user_display_name"] = $users[$userId]["user_display_name"];
            $stream[$index]["profile_image_url"] = $users[$userId]["profile_image_url"];
        }
    }
}

function fetchResponse($response)
{
    switch ($response[0]) {
        case 200:
            http_response_code(200);
            echo json_encode($response[1]);
            break;
        case 400:
            http_response_code(400);
            echo json_encode(["error" => "Invalid 'limit' parameter."]);
            break;
        case 401:
            http_response_code(401);
            echo json_encode(["error" => "Unauthorized. Twitch access token is invalid or has expired."]);
            break;
        case 500:
            http_response_code(500);
            echo json_encode(["error" => "Internal server error."]);
            break;
    }
}

function ordenar($var1, $var2)
{
    return $var2["viewer_count"] - $var1["viewer_count"];
}
