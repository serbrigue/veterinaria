# Documentación Técnica Unificada — Veterinaria Aprendizaje

Este repositorio contiene el ecosistema de software e infraestructura para la plataforma **Veterinaria Aprendizaje**, un sistema integral de gestión para clínicas veterinarias multi-sucursal.

## 📄 Información General del Proyecto

- **Proyecto:** `vaaladev/veterinaria-aprendizaje`
- **Stack Principal:** Laravel 12 + Vue 3 + Inertia.js + Bootstrap 5
- **Fecha de Auditoría:** 2026-07-07
- **Estado del Arte:** Consolidado Técnico Global

---

## 1. Resumen Ejecutivo y Arquitectura Global

La plataforma es una solución monolítica híbrida diseñada para resolver los flujos clínicos, logísticos y administrativos de una red de clínicas veterinarias. Su estructura permite la interacción de tres actores principales:

1. **Clientes:** Autogestión de fichas de mascotas y reserva/pago de citas médicas.
2. **Veterinarios:** Gestión de consultas de la sucursal, actualización de historial clínico y control de inventario base.
3. **Administradores:** Supervisión total de métricas operativas, control financiero global y reportería transaccional.

### 1.1 Stack Tecnológico Exhaustivo

| Capa                              | Tecnología          | Versión / Configuración                          |
| :-------------------------------- | :------------------ | :----------------------------------------------- |
| **Backend**                       | Laravel (PHP)       | 12.x (PHP &ge; 8.2)                              |
| **Frontend**                      | Vue 3 + Inertia.js  | Vue 3.5 / Inertia 1.x (Core SPA)                 |
| **UI Framework**                  | Bootstrap 5 + Icons | 5.3                                              |
| **Bundler / Compilador**          | Vite                | 6.x                                              |
| **Autenticación API**             | Laravel Sanctum     | 4.x                                              |
| **Routing Adaptativo JS**         | Ziggy               | 2.x                                              |
| **Base de Datos**                 | MySQL               | 8.0 (Relacional)                                 |
| **Caché / Colas de Correo**       | Redis               | 7.2                                              |
| **Alertas Interfaz**              | SweetAlert2         | 11.x                                             |
| **Manejo e Inferencia de Fechas** | Moment.js           | 2.x                                              |
| **Orquestación de Entornos**      | Docker Compose      | Nginx, PHP-FPM, MySQL, Redis, Queue Worker, Vite |

### 1.2 Patrón de Comunicación y Flujos de Datos

La arquitectura se desacopla en el cliente pero se centraliza en el backend gracias a **Inertia.js**, eliminando la necesidad de APIs REST complejas de mantener para la renderización de la SPA:

```
[ Datos / DB ] <---> [ Servidor (Laravel 12) ] <--- Inertia Middleware ---> [ Cliente (Vue 3 SPA) ]
                             |                                                    |
                      (Queue Worker) ---> [ SMTP / Email ]                 (Axios JSON Mutaciones)
```

- **Páginas (GET):** Las rutas alojadas en `routes/web.php` interceptan los requests de navegación tradicionales y despachan componentes Vue utilizando `Inertia::render('Modulo/Vista', $data)`. La información se inyecta como propiedades (`props`) reactivas de forma inmediata.
- **Mutaciones (POST/PUT/DELETE):** Los formularios críticos e interacciones asíncronas apuntan a `routes/api.php`, devolviendo respuestas estructuradas en JSON mediante `axios`. Las alertas de éxito o error son interceptadas en la interfaz por SweetAlert2.
- **Shared Data (Datos Compartidos Globales):** El middleware `HandleInertiaRequests` inyecta en cada ciclo de navegación el objeto de sesión `auth.user` con su relación de `rol` cargada ansiosamente (`eager-loaded`), permitiendo comprobaciones de vistas ágiles en el cliente.

---

## 2. Documentación del Back-end (Laravel)

### 2.1 Estructura del Modelo de Persistencia (Base de Datos)

El sistema opera con **21 modelos relacionales** vinculados bajo estrictas políticas de integridad de datos y llaves foráneas en MySQL:

