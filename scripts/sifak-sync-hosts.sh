#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

if ! docker compose ps --status running php >/dev/null 2>&1; then
  echo "Container php belum running. Jalankan dulu: docker compose up -d"
  exit 1
fi

HOST_LINES="$(docker compose exec -T php php artisan sifak:hosts --plain)"

if [ -z "$HOST_LINES" ]; then
  echo "Tidak ada host SIFAK yang perlu disinkronkan."
  exit 0
fi

MISSING_LINES=""
while IFS= read -r line; do
  host="$(printf '%s' "$line" | awk '{print $2}')"

  if [ -n "$host" ] && ! getent hosts "$host" >/dev/null; then
    MISSING_LINES="${MISSING_LINES}${line}\n"
  fi
done <<< "$HOST_LINES"

if [ -z "$MISSING_LINES" ]; then
  echo "Semua host SIFAK sudah ada di /etc/hosts."
  echo "$HOST_LINES"
  exit 0
fi

echo "Entry berikut akan ditambahkan ke /etc/hosts:"
printf '%b' "$MISSING_LINES"
echo

printf '\n# SIFAK local tenants\n%b' "$MISSING_LINES" | sudo tee -a /etc/hosts >/dev/null

echo "Selesai. Host aktif:"
printf '%s\n' "$HOST_LINES"
