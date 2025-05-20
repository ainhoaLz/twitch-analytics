<?php

namespace App\Controllers;

use App\Domain\Exceptions\ApplicationException;

class ValidationException extends ApplicationException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
