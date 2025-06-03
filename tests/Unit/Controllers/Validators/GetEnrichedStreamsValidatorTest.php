<?php

namespace Tests\Unit\Controllers\Validators;

use App\Controllers\GetEnrichedStreams\GetEnrichedStreamsValidator;
use App\Controllers\ValidationException;
use PHPUnit\Framework\TestCase;

class GetEnrichedStreamsValidatorTest extends TestCase
{
    private GetEnrichedStreamsValidator $validator;
    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new GetEnrichedStreamsValidator();
    }

    /**
     * @test
     */
    public function givenValidIntegerReturnsNumber()
    {
        $result = $this->validator->validate(3);

        $this->assertEquals(3, $result);
    }

    /**
     * @test
     */
    public function givenValidStringReturnsNumber()
    {
        $result = $this->validator->validate('3');

        $this->assertEquals(3, $result);
    }

    /**
     * @test
     */
    public function givenNullThrowsValidationException()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("Invalid 'limit' parameter.");

        $this->validator->validate(null);
    }

    /**
     * @test
     */
    public function givenNegativeThrowsValidationException()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("Invalid 'limit' parameter.");

        $this->validator->validate('-1');
    }

    /**
     * @test
     */
    public function givenNonNumericStringThrowsValidationException()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("Invalid 'limit' parameter.");

        $this->validator->validate('abc');
    }

    /**
     * @test
     */
    public function givenEmptyStringThrowsValidationException()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("Invalid 'limit' parameter.");

        $this->validator->validate('');
    }
}
