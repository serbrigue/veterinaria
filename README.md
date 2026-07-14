# Documentación Técnica Unificada — Veterinaria Aprendizaje

Este repositorio contiene el ecosistema de software e infraestructura para la plataforma **Veterinaria Aprendizaje**, un sistema integral de gestión para clínicas veterinarias multi-sucursal.

## 📄 Información General del Proyecto

- **Proyecto:** `vaaladev/veterinaria-aprendizaje`
- **Stack Principal:** Laravel 12 + Vue 3 + Inertia.js + Bootstrap 5
- **Fecha de Auditoría:** 2026-07-14
- **Estado del Arte:** Consolidado Técnico Global (Rev. 2)

---

## 1. Resumen Ejecutivo y Arquitectura Global

La plataforma es una solución monolítica híbrida diseñada para resolver los flujos clínicos, logísticos y administrativos de una red de clínicas veterinarias. Su estructura permite la interacción de cuatro actores principales:

1. **Clientes:** Autogestión de fichas de mascotas y reserva/pago de citas médicas.
2. **Veterinarios:** Gestión de consultas de la sucursal, actualización de historial clínico y control de inventario base.
3. **Secretarias:** Agendamiento interactivo de citas mediante calendario FullCalendar, gestión operativa de la sucursal asignada.
4. **Administradores:** Supervisión total de métricas operativas, control financiero global, reportería transaccional, importación masiva de datos y panel de inteligencia de negocios (BI).

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

El sistema opera con **23 modelos relacionales** vinculados bajo estrictas políticas de integridad de datos y llaves foráneas en MySQL:

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
| **Secretaria**          | `secretarias`             | `user_id`, `sucursal_id`, `telefono`                                                           | —                                     |
| **BloqueoHorario**      | `bloqueos_horario`        | `fecha_inicio`, `fecha_fin`, `hora_inicio`, `hora_fin`, `motivo`, `veterinario_id`, `especialidad_id`, `sucursal_id` | —             |

### 2.2 Traits de Comportamiento Reutilizables

- `ClearsCache`: Limpia de forma reactiva las claves distribuidas en Redis (configuradas bajo la propiedad `$cacheKeys`) cuando ocurren eventos de mutación Eloquent `saved` o `deleted`.
- `HasStorageAttributes`: Intercepta y muta dinámicamente campos de tipo URI (`imagen_url`, `foto_perfil_url`) abstrayendo si el asset se sirve desde un CDN externo o de forma local en `/storage/`.
- `HandlesPhotoUploads`: Automatiza la carga e inyección de archivos multimedia dentro del disco local public, administrando nombres aleatorios UUID y forzando el descarte/eliminación selectiva de archivos anteriores para evitar fugas de almacenamiento.

### 2.3 Reactividad en el Servidor (Observadores y Mailables)

- **`CitaObserver`:** Diseñado como disparador de lógica asíncrona. Escucha el evento `created` para distribuir alertas de confirmación a clientes y veterinarios por correo. En el evento `updated`, si muta la columna `estado`, gatilla de forma automatizada los flujos de correo correspondientes a cancelaciones o reprogramaciones.
- **Catálogo de Mailables:** `CitaAgendadaMail`, `CitaCanceladaMail`, `CitaEstadoActualizadoMail`, `NotificacionMasivaMail` y `PagoConfirmadoMail`.

### 2.4 Sistema de Importación Consolidada de Datos (Excel)

El módulo de importación permite la carga masiva de datos desde archivos Excel (`.xlsx`, `.xls`, `.csv`) con un flujo transaccional de dos fases:

#### Arquitectura del Flujo

```
[ Archivo Excel ] → [ Fase 1: Análisis ] → [ Fase 2: Procesamiento Transaccional ] → [ Reporte de Descartados ]
                         ↓                            ↓                                       ↓
                   analyzeHeaders()              DB::transaction()                  DiscardedImportExport
                   (Pre-lectura)             (ConsolidatedImport)                 (Excel descargable)
```

#### Componentes del Sistema

| Clase | Responsabilidad |
| :--- | :--- |
| **`ImportController`** | Orquesta el flujo de importación: análisis de headers (`analyzeHeaders`), procesamiento transaccional (`importData`) y descarga de descartados (`downloadDiscarded`) |
| **`ConsolidatedImport`** | Implementa `ToCollection` de Laravel Excel. Procesa filas secuencialmente creando/actualizando Clientes, Mascotas y Citas con resolución inteligente de relaciones |
| **`DiscardedImportExport`** | Genera un archivo Excel con las filas rechazadas y su motivo de descarte para revisión del usuario |
| **`AnalyzeImportRequest`** | Valida el archivo (formatos permitidos, máx. 10 MB) |
| **`ProcessImportRequest`** | Valida archivo, mapping JSON y módulos seleccionados |