| Modelo                  | Tabla                     | Campos Clave / Foráneos de Interés                                                             | Traits Empleados                      |
| :---------------------- | :------------------------ | :--------------------------------------------------------------------------------------------- | :------------------------------------ |
| **User**                | `users`                   | `name`, `email`, `password`, `rol_id`                                                          | `HasApiTokens`, `Notifiable`          |
| **Rol**                 | `roles`                   | `nombre_interno`, `nombre_legible`                                                             | —                                     |
| **Permiso**             | `permisos`                | `nombre`, `descripcion`                                                                        | —                                     |
| **Cliente**             | `clientes`                | `telefono`, `direccion`, `foto_perfil_url`, `user_id`                                          | —                                     |
| **Veterinario**         | `veterinarios`            | `telefono`, `foto_perfil_url`, `user_id`, `sucursal_id`, `especialidad_id`, `horario` (JSON)   | `ClearsCache`, `HasStorageAttributes` |
| **Mascota**             | `mascotas`                | `nombre`, `sexo`, `fecha_nacimiento`, `peso_kg`, `cliente_id`, `raza_id`, `imagen_url`         | `HasStorageAttributes`                |
| **Especie**             | `especies`                | `nombre`, `descripcion`, `imagen_url`, `creado_por`                                            | `ClearsCache`, `HasStorageAttributes` |
| **Raza**                | `razas`                   | `nombre`, `especie_id`, `imagen_url`, `creado_por`                                             | `ClearsCache`, `HasStorageAttributes` |
| **Sucursal**            | `sucursales`              | `nombre`, `direccion`, `telefono`                                                              | `ClearsCache`                         |
| **Box**                 | `boxes`                   | `nombre`, `sucursal_id`, `categoria_prestacion_id`                                             | `ClearsCache`                         |
| **Especialidad**        | `especialidades`          | `nombre`, `descripcion`                                                                        | `ClearsCache`                         |
| **Cita**                | `citas`                   | `titulo`, `fecha_hora`, `hora_termino`, `estado`, `veterinario_id`, `box_id`, `mascota_id`     | —                                     |
| **CitaCargo**           | `citas_cargo`             | `cantidad`, `precio_unitario`, `subtotal`, `pago_vet`, `cita_id`, `prestacion_id`, `insumo_id` | —                                     |
| **Prestacion**          | `prestaciones`            | `nombre`, `precio_base`, `comision_vet`, `sucursal_id`, `categoria_prestacion_id`              | `ClearsCache`                         |
| **Insumo**              | `insumos`                 | `nombre`, `precio_venta`, `stock_actual`, `stock_minimo`, `sucursal_id`, `categoria_insumo_id` | `ClearsCache`                         |
| **Transaccion**         | `transacciones`           | `monto_total`, `monto_pagado`, `estado`, `metodo_pago`, `cita_id`, `cliente_id`                | —                                     |
| **EquipoMedico**        | `equipos_medicos`         | `cita_id`, `usuario_id`, `rol_id`                                                              | —                                     |
| **BloqueoHorario**      | `bloqueos_horario`        | `fecha_inicio`, `fecha_fin`, `hora_inicio`, `hora_fin`, `motivo`, `veterinario_id`             | —                                     |
| **PagoVeterinario**     | `pago_veterinarios`       | `mes`, `anio`, `monto_total`, `estado`, `veterinario_id`, `usuario_id`                         | —                                     |
| **CategoriaPrestacion** | `categorias_prestaciones` | `nombre`, `descripcion`                                                                        | `ClearsCache`                         |
| **CategoriaInsumo**     | `categorias_insumos`      | `nombre`, `descripcion`                                                                        | `ClearsCache`                         |

### 2.2 Traits de Comportamiento Reutilizables

- `ClearsCache`: Limpia de forma reactiva las claves distribuidas en Redis (configuradas bajo la propiedad `$cacheKeys`) cuando ocurren eventos de mutación Eloquent `saved` o `deleted`.
- `HasStorageAttributes`: Intercepta y muta dinámicamente campos de tipo URI (`imagen_url`, `foto_perfil_url`) abstrayendo si el asset se sirve desde un CDN externo o de forma local en `/storage/`.
- `HandlesPhotoUploads`: Automatiza la carga e inyección de archivos multimedia dentro del disco local public, administrando nombres aleatorios UUID y forzando el descarte/eliminación selectiva de archivos anteriores para evitar fugas de almacenamiento.

### 2.3 Reactividad en el Servidor (Observadores y Mailables)

- **`CitaObserver`:** Diseñado como disparador de lógica asíncrona. Escucha el evento `created` para distribuir alertas de confirmación a clientes y veterinarios por correo. En el evento `updated`, si muta la columna `estado`, gatilla de forma automatizada los flujos de correo correspondientes a cancelaciones o reprogramaciones.
- **Catálogo de Mailables:** `CitaAgendadaMail`, `CitaCanceladaMail`, `CitaEstadoActualizadoMail`, `NotificacionMasivaMail` y `PagoConfirmadoMail`.

