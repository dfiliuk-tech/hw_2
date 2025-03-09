<?php
declare(strict_types=1);

namespace App\gof\AbstractFactory\Repository;

use App\gof\AbstractFactory\Entity\Person;
use PDO;
use PDOException;
use RuntimeException;

class DatabasePersonRepository implements PersonRepositoryInterface {
    private PDO $conn;

    public function __construct() {
        $host = 'localhost';
        $db = 'people';
        $user = 'root';
        $pass = '';
        
        try {
            $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->conn = new PDO($dsn, $user, $pass, $options);
            
            $this->createTableIfNotExists();
        } catch (PDOException $e) {
            throw new RuntimeException("Database connection failed: " . $e->getMessage());
        }
    }

    private function createTableIfNotExists(): void {
        $sql = "CREATE TABLE IF NOT EXISTS people (
            name VARCHAR(100) PRIMARY KEY,
            age INT NOT NULL,
            email VARCHAR(100) NOT NULL
        )";
        $this->conn->exec($sql);
    }

    public function savePerson(Person $person): void {
        $sql = "INSERT INTO people (name, age, email) VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE age = ?, email = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $person->getName(),
            $person->getAge(),
            $person->getEmail(),
            $person->getAge(),
            $person->getEmail()
        ]);
    }

    public function readPeople(): array {
        $sql = "SELECT * FROM people";
        $stmt = $this->conn->query($sql);
        
        $people = [];
        while ($row = $stmt->fetch()) {
            $people[] = new Person($row['name'], (int)$row['age'], $row['email']);
        }
        
        return $people;
    }

    public function readPerson(string $name): ?Person {
        $sql = "SELECT * FROM people WHERE name = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$name]);
        
        $row = $stmt->fetch();
        if ($row) {
            return new Person($row['name'], (int)$row['age'], $row['email']);
        }
        
        return null;
    }
}
