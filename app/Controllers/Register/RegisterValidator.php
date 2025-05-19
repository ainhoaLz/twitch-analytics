<?php

namespace App\Controllers\Register;

use App\Controllers\ValidationException;

class RegisterValidator
{
    public function validate(?string $email): string
    {
        if (!isset($email)) {
            throw new ValidationException("The email is mandatory");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException("The email must be a valid email address");
        }
        return $email;
    }
}
