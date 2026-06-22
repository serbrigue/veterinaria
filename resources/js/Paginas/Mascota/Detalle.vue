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
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1" @click="abrirEditar">
                        <i class="bi bi-pencil"></i> Editar
                    </button>
                    <button class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1" @click="confirmarEliminar">
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
                                            {{ cliente?.name }} <i class="bi bi-box-arrow-up-right ms-1 small"></i>
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
                            <button class="btn btn-sm btn-primary d-flex align-items-center gap-1">
                                <i class="bi bi-plus-lg"></i> Nueva Cita
                            </button>
                        </div>
                        <div class="card-body p-4 text-center text-muted">
                            <div class="py-4">
                                <i class="bi bi-calendar-x fs-1 text-light mb-3 d-block" style="color: #dee2e6 !important;"></i>
                                <p class="mb-0">No hay citas programadas próximamente.</p>
                            </div>
                            <!-- Placeholder: Aquí irá la lista de citas iterada con v-for -->
                        </div>
                    </div>

                    <!-- Historial Médico -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                            <h3 class="h6 mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                                <i class="bi bi-journal-medical text-primary"></i> Historial Médico
                            </h3>
                            <button class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1">
                                <i class="bi bi-plus-lg"></i> Añadir Registro
                            </button>
                        </div>
                        <div class="card-body p-4 text-center text-muted">
                            <div class="py-4">
                                <i class="bi bi-file-earmark-medical fs-1 text-light mb-3 d-block" style="color: #dee2e6 !important;"></i>
                                <p class="mb-0">El historial médico está vacío.</p>
                            </div>
                            <!-- Placeholder: Aquí irá la lista del historial iterada con v-for -->
                        </div>
                    </div>
                </div>            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from '@/Disenos/LayoutAutenticado.vue';
import { Head, Link } from '@inertiajs/vue3';

export default {
    name: 'MascotaDetalle',
    components: {
        AuthenticatedLayout,
        Head,
        Link
    },
    props: {
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
        }
    },
    methods: {
        formatearFecha(fecha) {
            if (!fecha) return 'Sin fecha';
            const date = new Date(fecha);
            return date.toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        },
        abrirEditar() {
            // Lógica para abrir modal de edición de mascota
        },
        confirmarEliminar() {
            // Lógica para confirmar eliminación de mascota
        }
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
</style>