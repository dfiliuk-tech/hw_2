<?php

declare(strict_types=1);

namespace App\GOF\AbstractFactory\Repository;

use App\GOF\AbstractFactory\Entity\Person;
use PDO;
use PDOException;
use RuntimeException;

class DatabasePersonRepository implements PersonRepositoryInterface
{
    private PDO $conn;

    public function __construct()
    {
        try {
            // Use SQLite instead of MySQL
            $dbPath = getenv('DB_DATABASE') ?: __DIR__ . '/../../../../database/database.sqlite';

            // Create database directory if it doesn't exist
            $dbDir = dirname($dbPath);
            if (!file_exists($dbDir)) {
                mkdir($dbDir, 0755, true);
            }

            $dsn = "sqlite:" . $dbPath;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->conn = new PDO($dsn, null, null, $options);

            $this->createTableIfNotExists();
        } catch (PDOException $e) {
            throw new RuntimeException("Database connection failed: " . $e->getMessage());
        }
    }

    private function createTableIfNotExists(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS people (
            name TEXT PRIMARY KEY,
            age INTEGER NOT NULL,
            email TEXT NOT NULL
        )";
        $this->conn->exec($sql);
    }

    public function savePerson(Person $person): void
    {
        // Use SQLite compatible SQL (no ON DUPLICATE KEY)
        $sql = "INSERT OR REPLACE INTO people (name, age, email) VALUES (?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $person->getName(),
            $person->getAge(),
            $person->getEmail()
        ]);
    }

    public function readPeople(): array
    {
        $sql = "SELECT * FROM people";
        $stmt = $this->conn->query($sql);

        $people = [];
        while ($row = $stmt->fetch()) {
            $people[] = new Person($row['name'], (int)$row['age'], $row['email']);
        }

        return $people;
    }

    public function readPerson(string $name): ?Person
    {
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
