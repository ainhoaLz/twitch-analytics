<?php

namespace Tests\Integration\Controllers;

use App\Application\Services\RegisterService;
use App\Application\Services\TwitchUserService;
use App\Controllers\Register\RegisterController;
use Illuminate\Http\Request;
use Mockery;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class RegisterControllerTest extends TestCase
{
    private RegisterController $controller;
    private RegisterService $service;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->createMock(RegisterService::class);
        $this->controller = new RegisterController($this->service);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function gets400WhenEmailParameterIsMissing(): void
    {
        $request = new Request();

        $response = $this->controller->__invoke($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals("The email is mandatory", $responseData['error']);
    }

    /**
     * @test
     */
    public function gets400WhenEmailParameterIsNull(): void
    {
        $request = new Request();
        $request->query->set('email', '');

        $response = $this->controller->__invoke($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals("The email must be a valid email address", $responseData['error']);
    }

    /**
     * @test
     */
    public function gets400WhenInvalidEmailIsGiven(): void
    {
        $request = new Request();
        $request->query->set('email', 'abc');

        $response = $this->controller->__invoke($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals("The email must be a valid email address", $responseData['error']);
    }
}
