<?php

declare(strict_types=1);

namespace App\GOF\Adapter;

class ASCIIStack implements ASCIIStackInterface
{
    private array $stack = [];

    public function push(string $char): void
    {

        $this->stack[] = $char;
    }

    public function pop(): ?string
    {
        if (empty($this->stack)) {
            return null;
        }

        return array_pop($this->stack);
    }
}
