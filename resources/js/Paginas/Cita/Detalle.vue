<template>
    <Head :title="'Cita - ' + (cita.titulo || 'Detalle')" />

    <AuthenticatedLayout>
        <div class="container py-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center gap-3">
                    <Link :href="route('citas.listado')" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                        <i class="bi bi-arrow-left"></i> Volver
                    </Link>
                    <h1 class="h3 mb-0">Detalle de la Cita</h1>
                </div>
                <div class="d-flex gap-2">
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
                                    <div class="text-muted small mb-2"><i class="bi bi-clock-fill me-1"></i> Programada: {{ formatearFechaHora(cita.fecha_hora) }}</div>
                                    <span class="badge rounded-pill px-3 py-2 mt-1" :class="{
                                        'bg-warning text-dark': estadoActual === 'pendiente',
                                        'bg-success':           estadoActual === 'completada',
                                        'bg-danger':            estadoActual === 'cancelada',
                                        'bg-primary':           estadoActual === 'en_curso',
                                    }">
                                        {{ estadoActual ? estadoActual.charAt(0).toUpperCase() + estadoActual.slice(1) : 'Pendiente' }}
                                    </span>
                                </div>
                                <!-- Botones de cambio de estado: solo visibles para vet asignado o admin -->
                                <div v-if="puedeEditarCita" class="d-flex flex-column align-items-end gap-2">
                                    <div v-if="estadoActual === 'pendiente' || estadoActual === 'en_curso'" class="btn-group btn-group-sm">
                                        <button @click="marcarEnCurso" class="btn btn-outline-primary" :disabled="procesando || estadoActual === 'en_curso'">
                                            <i class="bi bi-play-fill"></i> En curso
                                        </button>
                                        <button @click="confirmarCompletar" class="btn btn-success" :disabled="procesando">
                                            <i class="bi bi-check-lg"></i> Completada
                                        </button>
                                        <button @click="confirmarEliminar" class="btn btn-outline-danger" :disabled="procesando">
                                            <i class="bi bi-x-lg"></i> Cancelar
                                        </button>
                                    </div>
                                    <small v-if="procesando" class="text-muted">
                                        <span class="spinner-border spinner-border-sm me-1"></span> Actualizando...
                                    </small>
                                </div>
                            </div>

                            <h2 class="h4 fw-bold text-dark mb-3">{{ cita.titulo }}</h2>
                            <div class="mb-4">
                                <h3 class="h6 text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">Motivo o Descripción</h3>
                                <p class="text-secondary bg-light p-3 rounded border-start border-primary border-3 mb-0" style="white-space: pre-wrap;">
                                    {{ cita.descripcion || 'Sin descripción detallada.' }}
                                </p>
                            </div>

                            <div v-if="puedeEditarCita" class="mb-0">
                                <h3 class="h6 text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">Notas Clinicas (Autoguardado)</h3>
                                <div class="position-relative">
                                    <textarea 
                                        v-model="notasConsulta" 
                                        class="form-control" 
                                        rows="5" 
                                        placeholder="Escribe notas médicas, tratamiento o diagnóstico aquí..."
                                        :disabled="procesando"
                                    ></textarea>
                                    <button class="btn btn-primary" @click="guardarNotas(notasConsulta)" :disabled="procesando">
                                        <i class="bi bi-save"></i> Guardar
                                    </button>
                                    <div v-if="guardandoNotas" class="position-absolute bottom-0 end-0 p-2">
                                        <span class="spinner-border spinner-border-sm text-primary"></span>
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block">Las notas se guardan automáticamente al hacer clic fuera del cuadro de texto.</small>
                            </div>

                            <div v-if="!puedeEditarCita" class="mb-0">
                                <h3 class="h6 text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">Notas Clinicas</h3>
                                <p class="text-secondary bg-light p-3 rounded border-start border-primary border-3 mb-0" style="white-space: pre-wrap;">
                                    {{ cita.notas || 'Sin notas.' }}
                                </p>
                            </div>
                            
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
                                        <div v-if="cargosList && cargosList.some(c => c.insumo)" class="p-3 border-bottom bg-light bg-opacity-50">
                                            <h5 class="small fw-bold text-muted text-uppercase mb-3" style="letter-spacing: 0.5px;">Insumos Utilizados</h5>
                                            
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
                                    </div>
                                </div>
                            </div>
                            <!-- ===== SECCIÓN PRIVILEGIADA VETERINARIO ===== -->
                            <div v-if="puedeEditarCita" class="mt-4">

                                <!-- Prestación solicitada -->
                                <div class="mb-4 p-3 rounded-3 border border-info border-opacity-50 bg-info bg-opacity-10">
                                    <h3 class="h6 text-uppercase fw-bold text-info mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                        <i class="bi bi-clipboard2-check-fill me-1"></i> Prestación Solicitada
                                    </h3>
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

                                <!-- Elementos Utilizados (Insumos) -->
                                <div class="mb-0">
                                    <h3 class="h6 text-uppercase fw-bold text-dark mb-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                        <i class="bi bi-box-seam-fill me-1 text-warning"></i> Elementos Utilizados
                                    </h3>

                                    <!-- Lista de cargos de insumos ya añadidos -->
                                    <div v-if="cargosList && cargosList.some(c => c.insumo)" class="mb-3">
                                        <div v-for="cargo in cargosList.filter(c => c.insumo)" :key="'insumo-' + cargo.id"
                                             class="d-flex justify-content-between align-items-center p-2 rounded mb-1 bg-light border">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi bi-capsule text-warning"></i>
                                                <span class="small fw-semibold text-dark">{{ cargo.insumo.nombre }}</span>
                                                
                                                <!-- Controles de cantidad -->
                                                <div class="input-group input-group-sm ms-2" style="width: 90px;">
                                                    <button class="btn btn-outline-secondary px-2 fw-bold" type="button" @click="actualizarCantidad(cargo, -1)" :disabled="procesandoCargo === cargo.id || cargo.cantidad <= 1">
                                                        -
                                                    </button>
                                                    <input type="text" class="form-control text-center px-1 bg-white" :value="cargo.cantidad" readonly>
                                                    <button class="btn btn-outline-secondary px-2 fw-bold" type="button" @click="actualizarCantidad(cargo, 1)" :disabled="procesandoCargo === cargo.id">
                                                        +
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center gap-3">
                                                <span class="small text-muted fw-semibold">${{ Number(cargo.subtotal).toLocaleString('es-CL') }}</span>
                                                <button class="btn btn-sm btn-outline-danger p-1" @click="eliminarCargo(cargo.id)" :disabled="procesandoCargo === cargo.id" title="Eliminar Insumo">
                                                    <span v-if="procesandoCargo === cargo.id" class="spinner-border spinner-border-sm"></span>
                                                    <span v-else class="fw-bold px-2">X</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <p v-else class="text-muted small mb-3"><i class="bi bi-dash-circle me-1"></i> Aún no se han registrado elementos usados.</p>

                                    <!-- Agregar nuevo insumo -->
                                    <div class="border rounded-3 p-3 bg-white">
                                        <h4 class="h6 fw-semibold text-dark mb-2"><i class="bi bi-plus-circle me-1 text-success"></i> Agregar Insumo</h4>
                                        <div class="row g-2 align-items-end">
                                            <div class="col-12 col-sm-6">
                                                <label class="form-label small fw-semibold text-secondary mb-1">Insumo (stock disponible)</label>
                                                <select v-model="nuevoInsumoId" class="form-select form-select-sm">
                                                    <option value="">Seleccionar insumo...</option>
                                                    <option v-for="ins in insumosSucursal" :key="ins.id" :value="ins.id">
                                                        {{ ins.nombre }} (stock: {{ ins.stock_actual }}) — ${{ Number(ins.precio_venta).toLocaleString('es-CL') }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-6 col-sm-3">
                                                <label class="form-label small fw-semibold text-secondary mb-1">Cantidad</label>
                                                <input v-model.number="nuevaCantidad" type="number" min="1" class="form-control form-control-sm" placeholder="1">
                                            </div>
                                            <div class="col-6 col-sm-3">
                                                <button @click="agregarInsumo" class="btn btn-success btn-sm w-100" :disabled="!nuevoInsumoId || nuevaCantidad < 1 || guardandoCargo">
                                                    <span v-if="guardandoCargo" class="spinner-border spinner-border-sm me-1"></span>
                                                    <i v-else class="bi bi-plus-lg me-1"></i> Añadir
                                                </button>
                                            </div>
                                        </div>
                                        <div v-if="errorCargo" class="alert alert-danger alert-sm py-1 px-2 mt-2 small mb-0">{{ errorCargo }}</div>
                                    </div>
                                </div>
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
                                <div v-if="cita.veterinario" class="d-flex align-items-center gap-3">
                                    <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
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

                        <div class="card border-0 shadow-sm border-top border-primary border-4">
                            <div class="card-header bg-transparent border-0 pt-3 px-4 pb-0">
                                <h3 class="h6 mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                                    <i class="bi bi-paw-fill text-primary"></i> Paciente
                                </h3>
                            </div>
                            <div class="card-body p-4 pt-3">
                                <div v-if="cita.mascota" class="d-flex align-items-center gap-3">
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
                                        <p class="text-muted small mb-0">{{ mascota.raza?.especie?.nombre || 'Especie N/A' }} | {{ cita.mascota.sexo || 'Sexo N/A' }}</p>
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
                                <div v-if="cita.mascota && cita.mascota.cliente" class="d-flex align-items-center gap-3">
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
        </div>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from '@/Disenos/LayoutAutenticado.vue';
import { Head, Link } from '@inertiajs/vue3';

export default {
    name: 'CitaDetalle',
    components: {
        AuthenticatedLayout,
        Head,
        Link
    },
    props: {
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
        cargos: {
            type: Array,
            default: () => []
        },
        insumosSucursal: {
            type: Array,
            default: () => []
        },
        prestacion: {
            type: Object,
            default: () => ({})
        },
        mascota: {
            type: Object,
            default: () => ({})
        }
    },
    data() {
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
        }
    },
    computed: {
        totalCargos() {
            let total = 0;
            for (const cargo of this.cargosList) {
                if (cargo.insumo) {
                    total += parseFloat(cargo.subtotal || 0);
                }
            }
            return total;
        },

        totalFinal() {
            const precioBase = parseFloat(this.prestacion?.precio_base || 0);
            return this.totalCargos + precioBase;
        },

        totalFinalFormateado() {
            return '$' + Number(this.totalFinal).toLocaleString('es-CL');
        },

        puedeEditarCita() {
            const user = this.$page.props.auth.user;
            if (!user || !user.rol) return false;

            // Administrador tiene acceso total
            if (user.rol.nombre_interno === 'admin') {
                return true;
            }

            // Si es veterinario, debe ser estrictamente el veterinario asignado a la cita
            if (user.rol.nombre_interno === 'veterinario') {
                return this.cita.veterinario && this.cita.veterinario.user_id === user.id;
            }

            return false;
        },
    },
    watch: {
        'cita.notas'(nuevaNota) {
            this.notasConsulta = nuevaNota || '';
        },
        'cita.estado'(nuevoEstado) {
            this.estadoActual = nuevoEstado || 'pendiente';
        },
    },
    methods: {
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
        marcarEnCurso() {
            // Sin confirmación: pasar a "en curso" es reversible
            this.procesando = true;
            axios.patch(`/api/citas/${this.cita.id}/estado`, { estado: 'en_curso' })
                .then(() => { this.estadoActual = 'en_curso'; })
                .catch(error => { console.error('Error al actualizar estado:', error); })
                .finally(() => { this.procesando = false; });
        },
        guardarNotas(nuevaNota) {
            // El backend lo implementas tú — aquí solo el stub frontend
            this.guardandoNotas = true;
            axios.patch(`/api/citas/${this.cita.id}/notas`, { notas: nuevaNota })
                .catch(error => {
                    console.error('Error al guardar notas:', error);
                })
                .finally(() => {
                    this.guardandoNotas = false;
                });
        },
        confirmarEliminar() {
            // El backend lo implementas tú — aquí solo el stub frontend
            this.$confirmar('¿Cancelar cita?', 'El registro se conservará con estado Cancelada.')
                .then(resultado => {
                    if (resultado.isConfirmed) {
                        this.procesando = true;
                        axios.patch(`/api/citas/${this.cita.id}/cancelar`)
                            .then(() => { this.estadoActual = 'cancelada'; })
                            .catch(error => console.error(error))
                            .finally(() => { this.procesando = false; });
                    }
                });
        },
        confirmarCompletar() {
            this.$confirmar('¿Completar cita?', 'El registro se conservará con estado Completada.')
                .then(resultado => {
                    if (resultado.isConfirmed) {
                        this.procesando = true;
                        axios.patch(`/api/citas/${this.cita.id}/estado`, { estado: 'completada' })
                            .then(() => { this.estadoActual = 'completada'; })
                            .catch(error => console.error(error))
                            .finally(() => { this.procesando = false; });
                    }
                });
        },
        agregarInsumo() {
            if (!this.nuevoInsumoId || this.nuevaCantidad < 1) return;
            this.guardandoCargo = true;
            this.errorCargo = null;
            axios.post(`/api/citas/${this.cita.id}/cargo`, {
                cita_id: this.cita.id,
                insumo_id: this.nuevoInsumoId,
                cantidad:  this.nuevaCantidad,
            })
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
            .catch(error => {
                this.errorCargo = error.response?.data?.error || 'Error al añadir el insumo.';
            })
            .finally(() => {
                this.guardandoCargo = false;
            });
        },
        eliminarCargo(cargoId) {
            this.procesandoCargo = cargoId;
            axios.delete(`/api/cargos/${cargoId}`)
                .then(() => {
                    const cargoAEliminar = this.cargosList.find(c => c.id === cargoId);
                    if (cargoAEliminar && cargoAEliminar.insumo_id) {
                        const insumoRepo = this.insumosSucursal.find(i => i.id === cargoAEliminar.insumo_id);
                        if (insumoRepo) {
                            insumoRepo.stock_actual += cargoAEliminar.cantidad;
                        }
                    }
                    this.cargosList = this.cargosList.filter(c => c.id !== cargoId);
                })
                .catch(error => {
                    console.error('Error al eliminar cargo:', error);
                    alert('Error al eliminar el insumo. Comprueba si tienes permisos y recarga la página.');
                })
                .finally(() => {
                    this.procesandoCargo = null;
                });
        },
        actualizarCantidad(cargo, cambio) {
            const nuevaCantidad = cargo.cantidad + cambio;
            if (nuevaCantidad < 1) return;
            
            this.procesandoCargo = cargo.id;
            this.errorCargo = null;
            
            axios.put(`/api/cargos/${cargo.id}`, { cantidad: nuevaCantidad })
                .then(response => {
                    const cargoActualizado = response.data;
                    const index = this.cargosList.findIndex(c => c.id === cargo.id);
                    if (index !== -1) {
                        this.cargosList[index] = cargoActualizado;
                    }
                    
                    // Actualizar stock local
                    const insumoRepo = this.insumosSucursal.find(i => i.id === cargo.insumo_id);
                    if (insumoRepo) {
                        insumoRepo.stock_actual -= cambio;
                    }
                })
                .catch(error => {
                    console.error('Error al actualizar cargo:', error);
                    this.errorCargo = error.response?.data?.error || 'Error al actualizar la cantidad.';
                })
                .finally(() => {
                    this.procesandoCargo = null;
                });
        }
    }
}
</script>

<style scoped>
.fs-7 {
    font-size: 0.85rem;
}
.hover-primary:hover {
    color: var(--bs-primary) !important;
}
.hover-success:hover {
    color: var(--bs-success) !important;
}
</style>
