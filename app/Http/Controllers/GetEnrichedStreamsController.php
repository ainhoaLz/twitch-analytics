<?php

namespace App\Http\Controllers;

use App\Services\DatabaseConnection;
use App\Services\TwitchEnrichedStreamService;
use App\Services\TwitchTokenService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GetEnrichedStreamsController
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

    public function __invoke(Request $request): JsonResponse
    {
        $tokenValidation = $this->controller->validateToken();
        if ($tokenValidation != 200) {
            return response()->json($tokenValidation, 401);
        }

        $enrichedService = new TwitchEnrichedStreamService();
        $response = $enrichedService->getEnrichedStreams($this->accessToken, $this->clientId, request('limit'));
        $httpCode = $response['httpCode'] ?? 200;
        unset($response['httpCode']);
        return response()->json($response, $httpCode);
    }
}
