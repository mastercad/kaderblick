// Kopiert das gemeinsame Zertifikat aus .docker/certs ins Frontend-Verzeichnis
const fs = require('fs');
const path = require('path');

const certSrc = path.resolve(__dirname, '../../.docker/certs/dev-local.crt');
const keySrc = path.resolve(__dirname, '../../.docker/certs/dev-local.key');
const certDst = path.resolve(__dirname, '../cert.pem');
const keyDst = path.resolve(__dirname, '../cert-key.pem');

if (!fs.existsSync(certSrc) || !fs.existsSync(keySrc)) {
  console.error(`Zertifikat "${certSrc}" oder "${keySrc}" nicht gefunden. Bitte erst .docker/scripts/gen-shared-cert.sh ausführen!`);
  process.exit(1);
}

fs.copyFileSync(certSrc, certDst);
fs.copyFileSync(keySrc, keyDst);
