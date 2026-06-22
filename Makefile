# ============================================================
# Makefile — Veterinaria Docker Commands
# ============================================================
.PHONY: help up down build restart logs shell artisan migrate seed \
        fresh test cache-clear queue-restart

help: ## Mostrar ayuda
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

# ------ Ciclo de vida ------

build: ## Construir imágenes Docker
	docker compose build --no-cache

up: ## Levantar todos los servicios (producción)
	docker compose up -d

up-dev: ## Levantar todos los servicios + Vite (desarrollo)
	docker compose --profile dev up -d

down: ## Detener y eliminar contenedores
	docker compose down

restart: ## Reiniciar todos los servicios
	docker compose restart

# ------ Logs ------

logs: ## Ver logs de todos los servicios
	docker compose logs -f

logs-app: ## Ver logs del contenedor app
	docker compose logs -f app

logs-nginx: ## Ver logs de Nginx
	docker compose logs -f nginx

logs-queue: ## Ver logs del queue worker
	docker compose logs -f queue

# ------ Acceso ------

shell: ## Abrir shell en el contenedor app
	docker compose exec app bash

shell-mysql: ## Acceder a la CLI de MySQL
	docker compose exec mysql mysql -u${DB_USERNAME:-vet_user} -p${DB_PASSWORD:-vet_secret} ${DB_DATABASE:-veterinaria}

shell-redis: ## Acceder a la CLI de Redis
	docker compose exec redis redis-cli -a ${REDIS_PASSWORD:-redis_secret}

# ------ Laravel ------

artisan: ## Ejecutar comando artisan (make artisan cmd="route:list")
	docker compose exec app php artisan $(cmd)

migrate: ## Ejecutar migraciones
	docker compose exec app php artisan migrate

migrate-fresh: ## Refrescar BD con migraciones y seeders
	docker compose exec app php artisan migrate:fresh --seed

seed: ## Ejecutar seeders
	docker compose exec app php artisan db:seed

cache-clear: ## Limpiar todas las cachés de Laravel
	docker compose exec app php artisan cache:clear
	docker compose exec app php artisan config:clear
	docker compose exec app php artisan route:clear
	docker compose exec app php artisan view:clear

cache-warm: ## Cachear configuración, rutas y vistas
	docker compose exec app php artisan config:cache
	docker compose exec app php artisan route:cache
	docker compose exec app php artisan view:cache

queue-restart: ## Reiniciar el queue worker
	docker compose exec app php artisan queue:restart

# ------ Frontend ------

npm-install: ## Instalar dependencias Node
	docker compose --profile dev run --rm vite npm install

npm-build: ## Compilar assets para producción
	docker compose --profile dev run --rm vite npm run build

# ------ Tests ------

test: ## Ejecutar tests con Pest
	docker compose exec app php artisan test

# ------ Utilidades ------

ps: ## Estado de los contenedores
	docker compose ps

setup: ## Configuración inicial del proyecto
	@echo "📋 Copiando .env..."
	cp -n .env.example .env || true
	@echo "🔨 Construyendo imágenes..."
	docker compose build
	@echo "🚀 Levantando servicios..."
	docker compose up -d
	@echo "⏳ Esperando servicios..."
	sleep 20
	@echo "📦 Instalando dependencias PHP..."
	docker compose exec app composer install
	@echo "🔑 Generando clave..."
	docker compose exec app php artisan key:generate
	@echo "🗄️  Ejecutando migraciones..."
	docker compose exec app php artisan migrate
	@echo "✅ ¡Proyecto listo en http://localhost:8080"
