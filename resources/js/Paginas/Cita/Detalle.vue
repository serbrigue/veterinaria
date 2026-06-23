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
                                <h3 class="h6 text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">Notas Clínicas (Autoguardado)</h3>
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
                            <div v-else class="mb-0">
                                <h3 class="h6 text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">Notas Clínicas</h3>
                                <p class="text-secondary bg-light p-3 rounded border-start border-primary border-3 mb-0" style="white-space: pre-wrap;">
                                    {{ cita.notas || 'Sin notas.' }}
                                </p>
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
                                                Dr. {{ cita.veterinario.usuario?.name || 'Desconocido' }}
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
                                        <p class="text-muted small mb-0">{{ cita.mascota.especie?.nombre || 'Especie N/A' }} | {{ cita.mascota.sexo || 'Sexo N/A' }}</p>
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
        }
    },
    data() {
        return {
            notasConsulta: this.cita.notas || '',
            estadoActual:  this.cita.estado || 'pendiente',
            procesando:    false,
            guardandoNotas: false,
        }
    },
    computed: {
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
