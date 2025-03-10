.PHONY: up down shell autoload run-abstract-factory run-adapter rebuild

# Start Docker containers
up:
	docker-compose up -d

# Stop Docker containers
down:
	docker-compose down

# Rebuild Docker containers with no cache
rebuild:
	docker-compose down
	docker-compose build #--no-cache
	docker-compose up -d


# Enter SQLite CLI
sqlite-cli:
	docker-compose exec sqlite sqlite3 /root/db/database.sqlite

# Access PHP container shell
shell:
	docker-compose exec php bash

# Regenerate autoloader
autoload:
	docker-compose exec php composer dump-autoload -o

# Run tests
test:
	docker-compose exec php vendor/bin/phpunit --config phpunit-config.xml

# Run tests with coverage report
test-coverage:
	docker-compose exec -e XDEBUG_MODE=coverage php vendor/bin/phpunit --config phpunit-config.xml --coverage-html ./coverage

# Run static analysis tools
analyze:
	docker-compose exec php vendor/bin/phpstan analyze -l 5 src
	docker-compose exec php vendor/bin/phpmd src text cleancode,codesize,controversial,design,naming,unusedcode

# Fix coding style issues
cs-fix:
	docker-compose exec php vendor/bin/phpcbf --standard=PSR12 src

# Run pattern demos
run-abstract-factory:
	docker-compose exec php php factory-demo.php

# Run Adapter pattern demo
run-adapter:
	docker-compose exec php php adapter-demo.php

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

# Run framework app
run-app:
	docker-compose up -d
	@echo "App is running at http://localhost:8000"

setup-hooks:
	mkdir -p .git/hooks
	cp pre-commit-hook .git/hooks/pre-commit
	chmod +x .git/hooks/pre-commit
	echo "Git pre-commit hook installed successfully."

# Check coding style according to PSR-12
cs-check:
	docker-compose exec php ./vendor/bin/phpcs --standard=PSR12 src