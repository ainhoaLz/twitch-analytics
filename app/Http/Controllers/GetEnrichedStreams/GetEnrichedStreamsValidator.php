<?php

namespace App\Http\Controllers\GetEnrichedStreams;

use App\Http\Controllers\ValidationException;

class GetEnrichedStreamsValidator
{
    public function validate(int|string|null $limit): int
    {
        if (is_null($limit) || !ctype_digit((string)$limit) || (int)$limit <= 0) {
            throw new ValidationException("Invalid 'limit' parameter.");
        }
        return (int)$limit;
    }
}
