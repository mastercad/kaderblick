#!/bin/bash

# Flexibles Zertifikat-Skript für verschiedene Umgebungen
set -e

# Hostname/Domain/IP als Argument oder Umgebungsvariable
HOST="${1:-${DOMAIN:-localhost}}"

# In CI einen Dummy-Host setzen
if [ "$CI" = "true" ]; then
  HOST="dev-local"
fi

CERT_DIR="$(dirname "$0")/../certs"
CA_KEY="$CERT_DIR/dev-local-rootCA.key"
CA_CERT="$CERT_DIR/dev-local-rootCA.pem"
#KEY="$CERT_DIR/$HOST.key"
#CERT="$CERT_DIR/$HOST.crt"
#CSR="$CERT_DIR/$HOST.csr"
KEY="$CERT_DIR/dev-local.key"
CERT="$CERT_DIR/dev-local.crt"
CSR="$CERT_DIR/dev-local.csr"

mkdir -p "$CERT_DIR"

# 1. Root-CA erzeugen (falls nicht vorhanden)
if [ ! -f "$CA_KEY" ] || [ ! -f "$CA_CERT" ]; then
  echo "Erzeuge lokale Root-CA..."
  cat > "$CERT_DIR/ca-ext.cnf" <<EOF
[ v3_ca ]
basicConstraints = critical,CA:TRUE
keyUsage = critical,keyCertSign,cRLSign
subjectKeyIdentifier = hash
authorityKeyIdentifier = keyid:always,issuer:always
EOF
  openssl req -x509 -nodes -days 3650 -sha256 \
    -subj "/C=DE/ST=Local/L=Local/O=Dev/CN=dev-local-rootCA" \
    -keyout "$CA_KEY" -out "$CA_CERT" \
    -config "$CERT_DIR/ca-ext.cnf" \
    -extensions v3_ca
  rm "$CERT_DIR/ca-ext.cnf"
  echo "Root-CA erzeugt: $CA_CERT"
fi

# 2. Server-Key und CSR erzeugen
openssl req -new -nodes -newkey rsa:2048 \
  -keyout "$KEY" -out "$CSR" \
  -subj "/C=DE/ST=Local/L=Local/O=Dev/CN=$HOST"

# 3. SAN-Konfig für Host/IP und localhost
cat > "$CERT_DIR/san.cnf" <<EOF
subjectAltName=DNS:localhost,DNS:$HOST,IP:127.0.0.1
EOF

# 4. Server-Zertifikat signieren
openssl x509 -req -in "$CSR" -CA "$CA_CERT" -CAkey "$CA_KEY" -CAcreateserial \
  -out "$CERT" -days 825 -sha256 -extfile "$CERT_DIR/san.cnf"

rm "$CSR" "$CERT_DIR/san.cnf"
echo "Signiertes Zertifikat erzeugt: $CERT (CA: $CA_CERT)"
echo "Importiere $CA_CERT als vertrauenswürdige Stammzertifizierungsstelle in deinen Browser!"
