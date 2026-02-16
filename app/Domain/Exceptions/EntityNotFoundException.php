<?php

namespace App\Domain\Exceptions;

class EntityNotFoundException extends DomainException
{
    public function __construct(string $entity, int|string $id)
    {
        parent::__construct("{$entity} não encontrada(o) com ID: {$id}", [], 404);
    }
}
