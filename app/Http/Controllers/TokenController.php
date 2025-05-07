<?php

namespace App\Http\Controllers;
use App\Services\DatabaseConnection;
use App\Services\TokenService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Routing\Controller as BaseController;

class TokenController extends BaseController
{
    private Controller $controller;
    public function __construct()
    {
        $this->controller = new Controller();
    }
    public function __invoke(Request $request): JsonResponse
    {
        $tokenService = new TokenService(new DatabaseConnection());
        $response = $tokenService->generateToken($this->controller->getBody());
        $httpCode = $response['httpCode'] ?? 200;
        unset($response['httpCode']);
        return response()->json($response, $httpCode);
    }
}
