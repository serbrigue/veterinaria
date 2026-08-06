<template>
    <!-- ================================================================================== -->
    <!-- COMPONENTE: Calendario -->
    <!-- ================================================================================== -->
    <Head title="Calendario General de Citas" />
    <AuthenticatedLayout>
        <div class="container py-4">
            <!-- Encabezado de la página -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 fw-bold text-dark mb-0">Agenda de Citas (Secretaría)</h1>
                    <p class="text-muted mb-0">Calendario interactivo con todas las citas futuras y del día</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill shadow-sm fs-6 border border-secondary">
                        <i class="bi bi-calendar-event me-1"></i> {{ totalCitas }} citas programadas
                    </span>
                </div>
            </div>

            <!-- Panel de Estadísticas / Estados Rápidos -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-sm-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 d-flex flex-row align-items-center">
                        <div class="bg-warning bg-opacity-10 text-warning p-2 rounded-3 me-3">
                            <i class="bi bi-clock-fill fs-4"></i>
                        </div>
                        <div>
                            <span class="d-block small text-muted">Pendientes</span>
                            <span class="fw-bold fs-5 text-dark">{{ countCitas('pendiente') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 d-flex flex-row align-items-center">
                        <div class="bg-info bg-opacity-10 text-info p-2 rounded-3 me-3">
                            <i class="bi bi-arrow-repeat fs-4"></i>
                        </div>
                        <div>
                            <span class="d-block small text-muted">En Curso</span>
                            <span class="fw-bold fs-5 text-dark">{{ countCitas('en_curso') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 d-flex flex-row align-items-center">
                        <div class="bg-success bg-opacity-10 text-success p-2 rounded-3 me-3">
                            <i class="bi bi-check-circle-fill fs-4"></i>
                        </div>
                        <div>
                            <span class="d-block small text-muted">Completadas</span>
                            <span class="fw-bold fs-5 text-dark">{{ countCitas('completada') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 d-flex flex-row align-items-center">
                        <div class="bg-danger bg-opacity-10 text-danger p-2 rounded-3 me-3">
                            <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                        </div>
                        <div>
                            <span class="d-block small text-muted">Urgencias</span>
                            <span class="fw-bold fs-5 text-dark">{{ countUrgencias }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendario Principal -->
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <div class="mb-3 d-flex align-items-center gap-2 text-muted small">
                    <i class="bi bi-info-circle"></i>
                    <span>Haz clic en un espacio vacío para agendar una nueva cita. Haz clic en una cita existente para ver sus detalles.</span>
                </div>
                <div class="calendar-wrapper">
                    <FullCalendar :options="opcionesCalendario" />
                </div>
            </div>
        </div>

        <!-- Modal de Detalle de Cita -->
        <!-- DIRECTIVA (v-if): Renderizado condicional basado en "citaSeleccionada" -->
        <div v-if="citaSeleccionada" class="modal-backdrop fade show" @click="cerrarDetalle"></div>
        <!-- DIRECTIVA (v-if): Renderizado condicional basado en "citaSeleccionada" -->
        <div v-if="citaSeleccionada" class="modal fade show d-block" tabindex="-1" @click.self="cerrarDetalle">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header border-bottom-0 pb-0">
                        <span class="badge" :class="badgeClass(citaSeleccionada.estado)">
                            {{ citaSeleccionada.estado.toUpperCase() }}
                        </span>
                        <!-- DIRECTIVA (v-if): Renderizado condicional basado en "citaSeleccionada.tipo === " -->
                        <span v-if="citaSeleccionada.tipo === 'urgencia'" class="badge bg-danger ms-2">URGENCIA</span>
                        <!-- EVENTO (@click): Dispara la acción "cerrarDetalle" -->
                        <button type="button" class="btn-close" @click="cerrarDetalle" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-3">
                        <h4 class="fw-bold text-dark mb-1">{{ citaSeleccionada.titulo || 'Cita de Control' }}</h4>
                        <p class="text-muted small mb-4">ID de Cita: #{{ citaSeleccionada.id }}</p>

                        <div class="d-flex flex-column gap-3">
                            <!-- Fecha y Hora -->
                            <div class="d-flex align-items-center">
                                <div class="bg-light p-2 rounded-3 me-3 text-primary">
                                    <i class="bi bi-clock fs-5"></i>
                                </div>
                                <div>
                                    <span class="d-block small text-muted">Fecha y Hora</span>
                                    <span class="fw-semibold">{{ formatoFechaHora(citaSeleccionada.fecha_hora) }}</span>
                                </div>
                            </div>

                            <!-- Paciente -->
                            <div class="d-flex align-items-center">
                                <div class="bg-light p-2 rounded-3 me-3 text-primary">
                                    <i class="bi bi-bug fs-5"></i>
                                </div>
                                <div>
                                    <span class="d-block small text-muted">Paciente (Mascota)</span>
                                    <span class="fw-semibold">{{ citaSeleccionada.mascota?.nombre || 'No asignado' }}</span>
                                </div>
                            </div>

                            <!-- Cliente/Dueño -->
                            <div class="d-flex align-items-center">
                                <div class="bg-light p-2 rounded-3 me-3 text-primary">
                                    <i class="bi bi-person fs-5"></i>
                                </div>
                                <div>
                                    <span class="d-block small text-muted">Cliente</span>
                                    <span class="fw-semibold">{{ citaSeleccionada.mascota?.cliente?.usuario?.name || 'No asignado' }}</span>
                                </div>
                            </div>

                            <!-- Veterinario -->
                            <div class="d-flex align-items-center">
                                <div class="bg-light p-2 rounded-3 me-3 text-primary">
                                    <i class="bi bi-person-badge fs-5"></i>
                                </div>
                                <div>
                                    <span class="d-block small text-muted">Veterinario</span>
                                    <span class="fw-semibold">{{ citaSeleccionada.veterinario?.usuario?.name || 'No asignado' }}</span>
                                </div>
                            </div>

                            <!-- Box -->
                            <div class="d-flex align-items-center">
                                <div class="bg-light p-2 rounded-3 me-3 text-primary">
                                    <i class="bi bi-building fs-5"></i>
                                </div>
                                <div>
                                    <span class="d-block small text-muted">Box de Atención</span>
                                    <span class="fw-semibold">{{ citaSeleccionada.box?.nombre || 'No asignado' }}</span>
                                </div>
                            </div>

                            <!-- Servicio -->
                            <div class="d-flex align-items-center">
                                <div class="bg-light p-2 rounded-3 me-3 text-primary">
                                    <i class="bi bi-file-medical fs-5"></i>
                                </div>
                                <div>
                                    <span class="d-block small text-muted">Servicio (Prestación)</span>
                                    <span class="fw-semibold">{{ citaSeleccionada.prestacion?.nombre || 'No asignado' }}</span>
                                </div>
                            </div>

                            <!-- Notas -->
                            <!-- DIRECTIVA (v-if): Renderizado condicional basado en "citaSeleccionada.notas" -->
                            <div v-if="citaSeleccionada.notas" class="mt-2 p-3 bg-light rounded-3">
                                <span class="d-block small text-muted mb-1 fw-bold">Notas Clínicas / Administrativas</span>
                                <p class="mb-0 small text-dark">{{ citaSeleccionada.notas }}</p>
                            </div>

                            <!-- Alertas para Secretaría -->
                            <!-- DIRECTIVA (v-if): Renderizado condicional basado en "citaSeleccionada.alertas_secretaria?.length" -->
                            <div v-if="citaSeleccionada.alertas_secretaria?.length" class="mt-3 p-3 bg-danger bg-opacity-10 rounded-3 border border-danger border-opacity-25">
                                <span class="d-block small text-danger mb-2 fw-bold">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>Alertas pendientes
                                </span>
                                <div class="d-flex flex-column gap-1">
                                    <!-- DIRECTIVA (v-for): Renderizado iterativo de lista -->
                                    <div
                                        v-for="alerta in citaSeleccionada.alertas_secretaria"
                                        :key="alerta.tipo"
                                        class="d-flex align-items-center gap-2 small text-danger"
                                    >
                                        <i class="bi" :class="alerta.icono"></i>
                                        <span>{{ alerta.mensaje }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0 pb-4 d-flex justify-content-between">
                        <div class="d-flex gap-2">
                            <!-- EVENTO (@click): Dispara la acción "cerrarDetalle" -->
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-3" @click="cerrarDetalle">Cerrar</button>
                            <!-- DIRECTIVA (v-if): Renderizado condicional basado en "citaSeleccionada.estado === " -->
                            <!-- EVENTO (@click): Dispara la acción "iniciarEdicion(citaSeleccionada)" -->
                            <button v-if="citaSeleccionada.estado === 'pendiente'" type="button" class="btn btn-outline-primary rounded-pill px-3" @click="iniciarEdicion(citaSeleccionada)">
                                <i class="bi bi-pencil me-1"></i> Editar
                            </button>
                        </div>
                        <div class="d-flex gap-2">
                            <!-- DIRECTIVA (v-if): Renderizado condicional basado en "citaSeleccionada.estado === " -->
                            <!-- EVENTO (@click): Dispara la acción "confirmarCancelar(citaSeleccionada)" -->
                            <button v-if="citaSeleccionada.estado === 'pendiente'" type="button" class="btn btn-outline-warning rounded-pill px-3" @click="confirmarCancelar(citaSeleccionada)">
                                <i class="bi bi-x-circle-fill me-1"></i> Cancelar
                            </button>
                            <Link :href="route('citas.detalle', citaSeleccionada.id)" class="btn btn-primary rounded-pill px-3">
                                Ficha <i class="bi bi-caret-right-fill ms-1"></i>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL: Crear / Editar Cita                 -->
        <!-- ========================================== -->
        <ModalCrud
            :visible="mostrarModal"
            :titulo="modoEdicion ? 'Editar Cita' : 'Nueva Cita'"
            :modo-edicion="modoEdicion"
            :processing="formulario.processing"
            tamanio="lg"
            texto-crear="Crear cita"
            texto-guardar="Guardar cambios"
            @cerrar="cerrarModal"
            @guardar="guardar"
        >
            <div class="row g-0" style="min-height: 70vh;">
                <!-- Columna izquierda: datos de la cita -->
                <div class="col-md-5 p-3 border-end">
                    <div class="row g-3">
                        <!-- DIRECTIVA (v-if): Renderizado condicional basado en "errorGeneral" -->
                        <div v-if="errorGeneral" class="col-12">
                            <div class="alert alert-danger d-flex align-items-center mb-0 border-0 shadow-sm" role="alert">
                                <i class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2"></i>
                                <div>{{ errorGeneral }}</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="titulo" class="form-label fw-semibold text-secondary small text-uppercase">Título</label>
                            <!-- DIRECTIVA (v-model): Enlace de datos bidireccional con "formulario.titulo" -->
                            <input id="titulo" v-model="formulario.titulo" type="text" class="form-control bg-light border-0 py-2" :class="{ 'is-invalid': formulario.errors.titulo }" required placeholder="Ej: Control general" />
                            <!-- DIRECTIVA (v-if): Renderizado condicional basado en "formulario.errors.titulo" -->
                            <div v-if="formulario.errors.titulo" class="invalid-feedback">{{ formulario.errors.titulo }}</div>
                        </div>
                        <div class="col-12">
                            <label for="descripcion" class="form-label fw-semibold text-secondary small text-uppercase">Descripción</label>
                            <!-- DIRECTIVA (v-model): Enlace de datos bidireccional con "formulario.descripcion" -->
                            <textarea id="descripcion" v-model="formulario.descripcion" class="form-control bg-light border-0 py-2" :class="{ 'is-invalid': formulario.errors.descripcion }" rows="2" required placeholder="Motivo de la cita..."></textarea>
                            <!-- DIRECTIVA (v-if): Renderizado condicional basado en "formulario.errors.descripcion" -->
                            <div v-if="formulario.errors.descripcion" class="invalid-feedback">{{ formulario.errors.descripcion }}</div>
                        </div>
                        <div class="col-12 position-relative">
                            <label class="form-label fw-semibold text-secondary small text-uppercase mb-1">Cliente</label>
                            <div class="dropdown">
                                <div 
                                    class="form-select bg-light border-0 py-2 d-flex justify-content-between align-items-center"
                                    :class="{ 'is-invalid': formulario.errors.cliente_id }"
                                    @click="mostrarDropdownCliente = !mostrarDropdownCliente"
                                    style="cursor: pointer;"
                                >
                                    <span>{{ nombreClienteSeleccionado || 'Selecciona un cliente' }}</span>
                                </div>
                                <!-- DIRECTIVA (v-if): Renderizado condicional basado en "formulario.errors.cliente_id" -->
                                <div v-if="formulario.errors.cliente_id" class="invalid-feedback d-block">{{ formulario.errors.cliente_id }}</div>
                                
                                <!-- Backdrop transparente para cerrar al hacer click fuera -->
                                <!-- DIRECTIVA (v-if): Renderizado condicional basado en "mostrarDropdownCliente" -->
                                <div 
                                    v-if="mostrarDropdownCliente" 
                                    class="position-fixed top-0 start-0 w-100 h-100" 
                                    style="z-index: 1040; background: transparent;" 
                                    @click.stop="mostrarDropdownCliente = false"
                                ></div>
                                
                                <!-- DIRECTIVA (v-if): Renderizado condicional basado en "mostrarDropdownCliente" -->
                                
                                <div 
                                    v-if="mostrarDropdownCliente" 
                                    class="dropdown-menu show w-100 p-2 shadow border-0 mt-1 bg-white" 
                                    style="max-height: 350px; overflow-y: auto; z-index: 1050; display: block;"
                                >
                                    <!-- DIRECTIVA (v-model): Enlace de datos bidireccional con "busquedaCliente" -->
                                    <input 
                                        type="text" 
                                        class="form-control form-control-sm mb-2" 
                                        v-model="busquedaCliente" 
                                        placeholder="Escribe para buscar cliente..."
                                        @click.stop
                                    />
                                    <ul class="list-unstyled mb-0">
                                        <!-- DIRECTIVA (v-for): Renderizado iterativo de lista -->
                                        <li v-for="cliente in clientesFiltradosPorBusqueda" :key="cliente.id">
                                             <!-- EVENTO (@click): Dispara la acción "seleccionarClienteDropdown(cliente)" -->
                                             <button 
                                                type="button" 
                                                class="dropdown-item py-2 rounded text-start"
                                                :class="{ 'active bg-primary text-white': formulario.cliente_id === cliente.id }"
                                                @click="seleccionarClienteDropdown(cliente)"
                                             >
                                                 {{ cliente.nombre }} ({{ cliente.email }})
                                             </button>
                                        </li>
                                        <!-- DIRECTIVA (v-if): Renderizado condicional basado en "clientesFiltradosPorBusqueda.length === 0" -->
                                        <li v-if="clientesFiltradosPorBusqueda.length === 0" class="text-muted small p-2 text-center">
                                            No se encontraron resultados
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="mascota_id" class="form-label fw-semibold text-secondary small text-uppercase">Mascota</label>
                            <!-- DIRECTIVA (v-model): Enlace de datos bidireccional con "formulario.mascota_id" -->
                            <select id="mascota_id" v-model="formulario.mascota_id" class="form-select bg-light border-0 py-2" :class="{ 'is-invalid': formulario.errors.mascota_id }" required :disabled="!formulario.cliente_id">
                                <option value="" disabled>{{ !formulario.cliente_id ? 'Debe seleccionar un cliente primero' : 'Selecciona una mascota' }}</option>
                                <!-- DIRECTIVA (v-for): Renderizado iterativo de lista -->
                                <option v-for="mascota in mascotasFiltradas" :key="mascota.id" :value="mascota.id">
                                    {{ mascota.nombre }} {{ mascota.sexo ? `(${mascota.sexo})` : '' }}
                                </option>
                            </select>
                            <!-- DIRECTIVA (v-if): Renderizado condicional basado en "formulario.errors.mascota_id" -->
                            <div v-if="formulario.errors.mascota_id" class="invalid-feedback">{{ formulario.errors.mascota_id }}</div>
                        </div>
                        <!-- DIRECTIVA (v-if): Renderizado condicional basado en "formulario.mascota_id" -->
                        <div v-if="formulario.mascota_id" class="col-12 position-relative">
                            <label class="form-label fw-semibold text-secondary small text-uppercase mb-1">Prestación o Servicio</label>
                            <div class="dropdown">
                                <div 
                                    class="form-select bg-light border-0 py-2 d-flex justify-content-between align-items-center"
                                    :class="{ 'is-invalid': formulario.errors.prestacion_id }"
                                    @click="mostrarDropdownPrestacion = !mostrarDropdownPrestacion"
                                    style="cursor: pointer;"
                                >
                                    <span>{{ nombrePrestacionSeleccionada || 'Selecciona una prestación o servicio' }}</span>
                                </div>
                                <!-- DIRECTIVA (v-if): Renderizado condicional basado en "formulario.errors.prestacion_id" -->
                                <div v-if="formulario.errors.prestacion_id" class="invalid-feedback d-block">{{ formulario.errors.prestacion_id }}</div>
                                
                                <!-- Backdrop transparente para cerrar al hacer click fuera -->
                                <!-- DIRECTIVA (v-if): Renderizado condicional basado en "mostrarDropdownPrestacion" -->
                                <div 
                                    v-if="mostrarDropdownPrestacion" 
                                    class="position-fixed top-0 start-0 w-100 h-100" 
                                    style="z-index: 1040; background: transparent;" 
                                    @click.stop="mostrarDropdownPrestacion = false"
                                ></div>
                                
                                <!-- DIRECTIVA (v-if): Renderizado condicional basado en "mostrarDropdownPrestacion" -->
                                
                                <div 
                                    v-if="mostrarDropdownPrestacion" 
                                    class="dropdown-menu show w-100 p-2 shadow border-0 mt-1 bg-white" 
                                    style="max-height: 350px; overflow-y: auto; z-index: 1050; display: block;"
                                >
                                    <!-- DIRECTIVA (v-model): Enlace de datos bidireccional con "busquedaPrestacion" -->
                                    <input 
                                        type="text" 
                                        class="form-control form-control-sm mb-2" 
                                        v-model="busquedaPrestacion" 
                                        placeholder="Escribe para buscar..."
                                        @click.stop
                                    />
                                    <ul class="list-unstyled mb-0">
                                        <!-- DIRECTIVA (v-for): Renderizado iterativo de lista -->
                                        <li v-for="prestacion in prestacionesFiltradasPorBusqueda" :key="prestacion.id">
                                             <!-- EVENTO (@click): Dispara la acción "seleccionarPrestacionDropdown(prestacion)" -->
                                             <button 
                                                type="button" 
                                                class="dropdown-item py-2 rounded text-start"
                                                :class="{ 'active bg-primary text-white': formulario.prestacion_id === prestacion.id }"
                                                @click="seleccionarPrestacionDropdown(prestacion)"
                                             >
                                                 {{ prestacion.nombre }} ({{ prestacion.sucursal?.nombre }})
                                             </button>
                                        </li>
                                        <!-- DIRECTIVA (v-if): Renderizado condicional basado en "prestacionesFiltradasPorBusqueda.length === 0" -->
                                        <li v-if="prestacionesFiltradasPorBusqueda.length === 0" class="text-muted small p-2 text-center">
                                            No se encontraron resultados
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- DIRECTIVA (v-if): Renderizado condicional basado en "formulario.prestacion_id" -->

                        <div v-if="formulario.prestacion_id" class="col-12">
                            <label class="form-label fw-semibold text-secondary small text-uppercase">Sucursal</label>
                            <!-- DIRECTIVA (v-model): Enlace de datos bidireccional con "formulario.sucursal_id" -->
                            <select id="sucursal_id" v-model="formulario.sucursal_id" class="form-select bg-light border-0 py-2" :class="{ 'is-invalid': formulario.errors.sucursal_id }" required disabled>
                                <option value="" disabled>Selecciona una sucursal</option>
                                <!-- DIRECTIVA (v-for): Renderizado iterativo de lista -->
                                <option v-for="sucursal in sucursalesFiltradas" :key="sucursal.id" :value="sucursal.id">
                                    {{ sucursal.nombre }}
                                </option>
                            </select>
                            <!-- DIRECTIVA (v-if): Renderizado condicional basado en "formulario.errors.sucursal_id" -->
                            <div v-if="formulario.errors.sucursal_id" class="invalid-feedback">{{ formulario.errors.sucursal_id }}</div>
                        </div>

                        <!-- DIRECTIVA (v-if): Renderizado condicional basado en "formulario.sucursal_id" -->

                        <template v-if="formulario.sucursal_id">
                            <div class="col-12">
                                <label class="form-label fw-semibold text-secondary small text-uppercase">Veterinario (Aptos)</label>
                                <!-- DIRECTIVA (v-model): Enlace de datos bidireccional con "formulario.veterinario_id" -->
                                <select id="veterinario_id" v-model="formulario.veterinario_id" class="form-select bg-light border-0 py-2" :class="{ 'is-invalid': formulario.errors.veterinario_id }">
                                    <option value="">Cualquier veterinario (opcional)</option>
                                    <!-- DIRECTIVA (v-for): Renderizado iterativo de lista -->
                                    <option v-for="vet in veterinariosFiltrados" :key="vet.id" :value="vet.id">
                                        {{ vet.usuario.name }}
                                    </option>
                                </select>
                                <!-- DIRECTIVA (v-if): Renderizado condicional basado en "formulario.errors.veterinario_id" -->
                                <div v-if="formulario.errors.veterinario_id" class="invalid-feedback">{{ formulario.errors.veterinario_id }}</div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Columna derecha: fecha y horarios -->
                <div class="col-md-7 p-3 bg-light bg-opacity-50">
                    <!-- DIRECTIVA (v-if): Renderizado condicional basado en "formulario.sucursal_id" -->
                    <template v-if="formulario.sucursal_id">
                        <div class="mb-3">
                            <label for="fecha_seleccionada" class="form-label fw-semibold text-secondary small text-uppercase">Fecha</label>
                            <!-- DIRECTIVA (v-model): Enlace de datos bidireccional con "formulario.fecha_seleccionada" -->
                            <input
                                id="fecha_seleccionada"
                                type="date"
                                v-model="formulario.fecha_seleccionada"
                                class="form-control bg-white border-0 py-2 shadow-sm"
                                :class="{ 'is-invalid': formulario.errors.fecha_hora }"
                                :min="hoy"
                                @change="cargarHorarios"
                            />
                            <!-- DIRECTIVA (v-if): Renderizado condicional basado en "formulario.errors.fecha_hora" -->
                            <div v-if="formulario.errors.fecha_hora" class="invalid-feedback">{{ formulario.errors.fecha_hora }}</div>
                        </div>

                        <!-- Banner explicativo si hay preselección desde el calendario semanal/diario -->
                        <!-- DIRECTIVA (v-if): Renderizado condicional basado en "horaPreseleccionada" -->
                        <div v-if="horaPreseleccionada" class="alert alert-info py-2 px-3 mb-3 border-0 small rounded-3 d-flex align-items-center">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            <span>Horario clickeado en calendario: <strong>{{ horaPreseleccionada }}</strong>. Selecciona el veterinario deseado abajo para confirmar la disponibilidad en ese bloque.</span>
                        </div>

                        <!-- DIRECTIVA (v-if): Renderizado condicional basado en "cargandoHorarios" -->

                        <div v-if="cargandoHorarios" class="text-center py-4">
                            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                            <span class="ms-2 text-muted small">Consultando disponibilidad...</span>
                        </div>

                        <template v-else-if="formulario.fecha_seleccionada">
                            <!-- CASO 1: Veterinario seleccionado -->
                            <!-- DIRECTIVA (v-if): Renderizado condicional basado en "formulario.veterinario_id" -->
                            <template v-if="formulario.veterinario_id">
                                <!-- Horarios normales -->
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-secondary small text-uppercase">Horarios disponibles</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        <!-- DIRECTIVA (v-for): Renderizado iterativo de lista -->
                                        <template v-for="slot in horariosNormales" :key="slot.hora">
                                            <!-- DIRECTIVA (v-if): Renderizado condicional basado en "slot.disponible" -->
                                            <!-- EVENTO (@click): Dispara la acción "seleccionarHorario(slot)" -->
                                            <button
                                                v-if="slot.disponible"
                                                type="button"
                                                :class="[
                                                    'btn btn-sm rounded-pill px-3',
                                                    formulario.fecha_hora === slot.fecha_hora
                                                        ? 'btn-primary'
                                                        : 'btn-outline-primary'
                                                ]"
                                                @click="seleccionarHorario(slot)"
                                            >
                                                {{ slot.hora }}
                                            </button>
                                        </template>
                                        <!-- DIRECTIVA (v-if): Renderizado condicional basado en "horariosNormales.filter(s => s.disponible).length === 0" -->
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
                                        <!-- DIRECTIVA (v-for): Renderizado iterativo de lista -->
                                        <template v-for="slot in horariosUrgencia" :key="slot.hora">
                                            <!-- DIRECTIVA (v-if): Renderizado condicional basado en "slot.disponible" -->
                                            <!-- EVENTO (@click): Dispara la acción "seleccionarHorario(slot)" -->
                                            <button
                                                v-if="slot.disponible"
                                                type="button"
                                                :class="[
                                                    'btn btn-sm rounded-pill px-3',
                                                    formulario.fecha_hora === slot.fecha_hora
                                                        ? 'btn-warning text-dark'
                                                        : 'btn-outline-warning'
                                                ]"
                                                @click="seleccionarHorario(slot)"
                                            >
                                                {{ slot.hora }}
                                            </button>
                                        </template>
                                        <!-- DIRECTIVA (v-if): Renderizado condicional basado en "horariosUrgencia.filter(s => s.disponible).length === 0" -->
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
                                    <!-- DIRECTIVA (v-for): Renderizado iterativo de lista -->
                                    <div v-for="vet in veterinariosFiltrados" :key="vet.id" class="accordion-item border-0 border-bottom border-light">
                                        <h4 class="accordion-header mb-0">
                                            <!-- EVENTO (@click): Dispara la acción "toggleAcordeon(vet.id)" -->
                                            <button 
                                                class="accordion-button d-flex align-items-center justify-content-between w-100 py-3 px-4 text-start border-0 fw-semibold"
                                                :class="vetAcordeonAbiertoId === vet.id ? 'bg-primary bg-opacity-10 text-primary' : 'bg-white text-dark'"
                                                type="button" 
                                                @click="toggleAcordeon(vet.id)"
                                            >
                                                <span class="d-flex align-items-center gap-2">
                                                    <i class="bi bi-person-badge-fill" :class="vetAcordeonAbiertoId === vet.id ? 'text-primary' : 'text-secondary'"></i>
                                                    {{ vet.usuario.name }}
                                                </span>
                                                <span class="badge rounded-pill bg-light text-secondary border border-light small px-2 py-1">
                                                    {{ obtenerCantSlotsDisponibles(vet.id) }} horarios disponibles
                                                    <i class="bi ms-1" :class="vetAcordeonAbiertoId === vet.id ? 'bi-caret-up-fill' : 'bi-caret-down-fill'"></i>
                                                </span>
                                            </button>
                                        </h4>
                                        <!-- DIRECTIVA (v-if): Renderizado condicional basado en "vetAcordeonAbiertoId === vet.id" -->
                                        <div 
                                            v-if="vetAcordeonAbiertoId === vet.id" 
                                            class="accordion-body p-4 bg-light bg-opacity-25"
                                        >
                                            <!-- Horarios del veterinario -->
                                            <!-- DIRECTIVA (v-if): Renderizado condicional basado en "horariosPorVeterinario[vet.id]" -->
                                            <div v-if="horariosPorVeterinario[vet.id]">
                                                <!-- Horarios normales -->
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold text-secondary small text-uppercase">Horarios disponibles</label>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <!-- DIRECTIVA (v-for): Renderizado iterativo de lista -->
                                                        <template v-for="slot in horariosPorVeterinario[vet.id].normal" :key="slot.hora">
                                                            <!-- DIRECTIVA (v-if): Renderizado condicional basado en "slot.disponible" -->
                                                            <!-- EVENTO (@click): Dispara la acción "seleccionarHorarioAcordeon(slot, vet.id)" -->
                                                            <button
                                                                v-if="slot.disponible"
                                                                type="button"
                                                                :class="[
                                                                    'btn btn-sm rounded-pill px-3',
                                                                    formulario.fecha_hora === slot.fecha_hora
                                                                        ? 'btn-primary'
                                                                        : 'btn-outline-primary'
                                                                ]"
                                                                @click="seleccionarHorarioAcordeon(slot, vet.id)"
                                                            >
                                                                {{ slot.hora }}
                                                            </button>
                                                        </template>
                                                        <!-- DIRECTIVA (v-if): Renderizado condicional basado en "horariosPorVeterinario[vet.id].normal.filter(s => s.disponible).length === 0" -->
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
                                                        <!-- DIRECTIVA (v-for): Renderizado iterativo de lista -->
                                                        <template v-for="slot in horariosPorVeterinario[vet.id].urgencia" :key="slot.hora">
                                                            <!-- DIRECTIVA (v-if): Renderizado condicional basado en "slot.disponible" -->
                                                            <!-- EVENTO (@click): Dispara la acción "seleccionarHorarioAcordeon(slot, vet.id)" -->
                                                            <button
                                                                v-if="slot.disponible"
                                                                type="button"
                                                                :class="[
                                                                    'btn btn-sm rounded-pill px-3',
                                                                    formulario.fecha_hora === slot.fecha_hora
                                                                        ? 'btn-warning text-dark'
                                                                        : 'btn-outline-warning'
                                                                ]"
                                                                @click="seleccionarHorarioAcordeon(slot, vet.id)"
                                                            >
                                                                {{ slot.hora }}
                                                            </button>
                                                        </template>
                                                        <!-- DIRECTIVA (v-if): Renderizado condicional basado en "horariosPorVeterinario[vet.id].urgencia.filter(s => s.disponible).length === 0" -->
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
    </AuthenticatedLayout>
</template>

<script>
// ==================================================================================
// LÓGICA DEL COMPONENTE (VUE 3)
// ==================================================================================

import AuthenticatedLayout from '@/Disenos/LayoutAutenticado.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import ModalCrud from '@/Componentes/ModalCrud.vue';
import axios from 'axios';

// ------------------------------------------------------------------------------
// EXPORT DEFAULT: Definición principal del componente
// ------------------------------------------------------------------------------
export default {
    // COMPONENTES (COMPONENTS): Registro de componentes importados
    components: {
        AuthenticatedLayout,
        Head,
        Link,
        FullCalendar,
        ModalCrud
    },
    // PROPIEDADES (PROPS): Datos inyectados desde el componente padre o estado
    props: {
        citas: {
            type: Array,
            default: () => []
        },
        mascotas: {
            type: Array,
            default: () => []
        },
        sucursales: {
            type: Array,
            default: () => []
        },
        prestaciones: {
            type: Array,
            default: () => []
        },
        veterinarios: {
            type: Array,
            default: () => []
        }
    },
    // ESTADO REACTIVO (DATA): Variables locales del componente
    data() {
        return {
            citaSeleccionada: null,
            mostrarModal: false,
            modoEdicion: false,
            citaEditando: null,
            cargandoHorarios: false,
            errorGeneral: null,
            horariosNormales: [],
            horariosUrgencia: [],
            horariosPorVeterinario: {},
            vetAcordeonAbiertoId: null,
            evitarResetHorario: false,
            horaPreseleccionada: null,
            busquedaPrestacion: '',
            mostrarDropdownPrestacion: false,
            busquedaCliente: '',
            mostrarDropdownCliente: false,
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
                cliente_id: '',
                errors: {},
                processing: false
            },
            opcionesCalendario: {
                plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
                initialView: 'dayGridMonth',
                locale: 'es',
                firstDay: 1, // Lunes
                selectable: true,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                buttonText: {
                    today: 'Hoy',
                    month: 'Mes',
                    week: 'Semana',
                    day: 'Día'
                },
                events: [],
                eventClick: this.manejarClickEvento,
                dateClick: this.manejarClickFecha,
                slotMinTime: '08:00:00',
                slotMaxTime: '21:00:00',
                allDaySlot: false,
                height: 'auto',
                eventTimeFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                }
            }
        };
    },
    // PROPIEDADES COMPUTADAS (COMPUTED): Variables reactivas que dependen de otras
    computed: {
        totalCitas() {
            return this.citas.length;
        },
        countUrgencias() {
            return this.citas.filter(c => c.tipo === 'urgencia').length;
        },
        hoy() {
            return new Date().toISOString().split('T')[0];
        },
        clientes() {
            const map = new Map();
            this.mascotas.forEach(mascota => {
                if (mascota.cliente && mascota.cliente.usuario) {
                    map.set(mascota.cliente.id, {
                        id: mascota.cliente.id,
                        nombre: mascota.cliente.usuario.name,
                        email: mascota.cliente.usuario.email
                    });
                }
            });
            return Array.from(map.values()).sort((a, b) => a.nombre.localeCompare(b.nombre));
        },
        mascotasFiltradas() {
            if (!this.formulario.cliente_id) return [];
            return this.mascotas.filter(m => m.cliente_id === this.formulario.cliente_id);
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
        prestacionesFiltradasPorBusqueda() {
            if (!this.busquedaPrestacion) {
                return this.prestaciones;
            }
            const term = this.busquedaPrestacion.toLowerCase();
            return this.prestaciones.filter(p => {
                if (p.id === this.formulario.prestacion_id) return true;
                return p.nombre.toLowerCase().includes(term) || (p.sucursal?.nombre && p.sucursal.nombre.toLowerCase().includes(term));
            });
        },
        nombrePrestacionSeleccionada() {
            if (!this.formulario.prestacion_id) return '';
            const prestacion = this.prestaciones.find(p => p.id === this.formulario.prestacion_id);
            return prestacion ? `${prestacion.nombre} (${prestacion.sucursal?.nombre || ''})` : '';
        },
        clientesFiltradosPorBusqueda() {
            if (!this.busquedaCliente) {
                return this.clientes;
            }
            const term = this.busquedaCliente.toLowerCase();
            return this.clientes.filter(c => {
                if (c.id === this.formulario.cliente_id) return true;
                return c.nombre.toLowerCase().includes(term) || (c.email && c.email.toLowerCase().includes(term));
            });
        },
        nombreClienteSeleccionado() {
            if (!this.formulario.cliente_id) return '';
            const cliente = this.clientes.find(c => c.id === this.formulario.cliente_id);
            return cliente ? `${cliente.nombre} (${cliente.email})` : '';
        }
    },
    // OBSERVADORES (WATCH): Reaccionan a cambios en propiedades o variables
    watch: {
        citas: {
            immediate: true,
            handler() {
                this.cargarEventos();
            }
        },
        'formulario.prestacion_id'(newVal, oldVal) {
            if (newVal) {
                const prestacion = this.prestaciones.find(p => p.id === newVal);
                if (prestacion && this.formulario.sucursal_id !== prestacion.sucursal_id) {
                    this.formulario.sucursal_id = prestacion.sucursal_id;
                }
                if (oldVal && newVal !== oldVal && !this.modoEdicion) {
                    this.formulario.box_id = '';
                    this.formulario.veterinario_id = '';
                    if (!this.horaPreseleccionada) {
                        this.formulario.fecha_seleccionada = '';
                        this.formulario.fecha_hora = '';
                    }
                }
                if (this.formulario.fecha_seleccionada) {
                    this.cargarHorarios();
                }
            } else {
                this.formulario.sucursal_id = '';
                this.formulario.box_id = '';
                this.formulario.veterinario_id = '';
            }
        },
        'formulario.sucursal_id'(newVal, oldVal) {
            if (oldVal && newVal !== oldVal && !this.modoEdicion) {
                this.formulario.veterinario_id = '';
                this.formulario.box_id = '';
            }
        },
        'formulario.veterinario_id'(newVal, oldVal) { 
            if (oldVal && newVal !== oldVal && !this.modoEdicion && !this.evitarResetHorario) {
                if (!this.horaPreseleccionada) {
                    this.formulario.fecha_seleccionada = '';
                    this.formulario.fecha_hora = '';
                }
            }
            this.cargarHorarios(); 
        }
    },
    // MÉTODOS (METHODS): Bloque de funciones y eventos
    methods: {
        cargarEventos() {
            this.opcionesCalendario.events = this.citas.map(cita => {
                let colorFondo = 'rgba(99, 102, 241, 0.1)';
                let colorBorde = '#6366f1';
                let colorTexto = '#4f46e5';

                if (cita.estado === 'completada') {
                    colorFondo = 'rgba(16, 185, 129, 0.1)';
                    colorBorde = '#10b981';
                    colorTexto = '#065f46';
                } else if (cita.estado === 'cancelada') {
                    colorFondo = 'rgba(239, 68, 68, 0.1)';
                    colorBorde = '#ef4444';
                    colorTexto = '#991b1b';
                } else if (cita.estado === 'pendiente') {
                    colorFondo = 'rgba(245, 158, 11, 0.1)';
                    colorBorde = '#f59e0b';
                    colorTexto = '#92400e';
                } else if (cita.estado === 'en_curso') {
                    colorFondo = 'rgba(6, 182, 212, 0.1)';
                    colorBorde = '#06b6d4';
                    colorTexto = '#0891b2';
                }

                if (cita.tipo === 'urgencia') {
                    colorFondo = 'rgba(220, 38, 38, 0.15)';
                    colorBorde = '#dc2626';
                    colorTexto = '#7f1d1d';
                }

                return {
                    id: cita.id,
                    title: `${cita.alertas_secretaria?.length ? '⚠ ' : ''}${cita.mascota?.nombre || 'Paciente'} - ${cita.prestacion?.nombre || 'Consulta'}`,
                    start: cita.fecha_hora,
                    end: cita.hora_termino || null,
                    backgroundColor: colorFondo,
                    borderColor: colorBorde,
                    textColor: colorTexto,
                    extendedProps: {
                        cita: cita
                    }
                };
            });
        },
        manejarClickEvento(info) {
            this.citaSeleccionada = info.event.extendedProps.cita;
        },
        manejarClickFecha(info) {
            // Comprobar si la fecha clickeada es anterior a hoy (soporta formatos mensual y timeGrid)
            const cadenaSoloFecha = info.dateStr.split('T')[0];
            const fechaClickeada = new Date(cadenaSoloFecha + 'T00:00:00');
            const hoy = new Date();
            hoy.setHours(0,0,0,0);
            if (fechaClickeada < hoy) {
                return;
            }

            // Cerrar modal de detalles si está abierto
            this.cerrarDetalle();
            this.abrirModalCrear();

            // Extraer fecha clickeada y hora opcional
            const partesFecha = info.dateStr.split('T');
            const fecha = partesFecha[0];
            const hora = partesFecha[1] ? partesFecha[1].substring(0, 5) : null;

            this.formulario.fecha_seleccionada = fecha;
            
            if (hora) {
                this.horaPreseleccionada = hora;
                this.formulario.fecha_hora = `${fecha} ${hora}:00`;
                this.evitarResetHorario = true;
                // Esperar a que los componentes se vinculen, luego recuperar los horarios
                setTimeout(() => {
                    this.cargarHorarios();
                    this.evitarResetHorario = false;
                }, 50);
            } else {
                this.horaPreseleccionada = null;
            }

            this.mostrarModal = true;
        },
        seleccionarPrestacionDropdown(prestacion) {
            this.formulario.prestacion_id = prestacion.id;
            this.mostrarDropdownPrestacion = false;
            this.busquedaPrestacion = '';
        },
        seleccionarClienteDropdown(cliente) {
            this.formulario.cliente_id = cliente.id;
            this.formulario.mascota_id = '';
            this.mostrarDropdownCliente = false;
            this.busquedaCliente = '';
        },
        cerrarDetalle() {
            this.citaSeleccionada = null;
        },
        abrirModalCrear() {
            this.modoEdicion = false;
            this.citaEditando = null;
            this.errorGeneral = null;
            this.horaPreseleccionada = null;
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
            this.formulario.cliente_id = '';
            this.busquedaPrestacion = '';
            this.mostrarDropdownPrestacion = false;
            this.busquedaCliente = '';
            this.mostrarDropdownCliente = false;
            this.formulario.errors = {};
            this.horariosNormales = [];
            this.horariosUrgencia = [];
            this.horariosPorVeterinario = {};
            this.vetAcordeonAbiertoId = null;
            this.evitarResetHorario = false;
        },
        iniciarEdicion(cita) {
            this.cerrarDetalle();
            this.modoEdicion = true;
            this.citaEditando = cita;
            this.errorGeneral = null;
            this.horaPreseleccionada = null;

            // Llenar formulario con valores existentes
            this.formulario.titulo = cita.titulo;
            this.formulario.descripcion = cita.descripcion;
            this.formulario.tipo = cita.tipo;
            this.formulario.mascota_id = cita.mascota_id;
            this.formulario.prestacion_id = cita.prestacion_id;
            this.formulario.sucursal_id = cita.box?.sucursal_id || '';
            this.formulario.veterinario_id = cita.veterinario_id;
            this.formulario.box_id = cita.box_id || '';
            this.formulario.cliente_id = cita.mascota?.cliente_id || '';
            this.busquedaPrestacion = '';
            this.mostrarDropdownPrestacion = false;
            this.busquedaCliente = '';
            this.mostrarDropdownCliente = false;
            
            if (cita.fecha_hora) {
                const parts = cita.fecha_hora.split(' ');
                this.formulario.fecha_seleccionada = parts[0];
                this.formulario.fecha_hora = cita.fecha_hora;
            }

            this.evitarResetHorario = true;
            this.mostrarModal = true;

            setTimeout(() => {
                this.cargarHorarios();
                this.evitarResetHorario = false;
            }, 50);
        },
        cerrarModal() {
            this.mostrarModal = false;
            this.busquedaPrestacion = '';
            this.mostrarDropdownPrestacion = false;
            this.busquedaCliente = '';
            this.mostrarDropdownCliente = false;
            this.abrirModalCrear();
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
                box_id: this.formulario.box_id
            };
        },
        guardar() {
            this.formulario.processing = true;
            this.formulario.errors = {};
            this.errorGeneral = null;

            if (this.modoEdicion) {
                this.actualizarCita();
            } else {
                this.crearCita();
            }
        },
        actualizarCita() {
            axios.put(`/api/citas/${this.citaEditando.id}`, { ...this.datosFormulario() })
                .then(() => {
                    this.cerrarModal();
                    this.recargarDatos();
                })
                .catch((error) => { 
                    if (error.response?.status === 422) {
                        this.formulario.errors = error.response.data.errors;
                    } else if (error.response?.status === 409) {
                        this.errorGeneral = error.response.data.error;
                    }
                })
                .finally(() => { this.formulario.processing = false });
        },
        crearCita() {
            axios.post('/api/citas', { ...this.datosFormulario() })
                .then(() => {
                    this.cerrarModal();
                    this.recargarDatos();
                })
                .catch((error) => { 
                    if (error.response?.status === 422) {
                        this.formulario.errors = error.response.data.errors;
                    } else if (error.response?.status === 409) {
                        this.errorGeneral = error.response.data.error;
                    }
                })
                .finally(() => { this.formulario.processing = false });
        },
        confirmarCancelar(cita) {
            this.cerrarDetalle();
            this.$confirmar(
                '¿Cancelar cita?',
                `Se cancelará la cita "${cita.titulo}" de ${cita.mascota?.nombre || 'la mascota'}. El registro se conservará en el historial con estado Cancelada.`
            ).then((resultado) => {
                if (resultado.isConfirmed) {
                    this.cancelarCita(cita.id);
                }
            });
        },
        cancelarCita(citaId) {
            axios.patch(`/api/citas/${citaId}/cancelar`)
                .then(() => {
                    this.recargarDatos();
                })
                .catch((error) => {
                    console.error('Error al cancelar la cita:', error);
                });
        },
        recargarDatos() {
            router.reload({ only: ['citas'] });
        },
        cargarHorarios() {
            if (!this.formulario.fecha_seleccionada) return;

            this.cargandoHorarios = true;
            if (!this.evitarResetHorario) {
                this.formulario.fecha_hora = '';
                this.formulario.tipo = 'normal';
            }

            if (this.formulario.veterinario_id) {
                axios.get('/api/citas/horarios-disponibles', {
                    params: {
                        fecha: this.formulario.fecha_seleccionada,
                        veterinario_id: this.formulario.veterinario_id
                    }
                }).then(r => {
                    this.horariosNormales = r.data.normal;
                    this.horariosUrgencia = r.data.urgencia;

                    // Si se hizo click en una hora específica, verificar si está en la lista de slots devueltos
                    // y preseleccionarla
                    if (this.horaPreseleccionada) {
                        const fechaHoraObjetivo = `${this.formulario.fecha_seleccionada} ${this.horaPreseleccionada}:00`;
                        const encontradoNormal = this.horariosNormales.find(s => s.fecha_hora === fechaHoraObjetivo && s.disponible);
                        const encontradoUrgencia = this.horariosUrgencia.find(s => s.fecha_hora === fechaHoraObjetivo && s.disponible);

                        if (encontradoNormal) {
                            this.seleccionarHorario(encontradoNormal);
                        } else if (encontradoUrgencia) {
                            this.seleccionarHorario(encontradoUrgencia);
                        }
                    }
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
                            fecha: this.formulario.fecha_seleccionada,
                            veterinario_id: vet.id
                        }
                    }).then(r => {
                        this.horariosPorVeterinario[vet.id] = {
                            veterinario: vet,
                            normal: r.data.normal,
                            urgencia: r.data.urgencia
                        };

                        // Si se hizo click en una hora específica, verificar si está en la lista de slots devueltos
                        // y preseleccionarla
                        if (this.horaPreseleccionada) {
                            const fechaHoraObjetivo = `${this.formulario.fecha_seleccionada} ${this.horaPreseleccionada}:00`;
                            const encontradoNormal = r.data.normal.find(s => s.fecha_hora === fechaHoraObjetivo && s.disponible);
                            const encontradoUrgencia = r.data.urgencia.find(s => s.fecha_hora === fechaHoraObjetivo && s.disponible);

                            if (encontradoNormal) {
                                this.seleccionarHorarioAcordeon(encontradoNormal, vet.id);
                            } else if (encontradoUrgencia) {
                                this.seleccionarHorarioAcordeon(encontradoUrgencia, vet.id);
                            }
                        }
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
            this.formulario.veterinario_id = vetId;
            this.formulario.fecha_hora = slot.fecha_hora;
            this.formulario.tipo = slot.tipo;
            setTimeout(() => {
                this.evitarResetHorario = false;
            }, 100);
        },
        seleccionarHorario(slot) {
            this.formulario.fecha_hora = slot.fecha_hora;
            this.formulario.tipo = slot.tipo;
        },
        countCitas(estado) {
            return this.citas.filter(c => c.estado === estado).length;
        },
        formatoFechaHora(fechaStr) {
            if (!fechaStr) return '';
            const f = new Date(fechaStr);
            return f.toLocaleDateString('es-CL', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                hour: '2-digit',
                minute: '2-digit'
            });
        },
        badgeClass(estado) {
            const classes = {
                pendiente: 'bg-warning bg-opacity-10 text-warning',
                en_curso: 'bg-info bg-opacity-10 text-info',
                completada: 'bg-success bg-opacity-10 text-success',
                cancelada: 'bg-danger bg-opacity-10 text-danger'
            };
            return classes[estado] || 'bg-secondary bg-opacity-10 text-secondary';
        }
    }
}
</script>

<style>
/* Modificaciones estéticas de FullCalendar para acoplarse al diseño general de la App */
.fc {
    --fc-border-color: #f1f3f5;
    --fc-button-bg-color: #ffffff;
    --fc-button-border-color: #dee2e6;
    --fc-button-text-color: #212529;
    --fc-button-hover-bg-color: #f8f9fa;
    --fc-button-hover-border-color: #dee2e6;
    --fc-button-active-bg-color: #e9ecef;
    --fc-button-active-border-color: #ced4da;
    --fc-today-bg-color: rgba(79, 70, 229, 0.04);
}

.fc .fc-toolbar-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #212529;
}

.fc .fc-button {
    font-weight: 600;
    font-size: 0.875rem;
    padding: 0.5rem 1rem;
    border-radius: 9999px !important;
    text-transform: capitalize;
    box-shadow: none !important;
}

.fc .fc-button-group {
    gap: 0.25rem;
}

.fc .fc-button-group .fc-button {
    border-radius: 9999px !important;
}

.fc-event {
    cursor: pointer;
    border-radius: 6px !important;
    padding: 2px 6px !important;
    font-size: 0.85em !important;
    font-weight: 600 !important;
    border-left: 4px solid var(--fc-event-border-color) !important;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.fc-event:hover {
    filter: brightness(0.95);
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

.fc-col-header-cell {
    background-color: #f8f9fa;
    padding: 10px 0 !important;
    font-weight: 700 !important;
    color: #495057 !important;
}

.fc-dayGridMonth-view .fc-daygrid-day-number {
    font-weight: 600;
    color: #495057;
    padding: 8px !important;
}

/* Bloqueo y estilización visual de días pasados (Mes) */
.fc-day-past {
    background-color: rgba(240, 240, 240, 0.4) !important;
}
.fc-day-past .fc-daygrid-day-top,
.fc-day-past .fc-daygrid-day-frame {
    cursor: not-allowed !important;
}
.fc-day-past .fc-daygrid-day-number {
    color: #adb5bd !important;
}

/* Bloqueo y estilización visual de columnas pasadas (Semana y Día) */
.fc-timegrid-col.fc-day-past {
    background-color: rgba(240, 240, 240, 0.4) !important;
    cursor: not-allowed !important;
}
.fc-timegrid-col.fc-day-past .fc-timegrid-col-frame {
    cursor: not-allowed !important;
}
.fc-col-header-cell.fc-day-past {
    color: #adb5bd !important;
}
</style>
