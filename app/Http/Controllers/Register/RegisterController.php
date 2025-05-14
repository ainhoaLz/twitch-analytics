<?php

namespace App\Http\Controllers\Register;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ValidationException;
use App\Repositories\UserRepository;
use App\Services\DatabaseConnection;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class RegisterController extends BaseController
{
    private Controller $controller;
    private RegisterValidator $validator;
    public function __construct()
    {
        $this->controller = new Controller();
        $this->validator = new RegisterValidator();
    }
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $userRepository = new UserRepository(new DatabaseConnection());
            $userService = new UserService($userRepository);
            $userEmail = $this->validator->validate($request->input('email'));
            $response = $userService->register($userEmail);
            $httpCode = $response['httpCode'] ?? 200;
            unset($response['httpCode']);
            return response()->json($response, $httpCode);
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 400);
        }
    }
}