#### Reglas de Negocio Implementadas

- **RF-02 Pre-lectura Estructural:** Extrae encabezados y muestra de datos antes del procesamiento.
- **RF-04/RF-05 Upsert Inteligente:** `User::updateOrCreate` + `Cliente::updateOrCreate` evitan duplicados por email.
- **RF-06 Resolución Relacional:** Fallback automático de Raza/Especie a registros comodín "No Especificada" cuando la referencia no existe.
- **RNF-01 Integridad Transaccional:** Todo el procesamiento se envuelve en `DB::transaction()`. Si falla una fila, se captura como descartada sin abortar la transacción completa.
- **RNF-02 Validación de Tamaño:** Archivos limitados a 10 MB.
- **RNF-03 Feedback UX:** Las filas con errores (ej. solapamiento de citas) se acumulan en un array `$descartados` y se exportan como Excel descargable.
- **Lógica de Estados Dinámicos:** Citas importadas con fecha pasada se marcan automáticamente como `completada` (si tienen valor asociado) o `cancelada` (sin valor). Citas futuras se validan contra solapamientos existentes.
- **Limpieza Automática:** Los archivos de descartados se eliminan del servidor tras la descarga (`deleteFileAfterSend(true)`).
- **Protección Path Traversal:** El nombre del archivo descargable se valida con regex antes de servirlo.

#### Rutas del Importador

| Método | Ruta | Acción | Middleware |
| :--- | :--- | :--- | :--- |
| `GET` | `/importador-consolidado` | Renderiza la SPA del importador | `auth`, `can:importar-datos` |
| `POST` | `/api/import/analyze` | Pre-lectura de headers | `auth`, `can:importar-datos` |
| `POST` | `/api/import/process` | Procesamiento transaccional | `auth`, `can:importar-datos` |
| `GET` | `/api/import/download/{fileName}` | Descarga y elimina reporte de descartados | `auth`, `can:importar-datos` |

### 2.5 Panel de Inteligencia de Negocios (BI)

El `PanelController` expone un conjunto de KPIs avanzados organizados en 4 dimensiones analíticas, calculados en tiempo real y renderizados por el componente `BiKpiDashboard.vue`:

| Dimensión | KPIs Calculados |
| :--- | :--- |
| **Operación Clínica** | Tasa de ocupación de boxes (%), ticket promedio por cita ($), tasa de ausentismo (%), productividad por veterinario (citas + ingresos generados) |
| **Financiero** | Ingresos brutos totales, costo nómina variable (comisiones), margen neto por sucursal ($ y %) |
| **Logística e Inventario** | Índice de rotación de insumos (Top 3), alertas de stock bajo (insumos bajo mínimo), merma de inventario (placeholder para módulo futuro) |
| **Clientes y Fidelización** | LTV — Valor de Vida del Cliente ($), frecuencia de visita anual (citas/mascota), tasa de conversión registro → cita (%) |

Además se mantienen las estadísticas operativas previas: gráficos de ingresos por sucursal (últimos 6 meses), top 5 prestaciones y top 5 insumos, y comisiones acumuladas por veterinario.

### 2.6 Gestión de Bloqueos de Horario

El `BloqueoHorarioController` permite a los administradores registrar períodos de indisponibilidad para veterinarios con granularidad especializada:

- **Bloqueo por rango de fechas:** Soporta bloqueos de día completo o parciales (con `hora_inicio`/`hora_fin`).
- **Bloqueo por especialidad:** Filtra solo citas cuya prestación coincida con la `especialidad_id` indicada.
- **Bloqueo por sucursal:** Filtra citas asociadas a boxes o prestaciones de una sucursal específica.
- **Cancelación automática:** Al registrar un bloqueo, todas las citas pendientes que se solapen en el rango son marcadas como `cancelada` con nota explicativa automática.
- **Validación de conflictos:** Impide bloqueos duplicados y valida coherencia de horas.

### 2.7 API REST de Autenticación

El `AuthApiController` expone endpoints JSON para consumo desde clientes externos o integraciones (ej. chatbot n8n):

| Método | Ruta API | Acción |
| :--- | :--- | :--- |
| `POST` | `/api/registrarse` | Registro de usuario (crea User + Cliente automáticamente) |
| `POST` | `/api/iniciar-sesion` | Autenticación por credenciales |
| `POST` | `/api/cerrar-sesion` | Invalidación de sesión (protegido) |
| `POST` | `/api/recuperar-contrasena` | Envío de link de recuperación |
| `POST` | `/api/restablecer-contrasena` | Mutación de contraseña con token |
| `POST` | `/api/confirmar-contrasena` | Confirmación de contraseña activa (protegido) |
| `POST` | `/api/verificacion/enviar` | Reenvío de email de verificación (protegido) |

