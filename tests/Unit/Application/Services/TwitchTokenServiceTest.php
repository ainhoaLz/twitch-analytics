<?php

namespace Tests\Unit\Application\Services;

use App\Application\Services\TwitchTokenService;
use App\Domain\Interfaces\Database\TwitchTokenRepositoryInterface as DatabaseTwitchTokenRepositoryInterface;
use App\Domain\Interfaces\TwitchAPI\TwitchTokenRepositoryInterface as ApiTwitchTokenRepositoryInterface;
use DateTime;
use Illuminate\Http\Request;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class TwitchTokenServiceTest extends TestCase
{
    private TwitchTokenService $twitchTokenService;
    private MockInterface $dbRepositoryMock;
    private MockInterface $apiRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbRepositoryMock = Mockery::mock(DatabaseTwitchTokenRepositoryInterface::class);
        $this->apiRepositoryMock = Mockery::mock(ApiTwitchTokenRepositoryInterface::class);
        $this->twitchTokenService = new TwitchTokenService($this->dbRepositoryMock, $this->apiRepositoryMock);
    }

    public function testGetTokenReturnsStoredToken()
    {
        $clientId = 'test_client_id';
        $clientSecret = 'test_client_secret';
        $storedToken = [
            'token' => 'valid_token',
            'expires_at' => (new DateTime('+1 hour'))->format('Y-m-d H:i:s')
        ];

        $this->dbRepositoryMock
            ->shouldReceive('getStoredToken')
            ->once()
            ->andReturn($storedToken);

        $this->apiRepositoryMock
            ->shouldNotReceive('getNewToken');

        $token = $this->twitchTokenService->getToken($clientId, $clientSecret);

        $this->assertEquals('valid_token', $token);
    }

    public function testGetTokenFetchesNewTokenWhenExpired()
    {
        $clientId = 'test_client_id';
        $clientSecret = 'test_client_secret';
        $storedToken = [
            'token' => 'expired_token',
            'expires_at' => (new DateTime('-1 hour'))->format('Y-m-d H:i:s')
        ];

        $this->dbRepositoryMock
            ->shouldReceive('getStoredToken')
            ->once()
            ->andReturn($storedToken);

        $this->apiRepositoryMock
            ->shouldReceive('getNewToken')
            ->once()
            ->with($clientId, $clientSecret)
            ->andReturn('new_token');

        $token = $this->twitchTokenService->getToken($clientId, $clientSecret);

        $this->assertEquals('new_token', $token);
    }

    public function testIsTokenValidReturnsTrueForValidToken()
    {
        $request = Mockery::mock(Request::class);

        $request->shouldReceive('header')
            ->with('Authorization')
            ->once()
            ->andReturn('Bearer valid_token');

        $this->dbRepositoryMock
            ->shouldReceive('isTokenValidInDB')
            ->once()
            ->with('valid_token')
            ->andReturn(true);

        $isValid = $this->twitchTokenService->isTokenValid($request);

        $this->assertTrue($isValid);
    }

    public function testIsTokenValidReturnsFalseForMissingAuthorizationHeader()
    {
        $request = Mockery::mock(Request::class);

        $request->shouldReceive('header')
            ->with('Authorization')
            ->once()
            ->andReturn(null);

        $this->dbRepositoryMock
            ->shouldNotReceive('isTokenValidInDB');

        $isValid = $this->twitchTokenService->isTokenValid($request);

        $this->assertFalse($isValid);
    }


    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