### 2.4 Catálogo Completo de Endpoints

#### Rutas de Autenticación (`routes/auth.php`) — Vistas e Interacciones Web

- `GET /registro` &rarr; Formulario físico de registro (`registrarse`)
- `POST /registro` &rarr; Consolidación y procesamiento de creación de cuenta
- `GET /iniciar-sesion` &rarr; Formulario de login primario (`iniciar-sesion`)
- `POST /iniciar-sesion` &rarr; Autenticación de credenciales y arranque de sesión
- `GET /recuperar-contrasena` &rarr; Formulario para solicitud de recuperación (`contrasena.solicitar`)
- `POST /recuperar-contrasena` &rarr; Despacho de token criptográfico al correo (`contrasena.correo`)
- `GET /restablecer-contrasena/{token}` &rarr; Formulario para asignación de nueva clave (`contrasena.restablecer`)
- `POST /restablecer-contrasena` &rarr; Mutación física de contraseña en el modelo de datos (`contrasena.guardar`)
- `POST /cerrar-sesion` &rarr; Invalida la sesión activa (`cerrar-sesion`)

#### Rutas Web Protegidas (`routes/web.php`) — Renderizado Inertia (Middleware `auth`)

- `GET /panel` &rarr; `PanelController@index` `[can:ver-panel]` (Acceso exclusivo a Administradores)
- `GET /perfil` &rarr; `ProfileController@editar`
- `PATCH /perfil` &rarr; `ProfileController@actualizar`
- `DELETE /perfil` &rarr; `ProfileController@eliminar`
- `GET /mascotas` &rarr; `MascotaController@listado` `[can:verTodas,Mascota]`
- `GET /mascotas/{mascota}` &rarr; `MascotaController@detalle` `[can:ver,mascota]`
- `GET /especies` &rarr; `EspecieController@listado` `[can:verTodas,Especie]`
- `GET /especies/{especie}` &rarr; `EspecieController@detalle` `[can:ver,especie]`
- `GET /razas` &rarr; `RazaController@listado` `[can:verTodas,Raza]`
- `GET /razas/{raza}` &rarr; `RazaController@detalle` `[can:ver,raza]`
- `GET /clientes` &rarr; `ClienteController@listado` `[can:verTodas,Cliente]`
- `GET /clientes/{cliente}` &rarr; `ClienteController@detalle` `[can:ver,cliente]`
- `GET /citas` &rarr; `CitaController@listado` `[can:verTodas,Cita]`
- `GET /citas/{cita}` &rarr; `CitaController@detalle` `[can:ver,cita]`
- `GET /sucursales` &rarr; `SucursalController@listado` `[can:verTodas,Sucursal]`
- `GET /sucursales/{sucursal}` &rarr; `SucursalController@detalle` `[can:ver,sucursal]`
- `GET /boxes` &rarr; `BoxController@listado` `[can:verTodas,Box]`
- `GET /boxes/{box}` &rarr; `BoxController@detalle` `[can:ver,box]`
- `GET /veterinarios` &rarr; `VeterinarioController@listado` `[can:verTodas,Veterinario]`
- `GET /veterinarios/{vet}` &rarr; `VeterinarioController@detalle` `[can:ver,veterinario]`
- `GET /prestaciones` &rarr; `PrestacionController@listado` `[can:verTodas,Prestacion]`
- `GET /prestaciones/{p}` &rarr; `PrestacionController@detalle` `[can:ver,prestacion]`
- `GET /insumos` &rarr; `InsumoController@listado` `[can:verTodas,Insumo]`
- `GET /insumos/{insumo}` &rarr; `InsumoController@detalle` `[can:ver,insumo]`
- `GET /ingresos` &rarr; `TransaccionController@listado`
- `GET /realizar-pagos` &rarr; `PagoVeterinarioController@index`
- `GET /realizar-pagos/{usuario}` &rarr; `PagoVeterinarioController@detalle`
- `POST /realizar-pagos/{usuario}/pagar` &rarr; `PagoVeterinarioController@procesarPago`
- `GET /transacciones/{t}/checkout` &rarr; `TransaccionController@checkout` `[can:pagar,transaccion]`
- `POST /transacciones/{t}/pagar` &rarr; `TransaccionController@procesarPago` `[can:pagar,transaccion]`

#### Rutas de Mutación API (`routes/api.php`) — Endpoints JSON Protegidos por Sanctum

Todas las interacciones operan bajo el middleware `auth:sanctum`. El comportamiento por recurso es:

