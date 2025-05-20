<?php

namespace App\Controllers\GetStreams;

use App\Application\Services\TwitchStreamService;
use App\Application\Services\TwitchTokenService;
use App\Controllers\Controller;
use App\Domain\Exceptions\ServerException;
use App\Domain\Exceptions\UnauthorizedException;
use App\Domain\Exceptions\UnknownException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class GetStreamsController extends BaseController
{
    private Controller $controller;
    private TwitchTokenService $tokenService;
    private TwitchStreamService $twitchStreamService;

    public function __construct(TwitchTokenService $tokenService, TwitchStreamService $twitchStreamService)
    {
        $this->tokenService = $tokenService;
        $this->controller = new Controller($this->tokenService);
        $this->twitchStreamService = $twitchStreamService;
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
            $response = $this->twitchStreamService->getStreams();
            $httpCode = $response['httpCode'] ?? 200;
            unset($response['httpCode']);
            return response()->json($response, $httpCode);
        } catch (UnauthorizedException $exception) {
            return response()->json(['error' => $exception->getMessage()], 401);
        } catch (ServerException $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        } catch (UnknownException $exception) {
            return response()->json(['error' => $exception->getMessage()], $exception->getCode());
        }
    }
}
