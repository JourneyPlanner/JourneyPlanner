FROM node:lts as build-stage
WORKDIR /app
ARG INFISICAL_API_URL
ARG INFISICAL_TOKEN

COPY package*.json ./
RUN npm install

RUN apt-get update && apt-get install -y bash curl && curl -1sLf \
'https://dl.cloudsmith.io/public/infisical/infisical-cli/setup.deb.sh' | bash \
&& apt-get update && apt-get install -y infisical

COPY . .
RUN infisical run --env=prod -- npm run build

FROM node:lts as production-stage
WORKDIR /app

COPY ecosystem.config.cjs .
COPY --from=build-stage /app/.output /app

EXPOSE 3000

RUN npm install pm2 -g

CMD pm2-runtime ecosystem.config.cjs
