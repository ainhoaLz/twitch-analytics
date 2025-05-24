<?php

namespace Tests\Unit\Application\Services;

use App\Application\Services\TwitchTokenService;
use App\Domain\Interfaces\Database\TwitchTokenRepositoryInterface as DatabaseTwitchTokenRepositoryInterface;
use App\Domain\Interfaces\TwitchAPI\TwitchTokenRepositoryInterface as ApiTwitchTokenRepositoryInterface;
use DateTime;
use Illuminate\Http\Request;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TwitchTokenServiceTest extends TestCase
{
    private TwitchTokenService $twitchTokenService;
    private MockObject $dbRepositoryMock;
    private MockObject $apiRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbRepositoryMock = $this->createMock(DatabaseTwitchTokenRepositoryInterface::class);
        $this->apiRepositoryMock = $this->createMock(ApiTwitchTokenRepositoryInterface::class);
        $this->twitchTokenService = new TwitchTokenService($this->dbRepositoryMock, $this->apiRepositoryMock);
    }
    /**
     * @test
     */
    public function getsToken(): void
    {
        $clientId = 'test_client_id';
        $clientSecret = 'test_client_secret';
        $storedToken = [
            'token' => 'valid_token',
            'expires_at' => (new DateTime('+1 hour'))->format('Y-m-d H:i:s')
        ];
        $this->dbRepositoryMock
            ->expects($this->once())
            ->method('getStoredToken')
            ->willReturn($storedToken);
        $this->apiRepositoryMock
            ->expects($this->never())
            ->method('getNewToken');

        $token = $this->twitchTokenService->getToken($clientId, $clientSecret);

        $this->assertEquals('valid_token', $token);
    }

    /**
     * @test
     */
    public function givenUserWithExpiredTokenReturnsNewToken(): void
    {
        $clientId = 'test_client_id';
        $clientSecret = 'test_client_secret';
        $storedToken = [
            'token' => 'expired_token',
            'expires_at' => (new DateTime('-1 hour'))->format('Y-m-d H:i:s')
        ];
        $this->dbRepositoryMock
            ->expects($this->once())
            ->method('getStoredToken')
            ->willReturn($storedToken);
        $this->apiRepositoryMock
            ->expects($this->once())
            ->method('getNewToken')
            ->with($clientId, $clientSecret)
            ->willReturn('new_token');

        $token = $this->twitchTokenService->getToken($clientId, $clientSecret);

        $this->assertEquals('new_token', $token);
    }

    /**
     * @test
     * @throws Exception
     */
    public function isTokenValidReturnsTrueForValidToken(): void
    {
        $request = $this->createMock(Request::class);
        $request
            ->expects($this->once())
            ->method('header')
            ->with('Authorization')
            ->willReturn('Bearer valid_token');
        $this->dbRepositoryMock
            ->expects($this->once())
            ->method('isTokenValidInDB')
            ->with('valid_token')
            ->willReturn(true);

        $isValid = $this->twitchTokenService->isTokenValid($request);

        $this->assertTrue($isValid);
    }

    /**
     * @test
     * @throws Exception
     */
    public function isTokenValidReturnsFalseForMissingAuthorizationHeader(): void
    {
        $request = $this->createMock(Request::class);
        $request
            ->expects($this->once())
            ->method('header')
            ->with('Authorization')
            ->willReturn(null);
        $this->dbRepositoryMock
            ->expects($this->never())
            ->method('isTokenValidInDB');

        $isValid = $this->twitchTokenService->isTokenValid($request);

        $this->assertFalse($isValid);
    }
}
