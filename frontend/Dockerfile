FROM node:lts as build-stage
WORKDIR /app
ENV NUXT_BACKEND_URL=https://api.journeyplanner.io/
ENV NUXT_FRONTEND_URL=https://dev.journeyplanner.io/

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

CMD ["NUXT_MAPBOX_API_KEY=${NUXT_MAPBOX_API_KEY}", "pm2-runtime", "ecosystem.config.cjs"]
