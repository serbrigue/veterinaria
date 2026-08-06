#!/bin/sh
# ============================================================
# Script para ejecutar el Queue Worker de Laravel
# ============================================================

#Se detiene el script si algún comando falla
set -e

#Esperar a que PHP-FPM (app) esté listo
echo "⏳ Esperando a que PHP-FPM (app) esté listo..."
sleep 10

#Ejecutamos el worker cada 3 segundos y con un máximo de 500 trabajos
echo "⚙️  Iniciando Queue Worker..."
exec php artisan queue:work \
    --verbose \
    --tries=3 \
    --timeout=90 \
    --sleep=3 \
    --max-jobs=500 \
    --max-time=3600
