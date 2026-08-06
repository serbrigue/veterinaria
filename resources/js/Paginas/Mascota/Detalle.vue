<template>
    <!-- ================================================================================== -->
    <!-- COMPONENTE: Detalle -->
    <!-- ================================================================================== -->
    <Head :title="'Mascota - ' + (mascota.nombre || 'Detalle')" />

    <AuthenticatedLayout>
        <div class="container py-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center gap-3">
                    <Link :href="route('mascotas.listado')" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                        <i class="bi bi-arrow-left"></i> Volver
                    </Link>
                    <h1 class="h3 mb-0 text-dark fw-bold">Perfil del Paciente</h1>
                </div>
                <!--Si el usuario tiene permiso de editar la mascota, se muestra el botón de editar y eliminar-->
                <div v-if="puedeEditarMascota" class="d-flex gap-2">
                    <!--Se muestra el botón de eliminar-->
                    <button @click="confirmarEliminar" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1">
                        <i class="bi bi-trash"></i> Eliminar
                    </button>
                </div>
            </div>

            <div class="row g-4">
                
                <div class="col-lg-4 col-md-5">
                    <div class="card border-0 shadow-sm sticky-top rounded-4 overflow-hidden" style="top: 2rem;">
                        <!-- Header Background -->
                        <div class="card-img-top bg-light position-relative" style="height: 140px;">
                            <div class="w-100 h-100 bg-primary bg-gradient bg-opacity-75 d-flex align-items-center justify-content-center">
                                <i class="bi bi-heart-pulse text-white opacity-25" style="font-size: 5rem; transform: rotate(15deg);"></i>
                            </div>
                            <div class="position-absolute top-0 end-0 m-3">
                                <span class="badge bg-white text-success px-3 py-2 rounded-pill shadow-sm fw-bold">
                                    <i class="bi bi-circle-fill text-success small me-1"></i> Paciente Activo
                                </span>
                            </div>
                        </div>

                        <!-- Avatar -->
                        <div class="card-body text-center position-relative pt-0 pb-4">
                            <div class="position-absolute top-0 start-50 translate-middle" style="z-index: 2;">
                                <div class="bg-white p-2 rounded-circle shadow-sm" style="width: 110px; height: 110px;">
                                    <div class="w-100 h-100 rounded-circle bg-light d-flex align-items-center justify-content-center text-primary fw-bold fs-1 overflow-hidden">
                                        <!-- Si la mascota tiene imagen, se muestra, si no, se muestra un icono -->
                                        <img v-if="mascota.imagen_url" :src="mascota.imagen_url" alt="Foto de la mascota" class="w-100 h-100 object-fit-cover">
                                        <i v-else class="bi bi-heart-pulse-fill text-primary opacity-50"></i>
                                    </div>
                                </div>
                            </div>

                            <div style="height: 60px;"></div>
                            
                            <h2 class="h4 mb-1 fw-bold text-dark">{{ mascota.nombre }}</h2>
                            <p class="text-muted mb-0">{{ mascota.sexo ? mascota.sexo.charAt(0).toUpperCase() + mascota.sexo.slice(1) : 'Sexo N/A' }}</p>
                        </div>
                        
                        <div class="card-body p-4 bg-light border-top border-light">
                            <h3 class="h6 text-uppercase text-secondary fw-bold mb-4" style="font-size: 0.75rem; letter-spacing: 1px;">Detalles Principales</h3>
                            
                            <div class="d-flex align-items-center gap-3 mb-3 p-3 bg-white rounded-3 shadow-sm border border-light">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-circle text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bi bi-person-badge fs-5"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.65rem;">Propietario</div>
                                        <!--Si la mascota tiene propietario, se muestra el nombre del propietario con enlace a su detalle-->
                                        <Link v-if='cliente':href="route('clientes.detalle', cliente.id)" class="text-decoration-none fw-bold text-dark">
                                            {{ cliente?.usuario?.name }} <i class="bi bi-box-arrow-up-right ms-1 small text-primary"></i>
                                        </Link>
                                        <!--Si la mascota no tiene propietario, se muestra "No asignado"-->
                                        <span v-else class="text-dark fw-medium">No asignado</span>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-3 mb-3 p-3 bg-white rounded-3 shadow-sm border border-light">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-circle text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bi bi-clipboard2-pulse fs-5"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.65rem;">Especie y Raza</div>
                                    <span class="text-dark fw-bold d-block">
                                        {{ especie?.nombre || 'N/A' }} 
                                        <span class="text-muted mx-1 fw-normal">•</span> 
                                        {{ raza?.nombre || 'N/A' }}
                                    </span>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-3 p-3 bg-white rounded-3 shadow-sm border border-light">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-circle text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bi bi-calendar3 fs-5"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.65rem;">Fecha de Nac. / Edad</div>
                                    <span class="text-dark fw-bold d-block">
                                        {{ mascota.fecha_nacimiento ? formatearFecha(mascota.fecha_nacimiento) : 'N/A' }}
                                        <!--Si la mascota tiene fecha de nacimiento, se muestra la edad-->
                                        <span v-if="mascota.fecha_nacimiento" class="text-muted fw-normal ms-1">({{ $edadDesde(mascota.fecha_nacimiento) }})</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <!-- Footer del card -->
                        <div class="card-footer bg-white border-top border-light p-3 text-center">
                            <small class="text-muted fw-medium"><i class="bi bi-clock-history me-1"></i> Registrado el: {{ formatearFecha(mascota.created_at) }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8 col-md-7">
                    <!-- Citas Próximas -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                            <h3 class="h6 mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                                <i class="bi bi-calendar-event text-primary"></i> Citas Próximas
                            </h3>
                            <!--Si el usuario tiene permiso para crear citas, se muestra el botón de nueva cita-->
                            <button v-if="puedeCrearCita" @click="abrirModalCita" class="btn btn-sm btn-primary d-flex align-items-center gap-1">
                                <i class="bi bi-plus-lg"></i> Nueva Cita
                            </button>
                        </div>
                        <div class="card-body p-3">
                            <!--Si la mascota no tiene citas próximas, se muestra un mensaje de no hay citas programadas-->
                            <div v-if="!proximasCitas || !proximasCitas.data || proximasCitas.data.length === 0" class="py-4 text-center text-muted">
                                <i class="bi bi-calendar-x fs-1 mb-3 d-block" style="color: #dee2e6;"></i>
                                <p class="mb-0">No hay citas programadas próximamente.</p>
                            </div>
                            <div v-else class="d-flex flex-column gap-3">
                                <!--Si la mascota tiene citas próximas, se muestra la lista de citas-->
                                <div
                                    v-for="cita in proximasCitas.data"
                                    :key="cita.id"
                                    class="border rounded-3 p-3 bg-white shadow-sm cita-card"
                                >
                                    <!-- Cabecera: Título + Badge de Estado -->
                                    <Link :href="route('citas.detalle', cita.id)">
                                        <div class="d-flex align-items-start justify-content-between mb-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-2">
                                                    <i class="bi bi-calendar-event fs-6"></i>
                                                </div>
                                                <span class="fw-bold text-dark">{{ cita.titulo }}</span>
                                            </div>
                                            <span class="badge rounded-pill px-3 py-2" :class="{
                                                'bg-warning text-dark': cita.estado === 'pendiente',
                                                'bg-success':           cita.estado === 'completada',
                                                'bg-danger':            cita.estado === 'cancelada',
                                                'bg-primary':           cita.estado === 'en_curso',
                                            }">
                                                {{ cita.estado ? cita.estado.charAt(0).toUpperCase() + cita.estado.slice(1) : 'Pendiente' }}
                                            </span>
                                        </div>

                                        <!-- Detalles en fila -->
                                        <div class="row g-2 ps-1 mt-1">
                                            <div class="col-12 col-md-6 d-flex align-items-center gap-2 text-muted small">
                                                <i class="bi bi-clock text-primary"></i>
                                                <span>{{ cita.fecha_hora }}</span>
                                            </div>
                                            <div class="col-12 col-md-6 d-flex align-items-center gap-2 text-muted small">
                                                <i class="bi bi-heart-pulse text-danger"></i>
                                                <span>{{ cita.veterinario?.usuario?.name || 'Sin veterinario' }}</span>
                                            </div>
                                            <div class="col-12 col-md-6 d-flex align-items-center gap-2 text-muted small">
                                                <i class="bi bi-building text-secondary"></i>
                                                <span>{{ cita.box?.sucursal?.nombre || 'Sin sucursal' }}</span>
                                            </div>
                                            <div class="col-12 col-md-6 d-flex align-items-center gap-2 text-muted small">
                                                <i class="bi bi-door-open text-secondary"></i>
                                                <span>{{ cita.box?.nombre || 'Sin box' }}</span>
                                            </div>
                                        </div>
                                    </Link>
                                </div>
                                <Paginador :data="proximasCitas" entidad="citas" @cambiar-pagina="cambiarPagina" />
                            </div>
                        </div>
                    </div>

                    <!-- Historial Médico -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                      <div class="card-body p-3">
                            <!--Si la mascota no tiene historial clínico, se muestra un mensaje de no hay historial clínico-->
                            <div v-if="!historialClinico || !historialClinico.data || historialClinico.data.length === 0" class="py-4 text-center text-muted">
                                <i class="bi bi-calendar-x fs-1 mb-3 d-block" style="color: #dee2e6;"></i>
                                <p class="mb-0">No hay historial clínico.</p>
                            </div>
                            <div v-else class="d-flex flex-column gap-3">
                                <h3 class="h6 text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">Historial Clínico</h3>
                                <!--Si la mascota tiene historial clínico, se muestra la lista de historial clínico-->
                                <div
                                    v-for="cita in historialClinico.data"
                                    :key="cita.id"
                                    class="border rounded-3 p-3 bg-white shadow-sm cita-card"
                                >
                                    <!-- Cabecera: Título + Badge de Estado -->
                                    <Link :href="route('citas.detalle', cita.id)">
                                        <div class="d-flex align-items-start justify-content-between mb-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-2">
                                                    <i class="bi bi-calendar-event fs-6"></i>
                                                </div>
                                                <span class="fw-bold text-dark">{{ cita.titulo }}</span>
                                            </div>
                                            <span class="badge rounded-pill px-3 py-2" :class="{
                                                'bg-warning text-dark': cita.estado === 'pendiente',
                                                'bg-success':           cita.estado === 'completada',
                                                'bg-danger':            cita.estado === 'cancelada',
                                                'bg-primary':           cita.estado === 'en_curso',
                                            }">
                                                {{ cita.estado ? cita.estado.charAt(0).toUpperCase() + cita.estado.slice(1) : 'Pendiente' }}
                                            </span>
                                        </div>

                                        <!-- Detalles en fila -->
                                        <div class="row g-2 ps-1 mt-1">
                                            <div class="col-12 col-md-6 d-flex align-items-center gap-2 text-muted small">
                                                <i class="bi bi-clock text-primary"></i>
                                                <span>{{ cita.fecha_hora }}</span>
                                            </div>
                                            <div class="col-12 col-md-6 d-flex align-items-center gap-2 text-muted small">
                                                <i class="bi bi-heart-pulse text-danger"></i>
                                                <span>{{ cita.veterinario?.usuario?.name || 'Sin veterinario' }}</span>
                                            </div>
                                            <div class="col-12 col-md-6 d-flex align-items-center gap-2 text-muted small">
                                                <i class="bi bi-building text-secondary"></i>
                                                <span>{{ cita.box?.sucursal?.nombre || 'Sin sucursal' }}</span>
                                            </div>
                                            <div class="col-12 col-md-6 d-flex align-items-center gap-2 text-muted small">
                                                <i class="bi bi-door-open text-secondary"></i>
                                                <span>{{ cita.box?.nombre || 'Sin box' }}</span>
                                            </div>
                                        </div>
                                    </Link>
                                </div>
                                
                                <Paginador :data="historialClinico" entidad="historial clínico" @cambiar-pagina="cambiarPagina" />
                            </div>
                         </div>

                        </div>
                    </div>
                </div>            
            </div>
            

            <!-- ========================================== -->
            <!-- MODAL: Nueva Cita                          -->
            <!-- ========================================== -->
            <ModalCrud
                :visible="mostrarModalCita"
                titulo="Nueva Cita"
                :processing="formularioCita.processing"
                tamanio="lg"
                texto-crear="Crear cita"
                @cerrar="cerrarModalCita"
                @guardar="crearCita"
            >
                                <div class="row g-0">
                                    <!-- Columna izquierda: datos de la cita -->
                                    <div class="col-md-5 p-3 border-end">
                                        <div class="row g-3">
                                            <!-- Si hay un erro general, se muestra -->
                                            <div v-if="errorGeneral" class="col-12">
                                                <div class="alert alert-danger d-flex align-items-center mb-0 border-0 shadow-sm" role="alert">
                                                    <i class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2"></i>
                                                    <div>{{ errorGeneral }}</div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <label for="titulo" class="form-label fw-semibold text-secondary small text-uppercase">Título</label>
                                                <!-- Almacenamos el titulo de la cita-->
                                                <input id="titulo" v-model="formularioCita.titulo" type="text" class="form-control bg-light border-0 py-2" :class="{ 'is-invalid': formularioCita.errors.titulo }" required placeholder="Ej: Control general" />
                                                <!-- Si hay un error, se muestra -->
                                                <div v-if="formularioCita.errors.titulo" class="invalid-feedback">{{ formularioCita.errors.titulo }}</div>
                                            </div>
                                            <div class="col-12">
                                                <label for="descripcion" class="form-label fw-semibold text-secondary small text-uppercase">Descripción</label>
                                                <!-- Almacenamos la descripcion de la cita -->
                                                <textarea id="descripcion" v-model="formularioCita.descripcion" class="form-control bg-light border-0 py-2" :class="{ 'is-invalid': formularioCita.errors.descripcion }" rows="2" required placeholder="Motivo de la cita..."></textarea>
                                                <!-- Si hay un error, se muestra -->
                                                <div v-if="formularioCita.errors.descripcion" class="invalid-feedback">{{ formularioCita.errors.descripcion }}</div>
                                            </div>
                                            <div class="col-12">
                                                <label for="mascota_id" class="form-label fw-semibold text-secondary small text-uppercase">Mascota</label>
                                                <!-- Almacenamos la mascota -->
                                                <select id="mascota_id" v-model="formularioCita.mascota_id" class="form-select bg-light border-0 py-2" :class="{ 'is-invalid': formularioCita.errors.mascota_id }" required disabled>
                                                    <option :value="mascota.id">
                                                        {{ mascota.nombre }} {{ mascota.sexo ? `(${mascota.sexo})` : '' }}
                                                    </option>
                                                </select>
                                                <!-- Si hay un error, se muestra -->
                                                <div v-if="formularioCita.errors.mascota_id" class="invalid-feedback">{{ formularioCita.errors.mascota_id }}</div>
                                            </div>
                                            <div class="col-12">
                                                <label for="prestacion_id" class="form-label fw-semibold text-secondary small text-uppercase">Prestación o Servicio</label>
                                                <!-- Almacenamos la prestacion de la cita -->
                                                <select id="prestacion_id" v-model="formularioCita.prestacion_id" class="form-select bg-light border-0 py-2" :class="{ 'is-invalid': formularioCita.errors.prestacion_id }" required>
                                                    <option value="" disabled>Selecciona una prestación</option>
                                                    <!-- Iteramos sobre las prestaciones  para mostrar opciones-->
                                                    <option v-for="prestacion in prestaciones" :key="prestacion.id" :value="prestacion.id">
                                                        {{ prestacion.nombre }} ({{ prestacion.sucursal?.nombre }})
                                                    </option>
                                                </select>
                                                <!-- Si hay un error, se muestra -->
                                                <div v-if="formularioCita.errors.prestacion_id" class="invalid-feedback">{{ formularioCita.errors.prestacion_id }}</div>
                                            </div>

                                            <!-- Si la prestacion está seleccionada, se muestra la sucursal-->

                                            <div v-if="formularioCita.prestacion_id" class="col-12">
                                                <label class="form-label fw-semibold text-secondary small text-uppercase">Sucursal</label>
                                                <!-- Almacenamos la sucursal de la prestacion-->
                                                <select id="sucursal_id" v-model="formularioCita.sucursal_id" class="form-select bg-light border-0 py-2" :class="{ 'is-invalid': formularioCita.errors.sucursal_id }" required disabled>
                                                    <option value="" disabled>Selecciona una sucursal</option>
                                                    <!-- Iteramos sobre las sucursales para mostrar opciones-->
                                                    <option v-for="sucursal in sucursalesFiltradas" :key="sucursal.id" :value="sucursal.id">
                                                        {{ sucursal.nombre }}
                                                    </option>
                                                </select>
                                                <!-- Si hay un error, se muestra -->
                                                <div v-if="formularioCita.errors.sucursal_id" class="invalid-feedback">{{ formularioCita.errors.sucursal_id }}</div>
                                            </div>

                                            <!-- Si hay una sucursal mostramos los veterinarios aptos-->
                                            <template v-if="formularioCita.sucursal_id">
                                                <div class="col-12">
                                                    <label class="form-label fw-semibold text-secondary small text-uppercase">Veterinario (Aptos)</label>
                                                    <!-- Almacenamos el veterinario-->
                                                    <select id="veterinario_id" v-model="formularioCita.veterinario_id" class="form-select bg-light border-0 py-2" :class="{ 'is-invalid': formularioCita.errors.veterinario_id }">
                                                        <option value="">Cualquier veterinario (opcional)</option>
                                                        <!-- Iteramos sobre los veterinarios aptos para mostrar las opciones -->
                                                        <option v-for="vet in veterinariosFiltrados" :key="vet.id" :value="vet.id">
                                                            {{ vet.usuario?.name }}
                                                        </option>
                                                    </select>
                                                    <!-- Si hay un error, se muestra -->
                                                    <div v-if="formularioCita.errors.veterinario_id" class="invalid-feedback">{{ formularioCita.errors.veterinario_id }}</div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Columna derecha: fecha y horarios -->
                                    <div class="col-md-7 p-3 bg-light bg-opacity-50">
                                        <!-- Si hay una sucursal mostramos los horarios -->
                                        <template v-if="formularioCita.sucursal_id">
                                            <div class="mb-3">
                                                <label for="fecha_seleccionada" class="form-label fw-semibold text-secondary small text-uppercase">Fecha</label>
                                                <!-- Enlace de datos bidireccional con "formularioCita.fecha_seleccionada" -->
                                                <input
                                                    id="fecha_seleccionada"
                                                    type="date"
                                                    v-model="formularioCita.fecha_seleccionada"
                                                    class="form-control bg-white border-0 py-2 shadow-sm"
                                                    :class="{ 'is-invalid': formularioCita.errors.fecha_hora }"
                                                    :min="hoy"
                                                    @change="cargarHorarios"
                                                />
                                                <!-- Si hay un error, se muestra -->
                                                <div v-if="formularioCita.errors.fecha_hora" class="invalid-feedback">{{ formularioCita.errors.fecha_hora }}</div>
                                            </div>

                                            <!-- Si hay una sucursal mostramos los horarios -->

                                            <div v-if="cargandoHorarios" class="text-center py-4">
                                                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                                <span class="ms-2 text-muted small">Consultando disponibilidad...</span>
                                            </div>

                                            <template v-else-if="formularioCita.fecha_seleccionada">

                                                <!-- Si hay un veterinario seleccionado mostramos los horarios -->
                                                <template v-if="formularioCita.veterinario_id">
                                                    <!-- Horarios normales -->
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold text-secondary small text-uppercase">Horarios disponibles</label>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            <!-- Iteramos sobre los horarios disponibles -->
                                                            <template v-for="slot in horariosNormales" :key="slot.hora">
                                                                <!-- Si el horario está disponible se muestra -->
                                                                <!-- Si el horario no está disponible no se muestra -->
                                                                <button
                                                                    v-if="slot.disponible"
                                                                    type="button"
                                                                    :class="[
                                                                        'btn btn-sm rounded-pill px-3',
                                                                        formularioCita.fecha_hora === slot.fecha_hora
                                                                            ? 'btn-primary'
                                                                            : 'btn-outline-primary'
                                                                    ]"
                                                                    @click="seleccionarHorario(slot)"
                                                                >
                                                                    {{ slot.hora }}
                                                                </button>
                                                            </template>
                                                            <!-- Si no hay horarios normales disponibles se muestra un mensaje -->
                                                            <div v-if="horariosNormales.filter(s => s.disponible).length === 0" class="text-muted small">
                                                                No hay horarios normales disponibles.
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Horarios de urgencia -->
                                                    <div>
                                                        <label class="form-label fw-semibold text-warning small text-uppercase">
                                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                                            Urgencia (fuera de horario)
                                                        </label>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            <!-- Iteramos sobre los horarios de urgencia -->
                                                            <template v-for="slot in horariosUrgencia" :key="slot.hora">
                                                                <!-- Si el horario está disponible se muestra -->
                                                                <!-- Si el horario no está disponible no se muestra -->
                                                                <button
                                                                    v-if="slot.disponible"
                                                                    type="button"
                                                                    :class="[
                                                                        'btn btn-sm rounded-pill px-3',
                                                                        formularioCita.fecha_hora === slot.fecha_hora
                                                                            ? 'btn-warning text-dark'
                                                                            : 'btn-outline-warning'
                                                                    ]"
                                                                    @click="seleccionarHorario(slot)"
                                                                >
                                                                    {{ slot.hora }}
                                                                </button>
                                                            </template>
                                                            <!-- Si no hay horarios de urgencia disponibles se muestra un mensaje -->
                                                            <div v-if="horariosUrgencia.filter(s => s.disponible).length === 0" class="text-muted small">
                                                                No hay horarios de urgencia disponibles.
                                                            </div>
                                                        </div>
                                                        <small class="text-muted mt-2 d-block">Las atenciones fuera de horario tienen un costo adicional.</small>
                                                    </div>
                                                </template>

                                                <template v-else>
                                                    <div class="mb-2 text-secondary small fw-semibold text-uppercase">Selecciona un veterinario y horario</div>

                                                    <div class="accordion border border-light rounded-3 overflow-hidden shadow-sm" id="vetSchedulesAccordion">
                                                        <!-- Iteramos sobre los veterinarios para mostrar sus horarios -->
                                                        <div v-for="vet in veterinariosFiltrados" :key="vet.id" class="accordion-item border-0 border-bottom border-light">
                                                            <h4 class="accordion-header mb-0">
                                                                <!-- Si hay un veterinario seleccionado mostramos los horarios -->
                                                                <button 
                                                                    class="accordion-button d-flex align-items-center justify-content-between w-100 py-3 px-4 text-start border-0 fw-semibold"
                                                                    :class="vetAcordeonAbiertoId === vet.id ? 'bg-primary bg-opacity-10 text-primary' : 'bg-white text-dark'"
                                                                    type="button" 
                                                                    @click="toggleAcordeon(vet.id)"
                                                                >
                                                                    <span class="d-flex align-items-center gap-2">
                                                                        <i class="bi bi-person-badge-fill" :class="vetAcordeonAbiertoId === vet.id ? 'text-primary' : 'text-secondary'"></i>
                                                                        {{ vet.usuario?.name }}
                                                                    </span>
                                                                    <span class="badge rounded-pill bg-light text-secondary border border-light small px-2 py-1">
                                                                        {{ obtenerCantSlotsDisponibles(vet.id) }} horarios disponibles
                                                                        <i class="bi ms-1" :class="vetAcordeonAbiertoId === vet.id ? 'bi-caret-up-fill' : 'bi-caret-down-fill'"></i>
                                                                    </span>
                                                                </button>
                                                            </h4>
                                                            <!-- Si el veterinario está seleccionado se muestra -->
                                                            <div 
                                                                v-if="vetAcordeonAbiertoId === vet.id" 
                                                                class="accordion-body p-4 bg-light bg-opacity-25"
                                                            >
                                                                <!-- Horarios del veterinario -->
                                                                <!-- Si hay horarios del veterinario disponibles mostramos los horarios -->
                                                                <div v-if="horariosPorVeterinario[vet.id]">
                                                                    <!-- Horarios normales -->
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-semibold text-secondary small text-uppercase">Horarios disponibles</label>
                                                                        <div class="d-flex flex-wrap gap-2">
                                                                            <!-- Iteramos sobre los horarios normales del veterinario -->
                                                                            <template v-for="slot in horariosPorVeterinario[vet.id].normal" :key="slot.hora">
                                                                                <!-- Si el horario está disponible se muestra -->
                                                                                <!-- Si el horario no está disponible no se muestra -->
                                                                                <button
                                                                                    v-if="slot.disponible"
                                                                                    type="button"
                                                                                    :class="[
                                                                                        'btn btn-sm rounded-pill px-3',
                                                                                        formularioCita.fecha_hora === slot.fecha_hora
                                                                                            ? 'btn-primary'
                                                                                            : 'btn-outline-primary'
                                                                                    ]"
                                                                                    @click="seleccionarHorarioAcordeon(slot, vet.id)"
                                                                                >
                                                                                    {{ slot.hora }}
                                                                                </button>
                                                                            </template>
                                                                            <!-- Si no hay horarios normales disponibles se muestra un mensaje -->
                                                                            <div v-if="horariosPorVeterinario[vet.id].normal.filter(s => s.disponible).length === 0" class="text-muted small">
                                                                                No hay horarios normales disponibles.
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <!-- Horarios de urgencia -->
                                                                    <div>
                                                                        <label class="form-label fw-semibold text-warning small text-uppercase">
                                                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                                                            Urgencia (fuera de horario)
                                                                        </label>
                                                                        <div class="d-flex flex-wrap gap-2">
                                                                            <!-- Iteramos sobre los horarios de urgencia del veterinario -->
                                                                            <template v-for="slot in horariosPorVeterinario[vet.id].urgencia" :key="slot.hora">
                                                                                <!-- Si el horario está disponible se muestra -->
                                                                                <!-- Si el horario no está disponible no se muestra -->
                                                                                <button
                                                                                    v-if="slot.disponible"
                                                                                    type="button"
                                                                                    :class="[
                                                                                        'btn btn-sm rounded-pill px-3',
                                                                                        formularioCita.fecha_hora === slot.fecha_hora
                                                                                            ? 'btn-warning text-dark'
                                                                                            : 'btn-outline-warning'
                                                                                    ]"
                                                                                    @click="seleccionarHorarioAcordeon(slot, vet.id)"
                                                                                >
                                                                                    {{ slot.hora }}
                                                                                </button>
                                                                            </template>
                                                                            <!-- Si no hay horarios de urgencia disponibles se muestra un mensaje -->
                                                                            <div v-if="horariosPorVeterinario[vet.id].urgencia.filter(s => s.disponible).length === 0" class="text-muted small">
                                                                                No hay horarios de urgencia disponibles.
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div v-else class="text-center py-3">
                                                                    <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>

                                            </template>

                                            <!-- Placeholder cuando aún no se elige fecha -->
                                            <div v-else class="text-center text-muted py-5">
                                                <i class="bi bi-calendar2-week fs-1 d-block mb-2" style="color: #dee2e6;"></i>
                                                <small>Selecciona una fecha para ver los horarios disponibles</small>
                                            </div>
                                        </template>

                                        <!-- Placeholder cuando aún no se elige sucursal -->
                                        <div v-else class="text-center text-muted py-5">
                                            <i class="bi bi-clock-history fs-1 d-block mb-2" style="color: #dee2e6;"></i>
                                            <small>Selecciona una prestación para ver la disponibilidad</small>
                                        </div>
                                    </div>
             </div>
            </ModalCrud>
        </div>
    </AuthenticatedLayout>
