<?php

namespace Tests\Unit\Application\Services;

use App\Application\Services\TopsofthetopsService;
use App\Domain\Exceptions\NotFoundException;
use App\Domain\Exceptions\ServerException;
use App\Domain\Exceptions\UnknownException;
use App\Domain\Interfaces\Database\TopGamesRepositoryInterface;
use App\Domain\Interfaces\TwitchAPI\TopsofthetopsRepositoryInterface;
use DateTime;
use PHPUnit\Framework\TestCase;

class TopsofthetopsServiceTest extends TestCase
{
    private $dbRepository;
    private $twitchRepository;
    private TopsofthetopsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbRepository = $this->createMock(TopGamesRepositoryInterface::class);
        $this->twitchRepository = $this->createMock(TopsofthetopsRepositoryInterface::class);
        $this->service = new TopsofthetopsService($this->dbRepository, $this->twitchRepository);
    }

    /**
     * @test
     * @throws \DateMalformedStringException
     */
    public function returnsDataFromTwitchWhenDbIsEmpty(): void
    {
        $this->dbRepository->method('getUpdatedAt')->willReturn(null);
        $topGamesResponse = json_encode([
            'data' => [
                ['id' => '1', 'name' => 'Game 1'],
                ['id' => '2', 'name' => 'Game 2'],
                ['id' => '3', 'name' => 'Game 3'],
            ]
        ]);
        $this->twitchRepository
            ->method('getTop3Games')
            ->willReturn(['response' => $topGamesResponse, 'httpCode' => 200]);
        $videosResponse = json_encode([
            'data' => [
                [
                    'user_name' => 'StreamerA',
                    'title' => 'Epic Play',
                    'view_count' => 1000,
                    'duration' => '2h30m',
                    'created_at' => '2024-01-01T00:00:00Z'
                ],
                [
                    'user_name' => 'StreamerA',
                    'title' => 'More Plays',
                    'view_count' => 500,
                    'duration' => '1h20m',
                    'created_at' => '2024-01-02T00:00:00Z'
                ]
            ]
        ]);
        $this->twitchRepository
            ->method('getTop40Videos')
            ->willReturn(['response' => $videosResponse, 'httpCode' => 200]);

        $result = $this->service->getTopsofthetops(3600);

        $this->assertCount(3, $result);
        foreach ($result as $entry) {
            $this->assertEquals('StreamerA', $entry['user_name']);
            $this->assertEquals(2, $entry['total_videos']);
            $this->assertEquals(1500, $entry['total_views']);
            $this->assertEquals('Epic Play', $entry['most_viewed_title']);
        }
    }

    /**
     * @test
     */
    public function givenEmptyTwitchResponseThrowsNotFoundException(): void
    {
        $this->dbRepository->method('getUpdatedAt')->willReturn(null);
        $this->twitchRepository->method('getTop3Games')
            ->willReturn(['response' => null, 'httpCode' => 200]);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage("Not Found. No data available.");

        $this->service->getTopsofthetops(3600);
    }

    /**
     * @test
     */
    public function givenHttp500FromTwitchThrowsServerException(): void
    {
        $this->dbRepository->method('getUpdatedAt')->willReturn(null);
        $this->twitchRepository->method('getTop3Games')
            ->willReturn(['response' => null, 'httpCode' => 500]);

        $this->expectException(ServerException::class);
        $this->expectExceptionMessage("Internal server error.");

        $this->service->getTopsofthetops(3600);
    }

    /**
     * @test
     */
    public function givenUnknownHttpCodeThrowsUnknownException(): void
    {
        $this->dbRepository->method('getUpdatedAt')->willReturn(null);
        $this->twitchRepository->method('getTop3Games')
            ->willReturn(['response' => null, 'httpCode' => 418]);

        $this->expectException(UnknownException::class);
        $this->expectExceptionMessage("Unknown error.");

        $this->service->getTopsofthetops(3600);
    }

    /**
     * @test
     * @throws \DateMalformedStringException
     */
    public function returnsDataFromDBWhenInfoIsRecent(): void
    {
        $now = new DateTime();
        $recentTime = $now->format('Y-m-d H:i:s');
        $this->dbRepository->method('getUpdatedAt')->willReturn(['updated_at' => $recentTime]);
        $expectedDbResult = [
            ['game_id' => '1', 'game_name' => 'Game 1'],
            ['game_id' => '2', 'game_name' => 'Game 2']
        ];
        $this->dbRepository->method('getTopGames')->willReturn($expectedDbResult);

        $result = $this->service->getTopsofthetops(3600);

        $this->assertEquals($expectedDbResult, $result);
    }
}
