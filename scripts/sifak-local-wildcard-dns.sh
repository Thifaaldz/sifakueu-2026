#!/usr/bin/env bash
set -euo pipefail

BASE_DOMAIN="${SIFAK_BASE_DOMAIN:-sifakueu.test}"

echo "==> Setup wildcard DNS lokal untuk *.${BASE_DOMAIN} -> 127.0.0.1"

if ! command -v nmcli >/dev/null 2>&1; then
  echo "NetworkManager/nmcli tidak ditemukan."
  echo "Fallback: jalankan ./scripts/sifak-local-setup.sh setiap selesai tambah tenant."
  exit 1
fi

if ! systemctl is-active --quiet NetworkManager; then
  echo "NetworkManager tidak aktif."
  echo "Fallback: jalankan ./scripts/sifak-local-setup.sh setiap selesai tambah tenant."
  exit 1
fi

sudo mkdir -p /etc/NetworkManager/conf.d /etc/NetworkManager/dnsmasq.d

sudo tee /etc/NetworkManager/conf.d/00-sifak-use-dnsmasq.conf >/dev/null <<'EOF'
[main]
dns=dnsmasq
EOF

sudo tee "/etc/NetworkManager/dnsmasq.d/sifak-${BASE_DOMAIN}.conf" >/dev/null <<EOF
address=/.${BASE_DOMAIN}/127.0.0.1
EOF

echo "==> Restart NetworkManager untuk aktivasi wildcard DNS."
sudo systemctl restart NetworkManager

echo "==> Selesai."
echo "Tes:"
echo "  getent hosts admin.${BASE_DOMAIN}"
echo "  getent hosts tenant-baru.${BASE_DOMAIN}"
