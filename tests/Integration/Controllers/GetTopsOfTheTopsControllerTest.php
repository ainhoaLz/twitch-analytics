<?php

namespace Tests\Integration\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\GetTopsOfTheTops\GetTopsOfTheTopsController;
use App\Http\Controllers\GetUserById\GetUserByIdController;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class GetTopsOfTheTopsControllerTest extends TestCase
{
    private GetTopsOfTheTopsController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new GetTopsOfTheTopsController();

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
    public function gets400WhenSinceValueIsBelowZero(): void
    {
        $request = new Request();
        $request->query->set('since', '-1');

        $response = $this->controller->__invoke($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals("Invalid or missing 'since' parameter.", $responseData['error']);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function gets400WhenSinceValueIsInvalid(): void
    {
        $request = new Request();
        $request->query->set('since', 'a');

        $response = $this->controller->__invoke($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals("Invalid or missing 'since' parameter.", $responseData['error']);
    }
}
