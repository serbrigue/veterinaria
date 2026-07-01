#!/bin/sh
# ============================================================
# Script para ejecutar el Queue Worker de Laravel
# ============================================================
set -e

echo "⏳ Esperando a que PHP-FPM (app) esté listo..."
sleep 10

echo "⚙️  Iniciando Queue Worker..."
exec php artisan queue:work \
    --verbose \
    --tries=3 \
    --timeout=90 \
    --sleep=3 \
    --max-jobs=500 \
    --max-time=3600
