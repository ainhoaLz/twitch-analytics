
<?php
$accessToken = "c9v255gz2xi5xij9293ei6vaob999x";
$clientId = "myqofuuwfbtja3s178j5x7osa78xfh";
$url = "https://api.twitch.tv/helix/streams";

$ch = curl_init($url);
$headers = ["Authorization: Bearer $accessToken", "Client-Id: $clientId", "Content-type: application/json"];

curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);

$data = json_decode($response, true);

foreach ($data["data"] as $stream) {
    $resp[] = [$stream["title"], $stream["user_name"]];

}

curl_close($ch);
echo json_encode($resp, JSON_PRETTY_PRINT);

?>
