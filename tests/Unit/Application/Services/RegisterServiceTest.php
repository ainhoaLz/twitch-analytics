<?php

namespace Tests\Unit\Application\Services;

use App\Application\Services\RegisterService;
use App\Domain\Exceptions\ServerException;
use App\Domain\Interfaces\Database\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;

class RegisterServiceTest extends TestCase
{
    public function testRegisterUpdatesApiKeyIfUserExists()
    {
        $mockedUserRepository = $this->createMock(UserRepositoryInterface::class);

        $mockedUserRepository->method('existsByEmail')->with('test@example.com')->willReturn(true);
        $mockedUserRepository->expects($this->once())->method('updateApiKeyByEmail');

        $service = new RegisterService($mockedUserRepository);

        $response = $service->register('test@example.com');

        $this->assertArrayHasKey('api_key', $response);
        $this->assertNotEmpty($response['api_key']);
    }

    public function testRegisterInsertsNewUserIfNotExists()
    {
        $mockedUserRepository = $this->createMock(UserRepositoryInterface::class);

        $mockedUserRepository->method('existsByEmail')->with('newuser@example.com')->willReturn(false);
        $mockedUserRepository->method('getNextUserId')->willReturn(1);
        $mockedUserRepository->expects($this->once())->method('insertNewUser');

        $service = new RegisterService($mockedUserRepository);

        $response = $service->register('newuser@example.com');

        $this->assertArrayHasKey('api_key', $response);
        $this->assertNotEmpty($response['api_key']);
    }

    public function testRegisterThrowsServerExceptionOnError()
    {
        $mockedUserRepository = $this->createMock(UserRepositoryInterface::class);

        $mockedUserRepository->method('existsByEmail')->willThrowException(new \Exception());

        $service = new RegisterService($mockedUserRepository);

        $this->expectException(ServerException::class);

        $service->register('error@example.com');
    }
}
