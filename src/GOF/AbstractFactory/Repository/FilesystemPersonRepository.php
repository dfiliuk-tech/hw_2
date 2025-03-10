<?php

declare(strict_types=1);

namespace App\GOF\AbstractFactory\Repository;

use App\GOF\AbstractFactory\Entity\Person;

class FilesystemPersonRepository implements PersonRepositoryInterface
{
    private string $dataDir;

    public function __construct()
    {

        $this->dataDir = dirname(__DIR__, 2) . '/data';
        if (!file_exists($this->dataDir)) {
            mkdir($this->dataDir, 0755, true);
        }
    }

    public function savePerson(Person $person): void
    {
        $filePath = $this->getPersonFilePath($person->getName());
        file_put_contents($filePath, json_encode($person->toArray(), JSON_PRETTY_PRINT));
    }

    public function readPeople(): array
    {
        $people = [];
        $files = glob($this->dataDir . '/*.json') ?: [];

        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data) {
                $people[] = new Person($data['name'], (int)$data['age'], $data['email']);
            }
        }

        return $people;
    }

    public function readPerson(string $name): ?Person
    {
        $filePath = $this->getPersonFilePath($name);

        if (file_exists($filePath)) {
            $data = json_decode(file_get_contents($filePath), true);
            if ($data) {
                return new Person($data['name'], (int)$data['age'], $data['email']);
            }
        }

        return null;
    }

    private function getPersonFilePath(string $name): string
    {
        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
        return $this->dataDir . '/' . $safeName . '.json';
    }
}
