<template>
    <!-- ================================================================================== -->
    <!-- COMPONENTE: DetalleKpi -->
    <!-- ================================================================================== -->
    <Head :title="titulo" />
    <AuthenticatedLayout>
        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <!-- Contenedor para el título de la sección -->
                <div class="d-flex align-items-center gap-3">
                    <Link :href="route('panel')" class="btn btn-outline-secondary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-arrow-left"></i>
                    </Link>
                    <div>
                        <h1 class="h3 fw-bold text-dark mb-0">{{ titulo }}</h1>
                        <p class="text-muted small mb-0">Desglose detallado del indicador</p>
                    </div>
                </div>
                <!-- Botón para imprimir el reporte -->
                <button @click="imprimir" class="btn btn-primary rounded-pill px-3 py-2 shadow-sm">
                    <i class="bi bi-printer me-1"></i> Imprimir Reporte
                </button>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <!--Renderizado iterativo de lista recorreiendo las columnas de la tabla obtenida de la props columnas -->
                                <th v-for="(col, index) in columnas" :key="index" class="px-4 py-3 text-uppercase text-secondary small fw-bold">
                                    {{ col }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <!--Si no hay datos, muestra un mensaje -->
                            <tr v-if="data.length === 0">
                                <td :colspan="columnas.length" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-3 text-secondary opacity-50"></i>
                                    No hay datos disponibles para este indicador en el período actual.
                                </td>
                            </tr>
                            <!-- Recorremos las filas del arreglo -->
                            <tr v-for="(fila, i) in data" :key="i" class="border-bottom">
                                <!-- Recorremos las celdas de cada fila -->
                                <td v-for="(celda, j) in fila" :key="j" class="px-4 py-3">
                                    <span :class="{'fw-semibold': j === 0, 'text-success fw-bold': typeof celda === 'string' && celda.startsWith('$')}">
                                        {{ celda }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
// ==================================================================================
// LÓGICA DEL COMPONENTE (VUE 3)
// ==================================================================================

import AuthenticatedLayout from '@/Disenos/LayoutAutenticado.vue';
import { Head, Link } from '@inertiajs/vue3';

// ------------------------------------------------------------------------------
// EXPORT DEFAULT: Definición principal del componente
// ------------------------------------------------------------------------------
export default {
    // COMPONENTES: Registro de componentes importados
    components: {
        AuthenticatedLayout,
        Head,
        Link,
    },
    // PROPIEDADES: Datos inyectados desde el componente padre o estado
    props: {
        kpi: String,
        titulo: String,
        columnas: Array,
        data: Array
    },
    // MÉTODOS: Bloque de funciones y eventos
    methods: {
        imprimir() {
            window.print();
        }
    }
}
</script>

<style scoped>
@media print {
    .btn, .shadow-sm {
        display: none !important;
        box-shadow: none !important;
    }
    .card {
        border: none !important;
    }
    .container {
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
    }
}
</style>
