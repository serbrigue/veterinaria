<template>
    <Head :title="'Insumo - ' + (insumo.nombre || 'Detalle')" />

    <AuthenticatedLayout>
        <div class="container py-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center gap-3">
                    <Link :href="route('insumos.listado')" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                        <i class="bi bi-arrow-left"></i> Volver
                    </Link>
                    <h1 class="h3 mb-0">Detalle del Insumo</h1>
                </div>
            </div>

            <div class="row g-4">
                <!-- Columna Izquierda: Detalles principales -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-4 bg-light p-3 rounded border">
                                <div>
                                    <span class="badge rounded-pill px-3 py-2 mt-1" :class="insumo.estado === 'activo' ? 'bg-primary' : 'bg-secondary'">
                                        {{ insumo.estado.toUpperCase() }}
                                    </span>
                                </div>
                            </div>

                            <h2 class="h4 fw-bold text-dark mb-3">{{ insumo.nombre }}</h2>

                            <!-- Categoría del insumo -->
                            <div class="mb-3">
                                <h3 class="h6 text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">Categoría</h3>
                                <span v-if="insumo.categoria_insumo" class="badge rounded-pill px-3 py-2" :class="badgeCategoria(insumo.categoria_insumo.nombre)">
                                    <i class="bi bi-tag-fill me-1"></i>{{ insumo.categoria_insumo.nombre }}
                                </span>
                                <span v-else class="badge bg-secondary">Sin categoría</span>
                            </div>

                            <div class="mb-4">
                                <h3 class="h6 text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">Descripción</h3>
                                <p class="text-secondary bg-light p-3 rounded border-start border-primary border-3 mb-0" style="white-space: pre-wrap;">
                                    {{ insumo.descripcion || 'Sin descripción detallada.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Inventario y Finanzas -->
                <div class="col-lg-4">
                    <div class="d-flex flex-column gap-4 h-100">
                        <!-- Tarjeta de Inventario -->
                        <div class="card border-0 shadow-sm border-top border-warning border-4">
                            <div class="card-header bg-transparent border-0 pt-3 px-4 pb-0">
                                <h3 class="h6 mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                                    <i class="bi bi-box-seam-fill text-warning"></i> Inventario
                                </h3>
                            </div>
                            <div class="card-body p-4 pt-3">
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="text-muted fw-medium">Stock Actual:</span>
                                    <span class="fw-bold" :class="insumo.stock_actual <= insumo.stock_minimo ? 'text-danger' : 'text-success'">
                                        {{ insumo.stock_actual }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="text-muted fw-medium">Stock Mínimo:</span>
                                    <span class="fw-bold text-dark">{{ insumo.stock_minimo }}</span>
                                </div>
                                
                                <div v-if="insumo.stock_actual <= insumo.stock_minimo" class="alert alert-danger mb-0 py-2 mt-3 text-center small">
                                    <i class="bi bi-exclamation-triangle-fill"></i> Nivel de stock crítico
                                </div>
                            </div>
                        </div>

                        <!-- Tarjeta de Finanzas -->
                        <div class="card border-0 shadow-sm border-top border-success border-4 flex-grow-1">
                            <div class="card-header bg-transparent border-0 pt-3 px-4 pb-0">
                                <h3 class="h6 mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                                    <i class="bi bi-currency-dollar text-success"></i> Finanzas
                                </h3>
                            </div>
                            <div class="card-body p-4 pt-3">
                                <div class="text-center mb-3">
                                    <h4 class="display-6 fw-bold text-success mb-0">${{ Math.round(insumo.precio_venta).toLocaleString('es-CL') }}</h4>
                                    <span class="text-muted small">Precio de Venta Unitario</span>
                                </div>
                                <div class="alert alert-info py-2 mb-0 small">
                                    <i class="bi bi-info-circle-fill"></i> Los insumos no generan comisión médica.
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
    name: 'InsumoDetalle',
    components: { AuthenticatedLayout, Head, Link },
    props: { insumo: { type: Object, required: true } },
    methods: {
        badgeCategoria(nombre) {
            const mapa = { 'Medicamento': 'bg-danger', 'Material Quirúrgico': 'bg-warning text-dark', 'Vacuna': 'bg-success', 'Consumible General': 'bg-secondary' };
            return mapa[nombre] || 'bg-info text-dark';
        },
    },
}
</script>
