<?php

namespace Tests\Unit\Controllers\Validators;

use App\Controllers\Register\RegisterValidator;
use App\Controllers\ValidationException;
use PHPUnit\Framework\TestCase;

class RegisterValidatorTest extends TestCase
{
    private RegisterValidator $validator;
    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new RegisterValidator();
    }

    /**
     * @test
     */
    public function givenValidEmailReturnsEmail()
    {
        $result = $this->validator->validate('ainhoa@gmail.com');

        $this->assertEquals('ainhoa@gmail.com', $result);
    }

    /**
     * @test
     */
    public function givenNullEmailThrowsValidationException()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("The email is mandatory");

        $this->validator->validate(null);
    }

    /**
     * @test
     */
    public function givenInvalidEmailThrowsValidationException()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("The email must be a valid email address");

        $this->validator->validate("abc@abc");
    }
}
