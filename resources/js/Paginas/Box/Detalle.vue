<template>
    <AuthenticatedLayout>
        <Head :title="`Detalle - ${box.nombre}`" />

        <div class="container py-4">
            <div class="mb-3">
                <Link :href="route('boxes.listado')" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    &larr; Volver al listado
                </Link>
            </div>

            <div class="card shadow border-0 overflow-hidden rounded-4">
                <div class="row g-0">
                    <!-- Columna de Imagen/Icono -->
                    <div class="col-md-5 col-lg-4 bg-light position-relative">
                        <div class="h-100 d-flex flex-column align-items-center justify-content-center bg-primary bg-opacity-10 text-primary p-5" style="min-height: 300px;">
                            <i class="bi bi-door-closed fs-1 mb-3 hover-zoom transition-all"></i>
                            <h2 class="h4 fw-bold text-center">{{ box.nombre }}</h2>
                            <span class="badge bg-primary mt-2">ID: #{{ box.id }}</span>
                        </div>
                    </div>

                    <!-- Columna de Detalles -->
                    <div class="col-md-7 col-lg-8">
                        <div class="card-body p-4 p-lg-5 h-100 d-flex flex-column">
                            <h3 class="h6 text-muted text-uppercase mb-4 pb-2 border-bottom fw-bold" style="letter-spacing: 1.5px;">Información del Box</h3>
                            
                            <div class="mb-4">
                                <h4 class="h6 fw-bold text-dark d-flex align-items-center gap-2 mb-3">
                                    <i class="bi bi-card-text text-primary"></i> Descripción
                                </h4>
                                <div class="bg-light bg-opacity-50 p-4 rounded-3 border border-light">
                                    <p class="text-secondary fs-6 lh-lg mb-0" style="text-align: justify;">
                                        {{ box.descripcion || 'No hay descripción detallada disponible para este box.' }}
                                    </p>
                                </div>
                            </div>

                            <div class="mb-4 flex-grow-1">
                                <h4 class="h6 fw-bold text-dark d-flex align-items-center gap-2 mb-3">
                                    <i class="bi bi-shop text-primary"></i> Sucursal Asociada
                                </h4>
                                <div class="bg-light bg-opacity-50 p-3 rounded-3 border border-light d-inline-block">
                                    <p class="text-secondary fs-6 mb-0 d-flex align-items-center gap-2">
                                        <span class="badge bg-primary rounded-pill">{{ box.sucursal?.nombre || 'No asignada' }}</span>
                                    </p>
                                </div>
                            </div>

                            <!-- Tipo de Box (Categoría de Prestación) -->
                            <div class="mb-4">
                                <h4 class="h6 fw-bold text-dark d-flex align-items-center gap-2 mb-3">
                                    <i class="bi bi-tag-fill text-primary"></i> Tipo de Atención
                                </h4>
                                <div v-if="box.categoria_prestacion" class="bg-light bg-opacity-50 p-3 rounded-3 border border-light d-inline-block">
                                    <span class="badge rounded-pill px-3 py-2 fs-6" :class="badgeCategoria(box.categoria_prestacion.nombre)">
                                        {{ box.categoria_prestacion.nombre }}
                                    </span>
                                    <p class="text-muted small mt-2 mb-0">{{ box.categoria_prestacion.descripcion }}</p>
                                </div>
                                <div v-else class="bg-light bg-opacity-50 p-3 rounded-3 border border-light d-inline-block">
                                    <span class="badge bg-secondary bg-opacity-50 rounded-pill px-3 py-2">Sin restricción</span>
                                    <p class="text-muted small mt-2 mb-0">Este box acepta cualquier tipo de prestación.</p>
                                </div>
                            </div>

                            <div class="row g-4 mt-auto pt-4 border-top">
                                <div class="col-sm-6">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary hover-zoom transition-all">
                                            <i class="bi bi-calendar-plus fs-5"></i>
                                        </div>
                                        <div>
                                            <p class="text-muted small mb-0 text-uppercase fw-semibold" style="letter-spacing: 0.5px;">Fecha de Creación</p>
                                            <p class="mb-0 fw-bold text-dark fs-5">{{ new Date(box.created_at).toLocaleDateString() }}</p>
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
                                            <p class="mb-0 fw-bold text-dark fs-5">{{ new Date(box.updated_at).toLocaleDateString() }}</p>
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
    components: { AuthenticatedLayout, Head, Link },
    props: { box: Object },
    methods: {
        badgeCategoria(nombre) {
            const mapa = { 'Consulta': 'bg-info text-dark', 'Cirugia': 'bg-danger', 'Urgencia': 'bg-warning text-dark', 'Estetica': 'bg-success' };
            return mapa[nombre] || 'bg-secondary';
        },
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
