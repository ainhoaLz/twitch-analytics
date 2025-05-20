<?php

namespace Tests\Unit\Application\Services;

use App\Application\Services\TwitchUserService;
use App\Domain\Exceptions\NotFoundException;
use App\Domain\Interfaces\TwitchAPI\TwichUserRepositoryInterface;
use Mockery;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class TwitchUserServiceTest extends TestCase
{
    private TwitchUserService $service;
    private TwichUserRepositoryInterface $repository;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(TwichUserRepositoryInterface::class);
        $this->service = new TwitchUserService($this->repository);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function errorThrownIfUserNotFound(): void
    {
        $userId = 145678765434;
        $this->repository->expects('getUser')
            ->with($userId)
            ->andReturn([
                'httpCode' => 200,
                'user' => json_encode(['data' => []])
            ]);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage("User not found.");

        $this->service->getUserById($userId);
    }
}