</template>

<script>
// ==================================================================================
// LÓGICA DEL COMPONENTE (VUE 3)
// ==================================================================================

import AuthenticatedLayout from '@/Disenos/LayoutAutenticado.vue';
import { Head, Link } from '@inertiajs/vue3';
import ModalCrud from '@/Componentes/ModalCrud.vue';
import Paginador from '@/Componentes/Paginador.vue';

// ------------------------------------------------------------------------------
// EXPORT DEFAULT: Definición principal del componente
// ------------------------------------------------------------------------------
export default {
    name: 'MascotaDetalle',
    // COMPONENTES: Registro de componentes importados
    components: {
        AuthenticatedLayout,
        Head,
        Link,
        ModalCrud,
        Paginador
    },
    // PROPIEDADES: Datos inyectados desde el componente padre o estado
    props: {
        proximasCitas: {
            type: Object,
            required: true
        },
        historialClinico: {
            type: Object,
            required: true
        },

        mascota: {
            type: Object,
            required: true
        },
        cliente: {
            type: Object,
            required: true
        },
        especie:{
            type:Object,
            required: true
        },
        raza:{
            type: Object,
            required: true
        },
        sucursales: {
            type: Array,
            default: () => [],
        },
        prestaciones: {
            type: Array,
            default: () => [],
        }
    },
    // PROPIEDADES COMPUTADAS: Variables reactivas que dependen de otras
    computed: {
        // Obtiene la fecha actual en formato YYYY-MM-DD
        hoy() {
            return new Date().toISOString().split('T')[0];
        },
        // Filtra las sucursales por prestacion_id
        sucursalesFiltradas() {
            if (!this.formularioCita.prestacion_id) return [];
            const prestacion = this.prestaciones.find(p => p.id === this.formularioCita.prestacion_id);
            if (!prestacion) return [];
            return this.sucursales.filter(s => s.id === prestacion.sucursal_id);
        },
        // Filtra los veterinarios por sucursal_id y prestacion_id
        veterinariosFiltrados() {
            // Si no se ha seleccionado sucursal o prestacion en el formulario, devuelve array vacio
            if (!this.formularioCita.sucursal_id || !this.formularioCita.prestacion_id) return [];
            //Busca la sucursal seleccionada
            const sucursal = this.sucursales.find(s => s.id === this.formularioCita.sucursal_id);
            if (!sucursal) return [];
            
            //Busca la prestacion seleccionada
            const prestacion = this.prestaciones.find(p => p.id === this.formularioCita.prestacion_id);
            
            // Filtra los veterinarios
            return sucursal.veterinarios.filter(vet => {
                if (!prestacion.especialidad_id) return true;
                return vet.especialidad_id === prestacion.especialidad_id;
            });
            
        },
        // Verifica si el usuario actual puede editar la mascota
        puedeEditarMascota() {
            const user = this.$page.props.auth.user;
            if (!user || !user.rol) return false;

            //Permite editar la mascota si el usuario es administrador o secretaria
            if (this.$isAdmin() || this.$isSecretaria()) {
                return true;
            }

            //Permite editar la mascota si el usuario es cliente y es el dueño de la mascota
            if (this.$isCliente()) {
                return this.mascota.cliente_id === user.cliente?.id;
            }

            return false;
        },
        // Verifica si el usuario actual puede crear una cita para la mascota
        puedeCrearCita() {
            const user = this.$page.props.auth.user;
            if (!user || !user.rol) return false;

            //Permite crear una cita si el usuario es administrador o secretaria
            if (this.$isAdmin() || this.$isSecretaria()) {
                return true;
            }

            //Permite crear una cita si el usuario es cliente y es el dueño de la mascota
            if (this.$isCliente()) {
                return this.mascota.cliente_id === user.cliente?.id;
            }

            return false;
        },
    },
    // OBSERVADORES: Reaccionan a cambios en propiedades o variables
    watch: {
        // Observa los cambios en el prestacion_id del formularioCita para actualizar la sucursal y limpiar otros campos
        'formularioCita.prestacion_id'(newVal, oldVal) {
            // Si hay un nuevo valor de prestacion_id
            if (newVal) {
                // Busca la prestacion seleccionada
                const prestacion = this.prestaciones.find(p => p.id === newVal);
                // Si la prestacion existe y la sucursal es diferente a la seleccionada, actualiza la sucursal
                if (prestacion && this.formularioCita.sucursal_id !== prestacion.sucursal_id) {
                    this.formularioCita.sucursal_id = prestacion.sucursal_id;
                }
                // Si hay un valor anterior y es diferente al nuevo, limpia los otros campos
                if (oldVal && newVal !== oldVal) {
                    this.formularioCita.box_id = '';
                    this.formularioCita.veterinario_id = '';
                    this.formularioCita.fecha_seleccionada = '';
                    this.formularioCita.fecha_hora = '';
                }
            } else {
                // Si no hay un nuevo valor de prestacion_id, limpia los otros campos
                this.formularioCita.sucursal_id = '';
                this.formularioCita.box_id = '';
                this.formularioCita.veterinario_id = '';
            }
        },
        // Observa los cambios en el sucursal_id del formularioCita para limpiar otros campos
        'formularioCita.sucursal_id'(newVal, oldVal) {
            // Si hay un valor anterior y es diferente al nuevo, limpia los otros campos
            if (oldVal && newVal !== oldVal) {
                this.formularioCita.veterinario_id = '';
                this.formularioCita.box_id = '';
            }
        }, 
        // Observa los cambios en el veterinario_id del formularioCita para limpiar otros campos
        'formularioCita.veterinario_id'(newVal, oldVal) { 
            // Si hay un valor anterior y es diferente al nuevo, limpia los otros campos
            if (oldVal && newVal !== oldVal && !this.evitarResetHorario) {
                this.formularioCita.fecha_seleccionada = '';
                this.formularioCita.fecha_hora = '';
            }
            this.cargarHorarios(); 
        },
    },
    // MÉTODOS (METHODS): Bloque de funciones y eventos
    methods: {
        // Función que abre el modal para crear una cita
        abrirModalCita() {
            // Limpia los campos del formulario
            this.formularioCita.titulo = '';
            this.formularioCita.descripcion = '';
            this.formularioCita.fecha_hora = '';
            this.formularioCita.fecha_seleccionada = '';
            this.formularioCita.tipo = 'normal';
            this.formularioCita.prestacion_id = '';
            this.formularioCita.sucursal_id = '';
            this.formularioCita.veterinario_id = '';
            this.formularioCita.box_id = '';
            this.formularioCita.mascota_id = this.mascota.id;
            this.formularioCita.errors = {};
            this.horariosNormales = [];
            this.horariosUrgencia = [];
            this.horariosPorVeterinario = {};
            this.vetAcordeonAbiertoId = null;
            this.evitarResetHorario = false;
            this.errorGeneral = null;

            this.mostrarModalCita = true;
        },
        // Función que confirma la eliminación de la mascota
        confirmarEliminar() {
            // Se abre un cuadro de diálogo de confirmación
            this.$confirmar('¿Eliminar mascota?', `Se eliminará a ${this.mascota.nombre}.`)
                // Si el usuario confirma la eliminación
                .then((resultado) => {
                    // Si el usuario no confirma la eliminación, se retorna
                    if (!resultado.isConfirmed) return;
                    // Se elimina la mascota
                    axios.delete(`/api/mascotas/${this.mascota.id}`)
                        // Si la eliminación es exitosa
                        .then(() => {
                            this.$alertaExito('Eliminada', `${this.mascota.nombre} fue eliminada.`);
                            this.$inertia.visit(route('mascotas.listado'));
                        })
                        .catch(() => this.$alertaError('Error', 'No se pudo eliminar la mascota.'));
                });
        },
        // Función que formatea la fecha
        formatearFecha(fecha) {
            if (!fecha) return 'Sin fecha';
            const date = new Date(fecha);
            return date.toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        },
        // Función que cierra el modal de la cita
        cerrarModalCita() {
            this.mostrarModalCita = false;
            this.formularioCita.errors = {};
            this.errorGeneral = null;
        },
        // Función que crea una cita
        crearCita() {
            this.formularioCita.processing = true;
            this.formularioCita.errors = {};
            this.errorGeneral = null;

            const datos = {
                titulo: this.formularioCita.titulo,
                descripcion: this.formularioCita.descripcion,
                fecha_hora: this.formularioCita.fecha_hora,
                tipo: this.formularioCita.tipo,
                mascota_id: this.formularioCita.mascota_id,
                prestacion_id: this.formularioCita.prestacion_id,
                veterinario_id: this.formularioCita.veterinario_id || null,
            };

            // Envía la solicitud para crear la cita
            axios.post('/api/citas', datos)
                // Si la solicitud es exitosa
                .then(() => {
                    // Cierra el modal
                    this.cerrarModalCita();
                    // Muestra un mensaje de éxito
                    this.$alertaExito('Cita Agendada', 'La cita se ha registrado con éxito.');
                    // Recarga la página
                    this.$inertia.reload();
                })
                // Si la solicitud falla
                .catch((error) => {
                    // Si la solicitud falla
                    // Si la solicitud falla con status 422
                    if (error.response?.status === 422) {
                        this.formularioCita.errors = error.response.data.errors;
                        this.$alertaValidacion(error.response.data.errors);
                    } 
                    // Si la solicitud falla con status 409
                    else if (error.response?.status === 409) {
                        this.errorGeneral = error.response.data.error || error.response.data.message;
                        this.$alertaError('Conflicto', this.errorGeneral);
                    } 
                    // Si la solicitud falla con otro status
                    else {
                        this.$alertaError('Error', 'No se pudo crear la cita.');
                    }
                })
                .finally(() => {
                    this.formularioCita.processing = false;
                });
        },
        // Función que cambia de página
        cambiarPagina(url) {
            if (!url) return;
            this.$inertia.visit(url, {
                preserveState: true,
                preserveScroll: true
            });
        },
        // Función que carga los horarios disponibles
        cargarHorarios() {
            // Si no hay fecha seleccionada, no se cargan los horarios
            if (!this.formularioCita.fecha_seleccionada) return;

            this.cargandoHorarios = true;
            // Si no se evita el reset de horarios, se resetean
            if (!this.evitarResetHorario) {
                this.formularioCita.fecha_hora = '';
                this.formularioCita.tipo = 'normal';
            }

            // Si el veterinario está seleccionado, se cargan los horarios
            if (this.formularioCita.veterinario_id) {
                axios.get('/api/citas/horarios-disponibles', {
                    params: {
                        fecha:           this.formularioCita.fecha_seleccionada,
                        veterinario_id:  this.formularioCita.veterinario_id,
                    }
                }).then(r => {
                    // Si la solicitud es exitosa
                    this.horariosNormales  = r.data.normal;
                    this.horariosUrgencia  = r.data.urgencia;
                }).catch(error => {
                    // Si la solicitud falla
                    console.error('Error al cargar horarios:', error);
                }).finally(() => {
                    // Se detiene la carga de horarios
                    this.cargandoHorarios = false;
                });
            } else {
                // Si el veterinario no está seleccionado, se cargan los horarios de todos los veterinarios
                this.horariosPorVeterinario = {};
                
                const promesas = this.veterinariosFiltrados.map(vet => {
                    // Se carga los horarios disponibles para cada veterinario
                    return axios.get('/api/citas/horarios-disponibles', {
                        params: {
                            fecha:           this.formularioCita.fecha_seleccionada,
                            veterinario_id:  vet.id,
                        }
                    }).then(r => {
                        // Si la solicitud es exitosa
                        this.horariosPorVeterinario[vet.id] = {
                            veterinario: vet,
                            normal: r.data.normal,
                            urgencia: r.data.urgencia
                        };
                    }).catch(error => {
                        // Si la solicitud falla
                        console.error(`Error al cargar horarios para veterinario ${vet.id}:`, error);
                    });
                });

                // Se espera a que todas las solicitudes se completen
                Promise.all(promesas).finally(() => {
                    // Se detiene la carga de horarios
                    this.cargandoHorarios = false;
                    if (this.veterinariosFiltrados.length > 0 && !this.vetAcordeonAbiertoId) {
                        this.vetAcordeonAbiertoId = this.veterinariosFiltrados[0].id;
                    }
                });
            }
        },
        // Función que alterna el acordeón
        toggleAcordeon(vetId) {
            this.vetAcordeonAbiertoId = this.vetAcordeonAbiertoId === vetId ? null : vetId;
        },
        // Función que obtiene la cantidad de slots disponibles
        obtenerCantSlotsDisponibles(vetId) {
            const data = this.horariosPorVeterinario[vetId];
            if (!data) return 0;
            const normales = data.normal?.filter(s => s.disponible).length || 0;
            const urgencias = data.urgencia?.filter(s => s.disponible).length || 0;
            return normales + urgencias;
        },
        seleccionarHorarioAcordeon(slot, vetId) {
            // Se evita el reset de horarios
            this.evitarResetHorario = true;
            // Se establece el veterinario
            this.formularioCita.veterinario_id = vetId;
            // Se establece la fecha y hora
            this.formularioCita.fecha_hora = slot.fecha_hora;
            // Se establece el tipo
            this.formularioCita.tipo       = slot.tipo;
            // Se resetea el evita el reset de horarios después de 100ms
            setTimeout(() => {
                this.evitarResetHorario = false;
            }, 100);
        },
        // Función que selecciona el horario
        seleccionarHorario(slot) {
            this.formularioCita.fecha_hora = slot.fecha_hora;
            this.formularioCita.tipo       = slot.tipo;
        },
    },
    // ESTADO REACTIVO (DATA): Variables locales del componente
    data() {
        return {
            // Inicializamos las variables del modal de cita
            mostrarModalCita: false,
            cargandoHorarios: false,
            errorGeneral: null,
            horariosNormales: [],
            horariosUrgencia: [],
            horariosPorVeterinario: {},
            vetAcordeonAbiertoId: null,
            evitarResetHorario: false,

            // Inicializamos el formulario de cita
            formularioCita: {
                titulo: '',
                descripcion: '',
                fecha_hora: '',
                fecha_seleccionada: '',
                tipo: 'normal',
                prestacion_id: '',
                sucursal_id: '',
                veterinario_id: '',
                box_id: '',
                mascota_id: '',
                errors: {},
                processing: false,
            },
        };
    }
}
</script>

<style scoped>
.hover-shadow:hover {
    box-shadow: 0 .25rem .75rem rgba(0,0,0,.05) !important;
}
.transition {
    transition: all 0.2s ease-in-out;
}
.hover-primary:hover {
    color: var(--bs-primary) !important;
}
.border-dashed {
    border-style: dashed !important;
}
/* Para la imagen redonda si la mascota tiene foto */
.object-fit-cover {
    object-fit: cover;
}
/* Hover effect para las tarjetas de cita */
.cita-card {
    transition: box-shadow 0.2s ease-in-out, transform 0.15s ease-in-out;
    cursor: default;
}
.cita-card:hover {
    box-shadow: 0 .35rem 1rem rgba(0,0,0,.08) !important;
    transform: translateY(-1px);
}
</style>