<?php

namespace Tests\Integration\Controllers;

use App\Application\Services\TopsofthetopsService;
use App\Application\Services\TwitchTokenService;
use App\Controllers\Controller;
use App\Controllers\GetTopsOfTheTops\GetTopsOfTheTopsController;
use App\Domain\Interfaces\Database\TopGamesRepositoryInterface;
use App\Domain\Interfaces\Database\TwitchTokenRepositoryInterface;
use App\Domain\Interfaces\TwitchAPI\TopsofthetopsRepositoryInterface;
use Illuminate\Http\Request;
use Mockery;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class GetTopsOfTheTopsControllerTest extends TestCase
{
    private GetTopsOfTheTopsController $controller;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $twitchTokenRepo = $this->createMock(TwitchTokenRepositoryInterface::class);
        $apiRepository = $this->createMock(\App\Domain\Interfaces\TwitchAPI\TwitchTokenRepositoryInterface::class);
        $tokenService = new TwitchTokenService($twitchTokenRepo, $apiRepository);

        $topGamesRepository = $this->createMock(TopGamesRepositoryInterface::class);
        $topsOfTheTopsRepo = $this->createMock(TopsofthetopsRepositoryInterface::class);
        $topsofthetopsService = new TopsofthetopsService($topGamesRepository, $topsOfTheTopsRepo);

        $this->controller = new GetTopsOfTheTopsController($tokenService, $topsofthetopsService);

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
