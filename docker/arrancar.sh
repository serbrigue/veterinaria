#!/bin/bash
# ============================================================
# Script de arranque inicial del proyecto Veterinaria
# Ejecutar desde la raíz del proyecto: bash docker/arrancar.sh
# ============================================================

set -e

echo ""
echo "🐾  Veterinaria — Setup Docker"
echo "================================"


# 2. Construir imágenes
echo ""
echo "🔨 [1/4] Construyendo imágenes Docker..."
docker compose build

# 3. Levantar todos los servicios
echo ""
echo "🚀 [2/4] Levantando servicios..."
docker compose up -d

# 4. Esperar a que los healthchecks pasen
echo ""
echo "⏳ [3/4] Esperando que los servicios estén listos..."
echo "    (MySQL tarda ~30s la primera vez)"
sleep 35

# 5. Verificar estado
echo ""
echo "📊 [4/4] Estado de los contenedores:"
docker compose ps

echo ""
echo "================================"
echo "✅ ¡Listo! La app está en: http://localhost:8080"
echo "   MySQL  → localhost:3307"
echo "   Redis  → localhost:6379"
echo ""
echo "📋 Comandos útiles:"
echo "   make logs        → ver logs en vivo"
echo "   make shell       → entrar al contenedor PHP"
echo "   make logs-app    → logs de Laravel"
echo "================================"
