.PHONY: up down shell autoload run-abstract-factory run-adapter rebuild test-sqlite sqlite-cli

# Start Docker containers
up:
	docker-compose up -d

# Stop Docker containers
down:
	docker-compose down

# Rebuild Docker containers with no cache
rebuild:
	docker-compose down
	docker-compose build --no-cache
	docker-compose up -d

# Test SQLite connection
test-sqlite:
	docker-compose exec php php test-sqlite-skript.php

# Enter SQLite CLI
sqlite-cli:
	docker-compose exec sqlite sqlite3 /root/db/database.sqlite

# Access PHP container shell
shell:
	docker-compose exec php bash

# Regenerate autoloader
autoload:
	docker-compose exec php composer dump-autoload -o

# Run Abstract Factory demo
run-abstract-factory:
	docker-compose exec php php factory.php

# Run Adapter pattern demo
run-adapter:
	docker-compose exec php php adapter-demo.php

# Run SQLite demo
run-sqlite:
	docker-compose exec php php sqlite-demo.php

# Build or rebuild Docker containers
build:
	docker-compose build

# Show container logs
logs:
	docker-compose logs -f

# Init project (start containers and prepare environment)
init: up
	docker-compose exec php composer install
	docker-compose exec php mkdir -p data
	docker-compose exec php composer dump-autoload -o

# Enter PHP container shell
shell:
	docker-compose exec php bash

# Regenerate autoloader
autoload:
	docker-compose exec php composer dump-autoload -o

# Run Abstract Factory demo
run-abstract-factory:
	docker-compose exec php php demo.php

# Run Adapter pattern demo
run-adapter:
	docker-compose exec php php adapter-demo.php

# Run SQLite demo
run-sqlite:
	docker-compose exec php php sqlite-demo.php

# Build or rebuild Docker containers
build:
	docker-compose build

# Show container logs
logs:
	docker-compose logs -f

# Init project (start containers and prepare environment)
init: up
	docker-compose exec php composer install
	docker-compose exec php mkdir -p database data
	docker-compose exec php touch database/database.sqlite
	docker-compose exec php composer dump-autoload -o