#!/bin/bash
# =============================================================
# Startup Script — GCP Free Tier (e2-micro 1GB RAM)
# =============================================================
# Se ejecuta automáticamente con privilegios root al iniciar la VM.
# Las variables $${var_name} son interpoladas por Terraform mediante templatefile.
# Las expresiones $${...} o $var son interpretadas por el bash de la VM.
# =============================================================

set -e
exec > /var/log/startup-script.log 2>&1

echo "================================================="
echo "  Veterinaria — Configurando Servidor GCP Free"
echo "================================================="

# -----------------------------------------------------------
# 1. Configurar Memoria Swap de 2GB
# -----------------------------------------------------------
# ESENCIAL: e2-micro tiene solo 1GB de RAM. Sin swap, MySQL o PHP
# provocan Out-Of-Memory (OOM Killer) y detienen los contenedores.
echo "[1/6] Configurando Swap de 2GB..."
if [ ! -f /swapfile ]; then
  fallocate -l 2G /swapfile
  chmod 600 /swapfile
  mkswap /swapfile
  swapon /swapfile
  echo '/swapfile none swap sw 0 0' >> /etc/fstab
  echo "Swap configurada correctamente."
else
  echo "Swap ya existente."
fi

# -----------------------------------------------------------
# 2. Instalar Docker y Docker Compose (con reintentos)
# -----------------------------------------------------------
echo "[2/6] Instalando Docker..."
export DEBIAN_FRONTEND=noninteractive

retry() {
  local n=1
  local max=5
  local delay=10
  while true; do
    "$@" && break || {
      if [[ $n -lt $max ]]; then
        echo "Comando falló. Reintentando ($n/$max) en $delay segundos..."
        sleep $delay
        ((n++))
      else
        echo "El comando falló después de $max intentos."
        return 1
      fi
    }
  done
}

retry apt-get update -y
retry apt-get install -y docker.io docker-compose-v2 curl

systemctl enable docker
systemctl start docker

