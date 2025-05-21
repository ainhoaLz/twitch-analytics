<?php

namespace Tests\Unit\Application\Controllers;

use App\Application\Services\TwitchUserService;
use App\Application\Services\TwitchTokenService;
use App\Controllers\Controller;
use App\Controllers\GetUserById\GetUserByIdController;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

// Add missing import

class GetUserByIdControllerTest extends TestCase
{
    public function testInvokeReturns200WithMockedService()
    {
        $request = new Request();
        $request->replace(['id' => 39518378]); // Set request data

        $mockedService = $this->createMock(TwitchUserService::class);
        $mockedService->method('getUserById')
            ->with(39518378)
            ->willReturn([
                'id' => 39518378,
                'name' => 'usuario_fake',
                'view_count' => 12345,
                'httpCode' => 200
            ]);

        $mockedTokenService = $this->createMock(TwitchTokenService::class);
        $mockedTokenService->method('getToken')
            ->willReturn('mocked_valid_token');

        $mockedController = $this->createMock(Controller::class);
        $mockedController->method('validateToken')
            ->willReturn(200);

        $controller = new GetUserByIdController($mockedService, $mockedTokenService);
        $reflection = new ReflectionClass($controller); // Use imported ReflectionClass
        $property = $reflection->getProperty('controller');
        $property->setValue($controller, $mockedController);

        $response = $controller($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertStringContainsString('usuario_fake', $response->getContent());
    }
}
