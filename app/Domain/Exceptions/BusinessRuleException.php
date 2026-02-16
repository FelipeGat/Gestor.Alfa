<?php

namespace App\Domain\Exceptions;

class BusinessRuleException extends DomainException
{
    public function __construct(string $message, array $errors = [])
    {
        parent::__construct($message, $errors, 422);
    }
}
