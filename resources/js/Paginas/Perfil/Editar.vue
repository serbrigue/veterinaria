<template>
    <Head title="Panel de Usuario" />
    <AuthenticatedLayout>
        <div class="container py-4 py-lg-5">
            <!-- Cabecera del Panel -->
            <div class="row mb-4 align-items-center">
                <div class="col-md-8">
                    <h1 class="h2 fw-bold text-dark mb-1">Mi Panel de Usuario</h1>
                    <p class="text-muted mb-0">Bienvenido/a, <strong>{{ $page.props.auth.user.name }}</strong>. Aquí puedes gestionar tu cuenta, mascotas y servicios.</p>
                </div>
            </div>

            <div class="row g-4">
                <!-- Columna Izquierda: Widgets del Dashboard -->
                <div class="col-lg-7 col-xl-8">
                    
                    <!-- WIDGETS PARA CLIENTE -->
                    <div v-if="isCliente" class="row g-4 mb-4">
                        <!-- Widget 1: Mascotas -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100 rounded-4 border-top border-success border-4 hover-lift transition-all">
                                <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                                    <div class="mb-3">
                                        <div class="d-inline-flex bg-success bg-opacity-10 rounded-circle p-2">
                                            <img :src="mascota?.imagen_url" alt="Foto de la mascota" class="rounded-circle object-fit-cover border shadow-sm" style="width: 80px; height: 80px;">
                                        </div>
                                    </div>  
                                    <h3 class="h5 fw-bold text-dark">Mis Mascotas</h3>
                                    <p class="text-muted small mb-4">Revisa las fichas médicas y perfiles de tus compañeros de vida.</p>
                                    <Link :href="route('mascotas.listado')" class="btn btn-outline-success btn-sm mt-auto rounded-pill px-4 fw-medium">Gestionar Mascotas</Link>
                                </div>
                            </div>
                        </div>

                        <!-- Widget 2: Citas -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100 rounded-4 border-top border-primary border-4 hover-lift transition-all">
                                <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                                    <div class="mb-3">
                                        <div class="d-inline-flex bg-primary bg-opacity-10 rounded-circle p-2">
                                            <img :src="veterinario?.foto_perfil_url" alt="Foto del veterinario" class="rounded-circle object-fit-cover border shadow-sm" style="width: 80px; height: 80px;">
                                        </div>
                                    </div>
                                    <h3 class="h5 fw-bold text-dark">Mis Citas</h3>
                                    <p class="text-muted small mb-4">Agenda nuevas horas o revisa tus próximas visitas a la clínica.</p>
                                    <Link :href="route('citas.listado')" class="btn btn-outline-primary btn-sm mt-auto rounded-pill px-4 fw-medium">Ver Agenda</Link>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Widget 3: Finanzas e Historial -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-4 border-top border-warning border-4 hover-lift transition-all">
                                <div class="card-body p-4 d-flex flex-column flex-sm-row align-items-center justify-content-between text-center text-sm-start gap-3">
                                    <div class="d-flex flex-column flex-sm-row align-items-center gap-3">
                                        <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-3 flex-shrink-0">
                                            <i class="bi bi-receipt fs-3"></i>
                                        </div>
                                        <div>
                                            <h3 class="h5 fw-bold text-dark mb-1">Historial Clínico y Finanzas</h3>
                                            <p class="text-muted small mb-0">Revisa recetas médicas, boletas emitidas y atenciones previas de tus mascotas.</p>
                                        </div>
                                    </div>
                                    <Link :href="route('citas.listado') + '?estado=completada'" class="btn btn-warning text-dark btn-sm rounded-pill px-4 fw-bold flex-shrink-0">Revisar Historial</Link>
                                </div>
                            </div>
                        </div>

                        <!-- Widget 4: Próxima Cita (Destacado) -->
                        <div class="col-md-6" v-if="proximasCitas && proximasCitas.fecha_hora">
                            <div class="card border-0 shadow-sm h-100 rounded-4 border-start border-info border-4">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h3 class="h6 fw-bold text-info text-uppercase mb-0"><i class="bi bi-clock-history me-1"></i> Próxima Cita</h3>
                                        <span class="badge bg-info text-dark rounded-pill">Agendada</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="bg-light rounded p-3 text-center border shadow-sm">
                                            <span class="d-block h4 fw-bold text-dark mb-0">{{ formatearDia(proximasCitas.fecha_hora) }}</span>
                                            <span class="d-block small text-muted text-uppercase fw-bold">{{ formatearMes(proximasCitas.fecha_hora) }}</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <img :src="proximasCitas.mascota?.foto_url || `https://ui-avatars.com/api/?name=${proximasCitas.mascota?.nombre || 'Mascota'}&background=random&color=fff&rounded=true`" 
                                                 alt="Foto mascota" class="rounded-circle shadow-sm" style="width: 50px; height: 50px; object-fit: cover;">
                                            <div>
                                                <h4 class="h6 fw-bold text-dark mb-1">{{ proximasCitas.mascota?.nombre || 'Tu mascota' }}</h4>
                                                <p class="small text-muted mb-0"><i class="bi bi-clock me-1"></i> {{ formatearHora(proximasCitas.fecha_hora) }} hrs</p>
                                            </div>
                                        </div>
                                    </div>
                                    <Link :href="route('citas.detalle', proximasCitas.id)" class="btn btn-outline-info w-100 btn-sm rounded-pill fw-medium">Ver Detalles</Link>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6" v-else>
                            <div class="card border-0 shadow-sm h-100 rounded-4 border-start border-secondary border-opacity-25 border-4 bg-light">
                                <div class="card-body p-4 text-center d-flex flex-column justify-content-center align-items-center">
                                    <i class="bi bi-calendar-x text-muted opacity-50 mb-2 fs-1"></i>
                                    <h3 class="h6 fw-bold text-secondary mb-1">Sin Citas Próximas</h3>
                                    <p class="small text-muted mb-0">No tienes agendada ninguna visita por ahora.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Widget 5: Última Cita (Historial) -->
                        <div class="col-md-6" v-if="historialClinico && historialClinico.id">
                            <div class="card border-0 shadow-sm h-100 rounded-4 border-start border-secondary border-4">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h3 class="h6 fw-bold text-secondary text-uppercase mb-0"><i class="bi bi-journal-check me-1"></i> Última Atención</h3>
                                        <span class="badge bg-secondary rounded-pill">Completada</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="bg-light rounded p-3 text-center border shadow-sm">
                                            <span class="d-block h4 fw-bold text-dark mb-0">{{ formatearDia(historialClinico.fecha_hora) }}</span>
                                            <span class="d-block small text-muted text-uppercase fw-bold">{{ formatearMes(historialClinico.fecha_hora) }}</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <img :src="historialClinico.mascota?.foto_url || `https://ui-avatars.com/api/?name=${historialClinico.mascota?.nombre || 'Mascota'}&background=random&color=fff&rounded=true`" 
                                                 alt="Foto mascota" class="rounded-circle shadow-sm border" style="width: 50px; height: 50px; object-fit: cover;">
                                            <div>
                                                <h4 class="h6 fw-bold text-dark mb-1">{{ historialClinico.mascota?.nombre || 'Tu mascota' }}</h4>
                                                <p class="small text-muted mb-0"><i class="bi bi-check2-circle text-success me-1"></i> Finalizada</p>
                                            </div>
                                        </div>
                                    </div>
                                    <Link :href="route('citas.detalle', historialClinico.id)" class="btn btn-outline-secondary w-100 btn-sm rounded-pill fw-medium">Ver Resumen</Link>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6" v-else>
                            <div class="card border-0 shadow-sm h-100 rounded-4 border-start border-secondary border-opacity-25 border-4 bg-light">
                                <div class="card-body p-4 text-center d-flex flex-column justify-content-center align-items-center">
                                    <i class="bi bi-journal-x text-muted opacity-50 mb-2 fs-1"></i>
                                    <h3 class="h6 fw-bold text-secondary mb-1">Sin Historial</h3>
                                    <p class="small text-muted mb-0">Aún no tienes registros de atenciones previas.</p>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- WIDGETS PARA VETERINARIO -->
                    <div v-if="isVeterinario" class="row g-4 mb-4">
                        <!-- Widget 1: Agenda Clínica -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100 rounded-4 border-top border-primary border-4 hover-lift transition-all">
                                <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                                    <div class="mb-3">
                                        <div class="d-inline-flex bg-primary bg-opacity-10 text-primary rounded-circle p-3">
                                            <i class="bi bi-calendar-check-fill fs-2"></i>
                                        </div>
                                    </div>
                                    <h3 class="h5 fw-bold text-dark">Mi Agenda Clínica</h3>
                                    <div class="d-flex flex-column gap-2 mt-auto">
                                        <Link :href="route('citas.listado') + (usuario.veterinario ? '?veterinario_id=' + usuario.veterinario.id : '')" class="btn btn-outline-primary btn-sm rounded-pill px-4 fw-medium">Ver Pacientes de Hoy</Link>
                                        <button @click="abrirModalHorario" class="btn btn-outline-secondary btn-sm rounded-pill px-4 fw-medium">
                                            <i class="bi bi-clock me-1"></i> Gestión de Horarios
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Widget 2: Fichas de Pacientes -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100 rounded-4 border-top border-success border-4 hover-lift transition-all">
                                <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                                    <div class="mb-3">
                                        <div class="d-inline-flex bg-success bg-opacity-10 text-success rounded-circle p-3">
                                            <i class="bi bi-clipboard2-pulse-fill fs-2"></i>
                                        </div>
                                    </div>
                                    <h3 class="h5 fw-bold text-dark">Gestión de Pacientes</h3>
                                    <p class="text-muted small mb-4">Accede rápidamente al historial y fichas médicas de las mascotas.</p>
                                    <Link :href="route('mascotas.listado')" class="btn btn-outline-success btn-sm mt-auto rounded-pill px-4 fw-medium">Buscar Paciente</Link>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Widget 3: Catálogo Médico -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-4 border-top border-info border-4 hover-lift transition-all">
                                <div class="card-body p-4 d-flex flex-column flex-sm-row align-items-center justify-content-between text-center text-sm-start gap-3">
                                    <div class="d-flex flex-column flex-sm-row align-items-center gap-3">
                                        <div class="bg-info bg-opacity-10 text-info rounded-circle p-3 flex-shrink-0">
                                            <i class="bi bi-box-seam-fill fs-3"></i>
                                        </div>
                                        <div>
                                            <h3 class="h5 fw-bold text-dark mb-1">Inventario y Catálogo Médico</h3>
                                            <p class="text-muted small mb-0">Consulta stock de insumos de tu sucursal y prestaciones disponibles.</p>
                                        </div>
                                    </div>
                                    <Link :href="route('insumos.listado')" class="btn btn-info text-white btn-sm rounded-pill px-4 fw-bold flex-shrink-0">Ir a Insumos</Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Próxima Cita para Veterinario -->
                    <div v-if="isVeterinario" class="row g-4 mb-4">
                        <div class="col-md-12" v-if="proximaCitaVet && proximaCitaVet.fecha_hora">
                            <div class="card border-0 shadow-sm rounded-4 border-start border-info border-4">
                                <div class="card-body p-4 d-flex flex-column flex-sm-row align-items-center justify-content-between text-center text-sm-start gap-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-light rounded p-3 text-center border shadow-sm">
                                            <span class="d-block h4 fw-bold text-dark mb-0">{{ formatearDia(proximaCitaVet.fecha_hora) }}</span>
                                            <span class="d-block small text-muted text-uppercase fw-bold">{{ formatearMes(proximaCitaVet.fecha_hora) }}</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <img :src="proximaCitaVet.mascota?.foto_url || `https://ui-avatars.com/api/?name=${proximaCitaVet.mascota?.nombre || 'Mascota'}&background=random&color=fff&rounded=true`" 
                                                 alt="Foto mascota" class="rounded-circle shadow-sm" style="width: 50px; height: 50px; object-fit: cover;">
                                            <div>
                                                <h4 class="h6 fw-bold text-dark mb-1">Próxima Cita: {{ proximaCitaVet.mascota?.nombre || 'Paciente' }}</h4>
                                                <p class="small text-muted mb-0"><i class="bi bi-clock me-1"></i> {{ formatearHora(proximaCitaVet.fecha_hora) }} hrs</p>
                                            </div>
                                        </div>
                                    </div>
                                    <Link :href="route('citas.detalle', proximaCitaVet.id)" class="btn btn-outline-info btn-sm rounded-pill px-4 fw-medium flex-shrink-0">Ver Detalles</Link>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12" v-else>
                            <div class="card border-0 shadow-sm rounded-4 border-start border-secondary border-opacity-25 border-4 bg-light">
                                <div class="card-body p-4 text-center d-flex align-items-center justify-content-center gap-3">
                                    <i class="bi bi-calendar-x text-muted opacity-50 fs-2"></i>
                                    <div class="text-start">
                                        <h3 class="h6 fw-bold text-secondary mb-0">Agenda Libre</h3>
                                        <p class="small text-muted mb-0">No tienes citas programadas próximamente.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- WIDGETS PARA ADMINISTRADOR -->
                    <div v-if="isAdmin" class="row g-4 mb-4">
                        <!-- Widget 1: Resumen General -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100 rounded-4 border-top border-danger border-4 hover-lift transition-all">
                                <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                                    <div class="mb-3">
                                        <div class="d-inline-flex bg-danger bg-opacity-10 text-danger rounded-circle p-3">
                                            <i class="bi bi-graph-up-arrow fs-2"></i>
                                        </div>
                                    </div>
                                    <h3 class="h5 fw-bold text-dark">Métricas del Sistema</h3>
                                    <p class="text-muted small mb-4">Supervisa citas, ingresos financieros y nuevas mascotas registradas.</p>
                                    <Link :href="route('panel')" class="btn btn-outline-danger btn-sm mt-auto rounded-pill px-4 fw-medium">Ver Reportes</Link>
                                </div>
                            </div>
                        </div>

                        <!-- Widget 2: Control de Usuarios -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100 rounded-4 border-top border-primary border-4 hover-lift transition-all">
                                <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                                    <div class="mb-3">
                                        <div class="d-inline-flex bg-primary bg-opacity-10 text-primary rounded-circle p-3">
                                            <i class="bi bi-people-fill fs-2"></i>
                                        </div>
                                    </div>
                                    <h3 class="h5 fw-bold text-dark">Gestión de Usuarios</h3>
                                    <p class="text-muted small mb-4">Administra cuentas de clientes, veterinarios y asigna permisos.</p>
                                    <Link :href="route('clientes.listado')" class="btn btn-outline-primary btn-sm mt-auto rounded-pill px-4 fw-medium">Directorio de Cuentas</Link>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Widget 3: Infraestructura -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-4 border-top border-warning border-4 hover-lift transition-all">
                                <div class="card-body p-4 d-flex flex-column flex-sm-row align-items-center justify-content-between text-center text-sm-start gap-3">
                                    <div class="d-flex flex-column flex-sm-row align-items-center gap-3">
                                        <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-3 flex-shrink-0">
                                            <i class="bi bi-building-fill fs-3"></i>
                                        </div>
                                        <div>
                                            <h3 class="h5 fw-bold text-dark mb-1">Sucursales y Boxes</h3>
                                            <p class="text-muted small mb-0">Control total sobre la red de clínicas y organización de boxes físicos.</p>
                                        </div>
                                    </div>
                                    <Link :href="route('sucursales.listado')" class="btn btn-warning text-dark btn-sm rounded-pill px-4 fw-bold flex-shrink-0">Gestionar Sedes</Link>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Columna Derecha: Configuración de la Cuenta -->
                <div class="col-lg-5 col-xl-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-light bg-opacity-50 border-bottom border-light pt-4 px-4 pb-3 rounded-top-4">
                            <h3 class="h5 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                <i class="bi bi-person-gear text-secondary"></i> Ajustes de Cuenta
                            </h3>
                            <p class="small text-muted mb-0 mt-1">Configura tu perfil y seguridad.</p>
                        </div>
                        <div class="card-body p-4">
                            <div class="accordion custom-accordion" id="accordionPerfil">
                                <!-- Info Personal -->
                                <div class="accordion-item bg-transparent">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button bg-transparent fw-semibold text-dark px-1 py-3" :class="{'collapsed': tabActiva !== 'info'}" type="button" @click="tabActiva = tabActiva === 'info' ? '' : 'info'">
                                            <i class="bi bi-person-lines-fill me-2 text-primary"></i> Información Personal
                                        </button>
                                    </h2>
                                    <div class="accordion-collapse collapse" :class="{'show': tabActiva === 'info'}">
                                        <div class="accordion-body px-1 pt-2 pb-4">
                                            <ActualizarInformacion :must-verify-email="mustVerifyEmail" :status="status" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Seguridad -->
                                <div class="accordion-item bg-transparent">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button bg-transparent fw-semibold text-dark px-1 py-3" :class="{'collapsed': tabActiva !== 'seguridad'}" type="button" @click="tabActiva = tabActiva === 'seguridad' ? '' : 'seguridad'">
                                            <i class="bi bi-shield-lock-fill me-2 text-success"></i> Seguridad y Contraseña
                                        </button>
                                    </h2>
                                    <div class="accordion-collapse collapse" :class="{'show': tabActiva === 'seguridad'}">
                                        <div class="accordion-body px-1 pt-2 pb-4">
                                            <ActualizarContrasena />
                                        </div>
                                    </div>
                                </div>

                                <!-- Eliminar Cuenta -->
                                <div class="accordion-item bg-transparent border-0">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button bg-transparent fw-semibold text-danger px-1 py-3" :class="{'collapsed': tabActiva !== 'eliminar'}" type="button" @click="tabActiva = tabActiva === 'eliminar' ? '' : 'eliminar'">
                                            <i class="bi bi-exclamation-triangle-fill me-2"></i> Zona de Peligro
                                        </button>
                                    </h2>
                                    <div class="accordion-collapse collapse" :class="{'show': tabActiva === 'eliminar'}">
                                        <div class="accordion-body px-1 pt-2 pb-0">
                                            <EliminarUsuario />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal de Gestión de Horarios -->
        <div v-if="mostrarModalHorario" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5); overflow-y: auto; z-index: 1060;">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow rounded-4 bg-white">
                    <div class="modal-header border-bottom-0 pt-4 px-4 pb-0">
                        <div>
                            <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                <i class="bi bi-clock-fill text-primary"></i> Configuración de Horario Semanal
                            </h5>
                            <p class="text-muted small mb-0">Define los días y horas en los que atiendes consultas normales y de urgencia.</p>
                        </div>
                        <button type="button" class="btn-close" @click="mostrarModalHorario = false" aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-4 py-3">
                        <div class="container-fluid px-0">
                            <!-- Loop de 7 días -->
                            <div v-for="day in horarioForm.horario" :key="day.dia" class="row align-items-center mb-3 pb-3 border-bottom border-light">
                                <div class="col-md-3 mb-2 mb-md-0">
                                    <h6 class="fw-bold text-dark mb-0">{{ nombreDia(day.dia) }}</h6>
                                </div>
                                <div class="col-md-9">
                                    <div class="row g-3">
                                        <!-- Horario Normal -->
                                        <div class="col-sm-6 border-end border-light">
                                            <div class="form-check mb-2">
                                                <input type="checkbox" v-model="day.normal.activo" class="form-check-input" :id="'normal-' + day.dia">
                                                <label class="form-check-label small fw-semibold text-secondary" :for="'normal-' + day.dia">
                                                    Horario Normal
                                                </label>
                                            </div>
                                            <div v-if="day.normal.activo" class="d-flex gap-2 align-items-center">
                                                <input type="time" v-model="day.normal.inicio" class="form-control form-control-sm border-light" required>
                                                <span class="text-muted small">a</span>
                                                <input type="time" v-model="day.normal.fin" class="form-control form-control-sm border-light" required>
                                            </div>
                                            <div v-else class="text-muted small italic">No atiende (Normal)</div>
                                        </div>
                                        <!-- Urgencias -->
                                        <div class="col-sm-6">
                                            <div class="form-check mb-2">
                                                <input type="checkbox" v-model="day.urgencia.activo" class="form-check-input" :id="'urgencia-' + day.dia">
                                                <label class="form-check-label small fw-semibold text-warning" :for="'urgencia-' + day.dia">
                                                    Urgencias
                                                </label>
                                            </div>
                                            <div v-if="day.urgencia.activo" class="d-flex gap-2 align-items-center">
                                                <input type="time" v-model="day.urgencia.inicio" class="form-control form-control-sm border-light" required>
                                                <span class="text-muted small">a</span>
                                                <input type="time" v-model="day.urgencia.fin" class="form-control form-control-sm border-light" required>
                                            </div>
                                            <div v-else class="text-muted small italic">No atiende (Urgencias)</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pb-4 px-4 pt-0 justify-content-end gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-medium" @click="mostrarModalHorario = false" :disabled="cargandoHorario">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" @click="guardarHorario" :disabled="cargandoHorario">
                            <span v-if="cargandoHorario" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                            Guardar Cambios
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.hover-lift:hover {
    transform: translateY(-4px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15) !important;
}
.custom-accordion .accordion-button:not(.collapsed) {
    background-color: transparent;
    color: var(--bs-primary);
    box-shadow: none;
}
.custom-accordion .accordion-button:focus {
    box-shadow: none;
}
</style>

