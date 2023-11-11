<?php

namespace App\Exceptions;

use Exception;

abstract class Referencable extends Exception
{
    public readonly string $reference;
    protected array $context = [];

    public function __construct(string $message = "", string $reference = null)
    {
        $this->reference = $reference ?? uniqid();
        parent::__construct($message);
    }

    public function reference(): string {
        return $this->reference;
    }

    public function context(): array {
        return ['reference' => $this->reference] + $this->context;
    }
}
