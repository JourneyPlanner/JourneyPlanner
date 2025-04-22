#!/bin/bash
infisical run --env=prod -- php artisan config:cache
php artisan migrate --force
php artisan octane:frankenphp
