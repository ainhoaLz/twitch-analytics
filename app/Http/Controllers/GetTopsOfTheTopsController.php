<?php

namespace App\Http\Controllers;

use App\Services\DatabaseConnection;
use App\Services\TopsofthetopsService;
use App\Services\TwitchTokenService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GetTopsOfTheTopsController
{
    private string $accessToken;
    private string $clientId;
    private Controller $controller;

    public function __construct(){
        $this->clientId = "myqofuuwfbtja3s178j5x7osa78xfh";
        $clientSecret = "wp871wusg2nboymbedhwkd9oxkllgu";
        $twitchService = new TwitchTokenService(new DatabaseConnection());
        $this->accessToken = $twitchService->getToken($this->clientId, $clientSecret);
        $this->controller = new Controller();
    }

    /**
     * @throws \Exception
     */
    public function __invoke(Request $request): JsonResponse
    {
        $tokenValidation = $this->controller->validateToken();
        if ($tokenValidation != 200) {
            return response()->json($tokenValidation, 401);
        }
        $service = new TopsofthetopsService(new DatabaseConnection());
        $response = $service->getTopsofthetops($this->accessToken, $this->clientId, request('since', '600'));
        $httpCode = $response['httpCode'] ?? 200;
        unset($response['httpCode']);
        return response()->json($response, $httpCode);
    }

}
