#!/bin/bash
infisical run --env=prod -- php artisan config:cache
php artisan migrate --force
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
