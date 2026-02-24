FROM nginx:alpine

# SSL-Zertifikate kopieren
COPY cert.pem /etc/nginx/ssl/cert.pem
COPY key.pem /etc/nginx/ssl/key.pem

# Nginx-Konfiguration kopieren
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Maintenance Page kopieren
COPY maintenance.html /usr/share/nginx/html/maintenance.html

# Health Check (HTTPS)
HEALTHCHECK --interval=10s --timeout=3s --start-period=5s --retries=3 \
    CMD wget --quiet --tries=1 --spider --no-check-certificate https://localhost/health || exit 1

EXPOSE 80 443

CMD ["nginx", "-g", "daemon off;"]