<script>
import AuthenticatedLayout from '@/Disenos/LayoutAutenticado.vue';
import EliminarUsuario from './Partials/EliminarUsuario.vue';
import ActualizarContrasena from './Partials/ActualizarContrasena.vue';
import ActualizarInformacion from './Partials/ActualizarInformacion.vue';
import { Head, Link } from '@inertiajs/vue3';

export default {
    components: {
        AuthenticatedLayout,
        EliminarUsuario,
        ActualizarContrasena,
        ActualizarInformacion,
        Head,
        Link,
    },
    props: {
        proximasCitas: {
            type: Object,
        },
        proximaCitaVet: {
            type: Object,
        },
        historialClinico: {
            type: Object,
           
        },
        mascota: {
            type: Object,
            
        },  
        veterinario: {
            type: Object,
            
        },
        mustVerifyEmail: {
            type: Boolean,
        },
        status: {
            type: String,
        },
    },
    data() {
        return {
            tabActiva: 'info',
            mostrarModalHorario: false,
            cargandoHorario: false,
            horarioForm: {
                horario: []
            }
        }
    },
    methods: {
        formatearDia(fechaIso) {
            if (!fechaIso) return '--';
            const fecha = new Date(fechaIso);
            return fecha.getDate().toString().padStart(2, '0');
        },
        formatearMes(fechaIso) {
            if (!fechaIso) return '---';
            const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            const fecha = new Date(fechaIso);
            return meses[fecha.getMonth()];
        },
        formatearHora(fechaIso) {
            if (!fechaIso) return '--:--';
            const fecha = new Date(fechaIso);
            return fecha.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' });
        },
        abrirModalHorario() {
            const diasDef = [1, 2, 3, 4, 5, 6, 7];
            const currentHorario = this.veterinario?.horario || [];
            
            this.horarioForm.horario = diasDef.map(d => {
                const match = currentHorario.find(h => h.dia === d);
                if (match) {
                    return JSON.parse(JSON.stringify(match));
                } else {
                    const esFinSemana = d === 6 || d === 7;
                    return {
                        dia: d,
                        normal: {
                            activo: !esFinSemana,
                            inicio: '09:00',
                            fin: '18:00'
                        },
                        urgencia: {
                            activo: !esFinSemana,
                            inicio: '18:00',
                            fin: '21:30'
                        }
                    };
                }
            });
            this.mostrarModalHorario = true;
        },
        nombreDia(diaNum) {
            const nombres = {
                1: 'Lunes',
                2: 'Martes',
                3: 'Miércoles',
                4: 'Jueves',
                5: 'Viernes',
                6: 'Sábado',
                7: 'Domingo'
            };
            return nombres[diaNum] || '';
        },
        guardarHorario() {
            this.cargandoHorario = true;
            axios.patch(`/api/veterinarios/${this.veterinario.id}/horario`, {
                horario: this.horarioForm.horario
            }).then(() => {
                this.$inertia.reload({
                    only: ['veterinario'],
                    onSuccess: () => {
                        this.mostrarModalHorario = false;
                    }
                });
            }).catch(err => {
                console.error(err);
                alert(err.response?.data?.message || 'Error al guardar el horario');
            }).finally(() => {
                this.cargandoHorario = false;
            });
        }
    },
    computed: {
        usuario() {
            return this.$page.props.auth.user;
        },
        isCliente() {
            return this.usuario?.rol?.nombre_interno === 'cliente';
        },
        isVeterinario() {
            return this.usuario?.rol?.nombre_interno === 'veterinario';
        },
        isAdmin() {
            return this.usuario?.rol?.nombre_interno === 'admin';
        }
    }
}
</script>
