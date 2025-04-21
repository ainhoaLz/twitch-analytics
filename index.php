<?php

include 'UserRepository.php';
include 'StreamRepository.php';
include 'EnrichedStreamsRepository.php';
include 'UserRegistration.php';
include 'TokenGenerator.php';
include 'DatabaseFunctions.php';
include 'TopStreamsCollection.php';
use DateTime;

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

/**
 * @throws DateMalformedStringException
 */
function getToken($clientId, $clientSecret)
{

    $con = connectToDB();
    $result = getStoredToken($con);
    if ($result && !isTokenExpired(new DateTime($result['expires_at']))) {
        return $result['token'];
    }

    return getNewToken($con, $clientId, $clientSecret);
}

function getStoredToken($con)
{

    $query = $con->prepare("SELECT token, expires_at FROM twitch_tokens");
    $query->execute();
    return $query->fetch(PDO::FETCH_ASSOC);
}

function isTokenExpired($expiresAt): bool
{
    $now = new DateTime();
    return $now >= $expiresAt;
}

function getNewToken($con, $clientId, $clientSecret)
{

    $url = "https://id.twitch.tv/oauth2/token";
    $headers = [
        "client_id" => $clientId,
        "client_secret" => $clientSecret,
        "grant_type" => "client_credentials"
    ];
    $response = getTokenFromTwitch($url, $headers);
    $result = json_decode($response, true);
    storeTokenInDDBB($con, $result['access_token'], $result['expires_in']);
    return $result["access_token"];
}

function getTokenFromTwitch($url, $headers)
{

    $curlh = curl_init();
    curl_setopt($curlh, CURLOPT_URL, $url);
    curl_setopt($curlh, CURLOPT_POST, true);
    curl_setopt($curlh, CURLOPT_POSTFIELDS, http_build_query($headers));
    curl_setopt($curlh, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curlh, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($curlh);
    curl_close($curlh);
    return $response;
}

function storeTokenInDDBB($con, $accessToken, $expiresIn)
{

    $expires_at = date("Y-m-d H:i:s", time() + $expiresIn);
    $insertQuery = $con -> prepare("INSERT INTO twitch_tokens (token, expires_at) VALUES (?, ?)");
    $insertQuery -> execute($accessToken, $expires_at);
}

function isTokenValid(): bool
{
    $headers = getAllHeaders();
    if (!isset($headers["Authorization"])) {
        return false;
    }
    $authHeader = $headers['Authorization'];
    $token = str_replace('Bearer ', '', $authHeader);
    $con = connectToDB();
    $query = $con->prepare("SELECT id FROM tokens WHERE token = ? AND expires_at > NOW()");
    $query->execute([$token]);
    $result = $query->fetchColumn();
    if (empty($result)) {
        return false;
    }

    return true;
}

/**
 * @throws DateMalformedStringException
 * @throws \Random\RandomException
 * @throws Exception
 */
function handleRequest($accessToken, $clientId): void
{
    $requestPath = strtok($_SERVER["REQUEST_URI"], '?');
    if ($requestPath == "/analytics/user") {
        getUsers($accessToken, $clientId, $_GET['id']);
        return;
    }
    if ($requestPath == "/analytics/streams") {
        getStreams($accessToken, $clientId);
        return;
    }
    if ($requestPath == "/analytics/streams/enriched") {
        getEnrichedStreams($accessToken, $clientId, $_GET['limit']);
        return;
    }
    if ($requestPath == "/analytics/topsofthetops") {
        getTopsofthetops($accessToken, $clientId, $_GET['since'] ?? '600');
        return;
    }
    if ($requestPath == "/register") {
        registerUser(getBody());
        return;
    }
    if ($requestPath == "/token") {
        generateToken(getBody());
    }
}

function getBody()
{
    $body = file_get_contents("php://input");
    return json_decode($body, true);
}
