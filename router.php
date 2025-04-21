<?php

/**
 * @throws DateMalformedStringException
 * @throws \Random\RandomException
 * @throws Exception
 */
function handleRequest($accessToken, $clientId): void
{
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    $requestPath = strtok($_SERVER['REQUEST_URI'], '?');

    $routes = [
        'GET' => [
            '/analytics/user' => fn () => getUsers($accessToken, $clientId, $_GET['id']),
            '/analytics/streams' => fn () => getStreams($accessToken, $clientId),
            '/analytics/streams/enriched' => fn () => getEnrichedStreams($accessToken, $clientId, $_GET['limit']),
            '/analytics/topsofthetops' => fn () => getTopsofthetops($accessToken, $clientId, $_GET['since'] ?? '600'),
        ],
        'POST' => [
            '/register' => fn () => registerUser(getBody()),
            '/token' => fn () => generateToken(getBody()),
        ],
    ];
    if (isset($routes[$requestMethod][$requestPath])) {
        $routes[$requestMethod][$requestPath]();
        return;
    }
    http_response_code(405);
    echo json_encode(["error" => "Method Not Allowed"]);
}


function getBody()
{
    $body = file_get_contents("php://input");
    return json_decode($body, true);
}
