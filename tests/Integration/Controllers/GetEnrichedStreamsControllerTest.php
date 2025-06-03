<?php

namespace Tests\Integration\Controllers;

use App\Application\Services\TwitchEnrichedStreamService;
use App\Application\Services\TwitchTokenService;
use App\Controllers\Controller;
use App\Controllers\GetEnrichedStreams\GetEnrichedStreamsController;
use App\Domain\Interfaces\Database\TwitchTokenRepositoryInterface;
use App\Domain\Interfaces\TwitchAPI\TwitchStreamRepositoryInterface;
use App\Domain\Interfaces\TwitchAPI\TwitchUserRepositoryInterface;
use Illuminate\Http\Request;
use Mockery;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class GetEnrichedStreamsControllerTest extends TestCase
{
    private GetEnrichedStreamsController $controller;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $streamRepository = $this->createMock(TwitchStreamRepositoryInterface::class);
        $userRepository = $this->createMock(TwitchUserRepositoryInterface::class);
        $streamService = new TwitchEnrichedStreamService($streamRepository, $userRepository);

        $twitchTokenRepo = $this->createMock(TwitchTokenRepositoryInterface::class);
        $apiRepository = $this->createMock(\App\Domain\Interfaces\TwitchAPI\TwitchTokenRepositoryInterface::class);
        $tokenService = new TwitchTokenService($twitchTokenRepo, $apiRepository);

        $this->controller = new GetEnrichedStreamsController($streamService, $tokenService);

        $mockController = $this->createMock(Controller::class);
        $mockController->method('validateToken')
            ->willReturn(200);

        $reflection = new ReflectionClass($this->controller);
        $property = $reflection->getProperty('controller');
        $property->setValue($this->controller, $mockController);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function gets400WhenLimitParameterIsMissing(): void
    {
        $request = new Request();

        $response = $this->controller->__invoke($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals("Invalid 'limit' parameter.", $responseData['error']);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function gets400WhenLimitParameterIsInvalid(): void
    {
        $request = new Request();
        $request->query->set('limit', 'a');

        $response = $this->controller->__invoke($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals("Invalid 'limit' parameter.", $responseData['error']);
    }
}
