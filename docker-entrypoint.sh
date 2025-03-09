#!/bin/bash
set -e

# Create database directory if it doesn't exist
mkdir -p /var/www/html/database

# Create SQLite database file if it doesn't exist
if [ ! -f /var/www/html/database/database.sqlite ]; then
    touch /var/www/html/database/database.sqlite
    echo "Created new SQLite database file"
fi

# Install Composer dependencies if they don't exist
if [ ! -d /var/www/html/vendor ]; then
    composer install --no-interaction
    echo "Installed Composer dependencies"
else
    # Always regenerate the autoloader to ensure all classes are detected
    composer dump-autoload -o
    echo "Regenerated Composer autoloader"
fi
# First arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
    set -- php "$@"
fi

exec "$@"
