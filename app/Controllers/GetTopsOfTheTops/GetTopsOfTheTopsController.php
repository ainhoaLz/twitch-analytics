<?php

namespace App\Controllers\GetTopsOfTheTops;

use App\Application\Services\TopsofthetopsService;
use App\Application\Services\TwitchTokenService;
use App\Controllers\Controller;
use App\Controllers\ValidationException;
use App\Domain\Exceptions\NotFoundException;
use App\Domain\Exceptions\ServerException;
use App\Domain\Exceptions\UnknownException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetTopsOfTheTopsController
{
    private Controller $controller;
    private GetTopsOfTheTopsValidator $validator;
    private TwitchTokenService $tokenService;
    private TopsofthetopsService $topsofthetopsService;


    public function __construct(TwitchTokenService $tokenService, TopsofthetopsService $topsofthetopsService)
    {
        $this->tokenService = $tokenService;
        $this->controller = new Controller($this->tokenService);
        $this->validator = new GetTopsOfTheTopsValidator();
        $this->topsofthetopsService = $topsofthetopsService;
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
            $sinceValue = $this->validator->validate($request->input('since', '600'));
            $response = $this->topsofthetopsService->getTopsofthetops($sinceValue);
            $httpCode = $response['httpCode'] ?? 200;
            unset($response['httpCode']);
            return response()->json($response, $httpCode);
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 400);
        } catch (NotFoundException $exception) {
            return response()->json(['error' => $exception->getMessage()], 404);
        } catch (ServerException $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        } catch (UnknownException $exception) {
            return response()->json(['error' => $exception->getMessage()], $exception->getCode());
        }
    }
}
