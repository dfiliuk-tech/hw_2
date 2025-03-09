<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use App\gof\AbstractFactory\Factory\DatabasePersonRepositoryFactoryInterface;
use App\gof\AbstractFactory\Factory\FilesystemPersonRepositoryFactory;
use App\gof\AbstractFactory\Service\PersonService;

function main(): void {
    try {
        echo "Choose storage type (1 for Database, 2 for Filesystem): ";
        $choice = trim(fgets(STDIN) ?: '');
        
        $factory = match($choice) {
            '1' => new DatabasePersonRepositoryFactoryInterface(),
            default => new FilesystemPersonRepositoryFactory()
        };
        
        echo match($choice) {
            '1' => "Using Database storage.\n",
            default => "Using Filesystem storage.\n"
        };
        
        $repository = $factory->createPersonRepository();
        $personService = new PersonService($repository);
        
        while (true) {
            echo "\nOptions:\n";
            echo "1. Add a person\n";
            echo "2. Display all people\n";
            echo "3. Find a person by name\n";
            echo "4. Exit\n";
            echo "Enter your choice: ";
            $option = trim(fgets(STDIN) ?: '');
            
            match($option) {
                '1' => handleAddPerson($personService),
                '2' => $personService->displayAllPeople(),
                '3' => handleFindPerson($personService),
                '4' => exit("Goodbye!\n"),
                default => print("Invalid option. Try again.\n")
            };
        }
    } catch (Throwable $e) {
        echo "An error occurred: " . $e->getMessage() . "\n";
        exit(1);
    }
}

function handleAddPerson(PersonService $service): void {
    echo "Enter name: ";
    $name = trim(fgets(STDIN) ?: '');
    
    echo "Enter age: ";
    $ageInput = trim(fgets(STDIN) ?: '0');
    $age = (int)$ageInput;
    
    echo "Enter email: ";
    $email = trim(fgets(STDIN) ?: '');
    
    $service->addPerson($name, $age, $email);
}

function handleFindPerson(PersonService $service): void {
    echo "Enter name to search: ";
    $name = trim(fgets(STDIN) ?: '');
    $service->findPerson($name);
}

main();
