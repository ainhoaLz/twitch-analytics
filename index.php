<?php
include 'caso1.php';
include 'caso2.php';
include 'caso3.php';
function getToken($clientId, $clientSecret) {
    $url = "https://id.twitch.tv/oauth2/token";
    $headers = [
        "client_id" => $clientId,
        "client_secret" => $clientSecret,
        "grant_type" => "client_credentials"
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($headers));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    return $result["access_token"];
}

//$accessToken = "c9v255gz2xi5xij9293ei6vaob999x";
$clientId = "myqofuuwfbtja3s178j5x7osa78xfh";
$clientSecret = "q3vb6b3xi9inlhwvuy6gcysvy3lgx1";
$accessToken = getToken($clientId, $clientSecret);

$requestPath = strtok($_SERVER["REQUEST_URI"], '?');
//switch($requestPath) {
//    case "/analytics/user":
//        getUsers($accessToken, $clientId, $_GET['id']);
//        break;
//    case "/analytics/streams":
//        getStreams($accessToken, $clientId);
//        break;
//    case "/analytics/streams/enriched":
//        getEnrichedStreams($accessToken, $clientId, $_GET['limit']);
//        break;
//}
switch($requestPath) {
    case "/twitch-analytics/index.php/analytics/user":
        getUsers($accessToken, $clientId, $_GET['id']);
        break;
    case "/twitch-analytics/index.php/analytics/streams":
        getStreams($accessToken, $clientId);
        break;
    case "/twitch-analytics/index.php/analytics/streams/enriched":
        getEnrichedStreams($accessToken, $clientId, $_GET['limit']);
        break;
}
?>