<?php
include 'caso1.php';
include 'caso2.php';
include 'caso3.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Request URI: " . $_SERVER['REQUEST_URI'];


$accessToken = "c9v255gz2xi5xij9293ei6vaob999x";
$clientId = "myqofuuwfbtja3s178j5x7osa78xfh";

$requestPath = explode('?', $_SERVER['REQUEST_URI'][0]);
switch($requestPath) {
    case "/analytics/user":
        var_dump($_GET['id']);
        echo $_SERVER['REQUEST_URI'];
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