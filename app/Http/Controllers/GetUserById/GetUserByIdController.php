<?php

namespace App\Http\Controllers\GetUserById;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ValidationException;
use App\Services\DatabaseConnection;
use App\Services\TwitchTokenService;
use App\Services\TwitchUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class GetUserByIdController extends BaseController
{
    private string $accessToken;
    private string $clientId;
    private Controller $controller;
    private GetUserByIdValidator $userIdValidator;

    public function __construct()
    {
        $this->clientId = "myqofuuwfbtja3s178j5x7osa78xfh";
        $clientSecret = "wp871wusg2nboymbedhwkd9oxkllgu";
        $twitchService = new TwitchTokenService(new DatabaseConnection());
        $this->accessToken = $twitchService->getToken($this->clientId, $clientSecret);
        $this->controller = new Controller();
        $this->userIdValidator = new GetUserByIdValidator();
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
            $twitchUserId = $this->userIdValidator->validate($request->input('id'));
            $twitchService = new TwitchUserService($this->accessToken, $this->clientId);
            $response = $twitchService->getUser($twitchUserId);
            $httpCode = $response['httpCode'] ?? 200;
            unset($response['httpCode']);
            return response()->json($response, $httpCode);
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 400);
        }
    }
}
