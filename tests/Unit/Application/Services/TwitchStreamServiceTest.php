<?php

namespace Tests\Unit\Application\Services;

use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use App\Domain\Interfaces\TwitchAPI\TwitchStreamRepositoryInterface;
use App\Application\Services\TwitchStreamService;
use App\Domain\Exceptions\UnauthorizedException;
use App\Domain\Exceptions\UnknownException;
use App\Domain\Exceptions\ServerException;

class TwitchStreamServiceTest extends TestCase
{
    /**
     * @test
     * @throws Exception
     */
    public function getStreamsReturnsValidResponse()
    {
        $mockedRepository = $this->createMock(TwitchStreamRepositoryInterface::class);

        $twitchStreamService = new TwitchStreamService($mockedRepository);

        $expectedResponse = [
            [
                'title' => 'Test Stream 1',
                'user_name' => 'User1'
            ],
            [
                'title' => 'Test Stream 2',
                'user_name' => 'User2'
            ]
        ];

        $mockApiResponse = [
            'httpCode' => 200,
            'streams' => json_encode(['data' => [
                ['type' => 'live', 'title' => 'Test Stream 1', 'user_name' => 'User1'],
                ['type' => 'live', 'title' => 'Test Stream 2', 'user_name' => 'User2']
            ]])
        ];

        $mockedRepository->expects($this->once())
            ->method('getStreamsFromTwitch')
            ->willReturn($mockApiResponse);

        $response = $twitchStreamService->getStreams();
        $this->assertEquals($expectedResponse, $response);
    }

    /**
     * @test
     * @throws \Exception
     * @throws Exception
     */
    public function getStreamsThrowsUnauthorizedException()
    {
        $mockedRepository = $this->createMock(TwitchStreamRepositoryInterface::class);

        $twitchStreamService = new TwitchStreamService($mockedRepository);

        $mockApiResponse = [
            'httpCode' => 401,
            'streams' => ''
        ];

        $mockedRepository->expects($this->once())
            ->method('getStreamsFromTwitch')
            ->willReturn($mockApiResponse);

        $this->expectException(UnauthorizedException::class);
        $twitchStreamService->getStreams();
    }
    /**
     * @test
     * @throws \Exception
     * @throws Exception
     */
    public function getStreamsThrowsUnknownException()
    {
        $mockedRepository = $this->createMock(TwitchStreamRepositoryInterface::class);

        $twitchStreamService = new TwitchStreamService($mockedRepository);

        $mockApiResponse = [
            'httpCode' => 310,
            'streams' => ''
        ];

        $mockedRepository->expects($this->once())
            ->method('getStreamsFromTwitch')
            ->willReturn($mockApiResponse);

        $this->expectException(UnknownException::class);
        $twitchStreamService->getStreams();
    }
    /**
     * @test
     * @throws \Exception
     * @throws Exception
     */
    public function getStreamsThrowsServerException()
    {
        $mockedRepository = $this->createMock(TwitchStreamRepositoryInterface::class);

        $twitchStreamService = new TwitchStreamService($mockedRepository);

        $mockApiResponse = [
            'httpCode' => 500,
            'streams' => ''
        ];

        $mockedRepository->expects($this->once())
            ->method('getStreamsFromTwitch')
            ->willReturn($mockApiResponse);

        $this->expectException(ServerException::class);
        $twitchStreamService->getStreams();
    }
}
