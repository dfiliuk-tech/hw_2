<?php

declare(strict_types=1);

namespace App\GOF\Adapter;

class ASCIIToIntegerAdapter implements IntegerStackInterface
{
    private ASCIIStackInterface $asciiStack;

    public function __construct(ASCIIStackInterface $asciiStack)
    {
        $this->asciiStack = $asciiStack;
    }

    public function push(int $integer): void
    {
        $char = chr($integer);
        $this->asciiStack->push($char);
    }

    public function pop(): int
    {
        $char = $this->asciiStack->pop();

        if ($char === null) {
            throw new \RuntimeException("Stack is empty");
        }

        return ord($char);
    }
}
