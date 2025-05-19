<?php

namespace Tests\Integration\Controllers;

use App\Controllers\Controller;
use App\Controllers\GetEnrichedStreams\GetEnrichedStreamsController;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class GetEnrichedStreamsControllerTest extends TestCase
{
    private GetEnrichedStreamsController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new GetEnrichedStreamsController();

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
