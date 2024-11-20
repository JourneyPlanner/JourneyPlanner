#!/bin/bash

# Start the database container
echo "Starting the database container with necessary environment variables..."
infisical run --env=dev -- docker compose up -d

# Wait for the database to initialize
echo "Waiting for the database to initialize (this may take a few seconds)..."
sleep 5

# Dump the remote database to a file inside the container
echo "Creating a dump of the remote database..."
docker exec journeyplanner-db-1 bash -c 'mariadb-dump -h$CLONING_DB_HOST -P$CLONING_DB_PORT -u$CLONING_DB_USER -p$CLONING_DB_PASSWORD $CLONING_DB_DATABASE > /var/lib/mysql/journeyplanner.sql'

# Drop the existing local database
echo "Dropping the existing local database (if it exists)..."
docker exec journeyplanner-db-1 bash -c 'mariadb -u$MYSQL_USER -p$MYSQL_PASSWORD -e "DROP DATABASE IF EXISTS $MYSQL_DATABASE;"'

# Create a new local database
echo "Creating a new local database..."
docker exec journeyplanner-db-1 bash -c 'mariadb -u$MYSQL_USER -p$MYSQL_PASSWORD -e "CREATE DATABASE $MYSQL_DATABASE;"'

# Restore the database from the dump file
echo "Restoring the local database from the remote dump..."
docker exec journeyplanner-db-1 bash -c 'mariadb -u$MYSQL_USER -p$MYSQL_PASSWORD $MYSQL_DATABASE < /var/lib/mysql/journeyplanner.sql'

# Remove the dump file to clean up
echo "Removing the temporary dump file from the container..."
docker exec journeyplanner-db-1 bash -c 'rm /var/lib/mysql/journeyplanner.sql'

# Run database migrations to ensure schema is up to date
echo "Running database migrations to apply any pending changes..."
cd ../../backend
infisical run --env=dev -- php artisan migrate

# Stop the database container
echo "Stopping the database container to free up resources..."
cd ../docker/dev
infisical run --env=dev -- docker compose stop

# Final message
echo "Database cloning and migration process completed successfully."
