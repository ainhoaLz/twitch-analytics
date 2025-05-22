<?php

namespace Tests\Unit\Application\Services;

use App\Application\Services\TwitchUserService;
use App\Domain\Exceptions\NotFoundException;
use App\Domain\Interfaces\TwitchAPI\TwichUserRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TwitchUserServiceTest extends TestCase
{
    private TwitchUserService $twitchUserService;
    private MockObject $mockedRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockedRepository = $this->createMock(TwichUserRepositoryInterface::class);
        $this->twitchUserService = new TwitchUserService($this->mockedRepository);
    }

    /**
     * @test
     */
    public function getUserByIdReturnsValidResponse(): void
    {
        $userId = 12345678;
        $expectedResponse = [
            'id' => $userId,
            'name' => 'test_user',
            'view_count' => 1000
        ];

        $mockApiResponse = [
            'httpCode' => 200,
            'user' => json_encode([
                'data' => [$expectedResponse]
            ])
        ];

        $this->mockedRepository
            ->expects($this->once())
            ->method('getUser')
            ->with($userId)
            ->willReturn($mockApiResponse);

        $response = $this->twitchUserService->getUserById($userId);

        $this->assertEquals($expectedResponse, $response);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getUserByIdThrowsNotFoundException(): void
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
}
