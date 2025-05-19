<?php

namespace App\Controllers\GetTopsOfTheTops;

use App\Application\Services\DatabaseConnection;
use App\Application\Services\TopsofthetopsService;
use App\Application\Services\TwitchTokenService;
use App\Controllers\Controller;
use App\Controllers\ValidationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetTopsOfTheTopsController
{
    private string $accessToken;
    private string $clientId;
    private Controller $controller;
    private GetTopsOfTheTopsValidator $validator;

    public function __construct()
    {
        $this->clientId = "myqofuuwfbtja3s178j5x7osa78xfh";
        $clientSecret = "wp871wusg2nboymbedhwkd9oxkllgu";
        $twitchService = new TwitchTokenService(new DatabaseConnection());
        $this->accessToken = $twitchService->getToken($this->clientId, $clientSecret);
        $this->controller = new Controller();
        $this->validator = new GetTopsOfTheTopsValidator();
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
        try {
            $service = new TopsofthetopsService(new DatabaseConnection());
            $sinceValue = $this->validator->validate($request->input('since', '600'));
            $response = $service->getTopsofthetops($this->accessToken, $this->clientId, $sinceValue);
            $httpCode = $response['httpCode'] ?? 200;
            unset($response['httpCode']);
            return response()->json($response, $httpCode);
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 400);
        }
    }
}
