<?php

namespace App\Domain\Exceptions;

class UnknownException extends \RuntimeException
{
    public function __construct(string $message, int $httpCode)
    {
        parent::__construct($message, $httpCode);
    }
}
