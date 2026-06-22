#!/bin/sh
# ============================================================
# Entrypoint: inicializa Laravel al arrancar el contenedor app
# ============================================================
set -e

echo ""
echo "============================================"
echo "  🐾  Veterinaria — Iniciando contenedor"
echo "============================================"

# -----------------------------------------------------------
# 1. Esperar a que MySQL esté disponible
# -----------------------------------------------------------
echo "⏳ Esperando a MySQL en ${DB_HOST}:${DB_PORT}..."
until php -r "
    try {
        new PDO(
            'mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}',
            '${DB_USERNAME}',
            '${DB_PASSWORD}'
        );
        exit(0);
    } catch (Exception \$e) {
        exit(1);
    }
" 2>/dev/null; do
    echo "   MySQL no disponible todavía, reintentando en 3s..."
    sleep 3
done
echo "✅ MySQL disponible."

# -----------------------------------------------------------
# 2. Instalar dependencias Composer si no existen
# -----------------------------------------------------------
if [ ! -d "vendor" ]; then
    echo "📦 Instalando dependencias Composer..."
    composer install \
        --no-interaction \
        --no-progress \
        --prefer-dist \
        --optimize-autoloader
else
    echo "📦 Vendor ya existe, omitiendo composer install."
fi

# -----------------------------------------------------------
# 3. Limpiar cachés ANTES de cualquier comando artisan
# -----------------------------------------------------------
echo "🧹 Limpiando cachés..."
php artisan config:clear  || true
php artisan route:clear   || true
php artisan view:clear    || true
# Eliminar también el archivo de caché compilado por si existe
rm -f bootstrap/cache/config.php
rm -f bootstrap/cache/routes*.php

# -----------------------------------------------------------
# 4. Generar APP_KEY si está vacía
# -----------------------------------------------------------
APP_KEY_VALUE=$(grep "^APP_KEY=" .env | cut -d '=' -f2 | tr -d '[:space:]')
if [ -z "$APP_KEY_VALUE" ]; then
    echo "🔑 APP_KEY vacía — generando..."
    php artisan key:generate --force --no-interaction
    # Leer la clave recién generada del archivo .env
    APP_KEY_VALUE=$(grep "^APP_KEY=" .env | cut -d '=' -f2 | tr -d '[:space:]')
    echo "✅ APP_KEY generada correctamente."
else
    echo "🔑 APP_KEY ya configurada."
fi

# CRÍTICO: exportar al entorno del proceso actual
# Docker inyecta APP_KEY="" vacío al iniciar. phpdotenv (modo inmutable)
# no sobreescribe variables ya presentes en el entorno aunque estén vacías.
# Al exportarla aquí, php-fpm la hereda con el valor correcto.
export APP_KEY="$APP_KEY_VALUE"
echo "🔐 APP_KEY exportada al entorno del proceso."

# -----------------------------------------------------------
# 5. Ejecutar migraciones
# -----------------------------------------------------------
echo "🗄️  Ejecutando migraciones..."
php artisan migrate --force --no-interaction

# -----------------------------------------------------------
# 6. Ejecutar Seeders (solo si la tabla users está vacía)
# -----------------------------------------------------------
USER_COUNT=$(php -r "
    \$pdo = new PDO(
        'mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}',
        '${DB_USERNAME}',
        '${DB_PASSWORD}'
    );
    echo \$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
" 2>/dev/null || echo "0")

if [ "$USER_COUNT" = "0" ]; then
    echo "🌱 BD vacía — ejecutando seeders..."
    php artisan db:seed --force --no-interaction
    echo "✅ Seeders ejecutados."
else
    echo "🌱 BD ya tiene datos (${USER_COUNT} usuarios), omitiendo seeders."
fi

# -----------------------------------------------------------
# 7. Ajustar permisos de storage
# -----------------------------------------------------------
echo "🔒 Ajustando permisos de storage..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# -----------------------------------------------------------
# 8. Iniciar PHP-FPM
# -----------------------------------------------------------
echo ""
echo "✅ Laravel listo. Iniciando PHP-FPM..."
echo "============================================"
echo ""
exec php-fpm
