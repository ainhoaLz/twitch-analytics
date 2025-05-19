<?php

namespace App\Controllers;

use App\Application\Services\DatabaseConnection;
use App\Application\Services\TwitchTokenService;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public function __construct()
    {
    }
    public function validateToken(Request $request): array|int
    {
        $twitchService = new TwitchTokenService(new DatabaseConnection());
        if (!$twitchService->isTokenValid($request)) {
            http_response_code(401);
            return ["error" => "Unauthorized. Token is invalid or expired."];
        }



        return 200;
    }
}