| Recurso           | GET (Listado)         | POST (Crear)           | PUT (Editar)   | DELETE (Eliminar)     | Operaciones Especiales                                          |
| :---------------- | :-------------------- | :--------------------- | :------------- | :-------------------- | :-------------------------------------------------------------- |
| **Mascotas**      | `verTodas`            | `crear`                | `editar`       | `eliminar`            | —                                                               |
| **Especies**      | `verTodas`            | `crear`                | `editar`       | `eliminar`            | —                                                               |
| **Razas**         | `verTodas`            | `crear`                | `editar`       | `eliminar`            | —                                                               |
| **Clientes**      | `verTodas`            | `crear`                | `editar`       | `eliminar`            | `POST enviar-correo` (Masivo)                                   |
| **Citas**         | `verTodas`            | `crear`                | `editar`       | —                     | `PATCH cancelar`, `estado`, `notas`; `GET horarios-disponibles` |
| **Sucursales**    | `verTodas`            | `crear`                | `editar`       | `eliminar`            | —                                                               |
| **Boxes**         | `verTodas`            | `crear`                | `editar`       | `eliminar`            | —                                                               |
| **Veterinarios**  | `verTodas`            | `crear`                | `editar`       | `eliminar`            | `PATCH horario`; `POST/DELETE bloqueos`                         |
| **Insumos**       | `verTodas`            | `crear`                | `editar`       | `eliminar`            | —                                                               |
| **Prestaciones**  | `verTodas`            | `crear`                | `editar`       | `eliminar`            | —                                                               |
| **Cargos Cita**   | —                     | `POST .../{id}/cargo`  | `PUT .../{id}` | `DELETE .../{id}`     | Control de tarifas asociadas a la ficha                         |
| **Equipo Médico** | `GET .../{id}/equipo` | `POST .../{id}/equipo` | —              | `DELETE .../{id}/{m}` | Asignación de arsenaleros/cirujanos de apoyo                    |

### 2.5 Sistema de Roles y Permisos (RBAC)

El control de acceso opera sobre un esquema estricto de roles mapeados en base de datos interconectados por medio de una relación muchos a muchos (`permiso_rol`):

1. **`admin` (Administrador Supremo):** Posee bypass total de seguridad estructurado vía el método `before()` en todas las Policies del sistema, lo que otorga acceso irrestricto sin evaluar permisos atómicos.
2. **`veterinario` (Personal Clínico):** Dispone de **26 permisos** (ej: `ver-mascotas-sucursal`, `crear-recetas`, `gestionar-cargos-sucursal`, `ver-boxes`, `ver-insumos`). Está acotado a la manipulación de datos de su sucursal de pertenencia.
3. **`cliente` (Propietario de Mascotas):** Dispone de **9 permisos** (ej: `ver-mis-mascotas`, `crear-mis-mascotas`, `agendar-cita`, `pagar-transacciones`). Su acceso está rígidamente condicionado por validaciones de propiedad de registros (_Owner Checks_).

---

## 3. Documentación del Front-end (Vue 3 + Bootstrap 5)

### 3.1 Bootstrap y Configuración Global (`resources/js/app.js`)

Inicializa la instancia de Vue 3 acoplada a Inertia.js. Configura de forma transparente las siguientes capacidades de cara a los componentes:

- **Plugins integrados:** `ZiggyVue` para resolver nombres de rutas desde Javascript.
- **Propiedades Temporales e Interfaces:** Abstracciones para Moment.js (`$formatoFecha`, `$fechaInput`) y disparadores gráficos para SweetAlert2 (`$alertaExito`, `$alertaError`, `$confirmar`).
- **Helpers de Autorización Reactiva:** Métodos integrados (`$isAdmin()`, `$isVeterinario()`, `$isCliente()`) vinculados a los datos compartidos de la sesión cliente para ocultar componentes visuales redundantes.

### 3.2 Estructura de Directorios del Módulo Frontend

```
resources/js/
├── app.js                    # Entry point de la SPA (Plugins + Mixins globales)
├── bootstrap.js              # Configuración por defecto de Axios (Headers) + Bootstrap JS Core
├── alertas.js                # Definición de wrappers reutilizables de SweetAlert2
├── fechas.js                 # Manejadores de formateo temporal (Moment.js)
├── Componentes/              # 21 componentes atómicos reutilizables (Paginadores, Modales, Inputs)
├── Disenos/                  # 3 Layouts base (AppLayout, LayoutAutenticado, LayoutInvitado)
└── Paginas/                  # 16 módulos de negocio divididos en 38 vistas estructuradas
```

