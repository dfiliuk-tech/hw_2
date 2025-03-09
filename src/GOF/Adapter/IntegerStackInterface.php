<?php

declare(strict_types=1);

namespace App\GOF\Adapter;

interface IntegerStackInterface
{
    public function push(int $integer): void;
    public function pop(): int;
}
