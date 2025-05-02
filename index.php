<?php

require 'UserRepository.php';
require 'StreamRepository.php';
require 'EnrichedStreamsRepository.php';
require 'UserRegistration.php';
require 'TokenGenerator.php';
require 'DatabaseFunctions.php';
require 'TopStreamsCollection.php';
require 'router.php';
require 'tokenFunctions.php';

header("Content-Type: application/json");
$clientId = "myqofuuwfbtja3s178j5x7osa78xfh";
$clientSecret = "wp871wusg2nboymbedhwkd9oxkllgu";
$accessToken = getToken($clientId, $clientSecret);
if ($_SERVER["REQUEST_METHOD"] == 'GET' && !isTokenValid()) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized. Token is invalid or expired."]);
    exit;
}

handleRequest($accessToken, $clientId);
