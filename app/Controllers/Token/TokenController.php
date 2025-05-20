<?php

namespace App\Controllers\Token;

use App\Application\Services\TokenService;
use App\Controllers\ValidationException;
use App\Domain\Exceptions\ServerException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class TokenController extends BaseController
{
    private TokenValidator $validator;
    private TokenService $tokenService;
    public function __construct(TokenService $tokenService)
    {
        $this->validator = new TokenValidator();
        $this->tokenService = $tokenService;
    }
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $email = $this->validator->validateEmail($request->input('email'));
            $apiKey = $this->validator->validateApiKey($request->input('api_key'));
            $data = [
                'email' => $email,
                'api_key' => $apiKey,
            ];
            $response = $this->tokenService->generateToken($data);
            $httpCode = $response['httpCode'] ?? 200;
            unset($response['httpCode']);
            return response()->json($response, $httpCode);
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 400);
        } catch (ServerException $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }
}
