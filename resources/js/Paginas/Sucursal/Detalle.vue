<template>
    <AuthenticatedLayout>
        <Head :title="`Detalle - ${sucursal.nombre}`" />

        <div class="container py-4">
            <div class="mb-3">
                <Link :href="route('sucursales.listado')" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    &larr; Volver al listado
                </Link>
            </div>

            <div class="card shadow border-0 overflow-hidden rounded-4">
                <div class="row g-0">
                    <!-- Columna de Imagen/Icono -->
                    <div class="col-md-5 col-lg-4 bg-light position-relative">
                        <div class="h-100 d-flex flex-column align-items-center justify-content-center bg-primary bg-opacity-10 text-primary p-5" style="min-height: 300px;">
                            <i class="bi bi-shop fs-1 mb-3 hover-zoom transition-all"></i>
                            <h2 class="h4 fw-bold text-center">{{ sucursal.nombre }}</h2>
                            <span class="badge bg-primary mt-2">ID: #{{ sucursal.id }}</span>
                        </div>
                    </div>

                    <!-- Columna de Detalles -->
                    <div class="col-md-7 col-lg-8">
                        <div class="card-body p-4 p-lg-5 h-100 d-flex flex-column">
                            <h3 class="h6 text-muted text-uppercase mb-4 pb-2 border-bottom fw-bold" style="letter-spacing: 1.5px;">Información de Sucursal</h3>
                            
                            <div class="row g-4 flex-grow-1 mb-4">
                                <div class="col-12 col-sm-6">
                                    <div class="bg-light bg-opacity-50 p-4 rounded-3 border border-light h-100">
                                        <h4 class="h6 fw-bold text-dark d-flex align-items-center gap-2 mb-2">
                                            <i class="bi bi-geo-alt text-primary"></i> Dirección
                                        </h4>
                                        <p class="text-secondary fs-6 mb-0">
                                            {{ sucursal.direccion || 'No registrada' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="bg-light bg-opacity-50 p-4 rounded-3 border border-light h-100">
                                        <h4 class="h6 fw-bold text-dark d-flex align-items-center gap-2 mb-2">
                                            <i class="bi bi-telephone text-primary"></i> Teléfono
                                        </h4>
                                        <p class="text-secondary fs-6 mb-0">
                                            {{ sucursal.telefono || 'No registrado' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-4 mb-4">
                                <!-- Columna de Veterinarios -->
                                <div class="col-12 col-md-6">
                                    <h3 class="h6 text-muted text-uppercase mb-3 pb-2 border-bottom fw-bold" style="letter-spacing: 1.5px;">
                                        <i class="bi bi-person-badge text-primary me-2"></i>Veterinarios
                                    </h3>
                                    <div class="d-flex flex-wrap gap-2">
                                        <template v-if="veterinarios && veterinarios.length > 0">
                                            <span 
                                                v-for="veterinario in veterinarios" 
                                                :key="veterinario.id"
                                                class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill hover-elevate transition-all"
                                                style="cursor: default;"
                                            >
                                                <i class="bi bi-person-fill me-1"></i>{{ veterinario.nombre }}
                                            </span>
                                        </template>
                                        <div v-else class="text-muted small fst-italic w-100 p-3 bg-light rounded-3 border border-light text-center">
                                            No hay veterinarios asociados a esta sucursal.
                                        </div>
                                    </div>
                                </div>
                                <!-- Columna de Boxes -->
                                <div class="col-12 col-md-6">
                                    <h3 class="h6 text-muted text-uppercase mb-3 pb-2 border-bottom fw-bold" style="letter-spacing: 1.5px;">
                                        <i class="bi bi-door-closed text-primary me-2"></i>Boxes
                                    </h3>
                                    <div class="d-flex flex-wrap gap-2">
                                        <template v-if="boxes && boxes.length > 0">
                                            <span 
                                                v-for="box in boxes" 
                                                :key="box.id"
                                                class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-3 py-2 rounded-pill hover-elevate transition-all"
                                                style="cursor: default;"
                                            >
                                                <i class="bi bi-door-closed-fill me-1"></i>{{ box.nombre }}
                                            </span>
                                        </template>
                                        <div v-else class="text-muted small fst-italic w-100 p-3 bg-light rounded-3 border border-light text-center">
                                            No hay boxes registrados en esta sucursal.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-4 mt-auto pt-4 border-top">
                                <div class="col-sm-6">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary hover-zoom transition-all">
                                            <i class="bi bi-calendar-plus fs-5"></i>
                                        </div>
                                        <div>
                                            <p class="text-muted small mb-0 text-uppercase fw-semibold" style="letter-spacing: 0.5px;">Fecha de Registro</p>
                                            <p class="mb-0 fw-bold text-dark fs-5">{{ new Date(sucursal.created_at).toLocaleDateString() }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-info bg-opacity-10 p-3 rounded-circle text-info hover-zoom transition-all">
                                            <i class="bi bi-calendar-check fs-5"></i>
                                        </div>
                                        <div>
                                            <p class="text-muted small mb-0 text-uppercase fw-semibold" style="letter-spacing: 0.5px;">Última Actualización</p>
                                            <p class="mb-0 fw-bold text-dark fs-5">{{ new Date(sucursal.updated_at).toLocaleDateString() }}</p>
                                        </div>
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
    components: {
        AuthenticatedLayout,
        Head,
        Link 
    },
    props: {
        sucursal: Object,
        veterinarios: Array,
        boxes: Array,
    },
};
</script>

<style scoped>
.transition-all {
    transition: all 0.3s ease;
}
.hover-zoom:hover {
    transform: scale(1.1);
}
</style>
