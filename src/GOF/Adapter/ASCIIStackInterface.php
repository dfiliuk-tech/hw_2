<?php

declare(strict_types=1);

namespace App\GOF\Adapter;

interface ASCIIStackInterface
{
    public function push(string $char): void;
    public function pop(): ?string;
}
