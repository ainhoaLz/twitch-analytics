<?php

namespace Tests\Integration\Controllers;

use App\Application\Services\RegisterService;
use App\Application\Services\TwitchUserService;
use App\Controllers\Register\RegisterController;
use App\Domain\Interfaces\Database\UserRepositoryInterface;
use App\Infrastructure\Repositories\Database\UserRepository;
use Illuminate\Http\Request;
use Mockery;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class RegisterControllerTest extends TestCase
{
    private RegisterController $controller;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $service = new RegisterService($userRepository);
        $this->controller = new RegisterController($service);
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
