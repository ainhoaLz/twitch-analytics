<?php

namespace Tests\Unit\Controllers\Validators;

use App\Controllers\GetTopsOfTheTops\GetTopsOfTheTopsValidator;
use App\Controllers\ValidationException;
use PHPUnit\Framework\TestCase;

class GetTopsOfTheTopsValidatorTest extends TestCase
{
    private GetTopsOfTheTopsValidator $validator;
    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new GetTopsOfTheTopsValidator();
    }

    /**
     * @test
     */
    public function givenValidStringReturnsNumber()
    {
        $result = $this->validator->validate('10');

        $this->assertEquals(10, $result);
    }

    /**
     * @test
     */
    public function givenNegativeThrowsValidationException()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("Invalid or missing 'since' parameter.");

        $this->validator->validate('-5');
    }

    /**
     * @test
     */
    public function givenEmptyStringThrowsValidationException()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("Invalid or missing 'since' parameter.");

        $this->validator->validate('');
    }

    /**
     * @test
     */
    public function givenNonNumericStringThrowsValidationException()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("Invalid or missing 'since' parameter.");

        $this->validator->validate('abc');
    }
}
