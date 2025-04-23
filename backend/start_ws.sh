#!/bin/bash
infisical run --env=prod -- php artisan config:cache
php artisan reverb:start
