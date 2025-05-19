<?php

namespace Tests\Integration\Controllers;

use App\Controllers\Controller;
use App\Controllers\GetUserById\GetUserByIdController;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class GetUserByIdControllerTest extends TestCase
{
    private GetUserByIdController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new GetUserByIdController();

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