### 2.8 Catálogo Completo de Endpoints

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
- `POST /transacciones/{t}/pagar` → `TransaccionController@procesarPago` `[can:pagar,transaccion]`
- `GET /secretaria/calendario` → `CitaController@agendaSecretaria` `[auth]` (Vista de calendario interactivo)
- `GET /importador-consolidado` → Inertia `ConsolidatedImport` `[can:importar-datos]`
- `POST /api/import/analyze` → `ImportController@analyzeHeaders` `[can:importar-datos]`
- `POST /api/import/process` → `ImportController@importData` `[can:importar-datos]`
- `GET /api/import/download/{fileName}` → `ImportController@downloadDiscarded` `[can:importar-datos]`

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

### 2.9 Sistema de Roles y Permisos (RBAC)

El control de acceso opera sobre un esquema estricto de roles mapeados en base de datos interconectados por medio de una relación muchos a muchos (`permiso_rol`):

1. **`admin` (Administrador Supremo):** Posee bypass total de seguridad estructurado vía el método `before()` en todas las Policies del sistema, lo que otorga acceso irrestricto sin evaluar permisos atómicos. Incluye acceso al importador de datos y al panel BI.
2. **`veterinario` (Personal Clínico):** Dispone de **26 permisos** (ej: `ver-mascotas-sucursal`, `crear-recetas`, `gestionar-cargos-sucursal`, `ver-boxes`, `ver-insumos`). Está acotado a la manipulación de datos de su sucursal de pertenencia.
3. **`secretaria` (Asistente Administrativo):** Gestión operativa de agendamiento con acceso al calendario interactivo de citas de la sucursal asignada. Vista panorámica de citas por estado (pendientes, en curso, completadas, urgencias).
4. **`cliente` (Propietario de Mascotas):** Dispone de **9 permisos** (ej: `ver-mis-mascotas`, `crear-mis-mascotas`, `agendar-cita`, `pagar-transacciones`). Su acceso está rígidamente condicionado por validaciones de propiedad de registros (_Owner Checks_).

#### Policies Agregadas

