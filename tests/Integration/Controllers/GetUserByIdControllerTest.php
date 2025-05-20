<?php

namespace Tests\Integration\Controllers;

use App\Application\Services\TwitchTokenService;
use App\Application\Services\TwitchUserService;
use App\Controllers\Controller;
use App\Controllers\GetUserById\GetUserByIdController;
use Illuminate\Http\Request;
use Mockery;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class GetUserByIdControllerTest extends TestCase
{
    private GetUserByIdController $controller;
    private TwitchUserService $userService;
    private TwitchTokenService $tokenService;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->tokenService = $this->createMock(TwitchTokenService::class);
        $this->userService = $this->createMock(TwitchUserService::class);
        $this->controller = new GetUserByIdController($this->userService, $this->tokenService);

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
