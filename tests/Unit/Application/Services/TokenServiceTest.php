<?php

namespace Tests\Unit\Application\Services;

use App\Application\Services\TokenService;
use App\Domain\Exceptions\ServerException;
use App\Domain\Exceptions\UnauthorizedException;
use App\Domain\Interfaces\Database\TokenRepositoryInterface;
use App\Domain\Interfaces\Database\UserRepositoryInterface;
use Exception;
use PHPUnit\Framework\TestCase;

class TokenServiceTest extends TestCase
{
    private $tokenRepository;
    private $userRepository;
    private TokenService $tokenService;

    protected function setUp(): void
    {
        $this->tokenRepository = $this->createMock(TokenRepositoryInterface::class);
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);

        $this->tokenService = new TokenService($this->tokenRepository, $this->userRepository);
    }

    /**
     * @test
     */
    public function givenEmailAndApiKeyReturnsToken()
    {
        $email = 'test@example.com';
        $apiKey = 'valid_api_key';
        $userId = 123;
        $tokenId = 456;
        $this->userRepository->method('getUserId')->with($email, $apiKey)->willReturn($userId);
        $this->tokenRepository->method('getNewTokenId')->willReturn($tokenId);
        $this->tokenRepository->method('checkTokenExistence')->with($userId)->willReturn(null);
        $this->tokenRepository->expects($this->once())->method('insertNewToken');

        $result = $this->tokenService->generateToken([
            'email' => $email,
            'api_key' => $apiKey,
        ]);

        $this->assertArrayHasKey('token', $result);
        $this->assertIsString($result['token']);
        $this->assertEquals(64, strlen($result['token']));
    }

    /**
     * @test
     */
    public function givenNonExistentUserThrowsUnauthorizedException()
    {
        $this->userRepository->method('getUserId')->willReturn(null);
        $this->expectException(UnauthorizedException::class);

        $this->tokenService->generateToken([
            'email' => 'invalid@example.com',
            'api_key' => 'wrong_key',
        ]);
    }

    /**
     * @test
     */
    public function unexpectedErrorThrowsServerException()
    {
        $email = 'test@example.com';
        $apiKey = 'valid_key';
        $userId = 123;
        $this->userRepository->method('getUserId')->willReturn($userId);
        $this->tokenRepository->method('getNewTokenId')->willThrowException(new Exception('DB error'));
        $this->expectException(ServerException::class);

        $this->tokenService->generateToken([
            'email' => $email,
            'api_key' => $apiKey,
        ]);
    }
}
