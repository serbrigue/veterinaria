<template>
    <Head :title="'Servicio - ' + (prestacion.nombre || 'Detalle')" />

    <AuthenticatedLayout>

        <div class="container py-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center gap-3">
                    <Link :href="route('prestaciones.listado')" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2 rounded-pill px-3 transition-all hover-opacity">
                        <i class="bi bi-arrow-left"></i> Volver a Servicios
                    </Link>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card border-0 shadow-lg overflow-hidden rounded-4">
                        <!-- Cabecera decorativa -->
                        <div class="bg-primary bg-gradient p-4 p-md-5 text-white position-relative">
                            <div class="position-absolute top-0 end-0 p-4 opacity-25">
                                <i class="bi bi-heart-pulse-fill" style="font-size: 6rem;"></i>
                            </div>
                            <div class="position-relative z-1">
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <span class="badge bg-white text-primary rounded-pill px-3 py-2 fw-bold shadow-sm">
                                        <i class="bi bi-star-fill text-warning me-1"></i>
                                        {{ prestacion.especialidad?.nombre || 'Medicina General' }}
                                    </span>
                                    <span v-if="prestacion.categoria_prestacion" class="badge bg-white text-dark rounded-pill px-3 py-2 fw-bold shadow-sm">
                                        <i class="bi bi-tags-fill text-secondary me-1"></i>
                                        Categoría: {{ prestacion.categoria_prestacion.nombre }}
                                    </span>
                                </div>
                                <h1 class="display-6 fw-bold mb-2">{{ prestacion.nombre }}</h1>
                                <p class="mb-0 text-white-50 fs-5">Detalle del servicio veterinario</p>
                            </div>
                        </div>

                        <div class="card-body p-4 p-md-5">
                            <div class="row g-5">
                                <!-- Columna Izquierda: Información -->
                                <div class="col-md-7">
                                    <h3 class="h6 text-uppercase text-primary fw-bold mb-3" style="letter-spacing: 1px;">
                                        <i class="bi bi-info-circle-fill me-2"></i>¿En qué consiste?
                                    </h3>
                                    <p class="text-secondary fs-6 lh-lg mb-0 bg-light p-4 rounded-4 border" style="white-space: pre-wrap;">{{ prestacion.descripcion || 'Sin descripción detallada. Por favor, consulte con nuestro personal para más información sobre este servicio.' }}</p>

                                    <h3 class="mt-4 text-dark fw-bold">¿Quién realiza este servicio?</h3>
                                    <p class="text-secondary fs-6 lh-lg mb-0 bg-light p-4 rounded-4 border" style="white-space: pre-wrap;">{{ especialidad?.nombre || 'Veterinario General' }}</p>
                                </div>

                                <!-- Columna Derecha: Tarjetas de Acción -->
                                <div class="col-md-5">
                                    <div class="d-flex flex-column gap-3 h-100 justify-content-center">
                                        <!-- Sucursal -->
                                        <div class="bg-light p-4 rounded-4 border shadow-sm">
                                            <div class="d-flex align-items-center gap-3 mb-2">
                                                <div class="bg-danger bg-opacity-10 text-danger rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                                    <i class="bi bi-geo-alt-fill fs-5"></i>
                                                </div>
                                                <h4 class="h6 fw-bold mb-0 text-dark">Disponible en Sede</h4>
                                            </div>
                                            <p class="mb-0 fw-bold fs-5 text-dark" style="margin-left: 60px;">
                                                {{ prestacion.sucursal?.nombre || 'Sucursal no especificada' }}
                                            </p>
                                            <p class="mb-0 text-muted small mt-1" style="margin-left: 60px;">
                                                <i class="bi bi-telephone-fill me-1"></i> {{ prestacion.sucursal?.telefono || 'Sin teléfono' }}
                                            </p>
                                        </div>

                                        <!-- Precio -->
                                        <div class="bg-success bg-opacity-10 p-4 rounded-4 border border-success border-opacity-25 text-center shadow-sm mt-2">
                                            <span class="d-block text-success fw-bold text-uppercase mb-2" style="letter-spacing: 1px; font-size: 0.8rem;">Valor del Servicio</span>
                                            <h2 class="display-5 fw-bold text-success mb-0">
                                                ${{ Math.round(prestacion.precio_base).toLocaleString('es-CL') }}
                                            </h2>
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
    name: 'PrestacionDetalle',
    components: {
        AuthenticatedLayout,
        Head,
        Link
    },
    props: {
        prestacion: {
            type: Object,
            required: true,
        },
        especialidad: {
            type: Object,
            required: true,
        }
    }
}
</script>
