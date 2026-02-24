npx web-push generate-vapid-keys

npm run build

npm run set-publish-date

# generate ssl cert
openssl req -x509 -newkey rsa:4096 -keyout .docker/nginx/key.pem -out .docker/nginx/cert.pem -days 365 -nodes -subj "/C=DE/ST=State/L=City/O=Organization/CN=localhost"
