# 🐾 Sistema de Gestión de Clínica Veterinaria

Un sistema integral para la gestión y administración de una clínica veterinaria moderna, desarrollado con **Laravel 12**, **Vue 3**, **Inertia.js** y **Bootstrap 5**.

Este proyecto abarca desde la gestión clínica diaria hasta el manejo de pagos, control de inventario de insumos médicos y visualización de métricas de Business Intelligence (BI).

## 🌟 Beneficios y Casos de Uso

Este sistema está diseñado para resolver los problemas cotidianos de una clínica veterinaria, aportando valor a todos los involucrados:

- **Para la Clínica (Negocio):** Centraliza la información financiera y operativa. Reduce el ausentismo gracias a las notificaciones automatizadas de citas. Permite tomar decisiones informadas a través del panel de métricas (BI).
- **Para los Veterinarios:** Organiza sus agendas diarias, diferencia entre citas normales y urgencias, y ofrece total transparencia sobre las prestaciones realizadas y el cálculo de sus honorarios (pagos).
- **Para los Clientes (Dueños de Mascotas):** Ofrece un portal dedicado para ver el historial de sus mascotas, el estado de sus citas médicas y los pagos o transacciones realizadas en la clínica.

## 🚀 Tecnologías Principales (Stack)

- **Backend:** Laravel 12.0, PHP 8.2+, Sanctum (Autenticación de API)
- **Frontend:** Vue 3.5 (Options API / Composition API), Inertia.js 1.0, Bootstrap 5.3, Axios
- **Base de Datos:** MySQL
- **Caché y Colas:** Redis (Implementado para optimización de datos de referencia y procesamiento asíncrono de correos electrónicos)
- **Infraestructura:** Docker (Entorno completo con PHP-FPM, Nginx, MySQL y Redis)
- **Build Tool:** Vite 6.0

## 📦 Funcionalidades Detalladas (Por Perfil de Usuario)

El sistema utiliza un control de acceso basado en roles (RBAC) que adapta la interfaz y las capacidades según el tipo de usuario:

### 👑 Perfil Administrador
- **Dashboard BI:** Panel de administración avanzado con métricas clave de rendimiento financiero y operativo, incluyendo filtros dinámicos.
- **Gestión de Infraestructura:** Administración completa de `Sucursales` y asignación de `Boxes` de atención.
- **Gestión de Personal:** Registro de `Veterinarios`, asignación a sucursales y definición de sus `Especialidades`.
- **Inventario y Facturación:** Control de `Insumos` (inventario) y `Prestaciones` (servicios médicos).
- **Control de Honorarios:** Cálculo y liquidación de sueldos mediante el módulo de `PagoVeterinario`.

### 🩺 Perfil Veterinario
- **Gestión de Agenda:** Visualización y administración de sus `Citas` médicas diarias.
- **Atención Clínica:** Registro de uso de insumos y prestaciones durante una cita (generando `CitaCargos`).
- **Historial Clínico:** Acceso a la información de los `Clientes` y sus respectivas `Mascotas` (especie, raza, edad, etc.).

### 👤 Perfil Cliente
- **Portal de Autogestión:** Visualización de sus mascotas registradas.
- **Seguimiento de Citas:** Acceso al historial de atenciones médicas pasadas y futuras.
- **Historial de Pagos:** Visualización de las `Transacciones` asociadas a los servicios recibidos.

## 🗄️ Estructura y Relaciones de Base de Datos (Modelo ER)

La base de datos está normalizada e implementa relaciones completas a través de Eloquent ORM:

- **Autenticación y Autorización:**
  - `User` pertenece a un `Rol`. Un `Rol` tiene muchos `Permisos` (Relación M:N).
  - `User` tiene un (hasOne) `Veterinario` o un `Cliente` (Polimorfismo lógico).
- **Gestión de Pacientes (CRM):**
  - `Mascota` pertenece a un `Cliente` y a una `Raza`.
  - `Raza` pertenece a una `Especie`.
- **Infraestructura Médica:**
  - `Sucursal` tiene muchos `Boxes` y muchos `Veterinarios`.
  - `Veterinario` pertenece a una `Sucursal` y a una `Especialidad`.
- **Operación Clínica (Citas):**
  - `Cita` agrupa toda la operación: pertenece a un `Veterinario`, una `Mascota`, un `Box` y una `Prestacion` principal.
  - `CitaCargo` (Detalle de la cita): pertenece a una `Cita`, relacionando `Insumos` y `Prestaciones` adicionales utilizadas.
- **Finanzas:**
  - `Transaccion` pertenece a un `Cliente` y a una `Cita` (representa el pago del cliente).
  - `PagoVeterinario` pertenece a un `Veterinario` (representa la liquidación al profesional).

## 🛠 Instalación y Configuración

El proyecto está dockerizado para un despliegue rápido y consistente.

### Requisitos previos
- Docker y Docker Compose instalados.
- Node.js (opcional si se usa fuera del contenedor de desarrollo).
- Composer.

### Pasos de instalación

1. **Clonar e instalar dependencias:**
   ```bash
   composer install
   npm install
   ```

2. **Configuración del entorno:**
   Copiar el archivo de entorno y generar la clave de aplicación:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Asegúrate de configurar correctamente las credenciales de base de datos y Redis (para colas y caché) en tu `.env` antes de levantar los contenedores.*

3. **Levantar el entorno (Docker/Sail):**
   ```bash
   # Si utilizas Laravel Sail
   ./vendor/bin/sail up -d
   ```
   *Alternativamente, si usas `docker-compose` estándar:*
   ```bash
   docker-compose up -d
   ```

4. **Migraciones y Base de Datos:**
   ```bash
   php artisan migrate:fresh --seed
   ```
   *Esto generará la estructura e inyectará los datos históricos y catálogos de prueba usando los Seeders.*

5. **Compilar assets del Frontend (Vite):**
   ```bash
   npm run dev
   ```
   *(También puedes correr el worker para las notificaciones por correo electrónico en una terminal separada: `php artisan queue:listen`)*

### Credenciales de Prueba por defecto
- **Email:** `demo@veterinaria.test`
- **Contraseña:** `password`
- **Mascotas asociadas:** Luna y Rocky.

## 📖 Arquitectura y Decisiones de Diseño

- **Caché con Redis:** Implementación del trait `ClearsCache` junto con Model Observers para asegurar la invalidación automática del caché (por ejemplo, al actualizar catálogos como Sucursales o Veterinarios), reduciendo la carga en la base de datos para lectura de datos estables.
- **Procesamiento Asíncrono:** Uso intensivo de colas con Redis para no bloquear el flujo del usuario al enviar correos transaccionales (confirmaciones de citas).
- **Inertia.js:** Comunicación fluida sin recargas entre el backend (Laravel) y el frontend (Vue), pasando las propiedades dinámicamente según la lógica de controladores y los permisos del usuario actual.

---
**Nomenclatura y Convenciones:** Tablas en plural, columnas en `snake_case`, métodos y rutas en español, siguiendo los estándares de la comunidad de Laravel y Vue.