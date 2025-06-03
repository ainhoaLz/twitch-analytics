<?php

namespace Tests\Unit\Controllers\Validators;

use App\Controllers\Token\TokenValidator;
use App\Controllers\ValidationException;
use PHPUnit\Framework\TestCase;

class TokenValidatorTest extends TestCase
{
    private TokenValidator $validator;
    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new TokenValidator();
    }

    public function givenValidEmailReturnsEmail()
    {
        $result = $this->validator->validateEmail('ainhoa@gmail.com');

        $this->assertEquals('ainhoa@gmail.com', $result);
    }

    /**
     * @test
     */
    public function givenNullEmailThrowsValidationException()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("The email is mandatory");

        $this->validator->validateEmail(null);
    }

    /**
     * @test
     */
    public function givenInvalidEmailThrowsValidationException()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("The email must be a valid email address");

        $this->validator->validateEmail("abc@abc");
    }

    /**
     * @test
     */
    public function givenValidApiKeyReturnsApiKey()
    {
        $result = $this->validator->validateApiKey('hfiudashifs3924jkdasf');

        $this->assertEquals('hfiudashifs3924jkdasf', $result);
    }

    /**
     * @test
     */
    public function givenNullApiKeyThrowsValidationException()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("The api_key is mandatory");

        $this->validator->validateApiKey(null);
    }

    /**
     * @test
     */
    public function givenEmptyStringThrowsValidationException()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("The api_key is mandatory");

        $this->validator->validateApiKey('');
    }

    /**
     * @test
     */
    public function givenWhiteSpaceThrowsValidationException()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("The api_key is mandatory");

        $this->validator->validateApiKey('         ');
    }
}
