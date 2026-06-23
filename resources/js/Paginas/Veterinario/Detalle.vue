<template>
    <Head :title="`Veterinario - ${veterinario?.usuario?.name || 'Detalle'}`" />
    <AuthenticatedLayout>
        <div class="container py-4">
            
            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb bg-light p-3 rounded-pill shadow-sm border border-light">
                    <li class="breadcrumb-item">
                        <Link :href="route('panel')" class="text-decoration-none text-muted hover-primary transition-all">
                            <i class="bi bi-house-door"></i> Inicio
                        </Link>
                    </li>
                    <li class="breadcrumb-item">
                        <Link :href="route('veterinarios.listado')" class="text-decoration-none text-muted hover-primary transition-all">
                            Veterinarios
                        </Link>
                    </li>
                    <li class="breadcrumb-item active fw-bold text-primary" aria-current="page">
                        {{ veterinario?.usuario?.name || 'Detalles' }}
                    </li>
                </ol>
            </nav>

            <div v-if="veterinario" class="row g-4">
                <!-- Tarjeta Principal de Información -->
                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                        <div class="position-relative bg-light text-center py-5" style="min-height: 200px;">
                            <div class="position-absolute bottom-0 start-0 w-100 h-50 bg-gradient-dark pointer-events-none"></div>
                            <div class="d-flex align-items-center justify-content-center flex-column position-relative z-index-1 mt-3">
                                <img v-if="veterinario.foto_perfil_url" :src="veterinario.foto_perfil_url" class="rounded-circle shadow-lg mb-3" style="width: 140px; height: 140px; object-fit: cover; border: 4px solid white;" alt="Foto de perfil">
                                <div v-else class="rounded-circle shadow-lg mb-3 bg-primary text-white d-flex align-items-center justify-content-center" style="width: 140px; height: 140px; border: 4px solid white;">
                                    <i class="bi bi-person-fill display-1"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-body p-4 pt-2 text-center bg-white">
                            <h2 class="h4 mb-1 fw-bold text-dark">{{ veterinario.usuario?.name }}</h2>
                            <span class="badge bg-primary bg-opacity-10 text-primary mt-1 mb-4 rounded-pill px-3 py-2 fs-6">{{ veterinario.especialidad?.nombre || 'Sin Especialidad' }}</span>
                            
                            <hr class="text-muted opacity-25 mx-4">
                            
                            <div class="d-flex justify-content-center gap-2 mt-4">
                                <Link :href="route('veterinarios.listado')" class="btn btn-outline-secondary rounded-pill px-4">
                                    <i class="bi bi-arrow-left me-1"></i> Volver al Listado
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detalles de Contacto y Trabajo -->
                <div class="col-12 col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white border-bottom-0 p-4 pb-0">
                            <h3 class="h5 mb-0 text-primary fw-bold">Información de Contacto</h3>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <!-- Datos de Contacto -->
                                <div class="col-12 col-md-6">
                                    <div class="d-flex align-items-start mb-4">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                            <i class="bi bi-envelope fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-muted small text-uppercase mb-1 fw-bold">Correo Electrónico</h6>
                                            <p class="mb-0 fw-medium text-dark">{{ veterinario.usuario?.email || 'No registrado' }}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex align-items-start mb-4">
                                        <div class="bg-success bg-opacity-10 text-success rounded-circle p-3 me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                            <i class="bi bi-telephone fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-muted small text-uppercase mb-1 fw-bold">Teléfono</h6>
                                            <p class="mb-0 fw-medium text-dark">{{ veterinario.telefono || 'No registrado' }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-12 col-md-6">
                                    <div class="d-flex align-items-start mb-4">
                                        <div class="bg-info bg-opacity-10 text-info rounded-circle p-3 me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                            <i class="bi bi-geo-alt fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-muted small text-uppercase mb-1 fw-bold">Dirección</h6>
                                            <p class="mb-0 fw-medium text-dark">{{ veterinario.direccion || 'No registrada' }}</p>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-start mb-4">
                                        <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-3 me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                            <i class="bi bi-building fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-muted small text-uppercase mb-1 fw-bold">Sucursal Principal</h6>
                                            <p class="mb-0 fw-medium text-dark">
                                                <span v-if="veterinario.sucursal" class="badge bg-light text-dark border">{{ veterinario.sucursal.nombre }}</span>
                                                <span v-else class="text-muted fst-italic">No asignada</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4 text-muted opacity-25">

                            <!-- Actividad o Info Adicional (Placeholder) -->
                            
                            <div v-if="esVeterinarioOAdmin"class="row g-3">
                                <h3 class="h6 mb-3 text-secondary fw-bold text-uppercase">Estadísticas</h3>
                                <div class="col-12 col-md-4">
                                    <div class="p-3 bg-light rounded-4 border border-light text-center">
                                        <i class="bi bi-calendar-check text-primary fs-3 mb-2"></i>
                                        <h4 class="mb-0 fw-bold">{{ veterinario.citas_count || '0' }}</h4>
                                        <span class="text-muted small">Citas Atendidas</span>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="p-3 bg-light rounded-4 border border-light text-center">
                                        <i class="bi bi-clock-history text-primary fs-3 mb-2"></i>
                                        <h4 class="mb-0 fw-bold">{{ veterinario.citas_pendientes_count || '0' }}</h4>
                                        <span class="text-muted small">Citas Pendientes</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Manejo de Error / No Encontrado -->
            <div v-else class="text-center py-5 bg-white rounded-4 shadow-sm border border-light mt-4">
                <i class="bi bi-exclamation-circle text-danger display-1 d-block mb-4 opacity-50"></i>
                <h2 class="h4 text-dark mb-3">Veterinario no encontrado</h2>
                <p class="text-muted mb-4">No se pudo cargar la información de este veterinario.</p>
                <Link :href="route('veterinarios.listado')" class="btn btn-primary rounded-pill px-4">
                    Volver al Listado
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from '@/Disenos/LayoutAutenticado.vue';
import { Head, Link } from '@inertiajs/vue3';

export default {
    components: {
        AuthenticatedLayout,
        Head,
        Link
    },
    props: {
        veterinario: {
            type: Object,
            default: null,
        },
    },
    computed: {
        esVeterinarioOAdmin() {
            const user = this.$page.props.auth.user;
            return user && user.rol && (user.rol.nombre_interno === 'veterinario' || user.rol.nombre_interno === 'admin');
        }
    },
}
</script>

<style scoped>
.hover-primary {
    transition: color 0.3s ease;
}
.hover-primary:hover {
    color: var(--bs-primary) !important;
}
.bg-gradient-dark {
    background: linear-gradient(to top, rgba(0,0,0,0.4) 0%, rgba(0,0,0,0) 100%);
}
</style>
