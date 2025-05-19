<?php

namespace Tests\Unit\Application\Services;

use App\Application\Services\TokenService;
use App\Domain\Interfaces\TokenRepositoryInterface;
use App\Domain\Interfaces\UserRepositoryInterface;
use Mockery;
use PHPUnit\Framework\TestCase;

class TokenServiceTest extends TestCase
{
    private UserRepositoryInterface $userRepository;
    private TokenRepositoryInterface $tokenRepository;
    private TokenService $tokenService;

    protected function setUp(): void
    {
//        parent::setUp();
//        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
//        $this->tokenRepository = Mockery::mock(TokenRepositoryInterface::class);
//        $this->tokenService = new TokenService(
//            $this->userRepository,
//            $this->tokenRepository
//        );
    }

    /**
     * @test
     */
    public function getsTokenIfInputDataContainsEmailAndApikey()
    {
        //sin implementar todavia
    }

    /**
     * @test
     */
    public function errorThrownIfUserNotFound()
    {
    }
}
