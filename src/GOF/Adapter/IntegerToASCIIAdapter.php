<?php

declare(strict_types=1);

namespace App\GOF\Adapter;

class IntegerToASCIIAdapter implements ASCIIStackInterface
{
    private IntegerStackInterface $integerStack;

    public function __construct(IntegerStackInterface $integerStack)
    {
        $this->integerStack = $integerStack;
    }

    public function push(string $char): void
    {
        $ascii = ord($char);
        $this->integerStack->push($ascii);
    }

    public function pop(): ?string
    {
        try {
            $ascii = $this->integerStack->pop();
            return chr($ascii);
        } catch (\RuntimeException $e) {
            return null;
        }
    }
}
