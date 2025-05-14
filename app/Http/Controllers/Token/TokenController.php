<?php

namespace App\Http\Controllers\Token;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ValidationException;
use App\Services\DatabaseConnection;
use App\Services\TokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class TokenController extends BaseController
{
    private TokenValidator $validator;
    public function __construct()
    {
        $this->validator = new TokenValidator();
    }
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $tokenService = new TokenService(new DatabaseConnection());
            $email = $this->validator->validateEmail($request->input('email'));
            $apiKey = $this->validator->validateApiKey($request->input('api_key'));
            $data = [
                'email' => $email,
                'api_key' => $apiKey,
            ];
            $response = $tokenService->generateToken($data);
            $httpCode = $response['httpCode'] ?? 200;
            unset($response['httpCode']);
            return response()->json($response, $httpCode);
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 400);
        }
    }
}
