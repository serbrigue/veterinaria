<template>
    <Head title="Citas" />
    <AuthenticatedLayout>
        <div class="container py-4">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h1 class="h5 mb-0">Mis Citas</h1>

                    <button v-if="esCliente()" type="button" class="btn btn-primary" @click="abrirModalCrear">
                        + Nueva Cita
                    </button>
                </div>

                <div class="card-body">
                    <!-- Barra de búsqueda y filtros -->
                    <div class="bg-light p-3 rounded-3 border mb-4 shadow-sm">
                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label small fw-bold text-secondary mb-1" for="filtroTitulo">Buscar por Título</label>
                                <input type="text" class="form-control form-control-sm" id="filtroTitulo" placeholder="Buscar por título" v-model="filtroTitulo" @keyup.enter="obtenerCitas">
                            </div>
                            <!-- Buscar por Sucursal -->
                            <div class="col-12 col-md-4 col-lg-2">
                                <label class="form-label small fw-bold text-secondary mb-1" for="filtroSucursal">Sucursal</label>
                                <select 
                                    class="form-select form-select-sm" 
                                    id="filtroSucursal"
                                    v-model="filtroSucursal"
                                    @change="obtenerCitas()"
                                >
                                    <option value="">Todas</option>
                                    <option 
                                        v-for="sucursal in sucursales" 
                                        :key="sucursal.id" 
                                        :value="sucursal.id"
                                    >
                                        {{ sucursal.nombre }}
                                    </option>
                                </select>
                            </div>
                            <!-- Buscar por Mascota -->
                            <div class="col-12 col-md-4 col-lg-2">
                                <label class="form-label small fw-bold text-secondary mb-1" for="filtroMascota">Buscar por Mascota</label>
                                <select 
                                    class="form-select form-select-sm" 
                                    id="filtroMascota"
                                    v-model="filtroMascota"
                                    @change="obtenerCitas()"
                                >
                                    <option value="">Todas las mascotas</option>
                                    <option 
                                        v-for="mascota in mascotas" 
                                        :key="mascota.id" 
                                        :value="mascota.id"
                                    >
                                        {{ mascota.nombre }}
                                    </option>
                                </select>
                            </div>
                            <!-- Buscar por Veterinario -->
                            <div class="col-12 col-md-4 col-lg-2">
                                <label class="form-label small fw-bold text-secondary mb-1" for="filtroVeterinario">Buscar por Veterinario</label>
                                <select 
                                    class="form-select form-select-sm"
                                    id="filtroVeterinario"
                                    v-model="filtroVeterinario"
                                    @change="obtenerCitas()"
                                >
                                    <option value="">Todos los veterinarios</option>
                                    <option 
                                        v-for="veterinario in veterinarios" 
                                        :key="veterinario.id" 
                                        :value="veterinario.id"
                                    >
                                        {{ veterinario.nombre }}
                                    </option>
                                </select>
                            </div>

                            <!-- Buscar por Estado -->
                            <div class="col-12 col-md-4 col-lg-1">
                                <label class="form-label small fw-bold text-secondary mb-1" for="filtroEstado">Estado</label>
                                <select 
                                    class="form-select form-select-sm"
                                    id="filtroEstado"
                                    v-model="filtroEstado"
                                    @change="obtenerCitas()"
                                >
                                    <option value="">Todos</option>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="en_curso">En curso</option>
                                    <option value="completada">Completada</option>
                                    <option value="cancelada">Cancelada</option>
                                </select>
                            </div>

                            <!-- Limpiar Filtros -->
                            <div class="col-12 col-lg-2 d-flex gap-2 justify-content-lg-end">
                                <button class="btn btn-outline-secondary btn-sm w-100" @click="limpiarFiltros()">
                                    Limpiar
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-if="cargando" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted">Cargando citas...</p>
                    </div>

                    <div v-else-if="citas.length === 0" class="text-center py-5">
                        <p class="text-muted mb-3">No tienes citas registradas aún.</p>
                        <button type="button" class="btn btn-primary" @click="abrirModalCrear">
                            Registrar tu primera cita
                        </button>
                    </div>

                    <div v-else class="table-responsive">
                        <table class="table table-hover align-middle border">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">Detalle de la Cita</th>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Paciente y Propietario</th>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Atención</th>
                                    <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-7" style="width: 150px">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="cita in citas" :key="cita.id">
                                    <td class="ps-3">
                                        <div class="d-flex flex-column">
                                            <Link :href="route('citas.detalle', cita.id)" class="text-dark fw-bold text-decoration-none mb-1">
                                                {{ cita.titulo }}
                                            </Link>
                                            <span class="text-muted small mb-1">
                                                <i class="bi bi-calendar-event me-1"></i> {{ $formatoFecha(cita.fecha_hora, 'DD/MM/YYYY HH:mm') }}
                                                <span v-if="cita.hora_termino" class="ms-1">- {{ $formatoFecha(cita.hora_termino, 'HH:mm') }}</span>
                                            </span>
                                            <div>
                                                <span class="badge" :class="{
                                                    'bg-warning text-dark': cita.estado === 'pendiente',
                                                    'bg-success': cita.estado === 'completada',
                                                    'bg-danger': cita.estado === 'cancelada',
                                                    'bg-primary': cita.estado === 'en_curso'
                                                }">
                                                    {{ cita.estado ? cita.estado.charAt(0).toUpperCase() + cita.estado.slice(1) : 'Pendiente' }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold text-dark">{{ cita.mascota?.nombre || 'Sin Mascota' }} <span class="text-muted fw-normal small" v-if="cita.mascota?.edad_texto">({{ cita.mascota.edad_texto }})</span></span>
                                            <span class="text-muted small"><i class="bi bi-person me-1"></i> {{ cita.cliente?.nombre || 'Sin Propietario' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-medium text-dark"><i class="bi bi-heart-pulse text-danger me-1"></i> {{ cita.veterinario?.nombre || 'Sin Asignar' }}</span>
                                            <span class="text-muted small"><i class="bi bi-door-open me-1"></i> {{ cita.box?.nombre || 'Sin Box' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2 align-items-center">
                                            <template v-if="cita.estado === 'completada'">
                                                <div v-if="cita.transaccion" class="d-flex flex-column align-items-center gap-1">
                                                    <span class="fw-bold text-success small">Total: ${{ Math.round(cita.transaccion.monto_total).toLocaleString('es-CL') }}</span>
                                                    <button v-if="cita.transaccion.estado === 'pagado'" class="btn btn-outline-primary btn-sm rounded-pill px-2 py-0 shadow-sm" style="font-size: 0.75rem" @click.stop="verComprobante(cita.transaccion, cita)">
                                                        <i class="bi bi-receipt me-1"></i> Comprobante
                                                    </button>
                                                </div>
                                                <div v-else class="text-muted small fw-medium">
                                                    Sin registro de pago
                                                </div>
                                            </template>
                                            <template v-else-if="cita.estado === 'pendiente'">
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-primary rounded-pill px-3 transition-all hover-opacity"
                                                    @click="abrirModalEditar(cita)"
                                                >
                                                    Editar
                                                </button>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-warning rounded-pill px-3 transition-all hover-opacity"
                                                    @click="confirmarCancelar(cita)"
                                                >
                                                    <i class="bi bi-x-circle me-1"></i> Cancelar
                                                </button>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Controles de Paginación -->
                    <div v-if="citasData && citasData.last_page > 1" class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted small">
                            Mostrando {{ citasData.from }} a {{ citasData.to }} de {{ citasData.total }} citas
                        </div>
                        <nav aria-label="Navegación de páginas">
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item" :class="{ disabled: !citasData.prev_page_url }">
                                    <button class="page-link" @click.prevent="obtenerCitas(citasData.prev_page_url)">Anterior</button>
                                </li>
                                <li 
                                    v-for="link in citasData.links.slice(1, -1)" 
                                    :key="link.label" 
                                    class="page-item" 
                                    :class="{ active: link.active }"
                                >
                                    <button class="page-link" @click.prevent="obtenerCitas(link.url)" v-html="link.label"></button>
                                </li>
                                <li class="page-item" :class="{ disabled: !citasData.next_page_url }">
                                    <button class="page-link" @click.prevent="obtenerCitas(citasData.next_page_url)">Siguiente</button>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- MODAL COMPROBANTE DE PAGO -->
            <div v-if="mostrarModalComprobante && transaccionSeleccionada" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); z-index: 1055;">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow rounded-4">
                        <div class="modal-header bg-light border-bottom-0 rounded-top-4 p-4">
                            <h5 class="modal-title fw-bold text-dark"><i class="bi bi-receipt me-2 text-primary"></i> Comprobante de Pago</h5>
                            <button type="button" class="btn-close" @click="mostrarModalComprobante = false"></button>
                        </div>
                        <div class="modal-body p-4" id="comprobante-imprimir">
                            <div class="text-center mb-4">
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                                <h4 class="mt-2 fw-bold text-success">¡Pago Exitoso!</h4>
                                <p class="text-muted mb-0">Comprobante #{{ transaccionSeleccionada.id.toString().padStart(6, '0') }}</p>
                            </div>
                            
                            <div class="card bg-light border-0 rounded-4">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted small">Fecha de pago:</span>
                                        <span class="fw-medium text-dark">{{ formatearFecha(transaccionSeleccionada.fecha_pago) }} {{ formatearHora(transaccionSeleccionada.fecha_pago) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted small">Cliente:</span>
                                        <span class="fw-medium text-dark">{{ citaSeleccionadaParaComprobante?.cliente?.nombre || citaSeleccionadaParaComprobante?.mascota?.cliente?.nombre || 'Desconocido' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted small">Paciente:</span>
                                        <span class="fw-medium text-dark">{{ citaSeleccionadaParaComprobante?.mascota?.nombre || 'N/A' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted small">Método de pago:</span>
                                        <span class="fw-medium text-dark">{{ formatearMetodo(transaccionSeleccionada.metodo_pago) }}</span>
                                    </div>
                                    <hr class="border-secondary opacity-25">
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <span class="text-uppercase fw-bold text-muted small">Total Pagado</span>
                                        <span class="fs-4 fw-bold text-success">${{ Math.round(transaccionSeleccionada.monto_pagado).toLocaleString('es-CL') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mt-4">
                                <small class="text-muted">Gracias por confiar en nuestra clínica veterinaria.</small>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 p-4">
                            <button type="button" class="btn btn-secondary rounded-pill px-4" @click="mostrarModalComprobante = false">Cerrar</button>
                            <button type="button" class="btn btn-primary rounded-pill px-4" @click="imprimirComprobante"><i class="bi bi-printer me-2"></i>Imprimir</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- MODAL: Crear / Editar Cita                 -->
            <!-- ========================================== -->
            <div v-if="mostrarModal" class="modal fade show d-block" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ modoEdicion ? 'Editar Cita' : 'Nueva Cita' }}</h5>
                            <button type="button" class="btn-close" @click="cerrarModal"></button>
                        </div>
                        <div>
                            <div class="modal-body">
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
                                                <input id="titulo" v-model="formulario.titulo" type="text" class="form-control bg-light border-0 py-2" :class="{ 'is-invalid': formulario.errors.titulo }" required placeholder="Ej: Control general" />
                                                <div v-if="formulario.errors.titulo" class="invalid-feedback">{{ formulario.errors.titulo }}</div>
                                            </div>
                                            <div class="col-12">
                                                <label for="descripcion" class="form-label fw-semibold text-secondary small text-uppercase">Descripción</label>
                                                <textarea id="descripcion" v-model="formulario.descripcion" class="form-control bg-light border-0 py-2" :class="{ 'is-invalid': formulario.errors.descripcion }" rows="2" required placeholder="Motivo de la cita..."></textarea>
                                                <div v-if="formulario.errors.descripcion" class="invalid-feedback">{{ formulario.errors.descripcion }}</div>
                                            </div>
                                            <div class="col-12">
                                                <label for="mascota_id" class="form-label fw-semibold text-secondary small text-uppercase">Mascota</label>
                                                <select id="mascota_id" v-model="formulario.mascota_id" class="form-select bg-light border-0 py-2" :class="{ 'is-invalid': formulario.errors.mascota_id }" required>
                                                    <option value="" disabled>Selecciona una mascota</option>
                                                    <option v-for="mascota in mascotas" :key="mascota.id" :value="mascota.id">
                                                        {{ mascota.nombre }} {{ mascota.sexo ? `(${mascota.sexo})` : '' }}
                                                    </option>
                                                </select>
                                                <div v-if="formulario.errors.mascota_id" class="invalid-feedback">{{ formulario.errors.mascota_id }}</div>
                                            </div>
                                            <div v-if="formulario.mascota_id" class="col-12">
                                                <label for="prestacion_id" class="form-label fw-semibold text-secondary small text-uppercase">Prestación o Servicio</label>
                                                <select id="prestacion_id" v-model="formulario.prestacion_id" class="form-select bg-light border-0 py-2" :class="{ 'is-invalid': formulario.errors.prestacion_id }" required>
                                                    <option value="" disabled>Selecciona una prestación</option>
                                                    <option v-for="prestacion in prestaciones" :key="prestacion.id" :value="prestacion.id">
                                                        {{ prestacion.nombre }} ({{ prestacion.sucursal?.nombre }})
                                                    </option>
                                                </select>
                                                <div v-if="formulario.errors.prestacion_id" class="invalid-feedback">{{ formulario.errors.prestacion_id }}</div>
                                            </div>

                                            <div v-if="formulario.prestacion_id" class="col-12">
                                                <label class="form-label fw-semibold text-secondary small text-uppercase">Sucursal</label>
                                                <select id="sucursal_id" v-model="formulario.sucursal_id" class="form-select bg-light border-0 py-2" :class="{ 'is-invalid': formulario.errors.sucursal_id }" required disabled>
                                                    <option value="" disabled>Selecciona una sucursal</option>
                                                    <option v-for="sucursal in sucursalesFiltradas" :key="sucursal.id" :value="sucursal.id">
                                                        {{ sucursal.nombre }}
                                                    </option>
                                                </select>
                                                <div v-if="formulario.errors.sucursal_id" class="invalid-feedback">{{ formulario.errors.sucursal_id }}</div>
                                            </div>

                                            <template v-if="formulario.sucursal_id">
                                                <div class="col-12">
                                                    <label class="form-label fw-semibold text-secondary small text-uppercase">Veterinario (Aptos)</label>
                                                    <select id="veterinario_id" v-model="formulario.veterinario_id" class="form-select bg-light border-0 py-2" :class="{ 'is-invalid': formulario.errors.veterinario_id }" required>
                                                        <option value="" disabled>Selecciona un veterinario</option>
                                                        <option v-for="vet in veterinariosFiltrados" :key="vet.id" :value="vet.id">
                                                            {{ vet.usuario.name }}
                                                        </option>
                                                    </select>
                                                    <div v-if="formulario.errors.veterinario_id" class="invalid-feedback">{{ formulario.errors.veterinario_id }}</div>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label fw-semibold text-secondary small text-uppercase">Box Disponible</label>
                                                    <select id="box_id" v-model="formulario.box_id" class="form-select bg-light border-0 py-2" :class="{ 'is-invalid': formulario.errors.box_id }" required>
                                                        <option value="" disabled>Selecciona un box</option>
                                                        <option v-for="box in boxesFiltrados" :key="box.id" :value="box.id">
                                                            {{ box.nombre }}
                                                        </option>
                                                    </select>
                                                    <div v-if="formulario.errors.box_id" class="invalid-feedback">{{ formulario.errors.box_id }}</div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Columna derecha: fecha y horarios -->
                                    <div class="col-md-7 p-3 bg-light bg-opacity-50">
                                        <template v-if="formulario.veterinario_id && formulario.box_id">
                                            <div class="mb-3">
                                                <label for="fecha_seleccionada" class="form-label fw-semibold text-secondary small text-uppercase">Fecha</label>
                                                <input
                                                    id="fecha_seleccionada"
                                                    type="date"
                                                    v-model="formulario.fecha_seleccionada"
                                                    class="form-control bg-white border-0 py-2 shadow-sm"
                                                    :class="{ 'is-invalid': formulario.errors.fecha_hora }"
                                                    :min="hoy"
                                                    @change="cargarHorarios"
                                                />
                                                <div v-if="formulario.errors.fecha_hora" class="invalid-feedback">{{ formulario.errors.fecha_hora }}</div>
                                            </div>

                                            <div v-if="cargandoHorarios" class="text-center py-4">
                                                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                                <span class="ms-2 text-muted small">Consultando disponibilidad...</span>
                                            </div>

                                            <template v-else-if="formulario.fecha_seleccionada">
                                                <!-- Horarios normales -->
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold text-secondary small text-uppercase">Horarios disponibles</label>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <button
                                                            v-for="slot in horariosNormales"
                                                            :key="slot.hora"
                                                            type="button"
                                                            :disabled="!slot.disponible"
                                                            :class="[
                                                                'btn btn-sm rounded-pill px-3',
                                                                formulario.fecha_hora === slot.fecha_hora
                                                                    ? 'btn-primary'
                                                                    : slot.disponible
                                                                        ? 'btn-outline-primary'
                                                                        : 'btn-outline-secondary opacity-50'
                                                            ]"
                                                            @click="seleccionarHorario(slot)"
                                                        >
                                                            {{ slot.hora }}
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- Horarios de urgencia -->
                                                <div>
                                                    <label class="form-label fw-semibold text-warning small text-uppercase">
                                                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                                        Urgencia (fuera de horario)
                                                    </label>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <button
                                                            v-for="slot in horariosUrgencia"
                                                            :key="slot.hora"
                                                            type="button"
                                                            :disabled="!slot.disponible"
                                                            :class="[
                                                                'btn btn-sm rounded-pill px-3',
                                                                formulario.fecha_hora === slot.fecha_hora
                                                                    ? 'btn-warning text-dark'
                                                                    : slot.disponible
                                                                        ? 'btn-outline-warning'
                                                                        : 'btn-outline-secondary opacity-50'
                                                            ]"
                                                            @click="seleccionarHorario(slot)"
                                                        >
                                                            {{ slot.hora }}
                                                        </button>
                                                    </div>
                                                    <small class="text-muted mt-2 d-block">Las atenciones fuera de horario tienen un costo adicional.</small>
                                                </div>
                                            </template>

                                            <!-- Placeholder cuando aún no se elige fecha -->
                                            <div v-else class="text-center text-muted py-5">
                                                <i class="bi bi-calendar2-week fs-1 d-block mb-2" style="color: #dee2e6;"></i>
                                                <small>Selecciona una fecha para ver los horarios disponibles</small>
                                            </div>
                                        </template>

                                        <!-- Placeholder cuando aún no se elige vet + box -->
                                        <div v-else class="text-center text-muted py-5">
                                            <i class="bi bi-clock-history fs-1 d-block mb-2" style="color: #dee2e6;"></i>
                                            <small>Elige un veterinario y un box para ver la disponibilidad</small>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" @click="cerrarModal">
                                    Cancelar
                                </button>
                                <button type="button" class="btn btn-primary" :disabled="formulario.processing" @click="guardar">
                                    <span v-if="formulario.processing" class="spinner-border spinner-border-sm me-2"></span>
                                    {{ modoEdicion ? 'Guardar cambios' : 'Crear cita' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="mostrarModal" class="modal-backdrop fade show"></div>

            <!-- ========================================== -->
            <!-- MODAL: Confirmar Eliminación                -->
            <!-- ========================================== -->
            <div v-if="mostrarConfirmacion" class="modal fade show d-block" tabindex="-1">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirmar eliminación</h5>
                            <button type="button" class="btn-close" @click="mostrarConfirmacion = false"></button>
                        </div>
                        <div class="modal-body">
                            <!-- TODO: Mostrar título de la cita a eliminar -->
                            <p>¿Estás seguro de eliminar la cita <strong>{{ citaAEliminar?.titulo }}</strong>?</p>
                            <p class="text-muted small mb-0">Esta acción no se puede deshacer.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="mostrarConfirmacion = false">
                                Cancelar
                            </button>
                            <button type="button" class="btn btn-danger" :disabled="eliminando" @click="eliminarCita">
                                <span v-if="eliminando" class="spinner-border spinner-border-sm me-2"></span>
                                Sí, eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        
            <div v-if="mostrarConfirmacion" class="modal-backdrop fade show"></div>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from '@/Disenos/LayoutAutenticado.vue';
import { Head,Link } from '@inertiajs/vue3';

export default {
    components: {
        AuthenticatedLayout,
        Head,
        Link
    },
    props: {
        mascotas: {
            type: Array,
            default: () => [],
        },
        veterinarios: {
            type: Array,
            default: () => [],
        },
        sucursales: {
            type: Array,
            default: () => [],
        },
        prestaciones: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            cargando: false,
            mostrarModal: false,
            modoEdicion: false,
            citaEditando: null,
            mostrarConfirmacion: false,
            citaAEliminar: null,
            eliminando: false,
            filtroMascota:'',
            citasData: null,
            citas:[],
            filtroCliente:'',
            filtroVeterinario: '',
            filtroTitulo: '',
            filtroEstado: '',
            filtroSucursal: '',
            veterinariosSucursal: [],
            boxesSucursal: [],
            cargandoDetallesSucursal: false,
            errorGeneral: null,
            horariosNormales: [],
            horariosUrgencia: [],
            cargandoHorarios: false,
            mostrarModalComprobante: false,
            transaccionSeleccionada: null,
            citaSeleccionadaParaComprobante: null,
            formulario: {
                titulo: '',
                descripcion: '',
                fecha_hora: '',
                fecha_seleccionada: '',
                tipo: 'normal',
                mascota_id: '',
                veterinario_id: '',
                sucursal_id: '',
                box_id: '',
                prestacion_id: '',
                errors: {},
                processing: false,
            },
        }
    },
    computed: {
        hoy() {
            return new Date().toISOString().split('T')[0];
        },
        sucursalesFiltradas() {
            if (!this.formulario.prestacion_id) return [];
            const prestacion = this.prestaciones.find(p => p.id === this.formulario.prestacion_id);
            if (!prestacion) return [];
            return this.sucursales.filter(s => s.id === prestacion.sucursal_id);
        },
        veterinariosFiltrados() {
            if (!this.formulario.sucursal_id || !this.formulario.prestacion_id) return [];
            const sucursal = this.sucursales.find(s => s.id === this.formulario.sucursal_id);
            if (!sucursal) return [];
            const prestacion = this.prestaciones.find(p => p.id === this.formulario.prestacion_id);
            
            return sucursal.veterinarios.filter(vet => {
                if (!prestacion.especialidad_id) return true;
                return vet.especialidad_id === prestacion.especialidad_id;
            });
        },
        boxesFiltrados() {
            if (!this.formulario.sucursal_id) return [];
            const sucursal = this.sucursales.find(s => s.id === this.formulario.sucursal_id);
            return sucursal ? sucursal.boxes : [];
        }
    },
    watch: {
        'formulario.prestacion_id'(newVal) {
            if (newVal) {
                const prestacion = this.prestaciones.find(p => p.id === newVal);
                if (prestacion && this.formulario.sucursal_id !== prestacion.sucursal_id) {
                    this.formulario.sucursal_id = prestacion.sucursal_id;
                }
            } else {
                this.formulario.sucursal_id = '';
            }
        },
        'formulario.sucursal_id'(newVal, oldVal) {
            if (oldVal && newVal !== oldVal && !this.modoEdicion) {
                this.formulario.veterinario_id = '';
                this.formulario.box_id = '';
            }
        },
        'formulario.veterinario_id'(newVal, oldVal) { 
            if (oldVal && newVal !== oldVal && !this.modoEdicion) {
                this.formulario.fecha_seleccionada = '';
                this.formulario.fecha_hora = '';
            }
            this.cargarHorarios(); 
        },
        'formulario.box_id'(newVal, oldVal) { 
            if (oldVal && newVal !== oldVal && !this.modoEdicion) {
                this.formulario.fecha_seleccionada = '';
                this.formulario.fecha_hora = '';
            }
            this.cargarHorarios(); 
        },
    },
    methods: {
        abrirModalCrear() {
            this.modoEdicion = false;
            this.citaEditando = null;
            this.errorGeneral = null;
            this.formulario.titulo = '';
            this.formulario.descripcion = '';
            this.formulario.fecha_hora = '';
            this.formulario.fecha_seleccionada = '';
            this.formulario.tipo = 'normal';
            this.formulario.mascota_id = '';
            this.formulario.prestacion_id = '';
            this.formulario.sucursal_id = '';
            this.formulario.veterinario_id = '';
            this.formulario.box_id = '';
            this.formulario.errors = {};
            this.horariosNormales = [];
            this.horariosUrgencia = [];
            this.mostrarModal = true;
        },
        esCliente(){
            const user = this.$page.props.auth.user;
            return user && user.rol && (user.rol.nombre_interno === 'cliente')
        },
        datosFormulario() {
            return {
                titulo: this.formulario.titulo,
                descripcion: this.formulario.descripcion,
                fecha_hora: this.formulario.fecha_hora,
                tipo: this.formulario.tipo,
                mascota_id: this.formulario.mascota_id,
                prestacion_id: this.formulario.prestacion_id,
                veterinario_id: this.formulario.veterinario_id,
                box_id: this.formulario.box_id,
            };
        },

        abrirModalEditar(cita) {
            this.modoEdicion = true;
            this.citaEditando = cita;
            this.errorGeneral = null;
            this.formulario.titulo = cita.titulo;
            this.formulario.descripcion = cita.descripcion;
            this.formulario.fecha_hora = cita.fecha_hora;
            this.formulario.mascota_id = cita.mascota_id;
            // Obtenemos el ID del cliente mapeado en el accessor o mascota
            this.formulario.cliente_id = cita.cliente?.id || cita.mascota?.cliente_id || '';
            this.formulario.prestacion_id = cita.prestacion_id || '';
            
            // Allow watchers to run or simply assign:
            this.$nextTick(() => {
                this.formulario.sucursal_id = cita.box?.sucursal_id || '';
                
                this.$nextTick(() => {
                    this.formulario.veterinario_id = cita.veterinario_id;
                    this.formulario.box_id = cita.box_id;
                    this.cargarHorarios();
                });
            });

            this.formulario.errors = {};
            this.mostrarModal = true;

            // Cargamos automáticamente la lista de mascotas del cliente asociado al editar
            if (this.formulario.cliente_id && typeof this.obtenerMascotasCliente === 'function') {
                this.obtenerMascotasCliente(this.formulario.cliente_id);
            }
        },
        cerrarModal() {
            this.mostrarModal=false;
            this.modoEdicion=false;
            this.citaEditando=null;
            this.errorGeneral=null;
            this.formulario.titulo='';
            this.formulario.descripcion='';
            this.formulario.fecha_hora='';
            this.formulario.fecha_seleccionada='';
            this.formulario.tipo='normal';
            this.formulario.prestacion_id='';
            this.formulario.sucursal_id='';
            this.formulario.veterinario_id='';
            this.formulario.box_id='';
            this.formulario.mascota_id='';
            this.formulario.errors={};
            this.horariosNormales=[];
            this.horariosUrgencia=[];
        },

        obtenerCitas(url = '/citas'){
            if (!url) return;
            this.cargando=true;
            axios.get(url,{params:{
                mascota_id:this.filtroMascota,
                veterinario_id:this.filtroVeterinario,
                titulo:this.filtroTitulo,
                estado:this.filtroEstado,
                sucursal_id:this.filtroSucursal
            }})
                .then(response => {
                    if (response.data.citas.data) {
                        this.citasData = response.data.citas;
                        this.citas = response.data.citas.data;
                    } else {
                        // En caso de que se pase data sin paginar por algún motivo
                        this.citasData = null;
                        this.citas = response.data.citas;
                    }
                })
                .catch(error => {
                    console.error('Error al obtener las citas:', error);
                })
                .finally(() => {
                    this.cargando=false;
                })
        },
        limpiarFiltros(){
            this.filtroMascota='';
            this.filtroCliente='';
            this.filtroVeterinario='';
            this.filtroTitulo='';
            this.filtroEstado='';
            this.filtroSucursal='';
            this.obtenerCitas();
        },
        guardar() {
            this.formulario.processing=true;
            this.formulario.errors={};
            this.errorGeneral = null;
            if(this.modoEdicion){
                this.actualizarCita();
            }else{
                this.crearCita();
            }
        },
        actualizarCita(){
            axios.put(`/api/citas/${this.citaEditando.id}`, { ...this.datosFormulario() })
                .then(() => { this.cerrarModal(); this.obtenerCitas(); })
                .catch((error) => { 
                    if (error.response?.status === 422) {
                        this.formulario.errors = error.response.data.errors;
                    } else if (error.response?.status === 409) {
                        this.errorGeneral = error.response.data.error;
                    }
                })
                .finally(() => { this.formulario.processing = false });
        },
        crearCita(){
            axios.post('/api/citas', { ...this.datosFormulario() })
                .then(() => { this.cerrarModal(); this.obtenerCitas(); })
                .catch((error) => { 
                    if (error.response?.status === 422) {
                        this.formulario.errors = error.response.data.errors;
                    } else if (error.response?.status === 409) {
                        this.errorGeneral = error.response.data.error;
                    }
                })
                .finally(() => { this.formulario.processing = false });
        },
        cargarHorarios() {
            if (!this.formulario.fecha_seleccionada ||
                !this.formulario.veterinario_id ||
                !this.formulario.box_id) return;

            this.cargandoHorarios = true;
            this.formulario.fecha_hora = '';
            this.formulario.tipo = 'normal';

            axios.get('/api/citas/horarios-disponibles', {
                params: {
                    fecha:           this.formulario.fecha_seleccionada,
                    veterinario_id:  this.formulario.veterinario_id,
                    box_id:          this.formulario.box_id,
                }
            }).then(r => {
                this.horariosNormales  = r.data.normal;
                this.horariosUrgencia  = r.data.urgencia;
            }).catch(error => {
                console.error('Error al cargar horarios:', error);
            }).finally(() => {
                this.cargandoHorarios = false;
            });
        },
        seleccionarHorario(slot) {
            this.formulario.fecha_hora = slot.fecha_hora;
            this.formulario.tipo       = slot.tipo;
        },
        confirmarCancelar(cita) {
            this.citaAEliminar = cita;
            this.$confirmar(
                '¿Cancelar cita?',
                `Se cancelará la cita "${cita.titulo}" de ${cita.mascota?.nombre || 'la mascota'}. El registro se conservará en el historial con estado Cancelada.`
            ).then((resultado) => {
                if (resultado.isConfirmed) return this.cancelarCita();
            })
        },
        cancelarCita() {
            axios.patch(`/api/citas/${this.citaAEliminar.id}/cancelar`)
            .then(() => { this.obtenerCitas(); })
            .catch((error) => { console.error('Error al cancelar la cita:', error); })
            .finally(() => { this.formulario.processing = false; });
        },
        verComprobante(transaccion, cita) {
            this.transaccionSeleccionada = transaccion;
            this.citaSeleccionadaParaComprobante = cita;
            this.mostrarModalComprobante = true;
        },
        imprimirComprobante() {
            window.print();
        },
        formatearFecha(fechaStr) {
            if (!fechaStr) return 'N/A';
            const f = new Date(fechaStr);
            return f.toLocaleDateString('es-CL', { day: '2-digit', month: 'long', year: 'numeric' });
        },
        formatearHora(fechaStr) {
            if (!fechaStr) return '';
            const f = new Date(fechaStr);
            return f.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' });
        },
        formatearMetodo(metodo) {
            if (!metodo) return 'No registrado';
            return metodo.charAt(0).toUpperCase() + metodo.slice(1);
        }
    },
    mounted(){
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('estado')) {
            this.filtroEstado = urlParams.get('estado');
        }
        this.obtenerCitas();
    },

}
</script>

<style scoped>
@media print {
    body * {
        visibility: hidden;
    }
    #comprobante-imprimir, #comprobante-imprimir * {
        visibility: visible;
    }
    #comprobante-imprimir {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        background-color: white;
    }
}
</style>
