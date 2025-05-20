<?php

namespace App\Controllers\GetEnrichedStreams;

use App\Application\Services\TwitchEnrichedStreamService;
use App\Application\Services\TwitchTokenService;
use App\Controllers\Controller;
use App\Controllers\ValidationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetEnrichedStreamsController
{
    private Controller $controller;
    private GetEnrichedStreamsValidator $limitValidator;
    private TwitchEnrichedStreamService $enrichedService;
    private TwitchTokenService $twitchService;

    public function __construct(TwitchEnrichedStreamService $enrichedService, TwitchTokenService $twitchService)
    {
        $this->enrichedService = $enrichedService;
        $this->twitchService = $twitchService;
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
            $response = $this->enrichedService->getEnrichedStreams($limitValue);
            $httpCode = $response['httpCode'] ?? 200;
            unset($response['httpCode']);
            return response()->json($response, $httpCode);
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 400);
        }
    }
}
