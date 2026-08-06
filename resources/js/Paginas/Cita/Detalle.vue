<template>
    <!-- ================================================================================== -->
    <!-- COMPONENTE: Detalle -->
    <!-- ================================================================================== -->

    <Head :title="'Cita - ' + (cita.titulo || 'Detalle')" />
    <AuthenticatedLayout>
        <div class="container py-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center gap-3">
                    <!-- Boton para volver al listado de citas -->
                    <Link :href="route('citas.listado')" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                        <i class="bi bi-arrow-left"></i> Volver
                    </Link>
                    <h1 class="h3 mb-0">Detalle de la Cita</h1>
                </div>
                <!-- Si la cita está en estado pendiente y el usuario puede cancelar la cita renderizamos el boton de cancelar -->
                <div v-if="estadoActual === 'pendiente' && puedeCancelarCita" class="d-flex gap-2">
                    <!-- Al dar click se ejecuta la funcion confirmarEliminar -->
                    <button class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1" @click="confirmarEliminar" :disabled="procesando">
                        <i class="bi bi-trash"></i> Eliminar Cita
                    </button>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            
                            <div class="d-flex justify-content-between align-items-start mb-4 bg-light p-3 rounded border">
                                <div>
                                    <!-- Fecha y hora de la cita -->
                                    <div class="text-muted small mb-2"><i class="bi bi-clock-fill me-1"></i> Programada: {{ formatearFechaHora(cita.fecha_hora) }}</div>
                                    <!-- Estado de la cita -->
                                    <span class="badge rounded-pill px-3 py-2 mt-1" :class="{
                                        'bg-warning text-dark': estadoActual === 'pendiente',
                                        'bg-success':           estadoActual === 'completada',
                                        'bg-danger':            estadoActual === 'cancelada',
                                        'bg-primary':           estadoActual === 'en_curso',
                                    }">
                                        {{ estadoActual ? estadoActual.charAt(0).toUpperCase() + estadoActual.slice(1) : 'Pendiente' }}
                                    </span>
                                </div>
                                <!-- Si el usuario puede editar el estado renderizamos los botones de cambio de estado-->
                                <div v-if="puedeEditarEstado" class="d-flex flex-column align-items-end gap-2">
                                    <!-- Si la cita esta en estado pendiente o en curso renderizamos el grupo de botones -->
                                    <div v-if="estadoActual === 'pendiente' || estadoActual === 'en_curso'" class="btn-group btn-group-sm">
                                        <!-- Si la cita esta en estado pendiente o en curso y el usuario puede editar el estado renderizamos el boton de en curso-->
                                        <button @click="marcarEnCurso" class="btn btn-outline-primary" :disabled="procesando || estadoActual === 'en_curso'">
                                            <i class="bi bi-play-fill"></i> En curso
                                        </button>
                                        <!-- Si el usuario puede editar el estado renderizamos el boton de completada-->
                                        <button @click="confirmarCompletar" class="btn btn-success" :disabled="procesando">
                                            <i class="bi bi-check-lg"></i> Completada
                                        </button>
                                        <!-- Si la cita esta en estado pendiente o en curso y el usuario puede editar el estado renderizamos el boton de cancelar-->
                                        <button v-if="puedeCancelarCita" @click="confirmarEliminar" class="btn btn-outline-danger" :disabled="procesando">
                                            <i class="bi bi-x-circle-fill"></i> Cancelar
                                        </button>
                                    </div>
                                    <!-- Si el proceso de actualizacion esta en curso renderizamos un mensaje de cargando -->
                                    <small v-if="procesando" class="text-muted">
                                        <span class="spinner-border spinner-border-sm me-1"></span> Actualizando...
                                    </small>
                                </div>
                            </div>

                            <!-- Título de la cita -->
                            <h2 class="h4 fw-bold text-dark mb-3">{{ cita.titulo }}</h2>
                            <!-- Descripción o motivo de la cita -->
                            <div class="mb-4">
                                <h3 class="h6 text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">Motivo o Descripción</h3>
                                <p class="text-secondary bg-light p-3 rounded border-start border-primary border-3 mb-0" style="white-space: pre-wrap;">
                                    {{ cita.descripcion || 'Sin descripción detallada.' }}
                                </p>
                            </div>

                            <!-- Ficha Clínica Integrada -->
                            <!-- Si el estado de la cita es 'en_curso' o 'completada' renderizamos la ficha clínica -->
                            <div class="mb-4" v-if="estadoCita === 'en_curso' || estadoCita === 'completada'">
                                <!-- Pasamos las props necesarias al componente FichaClinicaPanel  -->
                                <FichaClinicaPanel
                                    :cita="cita"
                                    :insumos-sucursal="insumosSucursal"
                                    :catalogo-medicamentos="catalogoMedicamentos"
                                    :catalogo-vacunas="catalogoVacunas"
                                    :cargos-list="cargosList"
                                    :error-cargo="errorCargo"
                                    :procesando-cargo="procesandoCargo"
                                    :guardando-cargo="guardandoCargo"
                                    :estado-cita="estadoCita"
                                    :forzar-lectura="!puedeEditarNotas || estadoCita === 'completada'"
                                    @actualizado="$inertia.reload()"
                                    @actualizar-cantidad="actualizarCantidad"
                                    @eliminar-cargo="eliminarCargo"
                                    @agregar-cargo="manejarAgregarCargo"
                                />
                            </div>

                            <!-- Notas Administrativas / Observaciones (Solo personal interno) -->
                            <template v-if="puedeVerNotasInternas">
                                <!-- Si el usuario puede editar las notas y la cita no esta completada renderizamos el campo de notas administrativas -->
                                <div v-if="puedeEditarNotas && estadoCita != 'completada'" class="mb-0">
                                <h3 class="h6 text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">Notas Administrativas / Observaciones (Autoguardado)</h3>
                                <div class="position-relative">
                                    <!--Enlace de datos bidireccional con "notasConsulta" -->
                                    <textarea 
                                        v-model="notasConsulta" 
                                        class="form-control" 
                                        rows="3" 
                                        placeholder="Escribe notas generales o administrativas aquí..."
                                        :disabled="procesando"
                                    ></textarea>
                                    <!-- Al presionar este boton se guarda las notas administrativas -->
                                    <button class="btn btn-outline-primary btn-sm mt-2" @click="guardarNotas(notasConsulta)" :disabled="procesando">
                                        <i class="bi bi-save"></i> Guardar Notas
                                    </button>
                                    <!-- Si esta guardando notas, muestra un spinner -->
                                    <div v-if="guardandoNotas" class="position-absolute bottom-0 end-0 p-2">
                                        <span class="spinner-border spinner-border-sm text-primary"></span>
                                    </div>
                                </div>
                                <!-- Si no se puede editar las notas o la cita esta completada, muestra las notas -->
                                <small class="text-muted mt-1 d-block">Las notas se guardan automáticamente al hacer clic fuera del cuadro de texto.</small>
                            </div>
                            <div class="mb-0" v-else-if="!puedeEditarNotas || cita.estado === 'completada'">
                                <h3 class="h6 text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">Notas Administrativas / Observaciones</h3>
                                <p class="text-secondary bg-light p-3 rounded border-start border-primary border-3 mb-0" style="white-space: pre-wrap;">
                                    {{ cita.notas || 'Sin notas.' }}
                                </p>
                            </div>
                            </template>
                            
                            <!-- Si el cliente no puede editar la cita y la cita esta completada, muestra el resumen de cobro -->
                            <div v-if="!puedeEditarCita && cita.estado === 'completada'" class="mb-0 mt-4">
                                <h3 class="h6 text-uppercase text-muted fw-bold mb-3" style="font-size: 0.75rem; letter-spacing: 0.5px;"><i class="bi bi-receipt me-1"></i> Resumen de Cobro</h3>
                                
                                <div class="card border border-light-subtle shadow-sm rounded-4 overflow-hidden">
                                    <div class="card-body p-0">
                                        <!-- Prestación Base -->
                                        <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="bi bi-heart-pulse-fill fs-5"></i>
                                                </div>
                                                <div>
                                                    <h4 class="h6 fw-bold text-dark mb-0">{{ prestacion.nombre }}</h4>
                                                    <span class="small text-muted">Prestación médica base</span>
                                                </div>
                                            </div>
                                            <span class="fw-bold fs-5 text-dark">${{ Number(prestacion.precio_base).toLocaleString('es-CL') }}</span>
                                        </div>

                                        <!-- Insumos Adicionales -->
                                        <!-- Si hay cargos que sean insumos, los muestra -->
                                        <div v-if="cargosList && cargosList.some(c => c.insumo)" class="p-3 border-bottom bg-light bg-opacity-50">
                                            <h5 class="small fw-bold text-muted text-uppercase mb-3" style="letter-spacing: 0.5px;">Insumos Utilizados</h5>
                                            
                                            <!-- Si hay cargos que sean insumos, los muestra -->            
                                            <div v-for="cargo in cargosList.filter(c => c.insumo)" :key="'insumo-' + cargo.id"
                                                 class="d-flex justify-content-between align-items-center mb-2 p-2 rounded-3 border bg-white shadow-sm transition-all hover-shadow">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="bg-warning bg-opacity-10 text-warning rounded p-1 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                                        <i class="bi bi-capsule fs-6"></i>
                                                    </div>
                                                    <div>
                                                        <span class="small fw-bold text-dark d-block">{{ cargo.insumo.nombre }}</span>
                                                        <span class="text-muted d-block" style="font-size: 0.7rem;">
                                                            {{ cargo.cantidad }} unid. &times; ${{ Number(cargo.precio_unitario).toLocaleString('es-CL') }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <span class="small fw-bold text-secondary">${{ Number(cargo.subtotal).toLocaleString('es-CL') }}</span>
                                            </div>
                                        </div>

                                        <!-- Total Final -->
                                        <div class="d-flex justify-content-between align-items-center p-3 bg-primary text-white rounded-bottom-4 shadow-inner">
                                            <span class="fw-bold text-uppercase d-flex align-items-center gap-2" style="letter-spacing: 1px;">
                                                <i class="bi bi-cash-stack fs-5"></i> Total a Pagar
                                            </span>
                                            <h3 class="fw-bold mb-0 text-white">{{ totalFinalFormateado }}</h3>
                                        </div>
                                        
                                        <!-- Estado de Transacción -->
                                        <!-- Si la cita tiene una transacción, la muestra -->
                                        <div v-if="cita.transaccion" class="p-3 bg-light text-center border-top rounded-bottom-4 d-flex justify-content-between align-items-center">
                                            <span class="badge px-3 py-2 fs-6" :class="{
                                                'bg-success': cita.transaccion.estado === 'pagado',
                                                'bg-warning text-dark': cita.transaccion.estado === 'pendiente',
                                                'bg-info': cita.transaccion.estado === 'abonado',
                                                'bg-danger': cita.transaccion.estado === 'anulado'
                                            }">
                                                <i class="bi bi-info-circle me-1"></i> Estado: {{ cita.transaccion.estado.toUpperCase() }}
                                            </span>
                                            
                                            <!-- Si la transacción esta pendiente, muestra el botón de pagar en línea -->
                                            <Link v-if="cita.transaccion.estado === 'pendiente'" 
                                                  :href="route('transacciones.checkout', cita.transaccion.id)" 
                                                  class="btn btn-primary fw-bold px-4 shadow-sm">
                                                <i class="bi bi-credit-card me-2"></i> Pagar en Línea
                                            </Link>
                                            <!-- Si la transacción esta pagada, muestra el botón de ver comprobante -->
                                            <button v-else-if="cita.transaccion.estado === 'pagado'" class="btn btn-outline-primary fw-bold px-4 shadow-sm" @click="verComprobante">
                                                <i class="bi bi-receipt me-2"></i> Ver Comprobante
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- ===== SECCIÓN PRIVILEGIADA VETERINARIO ===== -->
                            <!-- Si el cliente puede editar la cita, muestra la sección privilegiada -->
                            <div v-if="puedeEditarCita" class="mt-4">

                                <!-- Prestación solicitada -->
                                <div class="mb-4 p-3 rounded-3 border border-info border-opacity-50 bg-info bg-opacity-10">
                                    <h3 class="h6 text-uppercase fw-bold text-info mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                        <i class="bi bi-clipboard2-check-fill me-1"></i> Prestación Solicitada
                                    </h3>
                                    <!-- Si la cita tiene una prestación, la muestra -->
                                    <div v-if="prestacion">
                                        <div :key="prestacion.id" class="d-flex justify-content-between align-items-center py-2 border-bottom border-info border-opacity-25">
                                            <div>
                                                <span class="fw-semibold text-dark">{{ prestacion.nombre }}</span>
                                                <span class="text-muted small ms-2">{{ prestacion.especialidad?.nombre }}</span>
                                            </div>
                                            <span class="badge bg-info text-dark rounded-pill">${{ Number(prestacion.precio_base).toLocaleString('es-CL') }}</span>
                                        </div>
                                    </div>
                                    <p v-else class="text-muted small mb-0"><i class="bi bi-dash-circle me-1"></i> Sin prestación solicitada para esta cita.</p>
                                </div>

                                <!-- Elementos Utilizados gestionados en la Ficha Clínica -->
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="d-flex flex-column gap-4">
                        
                        <div class="card border-0 shadow-sm border-top border-info border-4">
                            <div class="card-header bg-transparent border-0 pt-3 px-4 pb-0">
                                <h3 class="h6 mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                                    <i class="bi bi-bandaid-fill text-info"></i> Médico Tratante
                                </h3>
                            </div>
                            <div class="card-body p-4 pt-3">
                                <!-- Si la cita tiene un veterinario, lo muestra -->
                                <div v-if="cita.veterinario" class="d-flex align-items-center gap-3">
                                    <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        <!-- Si el veterinario tiene una foto de perfil, la muestra -->
                                        <img v-if="cita.veterinario.foto_perfil_url" :src="cita.veterinario.foto_perfil_url" :alt="cita.veterinario.usuario?.name" class="rounded-circle object-fit-cover shadow-sm" style="width: 60px; height: 60px;">
                                        <i v-else class="bi bi-person-fill fs-4"></i>
                                    </div>
                                    <div>
                                        <h4 class="h6 mb-1 fw-bold text-dark">
                                            <Link :href="route('veterinarios.detalle', cita.veterinario.id)" class="text-decoration-none text-dark hover-primary">
                                                {{ cita.veterinario.usuario?.name || 'Desconocido' }}
                                            </Link>
                                        </h4>
                                        <p class="text-muted small mb-0">Teléfono: {{ cita.veterinario.telefono || 'Sin teléfono' }}</p>
                                    </div>
                                </div>
                                <div v-else class="text-muted text-center py-2 small">
                                    No hay veterinario asignado.
                                </div>
                            </div>
                        </div>

                        <!-- UBICACIÓN / BOX -->
                        <div class="card border-0 shadow-sm border-top border-secondary border-4">
                            <div class="card-header bg-transparent border-0 pt-3 px-4 pb-0">
                                <h3 class="h6 mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                                    <i class="bi bi-door-open-fill text-secondary"></i> Ubicación / Box
                                </h3>
                            </div>
                            <div class="card-body p-4 pt-3">
                                <!-- Si la cita tiene un box, lo muestra -->
                                <div v-if="cita.box" class="d-flex align-items-center gap-3 mb-3">
                                    <img :src="cita.box.imagen_url || '/images/default_box.png'" :alt="cita.box.nombre" class="rounded-circle object-fit-cover shadow-sm" style="width: 50px; height: 50px;">
                                    <div>
                                        <h4 class="h6 mb-1 fw-bold text-dark">
                                            {{ cita.box.nombre }}
                                        </h4>
                                        <p class="text-muted small mb-0 d-flex align-items-center gap-1">
                                            Sucursal: 
                                            <img :src="cita.box.sucursal?.imagen_url || '/images/default_sucursal.png'" :alt="cita.box.sucursal?.nombre || 'Sucursal'" class="rounded-circle object-fit-cover shadow-sm" style="width: 20px; height: 20px;">
                                            {{ cita.box.sucursal?.nombre || 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                                <div v-else class="text-muted text-center py-2 small mb-3">
                                    No hay box asignado para esta cita.
                                </div>

                                <!-- Formulario de asignación/cambio de box para personal autorizado -->
                                <!-- Si el cliente puede asignar box y la cita no esta completada o cancelada, muestra el formulario de asignación de box -->
                                <div v-if="puedeAsignarBox && estadoActual !== 'completada' && estadoActual !== 'cancelada'" class="border rounded-3 p-3 bg-white mt-2">
                                    <h4 class="h6 fw-semibold text-dark mb-2"><i class="bi bi-pencil-square me-1 text-primary"></i> Asignar Box</h4>
                                    <div class="d-flex flex-column gap-2">
                                        <div>
                                            <!-- Si se asigna un box, se actualiza el estado de la cita -->
                                            <select v-model="boxAsignadoId" class="form-select form-select-sm" :disabled="guardandoBox">
                                                <option value="">Seleccionar box...</option>
                                                <!-- Si hay boxes disponibles, los muestra -->
                                                <option v-for="b in boxes" :key="b.id" :value="b.id">
                                                    {{ b.nombre }}
                                                </option>
                                            </select>
                                        </div>
                                        <!-- Si el box es guardado, muestra el spinner -->
                                        <button @click="guardarBox" class="btn btn-primary btn-sm w-100 mt-1 fw-semibold" :disabled="guardandoBox || boxAsignadoId === (cita.box_id || '')">
                                            <span v-if="guardandoBox" class="spinner-border spinner-border-sm me-1"></span>
                                            <i v-else class="bi bi-save me-1"></i> Guardar Box
                                        </button>
                                    </div>
                                    <!-- Si hay un error al guardar el box, lo muestra -->
                                    <div v-if="errorBox" class="alert alert-danger alert-sm py-1 px-2 mt-2 small mb-0">{{ errorBox }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- EQUIPO MEDICO DE APOYO (SOLO CIRUGIAS) -->
                        <!-- Si la prestacion es una cirugia, muestra el equipo medico de apoyo -->
                        <div v-if="cita.prestacion?.categoria_prestacion?.nombre === 'Cirugia'" class="card border-0 shadow-sm border-top border-warning border-4">
                            <div class="card-header bg-transparent border-0 pt-3 px-4 pb-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h3 class="h6 mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                                        <i class="bi bi-people-fill text-warning"></i> Equipo Médico de Apoyo
                                    </h3>
                                    <!-- Si hay un arsenalero, muestra el badge de arsenalero ok -->
                                    <span v-if="tieneArsenalero" class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 small">
                                        <i class="bi bi-check-circle-fill me-1"></i> Arsenalero ok
                                    </span>
                                    <!-- Si no hay un arsenalero, muestra el badge de falta arsenalero -->
                                    <span v-else class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1 small animate-pulse">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Falta Arsenalero
                                    </span>
                                </div>
                            </div>
                            <div class="card-body p-4 pt-3">
                                <!-- Si hay un equipo médico asignado, lo muestra -->
                                <div v-if="equipoList.length > 0" class="mb-3">
                                    <!-- Si hay un equipo médico asignado, lo muestra -->
                                    <div v-for="miembro in equipoList" :key="'miembro-' + miembro.id" class="d-flex justify-content-between align-items-center p-2 rounded mb-2 bg-light border">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                <i class="bi bi-person-fill"></i>
                                            </div>
                                            <div>
                                                <span class="small fw-semibold text-dark d-block">{{ miembro.usuario?.name }}</span>
                                                <span class="text-muted d-block" style="font-size: 0.75rem;">{{ miembro.rol?.nombre_legible }}</span>
                                            </div>
                                        </div>
                                        <!-- Si el usuario puede asignar equipo y la cita no esta completada o cancelada, muestra el boton de eliminar -->
                                        <button v-if="puedeAsignarEquipo && estadoActual !== 'completada' && estadoActual !== 'cancelada'" 
                                                class="btn btn-sm btn-outline-danger p-1 rounded-circle d-flex align-items-center justify-content-center" 
                                                style="width: 24px; height: 24px;" 
                                                @click="eliminarMiembroEquipo(miembro.id)" 
                                                :disabled="procesandoEquipo === miembro.id" 
                                                title="Eliminar miembro">
                                            <!-- Si el equipo esta siendo procesado, muestra el spinner -->
                                            <span v-if="procesandoEquipo === miembro.id" class="spinner-border spinner-border-sm"></span>
                                            <i v-else class="bi bi-x-circle-fill"></i>
                                        </button>
                                    </div>
                                </div>
                                <!-- Si no hay equipo medico asignado, muestra el mensaje -->
                                <p v-else class="text-muted text-center py-2 small">
                                    No hay personal de apoyo asignado aún.
                                </p>

                                <!-- Formulario para agregar personal de apoyo -->
                                <!-- Si el usuario puede asignar equipo y la cita no esta completada o cancelada, muestra el formulario de asignacion de equipo -->
                                <div v-if="puedeAsignarEquipo && estadoActual !== 'completada' && estadoActual !== 'cancelada'" class="border rounded-3 p-3 bg-white mt-3">
                                    <h4 class="h6 fw-semibold text-dark mb-2"><i class="bi bi-plus-circle me-1 text-success"></i> Agregar Personal</h4>
                                    <div class="d-flex flex-column gap-2">
                                        <div>
                                            <label class="form-label small fw-semibold text-secondary mb-1">Rol</label>
                                            <!-- Enlace de datos bidireccional con "nuevoRolId" -->
                                            <select v-model="nuevoRolId" class="form-select form-select-sm">
                                                <option value="">Seleccionar rol...</option>
                                                <!-- Renderizado iterativo de roles disponibles -->
                                                <option v-for="rol in rolesMedicos" :key="rol.id" :value="rol.id">
                                                    {{ rol.nombre_legible }}
                                                </option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="form-label small fw-semibold text-secondary mb-1">Personal Disponible</label>
                                            <!-- Enlace de datos bidireccional con "nuevoUsuarioId" -->
                                            <select v-model="nuevoUsuarioId" class="form-select form-select-sm" :disabled="!nuevoRolId">
                                                <option value="">Seleccionar persona...</option>
                                                <!-- Renderizado iterativo de personal disponible -->
                                                <option v-for="user in usuariosFiltradosMedicos" :key="user.id" :value="user.id">
                                                    {{ user.name }}
                                                </option>
                                            </select>
                                        </div>
                                        <button @click="agregarMiembroEquipo" class="btn btn-warning btn-sm w-100 mt-2 fw-semibold text-dark" :disabled="!nuevoUsuarioId || !nuevoRolId || guardandoEquipo">
                                            <!-- Si el equipo esta siendo procesado, muestra el spinner -->
                                            <span v-if="guardandoEquipo" class="spinner-border spinner-border-sm me-1"></span>
                                            <i v-else class="bi bi-plus-lg me-1"></i> Añadir al Equipo
                                        </button>
                                    </div>
                                    <!-- Si hay un error, lo muestra -->
                                    <div v-if="errorEquipo" class="alert alert-danger alert-sm py-1 px-2 mt-2 small mb-0">{{ errorEquipo }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm border-top border-primary border-4">
                            <div class="card-header bg-transparent border-0 pt-3 px-4 pb-0">
                                <h3 class="h6 mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                                    <i class="bi bi-paw-fill text-primary"></i> Paciente

                                </h3>
                            </div>
                            <div class="card-body p-4 pt-3">
                                <!-- Si la cita tiene una mascota, muestra la información de la mascota -->
                                <div v-if="cita.mascota" class="d-flex align-items-center gap-3">
                                    <!-- Si la mascota tiene una imagen, la muestra -->
                                    <img v-if="cita.mascota.imagen_url" :src="cita.mascota.imagen_url" :alt="cita.mascota.nombre" class="rounded-circle object-fit-cover shadow-sm" style="width: 60px; height: 60px;">
                                    <div v-else class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="bi bi-heart-fill fs-4"></i>
                                    </div>
                                    <div>
                                        <h4 class="h6 mb-1 fw-bold text-dark">
                                            <Link :href="route('mascotas.detalle', cita.mascota.id)" class="text-decoration-none text-dark hover-primary">
                                                {{ cita.mascota.nombre }}
                                            </Link>
                                        </h4>
                                        <p class="text-muted small mb-0">{{ cita.mascota.raza?.especie?.nombre || 'Especie N/A' }} | {{ cita.mascota.sexo || 'Sexo N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm border-top border-success border-4">
                            <div class="card-header bg-transparent border-0 pt-3 px-4 pb-0">
                                <h3 class="h6 mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                                    <i class="bi bi-person-badge-fill text-success"></i> Propietario
                                </h3>
                            </div>
                             <div class="card-body p-4 pt-3">
                                <!-- Si la cita tiene una mascota y la mascota tiene un cliente, muestra la información del cliente -->
                                <div v-if="cita.mascota && cita.mascota.cliente" class="d-flex align-items-center gap-3">
                                    <!-- Si el cliente tiene una foto de perfil, la muestra -->
                                    <img v-if="cita.mascota.cliente.foto_perfil_url" :src="cita.mascota.cliente.foto_perfil_url" :alt="cita.mascota.cliente.usuario?.name" class="rounded-circle object-fit-cover shadow-sm" style="width: 60px; height: 60px;">
                                    <div v-else class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="bi bi-person-fill fs-4"></i>
                                    </div>
                                    <div>
                                        <h4 class="h6 mb-1 fw-bold text-dark">
                                            <Link :href="route('clientes.detalle', cita.mascota.cliente.id)" class="text-decoration-none text-dark hover-primary">
                                                {{ cita.mascota.cliente.usuario?.name || 'Propietario Desconocido' }}
                                            </Link>
                                        </h4>
                                        <p class="text-muted small mb-0">{{ cita.mascota.cliente.telefono || 'Sin teléfono' }} | {{ cita.mascota.cliente.usuario?.email || 'Sin correo' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- MODAL COMPROBANTE DE PAGO -->
            <!-- Si se muestra el modal de comprobante y la cita tiene una transacción, muestra el modal -->
            <div v-if="mostrarModalComprobante && cita.transaccion" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); z-index: 1055;">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow rounded-4">
                        <div class="modal-header bg-light border-bottom-0 rounded-top-4 p-4">
                            <h5 class="modal-title fw-bold text-dark"><i class="bi bi-receipt me-2 text-primary"></i> Comprobante de Pago</h5>
                            <!-- Si el usuario cierra el modal -->
                            <button type="button" class="btn-close" @click="mostrarModalComprobante = false"></button>
                        </div>
                        <div class="modal-body p-4" id="comprobante-imprimir">
                            <!-- Mensaje de pago exitoso -->
                            <div class="text-center mb-4">
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                                <h4 class="mt-2 fw-bold text-success">¡Pago Exitoso!</h4>
                                <p class="text-muted mb-0">Comprobante #{{ cita.transaccion.id.toString().padStart(6, '0') }}</p>
                            </div>
                            <!-- Información del comprobante de pago -->
                            <div class="card bg-light border-0 rounded-4">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted small">Fecha de pago:</span>
                                        <span class="fw-medium text-dark">{{ formatearFechaComprobante(cita.transaccion.fecha_pago) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted small">Cliente:</span>
                                        <span class="fw-medium text-dark">{{ cita.mascota?.cliente?.usuario?.name || cita.cliente?.nombre || 'Desconocido' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted small">Paciente:</span>
                                        <span class="fw-medium text-dark">{{ cita.mascota?.nombre || 'N/A' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted small">Método de pago:</span>
                                        <span class="fw-medium text-dark">{{ formatearMetodo(cita.transaccion.metodo_pago) }}</span>
                                    </div>
                                    <hr class="border-secondary opacity-25">
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <span class="text-uppercase fw-bold text-muted small">Total Pagado</span>
                                        <span class="fs-4 fw-bold text-success">${{ Math.round(cita.transaccion.monto_pagado).toLocaleString('es-CL') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mt-4">
                                <small class="text-muted">Gracias por confiar en nuestra clínica veterinaria.</small>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 p-4">
                            <!-- Si el usuario desea cerrar el modal -->
                            <button type="button" class="btn btn-secondary rounded-pill px-4" @click="mostrarModalComprobante = false">Cerrar</button>
                            <!-- Si el usuario desea imprimir el comprobante -->
                            <button type="button" class="btn btn-primary rounded-pill px-4" @click="imprimirComprobante"><i class="bi bi-printer me-2"></i>Imprimir</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

<script>
// ==================================================================================
// LÓGICA DEL COMPONENTE (VUE 3)
// ==================================================================================

import AuthenticatedLayout from '@/Disenos/LayoutAutenticado.vue';
import FichaClinicaPanel from '@/Componentes/FichaClinicaPanel.vue';
import { Head, Link } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

// ------------------------------------------------------------------------------
// EXPORT DEFAULT: Definición principal del componente
// ------------------------------------------------------------------------------
export default {
    name: 'CitaDetalle',
    // Registro de componentes importados
    components: {
        AuthenticatedLayout,
        FichaClinicaPanel,
        Head,
        Link
    },
    // Datos inyectados desde el componente padre o estado
    props: {
        // Información de la cita
        cita: {
            type: Object,
            required: true,
            default: () => ({
                id: null,
                titulo: '',
                descripcion: '',
                fecha_hora: '',
                estado: 'programada',
                notas: '',
                mascota: null,
                cliente: null
            })
        },
        // Información de los cargos aplicados a la cita
        cargos: {
            type: Array,
            default: () => []
        },
        // Información de los insumos disponibles en la sucursal
        insumosSucursal: {
            type: Array,
            default: () => []
        },
        // Información del catálogo de medicamentos
        catalogoMedicamentos: {
            type: Array,
            default: () => []
        },
        // Información del catálogo de vacunas
        catalogoVacunas: {
            type: Array,
            default: () => []
        },
        // Información de la prestación de la cita
        prestacion: {
            type: Object,
            default: () => ({})
        },
        // Información de la mascota
        mascota: {
            type: Object,
            default: () => ({})
        },
        // Roles de los médicos 
        rolesMedicos: {
            type: Array,
            default: () => []
        },
        // Usuarios médicos 
        usuariosMedicos: {
            type: Array,
            default: () => []
        },
        // Boxes disponibles en la sucursal
        boxes: {
            type: Array,
            default: () => []
        }
    },
    // Variables locales del componente
    data() {
        // Datos de la cita
        return {
            notasConsulta:  this.cita.notas || '',
            estadoActual:   this.cita.estado || 'pendiente',
            cargosList:     [...this.cargos],
            procesando:     false,
            guardandoNotas: false,
            guardandoCargo: false,
            procesandoCargo: null,
            errorCargo:     null,
            nuevoInsumoId:  '',
            nuevaCantidad:  1,
            mostrarModalComprobante: false,
            equipoList:     this.cita.equipo_medico || this.cita.equipoMedico || [],
            nuevoRolId:     '',
            nuevoUsuarioId: '',
            guardandoEquipo: false,
            procesandoEquipo: null,
            errorEquipo:     null,
            boxAsignadoId:   this.cita.box_id || '',
            guardandoBox:    false,
            errorBox:        null,
        }
    },
    //Variables reactivas que dependen de otras
    computed: {
        // Verifica si algún miembro del equipo es arsenalero
        tieneArsenalero() {
            return this.equipoList.some(miembro => miembro.rol?.nombre_interno === 'arsenalero');
        },
        // Filtra los usuarios según el rol seleccionado
        usuariosFiltradosMedicos() {
            if (!this.nuevoRolId) return [];
            return this.usuariosMedicos.filter(user => user.rol_id === this.nuevoRolId);
        },
        // Calcula el total de los cargos aplicados
        totalCargos() {
            let total = 0;
            // Itera sobre los cargos y suma el subtotal
            for (const cargo of this.cargosList) {
                if (cargo.insumo) {
                    total += parseFloat(cargo.subtotal || 0);
                }
            }
            return total;
        },
        // Verifica el estado actual de la cita
        estadoCita() {
            return this.cita.estado;
        },
        // Calcula el total final de la cita
        totalFinal() {
            // Verifica si la cita tiene una transacción
            if (this.cita.transaccion) {
                return parseFloat(this.cita.transaccion.monto_total);
            }
            // Calcula el total sumando el precio base de la prestación y los cargos aplicados
            const precioBase = parseFloat(this.prestacion?.precio_base || 0);
            return this.totalCargos + precioBase;
        },
        // Formatea el total final
        totalFinalFormateado() {
            return '$' + Number(this.totalFinal).toLocaleString('es-CL');
        },
        // Verifica si el usuario puede editar la cita
        puedeEditarCita() {
            // Obtiene el usuario actual
            const user = this.$page.props.auth.user;
            // Verifica si el usuario existe y tiene un rol
            if (!user || !user.rol) return false;

            // Si es administrador, tiene acceso total
            if (user.rol.nombre_interno === 'admin') {
                return true;
            }

            // Si es veterinario, debe ser estrictamente el veterinario asignado a la cita
            if (user.rol.nombre_interno === 'veterinario') {
                return this.cita.veterinario && this.cita.veterinario.user_id === user.id;
            }

            return false;
        },

        puedeCancelarCita() {
            // Verifica si el usuario puede cancelar la cita
            const user = this.$page.props.auth.user;
            // Verifica si el usuario existe y tiene un rol
            if (!user || !user.rol) return false;

            // Si es administrador, puede cancelar la cita
            if (this.$isAdmin()) {
                return true;
            }
            // Si es secretaria, puede cancelar la cita si pertenece a su sucursal
            if (this.$isSecretaria()) {
                return this.cita.veterinario && this.cita.veterinario.sucursal_id === user.secretaria?.sucursal_id;
            }
            // Si es cliente, puede cancelar la cita si pertenece a su mascota
            if (this.$isCliente()) {
                return this.cita.mascota && this.cita.mascota.cliente_id === user.cliente?.id;
            }

            return false;
        },
        // Verifica si el usuario puede editar el estado de la cita
        puedeEditarEstado() {
            // Verifica si el usuario puede editar el estado de la cita 
            const user = this.$page.props.auth.user;
            if (!user || !user.rol) return false;
            // Si es administrador, puede editar el estado de la cita
            if (this.$isAdmin()) {
                return true;
            }
            // Si es veterinario, puede editar el estado de la cita si es el dueño de la cita
            if (this.$isVeterinario()) {
                return this.cita.veterinario && this.cita.veterinario.user_id === user.id;
            }
            // Si es secretaria, puede editar el estado de la cita si pertenece a su sucursal
            if (this.$isSecretaria()) {
                return this.cita.veterinario && this.cita.veterinario.sucursal_id === user.secretaria?.sucursal_id;
            }
            return false;
        },

        // Verifica si el usuario puede editar las notas
        puedeEditarNotas() {
            return this.puedeEditarEstado;
        },

        // Verifica si el usuario puede ver las notas internas
        puedeVerNotasInternas() {
            return this.$isAdmin() || this.$isVeterinario() || this.$isSecretaria();
        },

        // Verifica si el usuario puede asignar un box
        puedeAsignarBox() {
            const user = this.$page.props.auth.user;
            if (!user || !user.rol) return false;

            // Si es administrador, puede asignar un box
            if (this.$isAdmin()) {
                return true;
            }

            // Si es secretaria, puede asignar un box si pertenece a su sucursal
            if (this.$isSecretaria()) {
                return this.cita.veterinario && this.cita.veterinario.sucursal_id === user.secretaria?.sucursal_id;
            }

            return false;
        },

        // Verifica si el usuario puede asignar equipo médico
        puedeAsignarEquipo() {
            const user = this.$page.props.auth.user;
            if (!user || !user.rol) return false;

            // Si es administrador, puede asignar equipo médico
            if (this.$isAdmin()) {
                return true;
            }

            // Si es secretaria, puede asignar equipo médico si pertenece a su sucursal
            if (this.$isSecretaria()) {
                return this.cita.veterinario && this.cita.veterinario.sucursal_id === user.secretaria?.sucursal_id;
            }

            return false;
        },
    },
    //  Reaccionan a cambios en propiedades o variables
    watch: {
        // Por si acaso, se mantiene el soporte de 'cita.notes' o 'cita.notas' pero solo queda 'cita.notas'
        'cita.notes'(nuevaNota) {
            this.notasConsulta = nuevaNota || '';
        },
        // Por si acaso, se mantiene el soporte de 'cita.notes' o 'cita.notas'
        'cita.notas'(nuevaNota) {
            this.notasConsulta = nuevaNota || '';
        },
        'cita.estado'(nuevoEstado) {
            this.estadoActual = nuevoEstado || 'pendiente';
        },
        'cita.box_id'(nuevoBoxId) {
            this.boxAsignadoId = nuevoBoxId || '';
        },
    },
    // MÉTODOS (METHODS): Bloque de funciones y eventos
    methods: {
        // Formatea la fecha y hora dd/mm/aaaa hh:mm
        formatearFechaHora(fechaHora) {
            if (!fechaHora) return 'Sin fecha';
            const date = new Date(fechaHora);
            return date.toLocaleString('es-ES', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },
        //  Método para cambiar el estado de la cita a "en curso" sin pedir confirmación
        marcarEnCurso() {
            // Verifica si la cita tiene un box asignado y guardado
            if (!this.cita.box_id) {
                // Si no tiene un box asignado y guardado, muestra un mensaje de error
                Swal.fire({
                    title: 'Atención',
                    text: 'Debe asignar y guardar un box para la cita antes de iniciarla.',
                    icon: 'warning',
                    confirmButtonColor: '#0d6efd'
                });
                return;
            }
            //Si tiene un box asignado y guardado, cambia el estado a "en curso"
            this.procesando = true;
            // Llama a la ruta del backend que cambia el estado de la cita a "en curso"
            axios.patch(`/api/citas/${this.cita.id}/estado`, { estado: 'en_curso' })
            // Si la solicitud es exitosa, cambia el estado actual de la cita y recarga la página
                .then(() => {
                    this.estadoActual = 'en_curso';
                    this.$inertia.reload({ only: ['cita'] });
                })
                // Si la solicitud falla, muestra un mensaje de error
                .catch(error => {
                    console.error('Error al actualizar estado:', error);
                    const mensaje = error.response?.data?.error || 'Ocurrió un error al actualizar el estado.';
                    Swal.fire('Error', mensaje, 'error');
                })
                //finaliza el procesando
                .finally(() => { this.procesando = false; });
        },
        guardarNotas(nuevaNota) {
            //Inicia el procesando
            this.guardandoNotas = true;
            // Llama a la ruta del backend que guarda las notas
            axios.patch(`/api/citas/${this.cita.id}/notas`, { notas: nuevaNota })
            // Si la solicitud falla, muestra un mensaje de error
                .catch(error => {
                    console.error('Error al guardar notas:', error);
                    const mensaje = error.response?.data?.error || 'Ocurrió un error al guardar las notas.';
                    Swal.fire('Error', mensaje, 'error');
                })
                //finaliza el procesando
                .finally(() => { this.guardandoNotas = false; });
        },
        //Confirma la eliminación de la cita
        confirmarEliminar() {
            Swal.fire({
                title: '¿Cancelar cita?',
                text: "Escribe el motivo de la cancelación. Esto reemplazará cualquier nota clínica actual y se enviará al cliente.",
                input: 'textarea',
                inputPlaceholder: 'Motivo de la cancelación...',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, cancelar cita',
                cancelButtonText: 'No, volver'
            }).then(resultado => {
                //Si el usuario confirma la eliminación
                if (resultado.isConfirmed) {
                    //Si el usuario no especifica un motivo, se le asigna uno por defecto
                    const motivo = resultado.value || 'Cancelada sin motivo especificado.';
                    //Inicia el procesando
                    this.procesando = true;
                    // Llama a la ruta del backend que cancela la cita
                    axios.patch(`/api/citas/${this.cita.id}/cancelar`, { motivo_cancelacion: motivo })
                    // Si la solicitud es exitosa
                        .then(() => { 
                            this.estadoActual = 'cancelada'; 
                            this.notasConsulta = motivo;
                            this.$inertia.reload({ only: ['cita'] });
                        })
                        .catch(error => console.error(error))
                        .finally(() => { this.procesando = false; });
                }
            });
        },
        //Confirma la finalización de la cita
        confirmarCompletar() {
            // Verifica si la cita tiene un box asignado y guardado
            if (!this.cita.box_id) {
                // Si no tiene un box asignado y guardado, muestra un mensaje de error
                Swal.fire({
                    title: 'Atención',
                    text: 'Debe asignar y guardar un box para la cita antes de completarla.',
                    icon: 'warning',
                    confirmButtonColor: '#198754'
                });
                return;
            }
            // Confirma la finalización de la cita
            this.$confirmar('¿Completar cita?', 'El registro se conservará con estado Completada.')
                .then(resultado => {
                    //Si el usuario confirma la finalización
                    if (resultado.isConfirmed) {
                        //Inicia el procesando
                        this.procesando = true;
                        // Llama a la ruta del backend que finaliza la cita
                        axios.patch(`/api/citas/${this.cita.id}/estado`, { estado: 'completada' })
                        // Si la solicitud es exitosa
                            .then(() => { 
                                this.estadoActual = 'completada'; 
                                this.$inertia.reload({ only: ['cita'] });
                            })
                            // Si la solicitud falla, muestra un mensaje de error
                            .catch(error => {
                                console.error('Error al actualizar estado:', error);
                                const mensaje = error.response?.data?.error || 'Ocurrió un error al actualizar el estado.';
                                Swal.fire('Error', mensaje, 'error');
                            })
                            //finaliza el procesando
                            .finally(() => { this.procesando = false; });
                    }
                });
        },
        //Maneja la adición de un cargo
        manejarAgregarCargo(payload) {
            // Asigna el ID del nuevo insumo
            this.nuevoInsumoId = payload.insumoId;
            this.nuevaCantidad = payload.cantidad;
            this.agregarInsumo();
        },
        //Agrega un cargo a la cita
        agregarInsumo() {
            // Si no tiene un insumo nuevo o la cantidad es menor a 1, no hace nada
            if (!this.nuevoInsumoId || this.nuevaCantidad < 1) return;
            // Inicia el procesando
            this.guardandoCargo = true;
            // Limpia el error
            this.errorCargo = null;
            // Llama a la ruta del backend que agrega un cargo a la cita
            axios.post(`/api/citas/${this.cita.id}/cargo`, {
                cita_id: this.cita.id,
                insumo_id: this.nuevoInsumoId,
                cantidad:  this.nuevaCantidad,
            })
            // Si la solicitud es exitosa
            .then(response => {
                // Añadir el cargo devuelto por el servidor a la lista local
                this.cargosList.push(response.data);
                
                // Descontar stock localmente
                const insumoSeleccionado = this.insumosSucursal.find(i => i.id === this.nuevoInsumoId);
                if (insumoSeleccionado) {
                    insumoSeleccionado.stock_actual -= this.nuevaCantidad;
                }

                this.nuevoInsumoId = '';
                this.nuevaCantidad = 1;
            })
            // Si la solicitud falla
            .catch(error => {
                this.errorCargo = error.response?.data?.error || 'Error al añadir el insumo.';
            })
            //finally, finaliza el procesando
            .finally(() => {
                this.guardandoCargo = false;
            });
        },

        eliminarCargo(cargoId) {
            // Inicia el procesando
            this.procesandoCargo = cargoId;
            // Llama a la ruta del backend que elimina un cargo de la cita
            axios.delete(`/api/cargos/${cargoId}`)
            // Si la solicitud es exitosa
                .then(() => {
                    // Encuentra el cargo a eliminar
                    const cargoAEliminar = this.cargosList.find(c => c.id === cargoId);
                    // Si el cargo tiene un insumo, aumenta el stock
                    if (cargoAEliminar && cargoAEliminar.insumo_id) {
                        const insumoRepo = this.insumosSucursal.find(i => i.id === cargoAEliminar.insumo_id);
                        if (insumoRepo) {
                            insumoRepo.stock_actual += cargoAEliminar.cantidad;
                        }
                    }
                    // Filtra la lista de cargos, eliminando el cargo eliminado
                    this.cargosList = this.cargosList.filter(c => c.id !== cargoId);
                })
                // Si la solicitud falla
                .catch(error => {
                    console.error('Error al eliminar cargo:', error);
                    alert('Error al eliminar el insumo. Comprueba si tienes permisos y recarga la página.');
                })
                //finally, finaliza el procesando
                .finally(() => {
                    this.procesandoCargo = null;
                });
        },

        //Actualiza la cantidad de un cargo
        actualizarCantidad(cargo, cambio) {
            // Calcula la nueva cantidad
            const nuevaCantidad = cargo.cantidad + cambio;
            // Si la nueva cantidad es menor a 1, no hace nada
            if (nuevaCantidad < 1) return;
            // Inicia el procesando
            this.procesandoCargo = cargo.id;
            // Limpia el error
            this.errorCargo = null;
            // Llama a la ruta del backend que actualiza la cantidad de un cargo
            axios.put(`/api/cargos/${cargo.id}`, { cantidad: nuevaCantidad })
                .then(response => {
                    // Actualiza el cargo en la lista local
                    const cargoActualizado = response.data;
                    const index = this.cargosList.findIndex(c => c.id === cargo.id);
                    if (index !== -1) {
                        this.cargosList[index] = cargoActualizado;
                    }
                    // Actualiza el stock local
                    const insumoRepo = this.insumosSucursal.find(i => i.id === cargo.insumo_id);
                    if (insumoRepo) {
                        insumoRepo.stock_actual -= cambio;
                    }
                })
                // Si la solicitud falla
                .catch(error => {
                    console.error('Error al actualizar cargo:', error);
                    this.errorCargo = error.response?.data?.error || 'Error al actualizar la cantidad.';
                })
                //finaliza el procesando
                .finally(() => {
                    this.procesandoCargo = null;
                });
        },
        //Muestra el comprobante
        verComprobante() {
            this.mostrarModalComprobante = true;
        },
        //Imprime el comprobante
        imprimirComprobante() {
            if (this.cita && this.cita.transaccion) {
                const urlPdf = route('transacciones.comprobante', this.cita.transaccion.id);
                window.open(urlPdf, '_blank');
            }
        },
        //Formatea la fecha del comprobante
        formatearFechaComprobante(fechaStr) {
            if (!fechaStr) return 'N/A';
            const f = new Date(fechaStr);
            return f.toLocaleDateString('es-CL', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        },
        //Formatea el metodo de pago
        formatearMetodo(metodo) {
            if (!metodo) return 'No registrado';
            return metodo.charAt(0).toUpperCase() + metodo.slice(1);
        },
        // Agrega un miembro al equipo
        agregarMiembroEquipo() {
            // Si no tiene un usuario nuevo o un rol nuevo, no hace nada
            if (!this.nuevoUsuarioId || !this.nuevoRolId) return;
            // Inicia el procesando
            this.guardandoEquipo = true;
            // Limpia el error
            this.errorEquipo = null;
            // Llama a la ruta del backend que agrega un miembro al equipo
            axios.post(`/api/citas/${this.cita.id}/equipo`, {
                usuario_id: this.nuevoUsuarioId,
                rol_id: this.nuevoRolId
            })
            // Si la solicitud es exitosa
            .then(response => {
                // Agrega el miembro al equipo
                this.equipoList.push(response.data);
                // Limpia el usuario nuevo y el rol nuevo
                this.nuevoUsuarioId = '';
                this.nuevoRolId = '';
            })
            // Si la solicitud falla
            .catch(error => {
                this.errorEquipo = error.response?.data?.error || 'Error al añadir el miembro al equipo.';
            })
            //finaliza el procesando
            .finally(() => {
                this.guardandoEquipo = false;
            });
        },
        // Elimina un miembro del equipo
        eliminarMiembroEquipo(miembroId) {
            // Inicia el procesando
            this.procesandoEquipo = miembroId;
            // Limpia el error
            this.errorEquipo = null;
            // Llama a la ruta del backend que elimina un miembro del equipo
            axios.delete(`/api/citas/${this.cita.id}/equipo/${miembroId}`)
                // Si la solicitud es exitosa
                .then(() => {
                    // Filtra la lista de equipo, eliminando el miembro eliminado
                    this.equipoList = this.equipoList.filter(m => m.id !== miembroId);
                })
                // Si la solicitud falla
                .catch(error => {
                    console.error('Error al eliminar miembro:', error);
                    this.errorEquipo = error.response?.data?.error || 'Error al eliminar miembro del equipo.';
                })
                //finaliza el procesando
                .finally(() => {
                    this.procesandoEquipo = null;
                });
        },
        // Guarda el box asignado
        guardarBox() {
            // Inicia el procesando
            this.guardandoBox = true;
            // Limpia el error
            this.errorBox = null;
            // Llama a la ruta del backend que guarda el box asignado
            axios.put(`/api/citas/${this.cita.id}`, {
                titulo: this.cita.titulo,
                descripcion: this.cita.descripcion,
                fecha_hora: this.cita.fecha_hora,
                mascota_id: this.cita.mascota_id,
                veterinario_id: this.cita.veterinario_id,
                prestacion_id: this.cita.prestacion_id,
                box_id: this.boxAsignadoId || null,
            })
            // Si la solicitud es exitosa
            .then(response => {
                // Actualiza el box asignado
                this.cita.box_id = response.data.box_id;
                // Recarga la página
                this.$inertia.reload({
                    only: ['cita'],
                    onSuccess: () => {
                        // Muestra un mensaje de éxito
                        Swal.fire('¡Éxito!', 'Box asignado correctamente.', 'success');
                    }
                });  
            })
            // Si la solicitud falla
            .catch(error => {
                this.errorBox = error.response?.data?.error || 'Error al asignar el box.';
            })
            //finaliza el procesando
            .finally(() => {
                this.guardandoBox = false;
            });
        }
    }
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
.fs-7 {
    font-size: 0.85rem;
}
.hover-primary:hover {
    color: var(--bs-primary) !important;
}
.hover-success:hover {
    color: var(--bs-success) !important;
}
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}
.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
