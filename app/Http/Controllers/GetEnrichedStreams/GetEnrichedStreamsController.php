<?php

namespace App\Http\Controllers\GetEnrichedStreams;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ValidationException;
use App\Services\DatabaseConnection;
use App\Services\TwitchEnrichedStreamService;
use App\Services\TwitchTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetEnrichedStreamsController
{
    private string $accessToken;
    private string $clientId;
    private Controller $controller;
    private GetEnrichedStreamsValidator $limitValidator;

    public function __construct()
    {
        $this->clientId = "myqofuuwfbtja3s178j5x7osa78xfh";
        $clientSecret = "wp871wusg2nboymbedhwkd9oxkllgu";
        $twitchService = new TwitchTokenService(new DatabaseConnection());
        $this->accessToken = $twitchService->getToken($this->clientId, $clientSecret);
        $this->controller = new Controller();
        $this->limitValidator = new GetEnrichedStreamsValidator();
    }

    public function __invoke(Request $request): JsonResponse
    {
        $tokenValidation = $this->controller->validateToken($request);
        if ($tokenValidation != 200) {
            return response()->json($tokenValidation, 401);
        }
        try {
            $limitValue = $this->limitValidator->validate($request->input('limit'));
            $enrichedService = new TwitchEnrichedStreamService();
            $response = $enrichedService->getEnrichedStreams($this->accessToken, $this->clientId, $limitValue);
            $httpCode = $response['httpCode'] ?? 200;
            unset($response['httpCode']);
            return response()->json($response, $httpCode);
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 400);
        }
    }
}
