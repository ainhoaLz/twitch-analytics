<?php

namespace App\Http\Controllers;
use App\Repositories\UserRepository;
use App\Services\DatabaseConnection;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Routing\Controller as BaseController;

class RegisterController extends BaseController
{
    private Controller $controller;
    public function __construct()
    {
        $this->controller = new Controller();
    }
    public function __invoke(Request $request): JsonResponse
    {
        $userRepository = new UserRepository(new DatabaseConnection());
        $userService = new UserService($userRepository);
        $response = $userService->register($this->controller->getBody());
        $httpCode = $response['httpCode'] ?? 200;
        unset($response['httpCode']);
        return response()->json($response, $httpCode);
    }
}
