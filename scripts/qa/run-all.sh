#!/usr/bin/env bash
# Jalankan ulang seluruh alur M1–M7 (reset data uji, screenshot baru) lalu susun PDF.
set -u
cd "$(dirname "$0")/../.."
tinker() { docker compose exec -T php php artisan tinker --execute="require \"storage/app/$1\";" | tail -1; }
for m in m1 m2 m3 m4 m5 m6 m7; do
  rm -f docs/manual-guides/v2/screenshots/${m}-*.png
  case $m in m1) tinker qa_reset_m1.php ;; m3) tinker qa_reset_m3.php ;; m4) tinker qa_reset_m4.php ;; esac
  node scripts/qa/$m.cjs 2>&1 | grep -E "saved|registration|FATAL|Error:" 
  sleep 70   # batas login Filament: 5 kali/menit per IP
done
