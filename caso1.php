<?php
$accessToken = "c9v255gz2xi5xij9293ei6vaob999x";
$clientId = "myqofuuwfbtja3s178j5x7osa78xfh";
$streamerId = "39518378";
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
    echo curl_error($ch);
    curl_close($ch);
    exit;
}

echo $response;

curl_close($ch);
?>