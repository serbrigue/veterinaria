<template>
    <AuthenticatedLayout>
        <Head :title="`Detalle - ${raza.nombre}`" />

        <div class="container py-4">
            <div class="mb-3">
                <Link :href="route('razas.listado')" class="btn btn-outline-secondary btn-sm">
                    &larr; Volver al listado
                </Link>
            </div>

            <div class="card shadow border-0 overflow-hidden rounded-4">
                <div class="row g-0">
                    <!-- Columna de Imagen -->
                    <div class="col-md-5 col-lg-4 bg-light position-relative">
                        <div v-if="raza.imagen_url" class="h-100 position-relative group-image" style="min-height: 350px;">
                            <img 
                                :src="raza.imagen_url" 
                                :alt="raza.nombre" 
                                class="w-100 h-100 object-fit-cover cursor-pointer hover-zoom transition-all" 
                                @click="abrirModalImagen()"
                                title="Haz clic para ver imagen completa"
                            >
                            <!-- Overlay gradiente -->
                            <div class="position-absolute bottom-0 start-0 w-100 h-50 bg-gradient-dark pointer-events-none"></div>
                            <div class="position-absolute bottom-0 start-0 w-100 p-4 text-white pointer-events-none">
                                <h1 class="display-6 fw-bold mb-0 text-shadow">{{ raza.nombre }}</h1>
                                <span class="badge bg-primary mt-2 shadow-sm">ID: #{{ raza.id }}</span>
                            </div>
                            <!-- Botón indicador de zoom (visible en hover) -->
                            <div class="zoom-indicator position-absolute top-50 start-50 translate-middle pointer-events-none">
                                <div class="bg-dark bg-opacity-50 text-white rounded-circle p-3 backdrop-blur shadow">
                                    <i class="bi bi-zoom-in fs-3"></i>
                                </div>
                            </div>
                        </div>
                        <div v-else class="h-100 d-flex flex-column align-items-center justify-content-center bg-primary bg-opacity-10 text-primary p-5" style="min-height: 300px;">
                            <i class="bi bi-bug fs-1 mb-3"></i>
                            <h2 class="h4 fw-bold">{{ raza.nombre }}</h2>
                            <span class="badge bg-primary mt-2">ID: #{{ raza.id }}</span>
                        </div>
                    </div>

                    <!-- Columna de Detalles -->
                    <div class="col-md-7 col-lg-8">
                        <div class="card-body p-4 p-lg-5 h-100 d-flex flex-column">
                            <h3 class="h6 text-muted text-uppercase mb-4 pb-2 border-bottom fw-bold" style="letter-spacing: 1.5px;">Información General</h3>
                            
                            <div class="mb-4 flex-grow-1">
                                <h4 class="h6 fw-bold text-dark d-flex align-items-center gap-2 mb-3">
                                    <i class="bi bi-card-text text-primary"></i> Descripción
                                </h4>
                                <div class="bg-light bg-opacity-50 p-4 rounded-3 border border-light">
                                    <p class="text-secondary fs-6 lh-lg mb-0" style="text-align: justify;">
                                        {{ raza.descripcion || 'No hay descripción detallada disponible para esta raza.' }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-auto pt-4 border-top">
                                <h4 class="h6 fw-bold text-dark d-flex align-items-center gap-2 mb-3">
                                    <i class="bi bi-tags text-primary"></i> Especie:
                                </h4>
                                <div class="d-flex flex-wrap gap-2 m-3">
                                    <template v-if="especie">
                                        <Link 
                                            :key="especie.id" 
                                            :href="route('especies.detalle', especie.id)" 
                                            class="text-decoration-none"
                                        >
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill hover-elevate transition-all">
                                                <i class="bi bi-tag-fill me-1"></i>{{ especie.nombre }}
                                            </span>
                                        </Link>
                                    </template>
                                    <div v-else class="text-muted small fst-italic">
                                        No hay especie asociada a esta raza.
                                    </div>
                                </div>
                            </div>

                            <div class="row g-4 mt-auto pt-4 border-top">
                                <div class="col-sm-6">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                                            <i class="bi bi-calendar-plus fs-5"></i>
                                        </div>
                                        <div>
                                            <p class="text-muted small mb-0 text-uppercase fw-semibold" style="letter-spacing: 0.5px;">Fecha de Creación</p>
                                            <p class="mb-0 fw-bold text-dark fs-5">{{ new Date(raza.created_at).toLocaleDateString() }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-info bg-opacity-10 p-3 rounded-circle text-info">
                                            <i class="bi bi-calendar-check fs-5"></i>
                                        </div>
                                        <div>
                                            <p class="text-muted small mb-0 text-uppercase fw-semibold" style="letter-spacing: 0.5px;">Última Actualización</p>
                                            <p class="mb-0 fw-bold text-dark fs-5">{{ new Date(raza.updated_at).toLocaleDateString() }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="verModalImagen" class="modal fade show d-block" tabindex="-1" role="dialog" @click.self="cerrarImagenCompleta">
                    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                        <div class="modal-content border-0 bg-transparent">
                            <div class="modal-body p-0 card shadow-lg" @click.stop>
                                <button type="button" class="btn-close position-absolute top-0 end-0 m-3" @click="cerrarImagenCompleta"></button>
                                <h1 class="h4 mb-3 align-text-center">Raza: {{ raza.nombre }}</h1>
                                <img 
                                    :src="raza.imagen_url" 
                                    :alt="raza.nombre" 
                                    class="img-fluid m-0 p-4 rounded shadow-sm w-100" 
                                    style="object-fit: contain; max-height: 80vh;"
                                >
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
        raza: Object,
        especie: Object,
    },
    data() {
        return {
            verModalImagen: false,
        }
    },
    methods: {
        abrirModalImagen() {
            this.verModalImagen = true;
        },
        cerrarImagenCompleta() {
            this.verModalImagen = false;
        }
    }
};
</script>

<style scoped>
.transition-all {
    transition: all 0.3s ease;
}
.hover-zoom {
    transition: transform 0.5s ease;
}
.group-image:hover .hover-zoom {
    transform: scale(1.05);
}
.bg-gradient-dark {
    background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0) 100%);
}
.text-shadow {
    text-shadow: 0 2px 4px rgba(0,0,0,0.6);
}
.pointer-events-none {
    pointer-events: none;
}
.zoom-indicator {
    opacity: 0;
    transform: translate(-50%, -50%) scale(0.8) !important;
    transition: all 0.3s ease;
}
.group-image:hover .zoom-indicator {
    opacity: 1;
    transform: translate(-50%, -50%) scale(1) !important;
}
.backdrop-blur {
    backdrop-filter: blur(5px);
}
</style>