<?php

namespace Tests\Unit\Application\Services;

use App\Application\Services\RegisterService;
use App\Domain\Exceptions\ServerException;
use App\Domain\Interfaces\Database\UserRepositoryInterface;
use Exception;
use PHPUnit\Framework\TestCase;

class RegisterServiceTest extends TestCase
{
    /**
     * @test
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function givenExistingEmailUpdatesAndReturnsNewApiKey()
    {
        $email = 'test@example.com';
        $mockedUserRepository = $this->createMock(UserRepositoryInterface::class);
        $mockedUserRepository->method('existsByEmail')
            ->with($email)
            ->willReturn(true);
        $mockedUserRepository->expects($this->once())
            ->method('updateApiKeyByEmail')
            ->with($email, $this->isType('string'));
        $service = new RegisterService($mockedUserRepository);

        $response = $service->register($email);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('api_key', $response);
        $this->assertNotEmpty($response['api_key']);
    }


    /**
     * @test
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function givenEmailInsertsNewUserAndReturnsApiKey()
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

    /**
     * @test
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function givenErrorThrowsServerException()
    {
        $mockedUserRepository = $this->createMock(UserRepositoryInterface::class);

        $mockedUserRepository->method('existsByEmail')->willThrowException(new Exception());

        $service = new RegisterService($mockedUserRepository);

        $this->expectException(ServerException::class);

        $service->register('error@example.com');
    }
}
