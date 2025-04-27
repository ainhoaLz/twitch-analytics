<?php

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use App\Services\DatabaseConnection;
use App\Services\TokenService;
use App\Services\TopsofthetopsService;
use App\Services\TwitchEnrichedStreamService;
use App\Services\TwitchStreamService;
use App\Services\TwitchTokenService;
use App\Services\TwitchUserService;
use App\Services\UserService;
use Exception;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    private string $accessToken;
    private string $clientId;


    public function __construct()
    {
        $this->clientId = "myqofuuwfbtja3s178j5x7osa78xfh";
        $clientSecret = "wp871wusg2nboymbedhwkd9oxkllgu";

        $twitchService = new TwitchTokenService(new DatabaseConnection());
        $this->accessToken = $twitchService->getToken($this->clientId, $clientSecret);
    }

    /**
     * @throws Exception
     */
    public function getUsers(): \Illuminate\Http\JsonResponse
    {
        $tokenValidation = $this->validateToken();
        if ($tokenValidation != 200) {
            return response()->json($tokenValidation, 401);
        }
        $twitchService = new TwitchUserService($this->accessToken, $this->clientId);
        $response = $twitchService->getUser(request('id'));
        $httpCode = $response['httpCode'] ?? 200;
        unset($response['httpCode']);
        return response()->json($response, $httpCode);
    }

    /**
     * @throws Exception
     */
    public function getStreams(): \Illuminate\Http\JsonResponse
    {
        $tokenValidation = $this->validateToken();
        if ($tokenValidation != 200) {
            return response()->json($tokenValidation, 401);
        }

        $twitchStreamService = new TwitchStreamService();
        $response = $twitchStreamService->getStreams($this->accessToken, $this->clientId);
        $httpCode = $response['httpCode'] ?? 200;
        unset($response['httpCode']);
        return response()->json($response, $httpCode);
    }

    public function getEnrichedStreams(): \Illuminate\Http\JsonResponse
    {
        $tokenValidation = $this->validateToken();
        if ($tokenValidation != 200) {
            return response()->json($tokenValidation, 401);
        }

        $enrichedService = new TwitchEnrichedStreamService();
        $response = $enrichedService->getEnrichedStreams($this->accessToken, $this->clientId, request('limit'));
        $httpCode = $response['httpCode'] ?? 200;
        unset($response['httpCode']);
        return response()->json($response, $httpCode);
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function getTopsofthetops(): \Illuminate\Http\JsonResponse
    {
        $tokenValidation = $this->validateToken();
        if ($tokenValidation != 200) {
            return response()->json($tokenValidation, 401);
        }
        $service = new TopsofthetopsService(new DatabaseConnection());
        $response = $service->getTopsofthetops($this->accessToken, $this->clientId, request('since', '600'));
        $httpCode = $response['httpCode'] ?? 200;
        unset($response['httpCode']);
        return response()->json($response, $httpCode);
    }

    public function registerUser(): \Illuminate\Http\JsonResponse
    {
        $userRepository = new UserRepository(new DatabaseConnection());
        $userService = new UserService($userRepository);
        $response = $userService->register($this->getBody());
        $httpCode = $response['httpCode'] ?? 200;
        unset($response['httpCode']);
        return response()->json($response, $httpCode);
    }

    public function generateToken(): \Illuminate\Http\JsonResponse
    {
        $tokenService = new TokenService(new DatabaseConnection());
        $response = $tokenService->generateToken($this->getBody());
        $httpCode = $response['httpCode'] ?? 200;
        unset($response['httpCode']);
        return response()->json($response, $httpCode);
    }

    private function getBody()
    {
        $body = file_get_contents("php://input");
        return json_decode($body, true);
    }

    private function validateToken(): array|int
    {
        $twitchService = new TwitchTokenService(new DatabaseConnection());
        if ($_SERVER["REQUEST_METHOD"] == 'GET' && !$twitchService->isTokenValid()) {
            http_response_code(401);
            return ["error" => "Unauthorized. Token is invalid or expired."];
        }

        return 200;
    }
}
