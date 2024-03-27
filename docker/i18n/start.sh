#!/bin/bash
printenv | sed 's/^\(.*\)$/export \1/g' > /app/project_env.sh
echo "tolgee pull -o /app/i18n --project-id $TOLGEE_PROJECT_ID -au $TOLGEE_API_URL" > /app/pull_translations.sh
chmod +x /app/pull_translations.sh
service cron start
nginx -g 'daemon off;'
