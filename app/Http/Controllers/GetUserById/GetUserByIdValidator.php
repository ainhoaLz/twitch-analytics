<?php

namespace App\Http\Controllers\GetUserById;

use App\Http\Controllers\ValidationException;

class GetUserByIdValidator
{
    public function validate(int|string|null $userId): int
    {
        if (is_null($userId) || !ctype_digit((string)$userId) || (int)$userId < 1) {
            throw new ValidationException("Invalid or missing 'id' parameter.");
        }
        return (int)$userId;
    }
}
