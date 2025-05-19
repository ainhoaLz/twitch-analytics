<?php

namespace App\Controllers;

use App\Application\Services\TwitchTokenService;
use App\Infrastructure\Database\DatabaseConnection;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    private TwitchTokenService $twitchService;
    public function __construct(TwitchTokenService $twitchService)
    {
        $this->twitchService = $twitchService;
    }
    public function validateToken(Request $request): array|int
    {
        if (!$this->twitchService->isTokenValid($request)) {
            http_response_code(401);
            return ["error" => "Unauthorized. Token is invalid or expired."];
        }



        return 200;
    }
}
