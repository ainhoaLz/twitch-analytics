<?php

namespace App\Controllers\GetEnrichedStreams;

use App\Application\Services\TwitchEnrichedStreamService;
use App\Application\Services\TwitchTokenService;
use App\Controllers\Controller;
use App\Controllers\ValidationException;
use App\Infrastructure\Database\DatabaseConnection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetEnrichedStreamsController
{
    private string $accessToken;
    private string $clientId;
    private Controller $controller;
    private GetEnrichedStreamsValidator $limitValidator;
    private TwitchEnrichedStreamService $enrichedService;
    private TwitchTokenService $twitchService;

    public function __construct(TwitchEnrichedStreamService $enrichedService, TwitchTokenService $twitchService)
    {
        $this->clientId = "myqofuuwfbtja3s178j5x7osa78xfh";
        $clientSecret = "wp871wusg2nboymbedhwkd9oxkllgu";
        $this->enrichedService = $enrichedService;
        $this->twitchService = $twitchService;
        $this->accessToken = $this->twitchService->getToken($this->clientId, $clientSecret);
        $this->controller = new Controller($this->twitchService);
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
            $response = $this->enrichedService->getEnrichedStreams($this->accessToken, $this->clientId, $limitValue);
            $httpCode = $response['httpCode'] ?? 200;
            unset($response['httpCode']);
            return response()->json($response, $httpCode);
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 400);
        }
    }
}
