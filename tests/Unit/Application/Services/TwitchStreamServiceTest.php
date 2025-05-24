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
     * @throws \Exception
     */
    public function getsStream()
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
            'streams' => [
                ['title' => 'Test Stream 1', 'user_name' => 'User1'],
                ['title' => 'Test Stream 2', 'user_name' => 'User2']
            ]
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
    public function invalidTwitchAccessTokenThrowsUnauthorizedException()
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
        $this->expectExceptionMessage("Unauthorized. Twitch access token is invalid or has expired.");

        $twitchStreamService->getStreams();
    }
    /**
     * @test
     * @throws \Exception
     * @throws Exception
     */
    public function unexpectedErrorThrowsUnknownException()
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
        $this->expectExceptionCode(310);
        $this->expectExceptionMessage("Unknown error.");

        $twitchStreamService->getStreams();
    }
    /**
     * @test
     * @throws \Exception
     * @throws Exception
     */
    public function code500ThrowsServerException()
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
        $this->expectExceptionMessage("Internal server error.");

        $twitchStreamService->getStreams();
    }
}
