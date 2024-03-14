FROM node:lts as build-stage
WORKDIR /app

COPY package*.json ./
RUN npm install

COPY . .
RUN npm run build

FROM node:lts as production-stage
WORKDIR /app

COPY ecosystem.config.cjs .
COPY --from=build-stage /app/.output /app

EXPOSE 3000

RUN npm install pm2 -g

CMD ["pm2-runtime", "ecosystem.config.cjs"]
