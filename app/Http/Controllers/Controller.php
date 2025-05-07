<?php

namespace App\Http\Controllers;

use App\Services\DatabaseConnection;
use App\Services\TwitchTokenService;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public function __construct()
    {
    }

    public function getBody()
    {
        $body = file_get_contents("php://input");
        return json_decode($body, true);
    }

    public function validateToken(): array|int
    {
        $twitchService = new TwitchTokenService(new DatabaseConnection());
        if ($_SERVER["REQUEST_METHOD"] == 'GET' && !$twitchService->isTokenValid()) {
            http_response_code(401);
            return ["error" => "Unauthorized. Token is invalid or expired."];
        }

        return 200;
    }
}
