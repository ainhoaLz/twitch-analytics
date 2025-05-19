<?php

namespace App\Controllers\GetStreams;

use App\Application\Services\TwitchStreamService;
use App\Application\Services\TwitchTokenService;
use App\Controllers\Controller;
use App\Infrastructure\Database\DatabaseConnection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class GetStreamsController extends BaseController
{
    private string $accessToken;
    private string $clientId;
    private Controller $controller;
    private TwitchTokenService $tokenService;
    private TwitchStreamService $twitchStreamService;

    public function __construct(TwitchTokenService $tokenService, TwitchStreamService $twitchStreamService)
    {
        $this->clientId = "myqofuuwfbtja3s178j5x7osa78xfh";
        $clientSecret = "wp871wusg2nboymbedhwkd9oxkllgu";
        $this->tokenService = $tokenService;
        $this->accessToken = $this->tokenService->getToken($this->clientId, $clientSecret);
        $this->controller = new Controller($this->tokenService);
        $this->twitchStreamService = $twitchStreamService;
    }


    /**
     * @throws \Exception
     */
    public function __invoke(Request $request): JsonResponse
    {
        $tokenValidation = $this->controller->validateToken($request);
        if ($tokenValidation != 200) {
            return response()->json($tokenValidation, 401);
        }

        $response = $this->twitchStreamService->getStreams($this->accessToken, $this->clientId);
        $httpCode = $response['httpCode'] ?? 200;
        unset($response['httpCode']);
        return response()->json($response, $httpCode);
    }
}
