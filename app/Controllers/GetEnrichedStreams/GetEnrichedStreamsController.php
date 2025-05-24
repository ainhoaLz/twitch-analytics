<?php

namespace App\Controllers\GetEnrichedStreams;

use App\Application\Services\TwitchEnrichedStreamService;
use App\Application\Services\TwitchTokenService;
use App\Controllers\Controller;
use App\Controllers\ValidationException;
use App\Domain\Exceptions\InvalidParameterException;
use App\Domain\Exceptions\ServerException;
use App\Domain\Exceptions\UnauthorizedException;
use App\Domain\Exceptions\UnknownException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetEnrichedStreamsController
{
    private Controller $controller;
    private GetEnrichedStreamsValidator $limitValidator;
    private TwitchEnrichedStreamService $enrichedService;
    private TwitchTokenService $twitchService;

    public function __construct(TwitchEnrichedStreamService $enrichedService, TwitchTokenService $twitchService)
    {
        $this->enrichedService = $enrichedService;
        $this->twitchService = $twitchService;
        $this->controller = new Controller($this->twitchService);
        $this->limitValidator = new GetEnrichedStreamsValidator();
    }

    public function __invoke(Request $request): JsonResponse
    {
        $tokenValidation = $this->controller->validateToken($request);
        if ($tokenValidation != 200) {
            return response()->json($tokenValidation, 401);
        }
        try {
            $limitValue = $this->limitValidator->validate($request->input('limit'));
            $enrichedStreams = $this->enrichedService->getEnrichedStreams($limitValue);
            return response()->json(
                array_map(fn ($s) => $s->toArray(), $enrichedStreams),
                200
            );
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 400);
        } catch (UnauthorizedException $exception) {
            return response()->json(['error' => $exception->getMessage()], 401);
        } catch (InvalidParameterException $exception) {
            return response()->json(['error' => $exception->getMessage()], 400);
        } catch (ServerException $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        } catch (UnknownException $exception) {
            return response()->json(['error' => $exception->getMessage()], $exception->getCode());
        }
    }
}
