# Veterinaria Aprendizaje

Proyecto de prueba y aprendizaje: clínica veterinaria simple con Laravel 12 y Vue 3 (Options API), Inertia y Bootstrap 5.

## Documentación


**Base de datos:** relaciones solo en modelos Eloquent; columnas `tabla_id` sin claves foráneas ni índices explícitos en migraciones.

**Nomenclatura:** tablas en plural, columnas `snake_case`, métodos y rutas en español; ejemplos en [docs/MANUAL-PRUEBA.md](docs/MANUAL-PRUEBA.md#nomenclatura-laravel-y-vue).

## Stack

- **Backend:** Laravel 12, Sanctum (sesión en API)
- **Frontend:** Vue 3.5, Inertia.js, Bootstrap 5, axios
- **Build:** Vite 6
- **Idioma:** rutas, controladores y mensajes de validación en español

## Instalación

1. `composer install`
2. `npm install`
3. Copiar `.env.example` a `.env` y configurar la base de datos
4. `php artisan key:generate`
5. `php artisan migrate`
6. `php artisan db:seed` (usuario demo y 2 mascotas de prueba)
7. `npm run dev` (y en otra terminal `php artisan serve`, o `composer dev`)

**Datos de prueba:** `demo@veterinaria.test` / `password` — mascotas **Luna** y **Rocky**.

## Desarrollo

```bash
npm run dev
```

```bash
composer dev
```