# Agregar todos los usuarios reales del sistema (ej. sbg_daemon) al grupo docker para evitar usar sudo
for u in /home/*; do
  if [ -d "$u" ]; then
    usermod -aG docker $(basename "$u") 2>/dev/null || true
  fi
done

# -----------------------------------------------------------
# 3. Preparar Directorio de Trabajo y Obtener IP Pública
# -----------------------------------------------------------
echo "[3/6] Preparando entorno..."
mkdir -p /opt/veterinaria/docker/nginx
mkdir -p /opt/veterinaria/docker/mysql
cd /opt/veterinaria

# Obtener IP externa efímera desde el servidor de metadatos de GCP
PUBLIC_IP=$(curl -s -H "Metadata-Flavor: Google" http://metadata.google.internal/computeMetadata/v1/instance/network-interfaces/0/access-configs/0/external-ip || curl -s ifconfig.me)
echo "IP Pública detectada: $PUBLIC_IP"

# -----------------------------------------------------------
# 4. Generar archivo .env
# -----------------------------------------------------------
echo "[4/6] Generando archivo .env..."
cat > .env <<ENVEOF
APP_NAME="Veterinaria"
APP_ENV=production
APP_KEY="${app_key}"
APP_DEBUG=false
APP_URL="http://veterinaria.$PUBLIC_IP.nip.io"
SANCTUM_STATEFUL_DOMAINS="$PUBLIC_IP,veterinaria.$PUBLIC_IP.nip.io,localhost,127.0.0.1"
APP_TIMEZONE=America/Santiago

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE="${db_name}"
DB_USERNAME="${db_username}"
DB_PASSWORD="${db_password}"

REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD="${redis_password}"
REDIS_DB=0
REDIS_CACHE_DB=1

CACHE_DRIVER=redis
CACHE_PREFIX=vet_cache
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
QUEUE_CONNECTION=redis

# Almacenamiento en Google Cloud Storage (GCP Free Tier)
# Nota: Laravel usa las variables estándar AWS_* por compatibilidad con el controlador Flysystem,
# pero al apuntar a https://storage.googleapis.com, los archivos se guardan 100% en GCP (No en AWS).
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID="${aws_access_key}"
AWS_SECRET_ACCESS_KEY="${aws_secret_key}"
AWS_DEFAULT_REGION="${region}"
AWS_BUCKET="${bucket_name}"
AWS_ENDPOINT="https://storage.googleapis.com"
AWS_USE_PATH_STYLE_ENDPOINT=true

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME="${mail_username}"
MAIL_PASSWORD="${mail_password}"
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="vaalavet@gmail.com"
MAIL_FROM_NAME="Veterinaria"
ENVEOF

# -----------------------------------------------------------
# 5. Extraer Nginx y Generar Docker Compose para GCP
# -----------------------------------------------------------
echo "[5/6] Descargando imagen Docker (${ghcr_image})..."
retry docker pull ${ghcr_image}

echo "Extrayendo configuración de Nginx..."
docker create --name temp_extract ${ghcr_image} true
docker cp temp_extract:/var/www/docker/nginx/default.conf ./docker/nginx/default.conf
docker rm temp_extract

echo "Creando docker-compose.gcp.yml..."
cat > docker-compose.gcp.yml <<COMPOSEEOF
services:
  app:
    image: ${ghcr_image}
    container_name: vet_app_gcp
    restart: unless-stopped
    env_file:
      - .env
    environment:
      APP_ENV: production
      DB_HOST: mysql
      REDIS_HOST: redis
    depends_on:
      mysql:
        condition: service_healthy
      redis:
        condition: service_healthy
    volumes:
      - ./.env:/var/www/.env
      - public_assets:/var/www/public
    networks:
      - vet_network

  nginx:
    image: nginx:1.25-alpine
    container_name: vet_nginx_gcp
    restart: unless-stopped
    ports:
      - "80:80"
    volumes:
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
      - public_assets:/var/www/public
    depends_on:
      - app
    networks:
      - vet_network

  mysql:
    image: mysql:8.0
    container_name: vet_mysql_gcp
    restart: unless-stopped
    command: --default-authentication-plugin=mysql_native_password --character-set-server=utf8mb4 --collation-server=utf8mb4_unicode_ci
    environment:
      MYSQL_ROOT_PASSWORD: "${db_root_password}"
      MYSQL_DATABASE: "${db_name}"
      MYSQL_USER: "${db_username}"
      MYSQL_PASSWORD: "${db_password}"
    volumes:
      - vet_mysql_data:/var/lib/mysql
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-u", "root", "-p${db_root_password}"]
      interval: 10s
      timeout: 5s
      retries: 10
      start_period: 30s
    networks:
      - vet_network

  redis:
    image: redis:7.2-alpine
    container_name: vet_redis_gcp
    restart: unless-stopped
    volumes:
      - vet_redis_data:/data
    command: redis-server --appendonly yes --requirepass ${redis_password}
    healthcheck:
      test: ["CMD", "redis-cli", "-a", "${redis_password}", "ping"]
      interval: 10s
      timeout: 5s
      retries: 5
    networks:
      - vet_network

  queue:
    image: ${ghcr_image}
    container_name: vet_queue_gcp
    restart: unless-stopped
    env_file:
      - .env
    environment:
      APP_ENV: production
      DB_HOST: mysql
      REDIS_HOST: redis
    entrypoint: []
    command: >
      sh -c "sleep 20 && php artisan queue:work --verbose --tries=3 --timeout=90 --sleep=3 --max-jobs=500 --max-time=3600"
    depends_on:
      - app
      - mysql
      - redis
    volumes:
      - ./.env:/var/www/.env
    networks:
      - vet_network

volumes:
  vet_mysql_data:
  vet_redis_data:
  public_assets:

networks:
  vet_network:
    driver: bridge
COMPOSEEOF

# -----------------------------------------------------------
# 6. Levantar Servicios y Optimizar
# -----------------------------------------------------------
echo "[6/6] Levantando contenedores en GCP..."
docker compose -f docker-compose.gcp.yml up -d

echo "Esperando a que Laravel termine su inicialización y migraciones automáticas en entrypoint (30s)..."
sleep 30

echo "Optimizando cachés de la aplicación..."
docker exec vet_app_gcp php artisan storage:link || true
docker exec vet_app_gcp php artisan config:cache || true
docker exec vet_app_gcp php artisan route:cache || true
docker exec vet_app_gcp php artisan view:cache || true

echo "================================================="
echo "  ¡Despliegue en GCP completado exitosamente!"
echo "================================================="
