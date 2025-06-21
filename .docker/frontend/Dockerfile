# ./frontend/Dockerfile

# Node.js als Basis-Image (z.B. Node 18)
FROM node:18-alpine

# Arbeitsverzeichnis im Container
WORKDIR /app

# package.json und package-lock.json kopieren
COPY package*.json ./

# Abhängigkeiten installieren
RUN npm install

# Quellcode kopieren
COPY . .

# Build erstellen (für Produktion)
RUN npm run build

# nginx als Webserver, um die gebauten Dateien auszuliefern
FROM nginx:alpine

# Nginx-Konfiguration (optional, kann angepasst werden)
#COPY ./nginx.conf /etc/nginx/nginx.conf

# Kopiere das Vue-Build-Verzeichnis ins Nginx-Standard-Verzeichnis
#COPY --from=0 /app/dist /usr/share/nginx/html

EXPOSE 8080

CMD ["nginx", "-g", "daemon off;"]
