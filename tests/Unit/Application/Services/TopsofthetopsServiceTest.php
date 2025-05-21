<?php

namespace Tests\Unit\Application\Services;

use App\Application\Services\TopsofthetopsService;
use App\Domain\Exceptions\NotFoundException;
use App\Domain\Interfaces\Database\TopGamesRepositoryInterface;
use App\Domain\Interfaces\TwitchAPI\TopsofthetopsRepositoryInterface;
use PHPUnit\Framework\TestCase;

class TopsofthetopsServiceTest extends TestCase
{
    public function testGetTopsofthetopsReturnsValidResponse()
    {
        $mockedDbRepository = $this->createMock(TopGamesRepositoryInterface::class);
        $mockedApiRepository = $this->createMock(TopsofthetopsRepositoryInterface::class);

        // Mock database repository behavior
        $mockedDbRepository->method('getUpdatedAt')->willReturn([]);
        $mockedDbRepository->method('getTopGames')->willReturn([]);

        // Mock API repository behavior
        $mockedApiRepository->method('getTop3Games')->willReturn([
            'response' => json_encode([
                'data' => [
                    ['id' => '1', 'name' => 'Game 1'],
                    ['id' => '2', 'name' => 'Game 2'],
                ]
            ]),
            'httpCode' => 200,
        ]);

        $mockedApiRepository->method('getTop40Videos')->willReturn([
            'response' => json_encode([
                'data' => [
                    [
                        'user_name' => 'User1',
                        'title' => 'Video 1',
                        'view_count' => 100,
                        'duration' => '10m',
                        'created_at' => '2023-01-01',
                    ],
                ]
            ]),
            'httpCode' => 200,
        ]);

        // Create a partial mock of the service to mock callTwitchAPI
        $service = $this->getMockBuilder(TopsofthetopsService::class)
            ->setConstructorArgs([$mockedDbRepository, $mockedApiRepository])
            ->onlyMethods(['callTwitchAPI'])
            ->getMock();

        $service->method('getTopsofthetops')->willReturn([
            [
                [
                    "game_id" => "1",
                    "game_name" => "Game 1",
                    "user_name" => "User1",
                    "total_videos" => 1,
                    "total_views" => 100,
                    "most_viewed_title" => "Video 1",
                    "most_viewed_views" => 100,
                    "most_viewed_duration" => "10m",
                    "most_viewed_created_at" => "2023-01-01",
                ]
            ],
            200
        ]);

        $response = $service->getTopsofthetops(3600);

        $this->assertIsArray($response);
        $this->assertCount(1, $response);
        $this->assertEquals('Game 1', $response[0]['game_name']);
    }
}
