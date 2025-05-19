<?php

namespace App\Controllers\Register;

use App\Application\Services\DatabaseConnection;
use App\Application\Services\UserService;
use App\Controllers\Controller;
use App\Controllers\ValidationException;
use App\Infrastructure\Repositories\UserRepository;
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
