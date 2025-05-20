<?php

namespace App\Controllers\GetUserById;

use App\Application\Services\TwitchTokenService;
use App\Application\Services\TwitchUserService;
use App\Application\Services\RegisterService;
use App\Controllers\Controller;
use App\Controllers\ValidationException;
use App\Domain\Exceptions\ServerException;
use App\Domain\Exceptions\UnauthorizedException;
use App\Domain\Exceptions\UnknownException;
use App\Domain\Exceptions\NotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class GetUserByIdController extends BaseController
{
    private Controller $controller;
    private GetUserByIdValidator $userIdValidator;
    private TwitchUserService $userService;
    private TwitchTokenService $tokenService;

    public function __construct(TwitchUserService $userService, TwitchTokenService $tokenService)
    {
        $this->tokenService = $tokenService;
        $this->controller = new Controller($this->tokenService);
        $this->userIdValidator = new GetUserByIdValidator();
        $this->userService = $userService;
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
            $twitchUserId = $this->userIdValidator->validate($request->input('id'));
            $response = $this->userService->getUserById($twitchUserId);
            $httpCode = $response['httpCode'] ?? 200;
            unset($response['httpCode']);
            return response()->json($response, $httpCode);
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 400);
        } catch (NotFoundException $exception) {
            return response()->json(['error' => $exception->getMessage()], 404);
        } catch (UnauthorizedException $exception) {
            return response()->json(['error' => $exception->getMessage()], 401);
        } catch (ServerException $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        } catch (UnknownException $exception) {
            return response()->json(['error' => $exception->getMessage()], $exception->getCode());
        }
    }
}
