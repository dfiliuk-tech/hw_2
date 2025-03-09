<?php
declare(strict_types=1);

namespace App\gof\AbstractFactory\Repository;


use App\gof\AbstractFactory\Entity\Person;

interface PersonRepositoryInterface {
    public function savePerson(Person $person): void;
    
    public function readPeople(): array;
    
    public function readPerson(string $name): ?Person;
}
