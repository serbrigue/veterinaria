<template>
    <AuthenticatedLayout>
        <Head :title="`Detalle - ${raza.nombre}`" />

        <div class="container py-4">
            <div class="mb-3">
                <Link :href="route('razas.listado')" class="btn btn-outline-secondary btn-sm">
                    &larr; Volver al listado
                </Link>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h1 class="h4 mb-0 text-primary fw-bold">Detalle de la Raza</h1>
                </div>
                
                <div class="card-body">
                    <div class="row g-4 align-items-center">
                        <div class="col-12 col-md-7 col-lg-8">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item px-0 py-3">
                                    <h6 class="text-muted mb-1 small">ID de Registro</h6>
                                    <p class="mb-0 fw-semibold">#{{ raza.id }}</p>
                                </li>
                                <li class="list-group-item px-0 py-3">
                                    <h6 class="text-muted mb-1 small">Nombre de la raza</h6>
                                    <p class="mb-0 fs-5">{{ raza.nombre }}</p>
                                </li>
                                <li class="list-group-item px-0 py-3">
                                    <h6 class="text-muted mb-1 small">Descripción</h6>
                                    <p class="mb-0">{{ raza.descripcion }}</p>
                                </li>
                                <li class="list-group-item px-0 py-3">
                                    <div class="d-flex flex-wrap gap-4">
                                        <div>
                                            <h6 class="text-muted mb-1 small">Creada en</h6>
                                            <p class="mb-0 small">{{ new Date(raza.created_at).toLocaleDateString() }}</p>
                                        </div>
                                        <div>
                                            <h6 class="text-muted mb-1 small">Última Actualización</h6>
                                            <p class="mb-0 small">{{ new Date(raza.updated_at).toLocaleDateString() }}</p>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <div class="col-12 col-md-5 col-lg-4 text-center">
                            <div v-if="raza.imagen_url" @click="abrirModalImagen(raza)" class="p-2 border rounded bg-light" style="cursor: pointer; max-width: 100%; margin: 0 auto;">
                                <img 
                                    :src="raza.imagen_url" 
                                    :alt="raza.nombre" 
                                    class="img-fluid rounded shadow-sm" 
                                    style="max-height: 350px; object-fit: cover;"
                                >
                            </div>
                            <div v-else class="p-5 border rounded bg-light d-flex flex-column align-items-center justify-content-center text-muted" style="min-height: 250px;">
                                <span class="fs-1 mb-2">📷</span>
                                <span class="small">Sin foto registrada</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="verModalImagen" class="modal fade show d-block" tabindex="-1" role="dialog" @click.self="cerrarImagenCompleta">
                    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                        <div class="modal-content border-0 bg-transparent">
                            <div class="modal-body p-0 card shadow-lg" @click.stop>
                                <button type="button" class="btn-close position-absolute top-0 end-0 m-3" @click="cerrarImagenCompleta"></button>
                                <h1 class="h4 mb-3 align-text-center">Especie: {{ raza.nombre }}</h1>
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