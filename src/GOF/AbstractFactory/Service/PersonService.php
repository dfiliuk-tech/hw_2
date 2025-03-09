<?php
declare(strict_types=1);

namespace App\GOF\AbstractFactory\Service;

use App\GOF\AbstractFactory\Entity\Person;
use App\GOF\AbstractFactory\Repository\PersonRepositoryInterface;

readonly class PersonService {
    public function __construct(
        private PersonRepositoryInterface $repository
    ) {}

    public function addPerson(string $name, int $age, string $email): void {
        $person = new Person($name, $age, $email);
        $this->repository->savePerson($person);
        echo "Person {$name} saved successfully.\n";
    }

    public function displayAllPeople(): void {
        $people = $this->repository->readPeople();
        
        if (empty($people)) {
            echo "No people found.\n";
            return;
        }
        
        echo "All people:\n";
        foreach ($people as $person) {
            echo "- Name: {$person->getName()}, Age: {$person->getAge()}, Email: {$person->getEmail()}\n";
        }
    }

    public function findPerson(string $name): void {
        $person = $this->repository->readPerson($name);
        
        if ($person) {
            echo "Found person:\n";
            echo "- Name: {$person->getName()}, Age: {$person->getAge()}, Email: {$person->getEmail()}\n";
        } else {
            echo "Person {$name} not found.\n";
        }
    }
}