### 3.3 Catálogo de Componentes Reutilizables Destacados

- `TieneRol`: Filtro declarativo en el template encargado de evaluar accesos en la interfaz. Oculta el slot default si el rol del usuario no hace match con los parámetros.
- `ModalCrud`: Estructura genérica de formulario flotante con soporte nativo para spinners de carga, validaciones asíncronas y adaptabilidad para estados de inserción o actualización.
- `TarjetaEntidad`: Mapeo estándar en cuadrícula para catálogos con slots integrados para acciones CRUD y soporte de carga de imágenes fluidas.
- `Paginador`: Componente de control encargado de iterar y mapear los metadatos de paginación nativos distribuidos por Eloquent/Laravel.

### 3.4 Consumo de Datos y Estrategia de Caché en Redis

El backend optimiza los tiempos de respuesta de la SPA almacenando datos transversales en Redis mediante `Cache::remember()` por un TTL por defecto de 30 minutos:

- `sucursales_full` / `sucursales_simple`: Estructura global de clínicas y veterinarios asignados.
- `prestaciones_full` / `veterinarios_full` / `especies_simple` / `razas_full`.

> [!IMPORTANT]
> Todos los modelos asociados a estos catálogos implementan el trait `ClearsCache`, el cual invalida automáticamente estas llaves de Redis ante inserciones o ediciones en el panel administrativo, previniendo visualizaciones obsoletas en la interfaz Vue.

---

## 4. Guía de Instalación y Despliegue de Entornos

### 4.1 Instalación Convencional (Entorno de Desarrollo Local)

#### Requisitos Previos Mínimos

- PHP &ge; 8.2 con extensiones requeridas (PDO, OpenSSL, Mbstring, Tokenizer, XML, Ctype)
- Composer &ge; 2.x
- Node.js &ge; 20.x + npm
- Servidor MySQL 8.0 y servidor Redis activo en la máquina local

#### Pasos de Configuración

1. **Clonar e instalar dependencias concurrentes:**

    ```bash
    git clone <URL_REPOSITORIO> veterinaria
    cd veterinaria
    composer install
    npm install
    ```

2. **Configurar variables de entorno:**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

    _Abra el archivo `.env` y configure sus accesos locales a MySQL y Redis:_

    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=veterinaria
    DB_USERNAME=root
    DB_PASSWORD=secret

    CACHE_DRIVER=redis
    SESSION_DRIVER=redis
    QUEUE_CONNECTION=redis
    ```

3. **Ejecutar migraciones y estructuración de Storage:**

    ```bash
    php artisan migrate --seed
    php artisan storage:link
    ```

4. **Arranque del ecosistema local:**
    ```bash
    composer run dev
    ```
    _Nota: Este comando invoca `concurrently` para levantar el servidor interno de PHP (`php artisan serve`), el compilador de assets en tiempo real de Vite en el puerto `5173` y el daemon del worker de colas (`php artisan queue:listen`)._

### 4.2 Despliegue Automatizado Mediante Docker Compose

La raíz cuenta con una infraestructura contenerizada y dividida en 5 microservicios optimizados para operar de forma aislada:

- `vet_app`: Contenedor PHP 8.2 FPM dedicado al procesamiento de la aplicación Laravel.
- `vet_nginx`: Servidor web Nginx expuesto externamente en el puerto host `8080`.
- `vet_mysql`: Motor relacional MySQL 8.0 mapeado localmente en el puerto `3307`.
- `vet_redis`: Instancia Redis 7.2 encargada de la gestión de colas de correo y variables de caché rápida.
- `vet_queue`: Worker en segundo plano encargado de escuchar y vaciar los jobs de correo.

#### Pasos para Compilación e Inicialización de Contenedores

1. **Preparar entorno inicial:**

    ```bash
    cp .env.example .env
    ```

2. **Compilar y levantar servicios:**
    - **Para Desarrollo (con servidor de cambios en caliente Vite activo):**
        ```bash
        docker compose --profile dev up -d --build
        ```
    - **Para Entorno de Producción (Compilación estática de assets en `/public/build/`):**
        ```bash
        npm install && npm run build
        docker compose up -d --build
        ```

3. **Aprovisionar la base de datos dentro del contenedor en ejecución:**
    ```bash
    docker compose exec app php artisan key:generate
    docker compose exec app composer install
    docker compose exec app php artisan storage:link
    docker compose exec app php artisan migrate --seed
    ```
