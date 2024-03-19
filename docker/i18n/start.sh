#!/bin/bash
printenv | sed 's/^\(.*\)$/export \1/g' > /app/project_env.sh
service cron start
nginx -g 'daemon off;'
