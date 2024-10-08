# Start DB
Write-Output "Starting the database container..."
infisical run --env=dev -- docker compose up -d

# Wait for DB to start
Write-Output "Waiting for the database to initialize..."
Start-Sleep -s 10

# Dump DB
Write-Output "Creating a database dump..."
docker exec journeyplanner-db-1 bash -c 'mariadb-dump -h $CLONING_DB_HOST -P $CLONING_DB_PORT -u $CLONING_DB_USER -p$CLONING_DB_PASSWORD $CLONING_DB_DATABASE > /var/lib/mysql/journeyplanner.sql'

# Recreate DB
Write-Output "Dropping the existing database..."
docker exec journeyplanner-db-1 bash -c 'mariadb -u$MYSQL_USER -p$MYSQL_PASSWORD -e \"DROP DATABASE IF EXISTS $MYSQL_DATABASE;\"'
Write-Output "Creating a new database..."
docker exec journeyplanner-db-1 bash -c 'mariadb -u$MYSQL_USER -p$MYSQL_PASSWORD -e \"CREATE DATABASE $MYSQL_DATABASE;\"'

# Restore DB to local
Write-Output "Restoring the database from the dump..."
docker exec journeyplanner-db-1 bash -c 'mariadb -u$MYSQL_USER -p$MYSQL_PASSWORD $MYSQL_DATABASE < /var/lib/mysql/journeyplanner.sql'

# Remove dump
Write-Output "Cleaning up the database dump file..."
docker exec journeyplanner-db-1 bash -c 'rm /var/lib/mysql/journeyplanner.sql'

# Run migrations
Write-Output "Running database migrations..."
Set-Location ../../backend
infisical run --env=dev -- php artisan migrate

# Stop DB
Write-Output "Stopping the database container..."
Set-Location ../docker/dev
infisical run --env=dev -- docker compose stop

Write-Output "Database cloning and migration process completed successfully."
