# Node.js als Basis-Image (z.B. Node 20)
FROM node:20-alpine AS builder

# Arbeitsverzeichnis im Container
WORKDIR /app

# package.json und package-lock.json kopieren
COPY ./frontend/package*.json ./

# Abhängigkeiten installieren
RUN npm install

# Quellcode kopieren
COPY ./frontend .

RUN rm -rf ./public/uploads && mkdir -p ./public/uploads

# Build erstellen (für Produktion)
RUN npm run build

# nginx als Webserver, um die gebauten Dateien auszuliefern
FROM nginx:alpine

# Nginx-Konfiguration (optional, kann angepasst werden)
COPY .docker/dev/frontend/nginx.conf /etc/nginx/conf.d/default.conf

COPY --from=builder /app/dist /usr/share/nginx/html

CMD ["nginx", "-g", "daemon off;"]
