<?php

namespace App\Http\Controllers\GetTopsOfTheTops;

use App\Http\Controllers\ValidationException;

class GetTopsOfTheTopsValidator
{
    public function validate(?string $since): int
    {
        if (!ctype_digit($since) || $since < 0) {
            throw new ValidationException("Invalid or missing 'since' parameter.");
        }
        return ctype_digit($since);
    }
}
