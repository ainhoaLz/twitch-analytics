
<?php
$access_token = "c9v255gz2xi5xij9293ei6vaob999x";
$clientId = "myqofuuwfbtja3s178j5x7osa78xfh";
$url = "https://api.twitch.tv/helix/streams";

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $access_token));
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $access_token",
    "Client-Id: $clientId"
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Desactiva la verificación del certificado SSL
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Desactiva la verificación del host SSL

$response = curl_exec($ch);

$data = json_decode($response, true);
foreach ($data["data"] as $stream) {
    $resp[] = [
        "title" => $stream["title"],
        "user_name" => $stream["user_name"]
    ];

}

curl_close($ch);
echo json_encode($resp, JSON_PRETTY_PRINT);

?>