| Policy | Recurso Protegido | Notas |
| :--- | :--- | :--- |
| `BloqueoHorarioPolicy` | `BloqueoHorario` | Creación y eliminación restringida a administradores (bypass `before()`) |
| `PagosVeterinariosPolicy` | `PagoVeterinario` | CRUD completo restringido a administradores |

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
├── Componentes/              # 26 componentes atómicos reutilizables (Paginadores, Modales, Inputs, Calendario, Chatbot)
├── Disenos/                  # 3 Layouts base (AppLayout, LayoutAutenticado, LayoutInvitado)
└── Paginas/                  # 20 módulos de negocio divididos en 46 vistas estructuradas
```

### 3.3 Catálogo de Componentes Reutilizables Destacados

#### Componentes Base (Existentes)

- `TieneRol`: Filtro declarativo en el template encargado de evaluar accesos en la interfaz. Oculta el slot default si el rol del usuario no hace match con los parámetros.
- `ModalCrud`: Estructura genérica de formulario flotante con soporte nativo para spinners de carga, validaciones asíncronas y adaptabilidad para estados de inserción o actualización.
- `TarjetaEntidad`: Mapeo estándar en cuadrícula para catálogos con slots integrados para acciones CRUD y soporte de carga de imágenes fluidas.
- `Paginador`: Componente de control encargado de iterar y mapear los metadatos de paginación nativos distribuidos por Eloquent/Laravel.

#### Componentes Agregados

| Componente | Descripción |
| :--- | :--- |
| `BarraAccionesAgenda` | Barra de acciones contextuales para la vista de calendario de secretaría |
| `BarraFiltros` | Filtrado genérico por búsqueda textual y propiedades dinámicas para listados |
| `ModalBloqueoHorario` | Modal especializado para la creación de bloqueos de horario con campos de especialidad y sucursal |
| `ModalGestionHorario` | Modal avanzado para la gestión de planes de horario JSON de veterinarios con soporte multi-segmento |
| `BotonChatbotn8n` | FAB flotante con ventana de chat embebida que integra un webhook de n8n. Incluye animación de pulso, glassmorphism y carga dinámica del SDK `@n8n/chat` |
| `EstadoVacio` | Placeholder visual para vistas sin datos, con slot para acciones |
| `IndicadorCarga` | Spinner de carga reutilizable con estilos consistentes |
| `SinResultados` | Componente de feedback para búsquedas sin coincidencias |

### 3.4 Vistas de Módulos Nuevos

#### Calendario de Secretaría (`Paginas/Secretaria/Calendario.vue`)

Calendario interactivo construido con **FullCalendar** (plugins: `dayGridPlugin`, `timeGridPlugin`, `interactionPlugin`) que permite:

- Visualización de citas en vistas de mes, semana y día con codificación cromática por estado.
- Panel de estadísticas rápidas (pendientes, en curso, completadas, urgencias).
- Creación de citas desde clic en fecha/hora del calendario con pre-selección de horario.
- Modal de detalle con información completa (paciente, cliente, veterinario, box, prestación, notas).
- Dropdown con búsqueda textual para selección de clientes y prestaciones.
- Acordeón de disponibilidad por veterinario cuando no se pre-selecciona uno específico.
- Diferenciación visual de horarios normales vs. urgencias (fuera de horario).

#### Importador Consolidado (`Pages/ConsolidatedImport.vue` + `Paginas/Perfil/Partials/ConsolidatedImport.vue`)

Interfaz de importación en dos fases: (1) carga y análisis del archivo Excel con vista previa de encabezados, (2) mapeo interactivo de columnas a campos del sistema y selección de módulos a importar (Clientes, Mascotas, Citas). Muestra reporte de filas descartadas con link de descarga.

#### Dashboard BI (`Paginas/App/Partials/BiKpiDashboard.vue`)

Componente de panel analítico con 4 tarjetas de KPIs: operación clínica, financiero, clientes y logística. Renderiza datos calculados en `PanelController::getBiKpis()` con formato monetario localizado (es-CL).

#### Dashboards por Rol (`Paginas/Perfil/Partials/Dashboard*.vue`)

| Dashboard | Rol | Contenido |
| :--- | :--- | :--- |
| `DashboardAdmin.vue` | Administrador | Accesos rápidos a módulos de gestión |
| `DashboardVeterinario.vue` | Veterinario | Citas del día, próximas citas, estadísticas personales |
| `DashboardSecretaria.vue` | Secretaria | Resumen operativo de la sucursal asignada |
| `DashboardCliente.vue` | Cliente | Mascotas registradas, próximas citas, historial de pagos |

### 3.5 Integración con Chatbot n8n

El componente `BotonChatbotn8n` implementa un botón flotante (FAB) con ventana de chat embebida que se conecta a un workflow de **n8n** mediante webhook:

- **Carga diferida:** El SDK `@n8n/chat` se importa dinámicamente solo cuando el usuario abre el chat por primera vez.
- **Contexto de usuario:** Envía el `usuario_id` de la sesión activa como metadata al webhook para personalización de respuestas.
- **Diseño premium:** Glassmorphism, animación de pulso en el FAB, transiciones suaves de apertura/cierre, header personalizado con indicador de estado online.
- **Configuración:** El webhook URL se recibe como prop, permitiendo configuración por entorno sin hardcodear.

### 3.6 Consumo de Datos y Estrategia de Caché en Redis

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

---

## 5. Migraciones Relevantes (Changelog Estructural)

Migraciones añadidas desde la última auditoría documental:

| Migración | Descripción |
| :--- | :--- |
| `2026_07_02_140339_change_box_id_to_citas_table` | Modifica la relación `box_id` en `citas` para hacerla nullable (citas sin box asignado) |
| `2026_07_02_155852_add_horario_to_veterinarios_table` | Añade columna JSON `horario` a `veterinarios` para almacenar planes de horario personalizados |
| `2026_07_02_165203_create_bloqueos_horario_table` | Crea tabla `bloqueos_horario` con soporte para rangos de fechas, horas y motivos |
| `2026_07_07_160022_create_secretarias_table` | Crea tabla `secretarias` con relación a `users` y `sucursales` |
| `2026_07_09_140053_add_especialidad_id_and_sucursal_id_to_bloqueos_horario_table` | Añade filtros de especialidad y sucursal a bloqueos de horario |
| `2026_07_13_133840_add_imagen_to_boxes_table` | Añade columna `imagen_url` a `boxes` para soporte de imágenes |
| `2026_07_13_133853_add_imagen_to_sucursales_table` | Añade columna `imagen_url` a `sucursales` para soporte de imágenes |
| `2026_07_13_203435_drop_creado_por_column_to_especies_table` | Elimina columna legacy `creado_por` de `especies` |
| `2026_07_13_203450_drop_creado_por_column_to_razas_table` | Elimina columna legacy `creado_por` de `razas` |

---

## 6. Dependencias de Terceros Añadidas

| Paquete | Uso |
| :--- | :--- |
| `maatwebsite/excel` | Importación y exportación de archivos Excel (`.xlsx`, `.xls`, `.csv`) |
| `@fullcalendar/vue3` + plugins | Calendario interactivo para la vista de secretaría (dayGrid, timeGrid, interaction) |
| `@n8n/chat` (CDN) | SDK del chatbot n8n cargado dinámicamente desde jsDelivr |
