<?php

namespace App\Controllers\GetTopsOfTheTops;

use App\Controllers\ValidationException;

class GetTopsOfTheTopsValidator
{
    public function validate(?string $since): int
    {
        if (!ctype_digit($since) || $since < 0) {
            throw new ValidationException("Invalid or missing 'since' parameter.");
        }
        return (int)($since);
    }
}
