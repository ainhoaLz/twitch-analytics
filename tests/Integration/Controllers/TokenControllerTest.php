<?php

namespace Tests\Integration\Controllers;

use App\Http\Controllers\Token\TokenController;
use Exception;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class TokenControllerTest extends TestCase
{
    private TokenController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new TokenController();
    }

    /**
     * @test
     * @throws Exception
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

    /**
     * @test
     * @throws Exception
     */
    public function gets400WhenApiKeyParameterIsMissing(): void
    {
        $request = new Request();
        $request->query->set('email', 'ainhoa@gmail.com');

        $response = $this->controller->__invoke($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals("The api_key is mandatory", $responseData['error']);
    }
}
