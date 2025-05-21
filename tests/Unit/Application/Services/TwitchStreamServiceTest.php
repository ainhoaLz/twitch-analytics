<?php

namespace Tests\Unit\Application\Services;

use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use \App\Domain\Interfaces\TwitchAPI\TwitchStreamRepositoryInterface;
use \App\Application\Services\TwitchStreamService;
class TwitchStreamServiceTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testGetStreamsReturnsValidResponse()
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
}
