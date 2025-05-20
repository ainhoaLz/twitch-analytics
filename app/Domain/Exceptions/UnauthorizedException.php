<?php

namespace App\Domain\Exceptions;

class UnauthorizedException extends \RuntimeException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
