<?php
include 'caso1.php';
include 'caso2.php';
include 'caso3.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

$accessToken = "c9v255gz2xi5xij9293ei6vaob999x";
$clientId = "myqofuuwfbtja3s178j5x7osa78xfh";

$requestPath = strtok($_SERVER["REQUEST_URI"], '?');

switch($requestPath) {
    case "/analytics/user":
        getUsers($accessToken, $clientId, $_GET['id']);
        break;
    case "/analytics/streams":
        getStreams($accessToken, $clientId);
        break;
    case "/analytics/streams/enriched":
        getEnrichedStreams($accessToken, $clientId, $_GET['limit']);
        break;
}
?>