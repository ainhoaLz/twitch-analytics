<?php

namespace Tests\Unit\Controllers\Validators;

use App\Controllers\GetUserById\GetUserByIdValidator;
use App\Controllers\ValidationException;
use PHPUnit\Framework\TestCase;

class GetUserByIdValidatorTest extends TestCase
{
    private GetUserByIdValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new GetUserByIdValidator();
    }

    /**
     * @test
     */
    public function givenValidIntReturnsNumber()
    {
        $result = $this->validator->validate(123);

        $this->assertEquals(123, $result);
    }

    /**
     * @test
     */
    public function givenValidStringReturnsNumber()
    {
        $result = $this->validator->validate('123');

        $this->assertEquals(123, $result);
    }

    /**
     * @test
     */
    public function givenNullThrowsValidationException()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("Invalid or missing 'id' parameter.");

        $this->validator->validate(null);
    }

    /**
     * @test
     */
    public function givenNonNumericStringThrowsValidationException()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("Invalid or missing 'id' parameter.");

        $this->validator->validate('abc');
    }

    /**
     * @test
     */
    public function givenNegativeNumberThrowsValidationException()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("Invalid or missing 'id' parameter.");

        $this->validator->validate('-3');
    }

    /**
     * @test
     */
    public function givenZeroThrowsValidationException()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("Invalid or missing 'id' parameter.");

        $this->validator->validate(0);
    }
}
