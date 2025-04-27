<?php

namespace App\Http\Controllers;

use App\Repositories\TwitchApi\TwitchUserRepository;
use App\Repositories\UserRepository;
use App\Services\TokenService;
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

        $twitchService = new TwitchTokenService();
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
        $user = $twitchService->getUser(request('id'));

        return response()->json($user);
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
        $streams = $twitchStreamService->getStreams($this->accessToken, $this->clientId);

        return response()->json($streams);
    }

    public function getEnrichedStreams(): \Illuminate\Http\JsonResponse
    {
        $tokenValidation = $this->validateToken();
        if ($tokenValidation != 200) {
            return response()->json($tokenValidation, 401);
        }

        $enrichedService = new TwitchEnrichedStreamService();
        $streams = $enrichedService->getEnrichedStreams($this->accessToken, $this->clientId, request('limit'));

        return response()->json($streams);
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
        return response()->json(getTopsofthetops($this->accessToken, $this->clientId, request('since', '600')));
    }

    public function registerUser(): \Illuminate\Http\JsonResponse
    {
        $userRepository = new UserRepository();
        $userService = new UserService($userRepository);
        $response = $userService->register($this->getBody());
        return response()->json($response);
    }

    public function generateToken(): \Illuminate\Http\JsonResponse
    {
        $tokenService = new TokenService();
        $result = $tokenService->generateToken($this->getBody());
        return response()->json($result);
    }

    private function getBody()
    {
        $body = file_get_contents("php://input");
        return json_decode($body, true);
    }

    private function validateToken(): array|int
    {
        $twitchService = new TwitchTokenService();
        if ($_SERVER["REQUEST_METHOD"] == 'GET' && !$twitchService->isTokenValid()) {
            http_response_code(401);
            return ["error" => "Unauthorized. Token is invalid or expired."];
        }

        return 200;
    }
}
