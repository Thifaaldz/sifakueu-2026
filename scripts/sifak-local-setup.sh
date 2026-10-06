#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

BASE_DOMAIN="${SIFAK_BASE_DOMAIN:-sifakueu.test}"
CERT_FILE="nginx/ssl/${BASE_DOMAIN}.crt"
KEY_FILE="nginx/ssl/${BASE_DOMAIN}.key"

echo "==> Setup local SIFAK multi-tenant untuk ${BASE_DOMAIN}"

if command -v mkcert >/dev/null 2>&1; then
  echo "==> mkcert ditemukan. Install local CA jika belum trusted."
  mkcert -install

  echo "==> Generate wildcard certificate: ${BASE_DOMAIN}, *.${BASE_DOMAIN}"
  mkcert \
    -cert-file "$CERT_FILE" \
    -key-file "$KEY_FILE" \
    "$BASE_DOMAIN" "*.${BASE_DOMAIN}" localhost 127.0.0.1
else
  echo "!! mkcert belum terinstall. Fallback ke self-signed wildcard certificate."
  echo "!! Browser akan tetap memberi warning sampai certificate di-trust manual."
  openssl req -x509 -nodes -days 825 -newkey rsa:2048 \
    -keyout "$KEY_FILE" \
    -out "$CERT_FILE" \
    -subj "/CN=${BASE_DOMAIN}" \
    -addext "subjectAltName=DNS:${BASE_DOMAIN},DNS:*.${BASE_DOMAIN},DNS:localhost,IP:127.0.0.1"
fi

chmod 644 "$CERT_FILE"
chmod 600 "$KEY_FILE"

if docker compose ps --status running php >/dev/null 2>&1; then
  echo "==> Sync /etc/hosts dari daftar tenant central."
  ./scripts/sifak-sync-hosts.sh
else
  echo "==> Container belum running. Tambahkan host dasar dulu."
  if ! getent hosts "$BASE_DOMAIN" >/dev/null; then
    printf '127.0.0.1 %s\n' "$BASE_DOMAIN" | sudo tee -a /etc/hosts >/dev/null
  fi

  if ! getent hosts "admin.$BASE_DOMAIN" >/dev/null; then
    printf '127.0.0.1 %s\n' "admin.$BASE_DOMAIN" | sudo tee -a /etc/hosts >/dev/null
  fi
fi

if docker compose ps --status running nginx >/dev/null 2>&1; then
  echo "==> Restart Nginx agar memakai certificate baru."
  docker compose restart nginx
else
  echo "==> Nginx belum running. Jalankan: docker compose up -d --build"
fi

echo "==> Selesai."
echo "Central : https://admin.${BASE_DOMAIN}/admin"
echo "Tenant  : https://fasilkom.${BASE_DOMAIN}/admin"
