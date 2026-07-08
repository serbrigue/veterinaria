<template>
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
                <div v-if="puedeEditarMascota" class="d-flex gap-2">
                    <button @click="abrirEditar" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1">
                        <i class="bi bi-pencil"></i> Editar
                    </button>
                    <button @click="confirmarEliminar" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1">
                        <i class="bi bi-trash"></i> Eliminar
                    </button>
                </div>
            </div>

            <div class="row g-4">
                
                <div class="col-lg-4 col-md-5">
                    <div class="card border-0 shadow-sm sticky-top" style="top: 2rem;">
                        <div class="card-body text-center py-4">
                            <div v-if="mascota.imagen_url" class="mx-auto mb-3" style="width: 100px; height: 100px;">
                                <img :src="mascota.imagen_url" alt="Foto de la mascota" class="img-fluid rounded-circle object-fit-cover w-100 h-100 border shadow-sm">
                            </div>
                            <div v-else class="mx-auto bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 90px; height: 90px;">
                                <i class="bi bi-heart-pulse-fill fs-1"></i>
                            </div>
                            
                            <h2 class="h5 mb-1 fw-bold text-dark">{{ mascota.nombre }}</h2>
                            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill small mt-2">Paciente Activo</span>
                        </div>
                        
                        <div class="card-body border-top p-4 bg-light bg-opacity-50">
                            <h3 class="h6 text-uppercase text-muted fw-bold mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Información General</h3>
                            
                            <div class="d-flex align-items-start gap-3 mb-4">
                                <div class="bg-white p-2 rounded shadow-sm text-primary">
                                    <i class="bi bi-person-badge fs-5"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-medium mb-1">Dueño (Cliente)</div>
                                        <Link v-if='cliente':href="route('clientes.detalle', cliente.id)" class="text-decoration-none fw-bold text-primary hover-primary">
                                            {{ cliente?.usuario?.name }} <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                                        </Link>
                                        <span v-else class="text-dark fw-medium">No asignado</span>
                                    </div>
                                    
                            </div>

                            <div class="d-flex align-items-start gap-3 mb-4">
                                <div class="bg-white p-2 rounded shadow-sm text-primary">
                                    <i class="bi bi-clipboard2-pulse fs-5"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-medium mb-1">Especie y Raza</div>
                                    <span class="text-dark fw-medium d-block">
                                        {{ especie?.nombre || 'Especie N/A' }} 
                                        <span class="text-muted mx-1">•</span> 
                                        {{ raza?.nombre || 'Raza N/A' }}
                                    </span>
                                </div>
                            </div>

                            <div class="d-flex align-items-start gap-3">
                                <div class="bg-white p-2 rounded shadow-sm text-primary">
                                    <i class="bi bi-gender-ambiguous fs-5"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-medium mb-1">Sexo</div>
                                    <span class="text-dark fw-medium">{{ mascota.sexo || 'No especificado' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top p-3 text-center">
                            <small class="text-muted"><i class="bi bi-clock-history me-1"></i> Registrado el: {{ formatearFecha(mascota.created_at) }}</small>
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
                            <button v-if="puedeCrearCita" @click="abrirModalCita" class="btn btn-sm btn-primary d-flex align-items-center gap-1">
                                <i class="bi bi-plus-lg"></i> Nueva Cita
                            </button>
                        </div>
                        <div class="card-body p-3">
                            <div v-if="!proximasCitas || !proximasCitas.data || proximasCitas.data.length === 0" class="py-4 text-center text-muted">
                                <i class="bi bi-calendar-x fs-1 mb-3 d-block" style="color: #dee2e6;"></i>
                                <p class="mb-0">No hay citas programadas próximamente.</p>
                            </div>
                            <div v-else class="d-flex flex-column gap-3">
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
                            <div v-if="!historialClinico || !historialClinico.data || historialClinico.data.length === 0" class="py-4 text-center text-muted">
                                <i class="bi bi-calendar-x fs-1 mb-3 d-block" style="color: #dee2e6;"></i>
                                <p class="mb-0">No hay historial clínico.</p>
                            </div>
                            <div v-else class="d-flex flex-column gap-3">
                                <h3 class="h6 text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">Historial Clínico</h3>
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
            <ModalCrud
                :visible="mostrarModal"
                :titulo="tituloModal"
                :modo-edicion="modoEdicion"
                :processing="formulario.processing"
                tamanio="lg"
                texto-guardar="Guardar Cambios"
                texto-crear="Registrar Mascota"
                @cerrar="cerrarModal"
                @guardar="guardar"
            >
                <div class="row g-4">
                    <!-- Columna Izquierda: Identificación y Clasificación -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nombre" class="form-label fw-semibold text-secondary small text-uppercase">Nombre de la Mascota</label>
                            <input
                                id="nombre"
                                v-model="formulario.nombre"
                                type="text"
                                class="form-control bg-light border-0 py-2"
                                placeholder="Ej: Garfield"
                                :class="{ 'is-invalid': formulario.errors.nombre }"
                                required
                            />
                            <div v-if="formulario.errors.nombre" class="invalid-feedback">
                                {{ formulario.errors.nombre }}
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="cliente_id" class="form-label fw-semibold text-secondary small text-uppercase">Propietario / Cliente</label>
                            <select
                                id="cliente_id"
                                v-model="formulario.cliente_id"
                                class="form-select bg-light border-0 py-2"
                                :class="{ 'is-invalid': formulario.errors.cliente_id }"
                                required
                            >
                                <option value="" disabled>Seleccione un propietario</option>
                                <option
                                    v-for="cliente in clientes"
                                    :key="cliente.id"
                                    :value="cliente.id"
                                >
                                    {{ cliente.usuario?.name || 'Cliente sin nombre' }}
                                </option>
                            </select>
                            <div v-if="formulario.errors.cliente_id" class="invalid-feedback">
                                {{ formulario.errors.cliente_id }}
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="especie_id" class="form-label fw-semibold text-secondary small text-uppercase">Especie</label>
                            <select
                                id="especie_id"
                                v-model="formulario.especie_id"
                                class="form-select bg-light border-0 py-2"
                                :class="{ 'is-invalid': formulario.errors.especie_id }"
                                required
                                @change="obtenerRazasPorEspecie(formulario.especie_id)"
                            >
                                <option value="" disabled>Seleccione una especie</option>
                                <option
                                    v-for="especie in especies"
                                    :key="especie.id"
                                    :value="especie.id"
                                >
                                    {{ especie.nombre }}
                                </option>
                            </select>
                            <div v-if="formulario.errors.especie_id" class="invalid-feedback">
                                {{ formulario.errors.especie_id }}
                            </div>
                        </div>

                        <div class="mb-3" v-if="formulario.especie_id && razas.length > 0">
                            <label for="raza_id" class="form-label fw-semibold text-secondary small text-uppercase">Raza</label>
                            <select
                                id="raza_id"
                                v-model="formulario.raza_id"
                                class="form-select bg-light border-0 py-2"
                                :class="{ 'is-invalid': formulario.errors.raza_id }"
                                required
                            >
                                <option value="" disabled>Seleccione una raza</option>
                                <option
                                    v-for="raza in razas"
                                    :key="raza.id"
                                    :value="raza.id"
                                >
                                    {{ raza.nombre }}
                                </option>
                            </select>
                            <div v-if="formulario.errors.raza_id" class="invalid-feedback">
                                {{ formulario.errors.raza_id }}
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="sexo" class="form-label fw-semibold text-secondary small text-uppercase">Sexo</label>
                            <select
                                id="sexo"
                                v-model="formulario.sexo"
                                class="form-select bg-light border-0 py-2"
                                :class="{ 'is-invalid': formulario.errors.sexo }"
                                required
                            >
                                <option value="" disabled>Seleccione el sexo</option>
                                <option
                                    v-for="op in opcionesSexo"
                                    :key="op.value"
                                    :value="op.value"
                                >
                                    {{ op.label }}
                                </option>
                            </select>
                            <div v-if="formulario.errors.sexo" class="invalid-feedback">
                                {{ formulario.errors.sexo }}
                            </div>
                        </div>
                    </div>

                    <!-- Columna Derecha: Información Física y Médica -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="fecha_nacimiento" class="form-label fw-semibold text-secondary small text-uppercase">Fecha de Nacimiento</label>
                            <input
                                id="fecha_nacimiento"
                                v-model="formulario.fecha_nacimiento"
                                type="date"
                                class="form-control bg-light border-0 py-2"
                                :class="{ 'is-invalid': formulario.errors.fecha_nacimiento }"
                            />
                            <div v-if="formulario.errors.fecha_nacimiento" class="invalid-feedback">
                                {{ formulario.errors.fecha_nacimiento }}
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="peso_kg" class="form-label fw-semibold text-secondary small text-uppercase">Peso (kg)</label>
                            <input
                                id="peso_kg"
                                v-model="formulario.peso_kg"
                                type="number"
                                step="0.01"
                                min="0"
                                class="form-control bg-light border-0 py-2"
                                placeholder="Ej: 5.40"
                                :class="{ 'is-invalid': formulario.errors.peso_kg }"
                            />
                            <div v-if="formulario.errors.peso_kg" class="invalid-feedback">
                                {{ formulario.errors.peso_kg }}
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="color" class="form-label fw-semibold text-secondary small text-uppercase">Color / Pelaje</label>
                            <input
                                id="color"
                                v-model="formulario.color"
                                type="text"
                                class="form-control bg-light border-0 py-2"
                                placeholder="Ej: Blanco con manchas negras"
                                :class="{ 'is-invalid': formulario.errors.color }"
                            />
                            <div v-if="formulario.errors.color" class="invalid-feedback">
                                {{ formulario.errors.color }}
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="imagen_url" class="form-label fw-semibold text-secondary small text-uppercase">Imagen (URL)</label>
                            <input
                                id="imagen_url"
                                type="text"
                                name="imagen_url"
                                v-model="formulario.imagen_url"
                                class="form-control bg-light border-0 py-2"
                                placeholder="https://ejemplo.com/foto.jpg"
                                :class="{ 'is-invalid': formulario.errors.imagen_url }"
                            />
                            <div v-if="formulario.errors.imagen_url" class="invalid-feedback">
                                {{ formulario.errors.imagen_url }}
                            </div>
                        </div>

                        <div class="mb-3 pt-2">
                            <div class="form-check form-switch card p-3 border-light shadow-sm d-flex flex-row align-items-center justify-content-between bg-light border-0">
                                <div class="ms-1">
                                    <label class="form-check-label fw-semibold text-secondary small text-uppercase" for="esterilizado">Esterilizado / Castrado</label>
                                    <span class="d-block text-muted small mt-1" style="font-size: 0.75rem;">¿Ha sido sometido a cirugía de esterilización?</span>
                                </div>
                                <input
                                    id="esterilizado"
                                    v-model="formulario.esterilizado"
                                    type="checkbox"
                                    class="form-check-input ms-0 float-none"
                                    role="switch"
                                    style="width: 2.8em; height: 1.5em; cursor: pointer;"
                                    :class="{ 'is-invalid': formulario.errors.esterilizado }"
                                />
                            </div>
                            <div v-if="formulario.errors.esterilizado" class="invalid-feedback d-block mt-1">
                                {{ formulario.errors.esterilizado }}
                            </div>
                        </div>
                    </div>

                    <!-- Sección de Ancho Completo al Final: Descripción/Notas Médicas -->
                    <div class="col-12 mt-2">
                        <hr class="text-muted opacity-25 my-3">
                        <div class="mb-2">
                            <label for="descripcion" class="form-label fw-semibold text-secondary small text-uppercase">Descripción / Antecedentes Médicos</label>
                            <textarea
                                id="descripcion"
                                v-model="formulario.descripcion"
                                class="form-control bg-light border-0 py-2"
                                :class="{ 'is-invalid': formulario.errors.descripcion }"
                                rows="3"
                                placeholder="Registra condiciones previas, alergias, comportamiento o detalles relevantes de la mascota."
                                required
                            ></textarea>
                            <div v-if="formulario.errors.descripcion" class="invalid-feedback">
                                {{ formulario.errors.descripcion }}
                            </div>
                        </div>
                    </div>
                </div>
            </ModalCrud>

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
                                            <div v-if="errorGeneral" class="col-12">
                                                <div class="alert alert-danger d-flex align-items-center mb-0 border-0 shadow-sm" role="alert">
                                                    <i class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2"></i>
                                                    <div>{{ errorGeneral }}</div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <label for="titulo" class="form-label fw-semibold text-secondary small text-uppercase">Título</label>
                                                <input id="titulo" v-model="formularioCita.titulo" type="text" class="form-control bg-light border-0 py-2" :class="{ 'is-invalid': formularioCita.errors.titulo }" required placeholder="Ej: Control general" />
                                                <div v-if="formularioCita.errors.titulo" class="invalid-feedback">{{ formularioCita.errors.titulo }}</div>
                                            </div>
                                            <div class="col-12">
                                                <label for="descripcion" class="form-label fw-semibold text-secondary small text-uppercase">Descripción</label>
                                                <textarea id="descripcion" v-model="formularioCita.descripcion" class="form-control bg-light border-0 py-2" :class="{ 'is-invalid': formularioCita.errors.descripcion }" rows="2" required placeholder="Motivo de la cita..."></textarea>
                                                <div v-if="formularioCita.errors.descripcion" class="invalid-feedback">{{ formularioCita.errors.descripcion }}</div>
                                            </div>
                                            <div class="col-12">
                                                <label for="mascota_id" class="form-label fw-semibold text-secondary small text-uppercase">Mascota</label>
                                                <select id="mascota_id" v-model="formularioCita.mascota_id" class="form-select bg-light border-0 py-2" :class="{ 'is-invalid': formularioCita.errors.mascota_id }" required disabled>
                                                    <option :value="mascota.id">
                                                        {{ mascota.nombre }} {{ mascota.sexo ? `(${mascota.sexo})` : '' }}
                                                    </option>
                                                </select>
                                                <div v-if="formularioCita.errors.mascota_id" class="invalid-feedback">{{ formularioCita.errors.mascota_id }}</div>
                                            </div>
                                            <div class="col-12">
                                                <label for="prestacion_id" class="form-label fw-semibold text-secondary small text-uppercase">Prestación o Servicio</label>
                                                <select id="prestacion_id" v-model="formularioCita.prestacion_id" class="form-select bg-light border-0 py-2" :class="{ 'is-invalid': formularioCita.errors.prestacion_id }" required>
                                                    <option value="" disabled>Selecciona una prestación</option>
                                                    <option v-for="prestacion in prestaciones" :key="prestacion.id" :value="prestacion.id">
                                                        {{ prestacion.nombre }} ({{ prestacion.sucursal?.nombre }})
                                                    </option>
                                                </select>
                                                <div v-if="formularioCita.errors.prestacion_id" class="invalid-feedback">{{ formularioCita.errors.prestacion_id }}</div>
                                            </div>

                                            <div v-if="formularioCita.prestacion_id" class="col-12">
                                                <label class="form-label fw-semibold text-secondary small text-uppercase">Sucursal</label>
                                                <select id="sucursal_id" v-model="formularioCita.sucursal_id" class="form-select bg-light border-0 py-2" :class="{ 'is-invalid': formularioCita.errors.sucursal_id }" required disabled>
                                                    <option value="" disabled>Selecciona una sucursal</option>
                                                    <option v-for="sucursal in sucursalesFiltradas" :key="sucursal.id" :value="sucursal.id">
                                                        {{ sucursal.nombre }}
                                                    </option>
                                                </select>
                                                <div v-if="formularioCita.errors.sucursal_id" class="invalid-feedback">{{ formularioCita.errors.sucursal_id }}</div>
                                            </div>

                                            <template v-if="formularioCita.sucursal_id">
                                                <div class="col-12">
                                                    <label class="form-label fw-semibold text-secondary small text-uppercase">Veterinario (Aptos)</label>
                                                    <select id="veterinario_id" v-model="formularioCita.veterinario_id" class="form-select bg-light border-0 py-2" :class="{ 'is-invalid': formularioCita.errors.veterinario_id }">
                                                        <option value="">Cualquier veterinario (opcional)</option>
                                                        <option v-for="vet in veterinariosFiltrados" :key="vet.id" :value="vet.id">
                                                            {{ vet.usuario?.name }}
                                                        </option>
                                                    </select>
                                                    <div v-if="formularioCita.errors.veterinario_id" class="invalid-feedback">{{ formularioCita.errors.veterinario_id }}</div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Columna derecha: fecha y horarios -->
                                    <div class="col-md-7 p-3 bg-light bg-opacity-50">
                                        <template v-if="formularioCita.sucursal_id">
                                            <div class="mb-3">
                                                <label for="fecha_seleccionada" class="form-label fw-semibold text-secondary small text-uppercase">Fecha</label>
                                                <input
                                                    id="fecha_seleccionada"
                                                    type="date"
                                                    v-model="formularioCita.fecha_seleccionada"
                                                    class="form-control bg-white border-0 py-2 shadow-sm"
                                                    :class="{ 'is-invalid': formularioCita.errors.fecha_hora }"
                                                    :min="hoy"
                                                    @change="cargarHorarios"
                                                />
                                                <div v-if="formularioCita.errors.fecha_hora" class="invalid-feedback">{{ formularioCita.errors.fecha_hora }}</div>
                                            </div>

                                            <div v-if="cargandoHorarios" class="text-center py-4">
                                                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                                <span class="ms-2 text-muted small">Consultando disponibilidad...</span>
                                            </div>

                                            <template v-else-if="formularioCita.fecha_seleccionada">

                                                <!-- CASO 1: Veterinario seleccionado -->
                                                <template v-if="formularioCita.veterinario_id">
                                                    <!-- Horarios normales -->
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold text-secondary small text-uppercase">Horarios disponibles</label>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            <template v-for="slot in horariosNormales" :key="slot.hora">
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
                                                            <template v-for="slot in horariosUrgencia" :key="slot.hora">
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
                                                            <div v-if="horariosUrgencia.filter(s => s.disponible).length === 0" class="text-muted small">
                                                                No hay horarios de urgencia disponibles.
                                                            </div>
                                                        </div>
                                                        <small class="text-muted mt-2 d-block">Las atenciones fuera de horario tienen un costo adicional.</small>
                                                    </div>
                                                </template>

                                                <!-- CASO 2: Veterinario no seleccionado (Cualquier veterinario) -> Acordeón -->
                                                <template v-else>
                                                    <div class="mb-2 text-secondary small fw-semibold text-uppercase">Selecciona un veterinario y horario</div>

                                                    <div class="accordion border border-light rounded-3 overflow-hidden shadow-sm" id="vetSchedulesAccordion">
                                                        <div v-for="vet in veterinariosFiltrados" :key="vet.id" class="accordion-item border-0 border-bottom border-light">
                                                            <h4 class="accordion-header mb-0">
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
                                                                        <i class="bi ms-1" :class="vetAcordeonAbiertoId === vet.id ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                                                                    </span>
                                                                </button>
                                                            </h4>
                                                            <div 
                                                                v-if="vetAcordeonAbiertoId === vet.id" 
                                                                class="accordion-body p-4 bg-light bg-opacity-25"
                                                            >
                                                                <!-- Horarios del veterinario -->
                                                                <div v-if="horariosPorVeterinario[vet.id]">
                                                                    <!-- Horarios normales -->
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-semibold text-secondary small text-uppercase">Horarios disponibles</label>
                                                                        <div class="d-flex flex-wrap gap-2">
                                                                            <template v-for="slot in horariosPorVeterinario[vet.id].normal" :key="slot.hora">
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
                                                                            <template v-for="slot in horariosPorVeterinario[vet.id].urgencia" :key="slot.hora">
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
import AuthenticatedLayout from '@/Disenos/LayoutAutenticado.vue';
import { Head, Link } from '@inertiajs/vue3';
import ModalCrud from '@/Componentes/ModalCrud.vue';
import Paginador from '@/Componentes/Paginador.vue';

export default {
    name: 'MascotaDetalle',
    components: {
        AuthenticatedLayout,
        Head,
        Link,
        ModalCrud,
        Paginador
    },
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
    computed: {
        tituloModal() {
            return this.modoEdicion ? 'Editar Mascota' : 'Nueva Mascota'
        },
        hoy() {
            return new Date().toISOString().split('T')[0];
        },
        sucursalesFiltradas() {
            if (!this.formularioCita.prestacion_id) return [];
            const prestacion = this.prestaciones.find(p => p.id === this.formularioCita.prestacion_id);
            if (!prestacion) return [];
            return this.sucursales.filter(s => s.id === prestacion.sucursal_id);
        },
        veterinariosFiltrados() {
            if (!this.formularioCita.sucursal_id || !this.formularioCita.prestacion_id) return [];
            const sucursal = this.sucursales.find(s => s.id === this.formularioCita.sucursal_id);
            if (!sucursal) return [];
            const prestacion = this.prestaciones.find(p => p.id === this.formularioCita.prestacion_id);
            
            return sucursal.veterinarios.filter(vet => {
                if (!prestacion.especialidad_id) return true;
                return vet.especialidad_id === prestacion.especialidad_id;
            });
        },
        puedeEditarMascota() {
            const user = this.$page.props.auth.user;
            if (!user || !user.rol) return false;

            if (this.$isAdmin() || this.$isSecretaria()) {
                return true;
            }

            if (this.$isCliente()) {
                return this.mascota.cliente_id === user.cliente?.id;
            }

            return false;
        },
        puedeCrearCita() {
            const user = this.$page.props.auth.user;
            if (!user || !user.rol) return false;

            if (this.$isAdmin() || this.$isSecretaria()) {
                return true;
            }

            if (this.$isCliente()) {
                return this.mascota.cliente_id === user.cliente?.id;
            }

            return false;
        },
    },
    watch: {
        'formularioCita.prestacion_id'(newVal, oldVal) {
            if (newVal) {
                const prestacion = this.prestaciones.find(p => p.id === newVal);
                if (prestacion && this.formularioCita.sucursal_id !== prestacion.sucursal_id) {
                    this.formularioCita.sucursal_id = prestacion.sucursal_id;
                }
                if (oldVal && newVal !== oldVal) {
                    this.formularioCita.box_id = '';
                    this.formularioCita.veterinario_id = '';
                    this.formularioCita.fecha_seleccionada = '';
                    this.formularioCita.fecha_hora = '';
                }
            } else {
                this.formularioCita.sucursal_id = '';
                this.formularioCita.box_id = '';
                this.formularioCita.veterinario_id = '';
            }
        },
        'formularioCita.sucursal_id'(newVal, oldVal) {
            if (oldVal && newVal !== oldVal) {
                this.formularioCita.veterinario_id = '';
                this.formularioCita.box_id = '';
            }
        },
        'formularioCita.veterinario_id'(newVal, oldVal) { 
            if (oldVal && newVal !== oldVal && !this.evitarResetHorario) {
                this.formularioCita.fecha_seleccionada = '';
                this.formularioCita.fecha_hora = '';
            }
            this.cargarHorarios(); 
        },
    },
    methods: {
        abrirModalCita() {
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
        abrirEditar() {
            this.modoEdicion = true;
            this.mascotaEditando = this.mascota;
            this.formulario.nombre = this.mascota.nombre;
            this.formulario.descripcion = this.mascota.descripcion;
            this.formulario.sexo = this.mascota.sexo == 'Hembra' ? 'hembra' : 'macho';
            this.formulario.fecha_nacimiento = this.$fechaInput(this.mascota.fecha_nacimiento);
            this.formulario.especie_id = this.mascota.raza?.especie_id || '';
            this.formulario.raza_id = this.mascota.raza_id ?? '';
            this.formulario.cliente_id = this.mascota.cliente_id ?? '';
            this.formulario.imagen_url = this.mascota.imagen_url ?? '';
            this.formulario.peso_kg = this.mascota.peso_kg ?? '';
            this.formulario.color = this.mascota.color ?? '';
            this.formulario.esterilizado = !!this.mascota.esterilizado;
            this.formulario.errors = {};
            
            this.obtenerEspecies();
            this.obtenerClientes();
            if (this.formulario.especie_id) {
                this.obtenerRazasPorEspecie(this.formulario.especie_id);
            }
            this.mostrarModal = true;
        },
        cerrarModal() {
            this.mostrarModal = false;
            this.mascotaEditando = null;
        },
        obtenerEspecies() {
            axios.get('/especies')
                .then((response) => {
                    this.especies = response.data.especies;
                });
        },
        obtenerRazasPorEspecie(especieId) {
            if (!especieId) {
                this.razas = [];
                return;
            }
            axios.get(`/razas`, {
                params: {
                    especie_id: especieId
                }
            })
                .then((response) => {
                    this.razas = response.data.razas;
                });
        },
        obtenerClientes() {
            const user = this.$page.props.auth.user;
            if (this.$isAdmin() || this.$isVeterinario() || this.$isSecretaria()) {
                axios.get('/api/clientes')
                    .then((response) => {
                        this.clientes = response.data.clientes || response.data;
                    });
            } else {
                this.clientes = [{
                    id: this.mascota.cliente_id,
                    usuario: {
                        name: this.cliente?.name || 'Propietario'
                    }
                }];
            }
        },
        datosFormulario() {
            return {
                nombre: this.formulario.nombre,
                descripcion: this.formulario.descripcion,
                sexo: this.formulario.sexo,
                fecha_nacimiento: this.formulario.fecha_nacimiento || null,
                raza_id: this.formulario.raza_id,
                cliente_id: this.formulario.cliente_id,
                imagen_url: this.formulario.imagen_url,
                peso_kg: this.formulario.peso_kg === '' ? null : this.formulario.peso_kg,
                color: this.formulario.color || null,
                esterilizado: this.formulario.esterilizado,
            };
        },
        guardar() {
            this.formulario.processing = true;
            this.formulario.errors = {};

            axios.put(`/api/mascotas/${this.mascotaEditando.id}`, this.datosFormulario())
                .then(() => {
                    this.cerrarModal();
                    return this.$alertaExito('Mascota actualizada', 'Los cambios se guardaron correctamente.');
                })
                .then(() => {
                    this.$inertia.reload();
                })
                .catch((error) => {
                    if (error.response?.status === 422) {
                        this.formulario.errors = error.response.data.errors;
                        this.$alertaValidacion(error.response.data.errors);
                    } else {
                        this.$alertaError('Error', 'No se pudo guardar la mascota.');
                    }
                })
                .finally(() => {
                    this.formulario.processing = false;
                });
        },
        confirmarEliminar() {
            this.$confirmar('¿Eliminar mascota?', `Se eliminará a ${this.mascota.nombre}.`)
                .then((resultado) => {
                    if (!resultado.isConfirmed) return;
                    axios.delete(`/api/mascotas/${this.mascota.id}`)
                        .then(() => {
                            this.$alertaExito('Eliminada', `${this.mascota.nombre} fue eliminada.`);
                            this.$inertia.visit(route('mascotas.listado'));
                        })
                        .catch(() => this.$alertaError('Error', 'No se pudo eliminar la mascota.'));
                });
        },
        formatearFecha(fecha) {
            if (!fecha) return 'Sin fecha';
            const date = new Date(fecha);
            return date.toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        },
        cerrarModalCita() {
            this.mostrarModalCita = false;
            this.formularioCita.errors = {};
            this.errorGeneral = null;
        },
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

            axios.post('/api/citas', datos)
                .then(() => {
                    this.cerrarModalCita();
                    this.$alertaExito('Cita Agendada', 'La cita se ha registrado con éxito.');
                    this.$inertia.reload();
                })
                .catch((error) => {
                    if (error.response?.status === 422) {
                        this.formularioCita.errors = error.response.data.errors;
                        this.$alertaValidacion(error.response.data.errors);
                    } else if (error.response?.status === 409) {
                        this.errorGeneral = error.response.data.error || error.response.data.message;
                        this.$alertaError('Conflicto', this.errorGeneral);
                    } else {
                        this.$alertaError('Error', 'No se pudo crear la cita.');
                    }
                })
                .finally(() => {
                    this.formularioCita.processing = false;
                });
        },
        cambiarPagina(url) {
            if (!url) return;
            this.$inertia.visit(url, {
                preserveState: true,
                preserveScroll: true
            });
        },
        cargarHorarios() {
            if (!this.formularioCita.fecha_seleccionada) return;

            this.cargandoHorarios = true;
            if (!this.evitarResetHorario) {
                this.formularioCita.fecha_hora = '';
                this.formularioCita.tipo = 'normal';
            }

            if (this.formularioCita.veterinario_id) {
                axios.get('/api/citas/horarios-disponibles', {
                    params: {
                        fecha:           this.formularioCita.fecha_seleccionada,
                        veterinario_id:  this.formularioCita.veterinario_id,
                    }
                }).then(r => {
                    this.horariosNormales  = r.data.normal;
                    this.horariosUrgencia  = r.data.urgencia;
                }).catch(error => {
                    console.error('Error al cargar horarios:', error);
                }).finally(() => {
                    this.cargandoHorarios = false;
                });
            } else {
                this.horariosPorVeterinario = {};
                
                const promesas = this.veterinariosFiltrados.map(vet => {
                    return axios.get('/api/citas/horarios-disponibles', {
                        params: {
                            fecha:           this.formularioCita.fecha_seleccionada,
                            veterinario_id:  vet.id,
                        }
                    }).then(r => {
                        this.horariosPorVeterinario[vet.id] = {
                            veterinario: vet,
                            normal: r.data.normal,
                            urgencia: r.data.urgencia
                        };
                    }).catch(error => {
                        console.error(`Error al cargar horarios para veterinario ${vet.id}:`, error);
                    });
                });

                Promise.all(promesas).finally(() => {
                    this.cargandoHorarios = false;
                    if (this.veterinariosFiltrados.length > 0 && !this.vetAcordeonAbiertoId) {
                        this.vetAcordeonAbiertoId = this.veterinariosFiltrados[0].id;
                    }
                });
            }
        },
        toggleAcordeon(vetId) {
            this.vetAcordeonAbiertoId = this.vetAcordeonAbiertoId === vetId ? null : vetId;
        },
        obtenerCantSlotsDisponibles(vetId) {
            const data = this.horariosPorVeterinario[vetId];
            if (!data) return 0;
            const normales = data.normal?.filter(s => s.disponible).length || 0;
            const urgencias = data.urgencia?.filter(s => s.disponible).length || 0;
            return normales + urgencias;
        },
        seleccionarHorarioAcordeon(slot, vetId) {
            this.evitarResetHorario = true;
            this.formularioCita.veterinario_id = vetId;
            this.formularioCita.fecha_hora = slot.fecha_hora;
            this.formularioCita.tipo       = slot.tipo;
            setTimeout(() => {
                this.evitarResetHorario = false;
            }, 100);
        },
        seleccionarHorario(slot) {
            this.formularioCita.fecha_hora = slot.fecha_hora;
            this.formularioCita.tipo       = slot.tipo;
        },
    },
    data() {
        return {
            mostrarModal: false,
            modoEdicion: false,
            mascotaEditando: null,
            especies: [],
            razas: [],
            clientes: [],
            opcionesSexo: [
                { value: 'macho', label: 'Macho' },
                { value: 'hembra', label: 'Hembra' },
            ],
            formulario: {
                nombre: '',
                descripcion: '',
                sexo: '',
                fecha_nacimiento: '',
                especie_id: '',
                raza_id: '',
                cliente_id: '',
                imagen_url: '',
                peso_kg: '',
                color: '',
                esterilizado: false,
                errors: {},
                processing: false,
            },
            // Cita variables
            mostrarModalCita: false,
            cargandoHorarios: false,
            errorGeneral: null,
            horariosNormales: [],
            horariosUrgencia: [],
            horariosPorVeterinario: {},
            vetAcordeonAbiertoId: null,
            evitarResetHorario: false,
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