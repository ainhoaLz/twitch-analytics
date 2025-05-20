<?php

namespace App\Controllers\Register;

use App\Application\Services\UserService;
use App\Controllers\ValidationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class RegisterController extends BaseController
{
    private RegisterValidator $validator;
    private UserService $userService;
    public function __construct(UserService $userService)
    {
        $this->validator = new RegisterValidator();
        $this->userService = $userService;
    }
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $userEmail = $this->validator->validate($request->input('email'));
            $response = $this->userService->register($userEmail);
            $httpCode = $response['httpCode'] ?? 200;
            unset($response['httpCode']);
            return response()->json($response, $httpCode);
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 400);
        }
    }
}
