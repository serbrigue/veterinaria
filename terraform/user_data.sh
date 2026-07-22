#!/bin/bash
# =============================================================
# User Data — Script de arranque para instancias EC2 del ASG
# Se ejecuta automáticamente al lanzar cada instancia.
# Los valores de las variables son interpolados por Terraform (templatefile).
# =============================================================
set -e
exec > /var/log/user-data.log 2>&1

echo "=========================================="
echo "  Veterinaria — Configurando instancia"
echo "=========================================="

# -----------------------------------------------------------
# 1. Instalar Docker y Docker Compose
# -----------------------------------------------------------
echo "Instalando Docker..."
apt-get update -y
apt-get install -y docker.io docker-compose-v2 curl

systemctl enable docker
systemctl start docker

# Agregar ubuntu al grupo docker
usermod -aG docker ubuntu

# -----------------------------------------------------------
# 2. Crear directorio de trabajo
# -----------------------------------------------------------
mkdir -p /opt/veterinaria/docker/nginx
cd /opt/veterinaria

# -----------------------------------------------------------
# 3. Generar .env con valores inyectados por Terraform
# -----------------------------------------------------------
echo "Generando archivo .env..."
cat > .env <<ENVEOF
APP_NAME="Veterinaria"
APP_ENV=production
APP_KEY=${app_key}
APP_DEBUG=false
APP_URL=http://${alb_dns}
APP_TIMEZONE=America/Santiago

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=${db_host}
DB_PORT=${db_port}
DB_DATABASE=${db_name}
DB_USERNAME=${db_username}
DB_PASSWORD=${db_password}

REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=${redis_password}
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

FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=${aws_access_key}
AWS_SECRET_ACCESS_KEY=${aws_secret_key}
AWS_SESSION_TOKEN=${aws_session_token}
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=${s3_bucket}

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=${mail_username}
MAIL_PASSWORD=${mail_password}
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="vaalavet@gmail.com"
MAIL_FROM_NAME="Veterinaria"
ENVEOF

# -----------------------------------------------------------
# 4. Extraer archivos de configuración de la imagen Docker
# -----------------------------------------------------------
echo "Descargando imagen Docker..."
docker pull ${ghcr_image}

echo "Extrayendo archivos de configuracion..."
docker create --name temp_extract ${ghcr_image} true
docker cp temp_extract:/var/www/docker-compose.prod.yml ./docker-compose.prod.yml
docker cp temp_extract:/var/www/docker/nginx/default.conf ./docker/nginx/default.conf
docker rm temp_extract

# -----------------------------------------------------------
# 5. Levantar servicios con Docker Compose
# -----------------------------------------------------------
echo "Levantando servicios..."
docker compose -f docker-compose.prod.yml up -d

echo ""
echo "=========================================="
echo "  Instancia configurada correctamente"
echo "=========================================="
