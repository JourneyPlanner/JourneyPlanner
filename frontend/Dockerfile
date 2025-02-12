FROM node:lts AS build-stage
WORKDIR /app
ARG INFISICAL_API_URL
ARG INFISICAL_TOKEN
ENV INFISICAL_API_URL=$INFISICAL_API_URL
ENV INFISICAL_TOKEN=$INFISICAL_TOKEN

COPY package*.json ./
RUN npm ci

RUN apt-get update && apt-get install -y bash curl && curl -1sLf \
'https://dl.cloudsmith.io/public/infisical/infisical-cli/setup.deb.sh' | bash \
&& apt-get update && apt-get install -y infisical

COPY --link --exclude=node_modules . . 
RUN infisical run --env=prod -- npm run build

FROM node:lts AS production-stage
WORKDIR /app

COPY ecosystem.config.cjs .
COPY --from=build-stage /app/.output /app

EXPOSE 3000

RUN npm install pm2 -g

CMD pm2-runtime ecosystem.config.cjs
