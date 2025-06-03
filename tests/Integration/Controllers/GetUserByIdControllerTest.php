<?php

namespace Tests\Integration\Controllers;

use App\Application\Services\TwitchTokenService;
use App\Application\Services\TwitchUserService;
use App\Controllers\Controller;
use App\Controllers\GetUserById\GetUserByIdController;
use App\Domain\Interfaces\Database\TwitchTokenRepositoryInterface;
use App\Domain\Interfaces\TwitchAPI\TwitchUserRepositoryInterface;
use Illuminate\Http\Request;
use Mockery;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class GetUserByIdControllerTest extends TestCase
{
    private GetUserByIdController $controller;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $twitchTokenRepo = $this->createMock(TwitchTokenRepositoryInterface::class);
        $apiRepository = $this->createMock(\App\Domain\Interfaces\TwitchAPI\TwitchTokenRepositoryInterface::class);
        $tokenService = new TwitchTokenService($twitchTokenRepo, $apiRepository);

        $userRepository = $this->createMock(TwitchUserRepositoryInterface::class);
        $userService = new TwitchUserService($userRepository);

        $this->controller = new GetUserByIdController($userService, $tokenService);

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
    public function gets400WhenIdParameterIsMissing(): void
    {
        $request = new Request();

        $response = $this->controller->__invoke($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals("Invalid or missing 'id' parameter.", $responseData['error']);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function gets400WhenIdParameterIsInvalid(): void
    {
        $request = new Request();
        $request->query->set('id', 'a');

        $response = $this->controller->__invoke($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals("Invalid or missing 'id' parameter.", $responseData['error']);
    }
}
