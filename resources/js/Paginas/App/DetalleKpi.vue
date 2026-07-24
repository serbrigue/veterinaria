<template>
    <Head :title="titulo" />
    <AuthenticatedLayout>
        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center gap-3">
                    <Link :href="route('panel')" class="btn btn-outline-secondary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-arrow-left"></i>
                    </Link>
                    <div>
                        <h1 class="h3 fw-bold text-dark mb-0">{{ titulo }}</h1>
                        <p class="text-muted small mb-0">Desglose detallado del indicador</p>
                    </div>
                </div>
                <button @click="imprimir" class="btn btn-primary rounded-pill px-3 py-2 shadow-sm">
                    <i class="bi bi-printer me-1"></i> Imprimir Reporte
                </button>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th v-for="(col, index) in columnas" :key="index" class="px-4 py-3 text-uppercase text-secondary small fw-bold">
                                    {{ col }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="data.length === 0">
                                <td :colspan="columnas.length" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-3 text-secondary opacity-50"></i>
                                    No hay datos disponibles para este indicador en el período actual.
                                </td>
                            </tr>
                            <tr v-for="(fila, i) in data" :key="i" class="border-bottom">
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
import AuthenticatedLayout from '@/Disenos/LayoutAutenticado.vue';
import { Head, Link } from '@inertiajs/vue3';

export default {
    components: {
        AuthenticatedLayout,
        Head,
        Link,
    },
    props: {
        kpi: String,
        titulo: String,
        columnas: Array,
        data: Array
    },
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
