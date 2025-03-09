<?php
declare(strict_types=1);

namespace App\GOF\Adapter;

class IntegerStack implements IntegerStackInterface
{
    private array $stack = [];

    public function push(int $integer): void
    {
        $this->stack[] = $integer;
    }

    public function pop(): int
    {
        if (empty($this->stack)) {
            throw new \RuntimeException("Stack is empty");
        }

        return array_pop($this->stack);
    }
}