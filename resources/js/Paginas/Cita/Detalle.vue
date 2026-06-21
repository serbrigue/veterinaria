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
                                <div class="text-end">
                                    <div class="text-muted small mb-2"><i class="bi bi-clock-fill me-1"></i> Programada: {{ formatearFechaHora(cita.fecha_hora) }}</div>
                                    <div class="btn-group btn-group-sm" v-if="estadoActual === 'programada'">
                                        <button @click="cambiarEstado('completada')" class="btn btn-success" :disabled="procesando"><i class="bi bi-check-lg"></i> Marcar Completada</button>
                                        <button @click="cambiarEstado('cancelada')" class="btn btn-outline-danger" :disabled="procesando"><i class="bi bi-x-lg"></i> Cancelar</button>
                                    </div>
                                </div>
                            </div>

                            <h2 class="h4 fw-bold text-dark mb-3">{{ cita.titulo }}</h2>
                            <div class="mb-4">
                                <h3 class="h6 text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">Motivo o Descripción</h3>
                                <p class="text-secondary bg-light p-3 rounded border-start border-primary border-3 mb-0" style="white-space: pre-wrap;">
                                    {{ cita.descripcion || 'Sin descripción detallada.' }}
                                </p>
                            </div>

                            <div class="mb-0">
                                <h3 class="h6 text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">Notas Clínicas (Autoguardado)</h3>
                                <div class="position-relative">
                                    <textarea 
                                        v-model="notasConsulta" 
                                        class="form-control" 
                                        rows="5" 
                                        placeholder="Escribe notas médicas, tratamiento o diagnóstico aquí..."
                                        @blur="guardarNotas"
                                        :disabled="procesando"
                                    ></textarea>
                                    <div v-if="guardandoNotas" class="position-absolute bottom-0 end-0 p-2">
                                        <span class="spinner-border spinner-border-sm text-primary"></span>
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block">Las notas se guardan automáticamente al hacer clic fuera del cuadro de texto.</small>
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
                                        <img v-if="cita.veterinario.imagen" :src="cita.veterinario.imagen" :alt="cita.veterinario.nombre" class="rounded-circle object-fit-cover shadow-sm" style="width: 60px; height: 60px;">
                                        <i v-else class="bi bi-person-fill fs-4"></i>
                                    </div>
                                    <div>
                                        <h4 class="h6 mb-1 fw-bold text-dark">
                                            <Link :href="route('veterinarios.detalle', cita.veterinario.id)" class="text-decoration-none text-dark hover-primary">
                                                Dr. {{ cita.veterinario.nombre }}
                                            </Link>
                                        </h4>
                                        <p class="text-muted small mb-0">Especialidad: {{ cita.veterinario.especie?.nombre || 'General' }}</p>
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
                                    <img v-if="cita.mascota.imagen" :src="cita.mascota.imagen" :alt="cita.mascota.nombre" class="rounded-circle object-fit-cover shadow-sm" style="width: 60px; height: 60px;">
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
                                <div v-if="cita.cliente" class="d-flex align-items-center gap-3">
                                    <img v-if="cita.cliente.imagen" :src="cita.cliente.imagen" :alt="cita.cliente.nombre" class="rounded-circle object-fit-cover shadow-sm" style="width: 60px; height: 60px;">
                                    <div v-else class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="bi bi-heart-fill fs-4"></i>
                                    </div>
                                    <div>
                                        <h4 class="h6 mb-1 fw-bold text-dark">
                                            <Link :href="route('clientes.detalle', cita.cliente.id)" class="text-decoration-none text-dark hover-primary">
                                                {{ cita.cliente.nombre }}
                                            </Link>
                                        </h4>
                                        <p class="text-muted small mb-0">{{ cita.cliente.telefono || 'Especie N/A' }} | {{ cita.cliente.email || 'Sexo N/A' }}</p>
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
            notasConsulta: this.cita.notas || ''
        }
    },
    watch: {
        'cita.notas'(nuevaNota) {
            this.notasConsulta = nuevaNota || '';
        }
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
