<?php
declare(strict_types=1);

namespace App\gof\AbstractFactory\Entity;

readonly class Person {

    public function __construct(
        private string $name,
        private int    $age,
        private string $email
    ) {}

    public function getName(): string {
        return $this->name;
    }

    public function getAge(): int {
        return $this->age;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function toArray(): array {
        return [
            'name' => $this->name,
            'age' => $this->age,
            'email' => $this->email
        ];
    }
}
