FROM nginx:stable
WORKDIR /app

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && apt-get install -y nodejs

RUN npm install -g @tolgee/cli

RUN apt-get update && apt-get install -y cron
RUN echo "tolgee pull -o /app/i18n --project-id $TOLGEE_PROJECT_ID -au $TOLGEE_API_URL" > /app/pull_translations.sh
RUN chmod +x /app/pull_translations.sh
RUN crontab -l | { cat; echo "*/5 * * * * . /app/project_env.sh; bash /app/pull_translations.sh"; } | crontab -
RUN touch /var/log/cron.log
RUN chmod 0644 /var/log/cron.log

COPY ./nginx.conf /etc/nginx/nginx.conf

COPY ./start.sh /app/start.sh
RUN chmod +x /app/start.sh
CMD ["/app/start.sh"]