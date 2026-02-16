<?php

namespace App\Domain\Exceptions;

use Exception;

class DomainException extends Exception
{
    protected array $errors = [];

    public function __construct(string $message, array $errors = [], int $code = 422)
    {
        parent::__construct($message, $code);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
