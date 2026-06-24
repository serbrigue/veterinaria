<template>
    <Head title="Liquidación de Veterinarios" />

    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="h4 font-weight-bold text-dark mb-0">
                    <i class="bi bi-person-lines-fill me-2 text-primary"></i> Liquidación Mensual de Honorarios
                </h2>
                <Link :href="route('panel')" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm d-print-none">
                    <i class="bi bi-arrow-left me-1"></i> Volver al Panel
                </Link>
            </div>
        </template>

        <div class="container py-4">
            <!-- PANEL DE FILTROS -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white d-print-none">
                <div class="card-body p-4">
                    <h5 class="card-title fw-bold text-dark mb-3">Periodo de Liquidación</h5>
                    
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label text-muted small fw-semibold text-uppercase tracking-wide">Mes</label>
                            <select 
                                class="form-select form-select-lg rounded-3 shadow-none border-light bg-light" 
                                v-model="filtros.mes"
                                @change="aplicarFiltros"
                            >
                                <option v-for="(nombre, num) in meses" :key="num" :value="num">
                                    {{ nombre }}
                                </option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label text-muted small fw-semibold text-uppercase tracking-wide">Año</label>
                            <select 
                                class="form-select form-select-lg rounded-3 shadow-none border-light bg-light" 
                                v-model="filtros.anio"
                                @change="aplicarFiltros"
                            >
                                <option v-for="a in anios" :key="a" :value="a">
                                    {{ a }}
                                </option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-end">
                            <button 
                                class="btn btn-primary btn-lg rounded-3 w-100 shadow-sm"
                                @click="imprimirReporte"
                            >
                                <i class="bi bi-printer me-2"></i> Imprimir Reporte
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RESUMEN MÉTRICA -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-primary text-white overflow-hidden position-relative print-card">
                <div class="position-absolute top-0 end-0 p-4 opacity-25">
                    <i class="bi bi-cash-stack display-1"></i>
                </div>
                <div class="card-body p-4 position-relative z-1">
                    <p class="mb-1 fw-medium text-white-50 text-uppercase tracking-wide">Total a Pagar en Honorarios</p>
                    <h2 class="display-5 fw-bold mb-0">${{ totalGeneralComisiones }}</h2>
                    <p class="mb-0 mt-2 text-white-50 small">Corresponde a la suma de todas las comisiones de los veterinarios en {{ nombreMesActual }} {{ filtros.anio }}.</p>
                </div>
            </div>

            <!-- LISTADO DE LIQUIDACIONES -->
            <div class="card border-0 shadow-sm rounded-4 bg-white print-table-container">
                <div class="table-responsive">
                    <table class="table table-hover table-borderless align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-uppercase text-muted small fw-bold py-3 ps-4 rounded-start">Veterinario</th>
                                <th class="text-uppercase text-muted small fw-bold py-3 text-center">Servicios Realizados</th>
                                <th class="text-uppercase text-muted small fw-bold py-3 text-end">Generado (Bruto)</th>
                                <th class="text-uppercase text-muted small fw-bold py-3 text-end pe-4 rounded-end">Comisión a Pagar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="cargando">
                                <td colspan="4" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="text-muted mt-2 mb-0">Calculando honorarios...</p>
                                </td>
                            </tr>
                            <tr v-else-if="liquidaciones.length === 0 || totalGeneralComisiones === '0'">
                                <td colspan="4" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-piggy-bank display-4 d-block mb-3 opacity-50"></i>
                                        <p class="mb-0 fw-medium">No hay servicios completados ni comisiones generadas en este periodo.</p>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="vet in liquidacionesFiltradas" :key="vet.id" class="border-bottom">
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3 text-primary">
                                            <i class="bi bi-person-badge-fill fs-5"></i>
                                        </div>
                                        <div>
                                            <span class="fw-bold d-block text-dark">{{ vet.nombre }}</span>
                                            <span class="text-muted small">ID: VET-{{ String(vet.id).padStart(4, '0') }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border px-3 py-2 fs-6">
                                        {{ vet.servicios_realizados }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="text-secondary fw-semibold">${{ formatoDinero(vet.total_generado) }}</span>
                                </td>
                                <td class="text-end pe-4">
                                    <span class="fw-bold fs-5 text-success">${{ formatoDinero(vet.total_comision) }}</span>
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
import axios from 'axios';

export default {
    components: {
        AuthenticatedLayout,
        Head,
        Link
    },
    props: {
        liquidaciones_iniciales: {
            type: Array,
            required: true
        },
        mes_inicial: {
            type: [String, Number],
            required: true
        },
        anio_inicial: {
            type: [String, Number],
            required: true
        }
    },
    data() {
        return {
            liquidaciones: this.liquidaciones_iniciales,
            cargando: false,
            meses: {
                1: 'Enero', 2: 'Febrero', 3: 'Marzo', 4: 'Abril', 5: 'Mayo', 6: 'Junio',
                7: 'Julio', 8: 'Agosto', 9: 'Septiembre', 10: 'Octubre', 11: 'Noviembre', 12: 'Diciembre'
            },
            anios: [2024, 2025, 2026, 2027],
            filtros: {
                mes: this.mes_inicial,
                anio: this.anio_inicial,
            }
        };
    },
    computed: {
        liquidacionesFiltradas() {
            // Solo mostramos veterinarios que generaron comisiones o hicieron servicios
            return this.liquidaciones.filter(v => v.servicios_realizados > 0);
        },
        totalGeneralComisiones() {
            const total = this.liquidacionesFiltradas.reduce((sum, v) => sum + (parseFloat(v.total_comision) || 0), 0);
            return this.formatoDinero(total);
        },
        nombreMesActual() {
            return this.meses[this.filtros.mes] || '';
        }
    },
    methods: {
        async aplicarFiltros() {
            this.cargando = true;
            try {
                const response = await axios.get(route('pagos.veterinarios'), {
                    params: this.filtros,
                    headers: { 'Accept': 'application/json' }
                });
                this.liquidaciones = response.data;
            } catch (error) {
                console.error('Error al calcular liquidaciones:', error);
            } finally {
                this.cargando = false;
            }
        },
        formatoDinero(valor) {
            if (!valor) return '0';
            return Math.round(valor).toLocaleString('es-CL');
        },
        imprimirReporte() {
            window.print();
        }
    }
}
</script>

<style scoped>
.tracking-wide {
    letter-spacing: 0.05em;
}
.z-1 {
    z-index: 1;
}

@media print {
    body * {
        visibility: hidden;
    }
    .print-card, .print-card *, .print-table-container, .print-table-container * {
        visibility: visible;
    }
    .print-card {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .print-table-container {
        position: absolute;
        left: 0;
        top: 150px;
        width: 100%;
    }
    .d-print-none {
        display: none !important;
    }
    .bg-primary {
        background-color: #0d6efd !important;
        color: white !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .bg-light {
        background-color: #f8f9fa !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
</style>
