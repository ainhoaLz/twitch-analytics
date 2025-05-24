<?php

namespace Tests\Unit\Application\Services;

use App\Application\Services\TwitchUserService;
use App\Domain\Exceptions\NotFoundException;
use App\Domain\Interfaces\TwitchAPI\TwichUserRepositoryInterface;
use App\Domain\Models\User;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use App\Domain\Exceptions\UnauthorizedException;
use App\Domain\Exceptions\UnknownException;

class TwitchUserServiceTest extends TestCase
{
    private TwitchUserService $twitchUserService;
    private MockObject $mockedRepository;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockedRepository = $this->createMock(TwichUserRepositoryInterface::class);
        $this->twitchUserService = new TwitchUserService($this->mockedRepository);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function givenValidIdReturnsUser(): void
    {
        $userId = 12345678;
        $expectedUser = new User([
            'userId' => $userId,
            'login' => 'test_user',
            'displayName' => 'Test User',
            'type' => '',
            'broadcasterType' => '',
            'description' => 'Test description',
            'profileImageUrl' => 'https://example.com/image.jpg',
            'offlineImageUrl' => 'https://example.com/offline.jpg',
            'viewCount' => 1000,
            'createdAt' => '2020-01-01T00:00:00Z'
        ]);
        $mockApiResponse = [
            'httpCode' => 200,
            'user' => $expectedUser
        ];
        $this->mockedRepository
            ->expects($this->once())
            ->method('getUser')
            ->with($userId)
            ->willReturn($mockApiResponse);

        $response = $this->twitchUserService->getUserById($userId);

        $this->assertEquals($expectedUser, $response);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function givenNonExistentUserThrowsNotFoundException(): void
    {
        $userId = 12345678;
        $mockApiResponse = [
            'httpCode' => 404,
            'user' => json_encode(['data' => []])
        ];
        $this->mockedRepository
            ->expects($this->once())
            ->method('getUser')
            ->with($userId)
            ->willReturn($mockApiResponse);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage("User not found.");

        $this->twitchUserService->getUserById($userId);
    }
    /**
     * @test
     * @throws \Exception
     */
    public function invalidTwitchAccessTokenThrowsUnauthorizedException(): void
    {
        $userId = 12345678;
        $mockApiResponse = [
            'httpCode' => 401,
            'user' => json_encode(['data' => []])
        ];
        $this->mockedRepository
            ->expects($this->once())
            ->method('getUser')
            ->with($userId)
            ->willReturn($mockApiResponse);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage("Unauthorized. Twitch access token is invalid or has expired.");

        $this->assertEquals($mockApiResponse, $this->twitchUserService->getUserById($userId));
    }
    /**
     * @test
     * @throws \Exception
     */
    public function code500ThrowsServerException(): void
    {
        $userId = 12345678;
        $mockApiResponse = [
            'httpCode' => 500,
            'user' => json_encode(['data' => []])
        ];
        $this->mockedRepository
            ->expects($this->once())
            ->method('getUser')
            ->with($userId)
            ->willReturn($mockApiResponse);

        $this->expectException(\App\Domain\Exceptions\ServerException::class);
        $this->expectExceptionMessage("Internal server error.");

        $this->twitchUserService->getUserById($userId);
    }
    /**
     * @test
     * @throws \Exception
     */
    public function unexpectedErrorThrowsUnknownException(): void
    {
        $userId = 12345678;
        $mockApiResponse = [
            'httpCode' => 999,
            'user' => json_encode(['data' => []])
        ];
        $this->mockedRepository
            ->expects($this->once())
            ->method('getUser')
            ->with($userId)
            ->willReturn($mockApiResponse);

        $this->expectException(UnknownException::class);
        $this->expectExceptionCode(999);
        $this->expectExceptionMessage("Unknown error.");

        $this->twitchUserService->getUserById($userId);
    }
}
