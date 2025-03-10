# Stack Adapter Pattern with SQLite

This project demonstrates the Adapter design pattern with IntegerStack and ASCIIStack implementations, including a SQLite-based persistence option.

## Project Structure

- `src/Stack/IntegerStackInterface.php`: Interface for integer stacks
- `src/Stack/ASCIIStackInterface.php`: Interface for ASCII character stacks
- `src/Stack/IntegerStack.php`: In-memory implementation of IntegerStackInterface
- `src/Stack/ASCIIStack.php`: In-memory implementation of ASCIIStackInterface
- `src/Stack/SqliteIntegerStack.php`: SQLite-based implementation of IntegerStackInterface
- `src/Stack/IntegerToASCIIAdapter.php`: Adapter to use IntegerStack as ASCIIStack
- `src/Stack/ASCIIToIntegerAdapter.php`: Adapter to use ASCIIStack as IntegerStack

## Setup and Run with Docker

1. Make sure Docker and Docker Compose are installed
2. Clone the repository
3. Run the following commands:

```bash
# Start the Docker containers
docker-compose up -d

# SSH into the PHP container
docker-compose exec php bash

# Run the SQLite demo
php sqlite-demo.php

# Run the adapter demo
php adapter-demo.php
```

## Setup Without Docker

If you prefer to run without Docker:

1. Make sure PHP 8.1+ and SQLite are installed
2. Run `composer install`
3. Create a database directory: `mkdir -p database`
4. Create an empty SQLite database: `touch database/database.sqlite`
5. Run `php sqlite-demo.php` or `php adapter-demo.php`

