<?php

namespace App\Http\Controllers\Token;

use App\Http\Controllers\ValidationException;

class TokenValidator
{
    public function validateEmail(?string $email): string
    {
        if (!isset($email)) {
            throw new ValidationException("The email is mandatory");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException("The email must be a valid email address");
        }
        return $email;
    }
    public function validateApiKey(?string $apiKey): string
    {
        if (!isset($apiKey) || trim($apiKey) === '') {
            throw new ValidationException("The api_key is mandatory");
        }
        return $apiKey;
    }
}
