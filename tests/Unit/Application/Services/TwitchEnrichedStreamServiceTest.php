<?php

namespace Tests\Unit\Application\Services;

use App\Application\Services\TwitchEnrichedStreamService;
use App\Domain\Exceptions\InvalidParameterException;
use App\Domain\Exceptions\ServerException;
use App\Domain\Exceptions\UnauthorizedException;
use App\Domain\Exceptions\UnknownException;
use App\Domain\Interfaces\TwitchAPI\TwitchStreamRepositoryInterface;
use App\Domain\Interfaces\TwitchAPI\TwichUserRepositoryInterface;
use App\Domain\Models\EnrichedStream;
use PHPUnit\Framework\TestCase;

class TwitchEnrichedStreamServiceTest extends TestCase
{
    private TwitchStreamRepositoryInterface $streamRepository;
    private TwichUserRepositoryInterface $userRepository;
    private TwitchEnrichedStreamService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->streamRepository = $this->createMock(TwitchStreamRepositoryInterface::class);
        $this->userRepository = $this->createMock(TwichUserRepositoryInterface::class);
        $this->service = new TwitchEnrichedStreamService($this->streamRepository, $this->userRepository);
    }

    /**
     * @test
     */
    public function getsEnrichedStreams()
    {
        $streams = [
            $this->createEnrichedStream('1', 'u1', 'User1', 100, 'Stream A'),
            $this->createEnrichedStream('2', 'u2', 'User2', 50, 'Stream B'),
        ];
        $this->streamRepository->method('getEnrichedStreamsFromTwitch')->willReturn([
            'httpCode' => 200,
            'streams' => $streams
        ]);
        $this->userRepository->method('getMultipleUsers')->willReturn([
            'httpCode' => 200,
            'response' => json_encode([
                'data' => [
                    ['id' => 'u1', 'display_name' => 'DisplayUser1', 'profile_image_url' => 'url1'],
                    ['id' => 'u2', 'display_name' => 'DisplayUser2', 'profile_image_url' => 'url2'],
                ]
            ])
        ]);

        $result = $this->service->getEnrichedStreams(2);

        $this->assertCount(2, $result);
        $this->assertInstanceOf(EnrichedStream::class, $result[0]);
        $this->assertEquals('DisplayUser1', $result[0]->getUserDisplayName());
    }

    /**
     * @test
     */
    public function invalidTwitchTokenThrowsUnauthorizedException()
    {
        $this->streamRepository->method('getEnrichedStreamsFromTwitch')->willReturn([
            'httpCode' => 401,
            'streams' => null
        ]);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage("Unauthorized. Twitch access token is invalid or has expired.");

        $this->service->getEnrichedStreams(1);
    }


    /**
     * @test
     */
    public function code500ThrowsServerException()
    {
        $this->streamRepository->method('getEnrichedStreamsFromTwitch')->willReturn([
            'httpCode' => 500,
            'streams' => null
        ]);

        $this->expectException(ServerException::class);
        $this->expectExceptionMessage("Internal server error.");

        $this->service->getEnrichedStreams(1);
    }

    /**
     * @test
     */
    public function unexpectedErrorThrowsUnknownException()
    {
        $this->streamRepository->method('getEnrichedStreamsFromTwitch')->willReturn([
            'httpCode' => 418,
            'streams' => null
        ]);

        $this->expectException(UnknownException::class);
        $this->expectExceptionCode(418);
        $this->expectExceptionMessage("Unknown error.");

        $this->service->getEnrichedStreams(1);
    }

    /**
     * @test
     */
    public function givenInvalidLimitValueThrowsInvalidParameterException()
    {
        $streams = [
            $this->createEnrichedStream('1', '1', 'User1', 100, 'Stream A'),
        ];
        $this->streamRepository->method('getEnrichedStreamsFromTwitch')->willReturn([
            'httpCode' => 200,
            'streams' => $streams
        ]);

        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage("Invalid 'limit' parameter.");

        $this->service->getEnrichedStreams(5);
    }

    private function createEnrichedStream($streamId, $userId, $userName, $viewerCount, $title): EnrichedStream
    {
        return new EnrichedStream([
            'stream_id' => $streamId,
            'user_id' => $userId,
            'user_name' => $userName,
            'viewer_count' => $viewerCount,
            'title' => $title,
            'user_display_name' => $userName,
            'profile_image_url' => 'http://example.com/profile.jpg',
        ]);
    }
}
