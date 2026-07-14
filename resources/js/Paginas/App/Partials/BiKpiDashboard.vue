<template>
    <div class="bi-dashboard mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 fw-bold text-dark mb-0">
                <i class="bi bi-bar-chart-line-fill text-primary me-2"></i>
                Panel de Inteligencia de Negocios (BI)
            </h2>
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                <i class="bi bi-graph-up-arrow me-1"></i> Análisis Avanzado
            </span>
        </div>

        <div class="row g-4">
            <!-- 1. Operación Clínica y Eficiencia -->
            <div class="col-12 col-xl-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-bottom-0 p-4 pb-0">
                        <h5 class="fw-bold text-dark mb-0">1. Operación Clínica y Eficiencia</h5>
                        <p class="text-muted small">Productividad y uso de infraestructura</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="p-3 bg-light rounded-3 text-center h-100">
                                    <div class="text-muted small mb-1">Tasa Ocupación Boxes</div>
                                    <div class="fs-4 fw-bold text-primary">{{ biData.operacion?.tasa_ocupacion_boxes || 0 }}%</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-light rounded-3 text-center h-100">
                                    <div class="text-muted small mb-1">Tasa de Ausentismo</div>
                                    <div class="fs-4 fw-bold text-danger">{{ biData.operacion?.tasa_ausentismo || 0 }}%</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">Ticket Promedio por Cita</span>
                                    <span class="fs-5 fw-bold text-success">${{ formatoDinero(biData.operacion?.ticket_promedio) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Financieros y de Rentabilidad -->
            <div class="col-12 col-xl-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-bottom-0 p-4 pb-0">
                        <h5 class="fw-bold text-dark mb-0">2. Financieros y de Rentabilidad</h5>
                        <p class="text-muted small">Métricas monetarias y márgenes</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="p-3 bg-success bg-opacity-10 rounded-3 text-center h-100">
                                    <div class="text-success small fw-medium mb-1">Ingresos Brutos</div>
                                    <div class="fs-5 fw-bold text-success">${{ formatoDinero(biData.financiero?.ingresos_brutos) }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-danger bg-opacity-10 rounded-3 text-center h-100">
                                    <div class="text-danger small fw-medium mb-1">Costo Nómina Var.</div>
                                    <div class="fs-5 fw-bold text-danger">${{ formatoDinero(biData.financiero?.costo_nomina_variable) }}</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mt-2">
                                    <div class="text-muted small fw-medium mb-2">Margen Neto por Sucursal:</div>
                                    <div v-for="suc in biData.financiero?.margen_neto_sucursal" :key="suc.nombre" class="d-flex justify-content-between align-items-center mb-1 p-2 bg-light rounded">
                                        <span class="small fw-semibold">{{ suc.nombre }}</span>
                                        <div class="text-end">
                                            <span class="d-block small text-dark fw-bold">${{ formatoDinero(suc.margen_neto) }}</span>
                                            <span class="badge bg-primary rounded-pill" style="font-size: 0.65rem">{{ suc.margen_porcentaje }}%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Clientes y Fidelización -->
            <div class="col-12 col-xl-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-bottom-0 p-4 pb-0">
                        <h5 class="fw-bold text-dark mb-0">3. Clientes y Fidelización</h5>
                        <p class="text-muted small">Retención y comportamiento de usuarios</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="d-flex align-items-center p-3 bg-light rounded-3">
                                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary me-3">
                                        <i class="bi bi-gem fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="text-muted small mb-0">Valor de Vida del Cliente (LTV)</div>
                                        <div class="fs-4 fw-bold text-dark">${{ formatoDinero(biData.clientes?.ltv) }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-light rounded-3 text-center h-100">
                                    <div class="text-muted small mb-1">Frecuencia Visita Anual</div>
                                    <div class="fs-5 fw-bold text-info">{{ biData.clientes?.frecuencia_visita }} <span class="fs-6 fw-normal">citas/mascota</span></div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-light rounded-3 text-center h-100">
                                    <div class="text-muted small mb-1">Conversión Registro &rarr; Cita</div>
                                    <div class="fs-5 fw-bold text-primary">{{ biData.clientes?.tasa_conversion }}%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Logística e Inventario -->
            <div class="col-12 col-xl-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-bottom-0 p-4 pb-0 d-flex justify-content-between">
                        <div>
                            <h5 class="fw-bold text-dark mb-0">4. Logística e Inventario</h5>
                            <p class="text-muted small">Control de stock y movimiento</p>
                        </div>
                        <span class="badge bg-warning text-dark align-self-start py-1">
                            <i class="bi bi-exclamation-triangle"></i> {{ biData.inventario?.alertas_stock?.length || 0 }} Alertas
                        </span>
                    </div>
                    <div class="card-body p-4 pt-2">
                        <div class="mb-3">
                            <div class="text-muted small fw-medium mb-2">Índice de Rotación (Top 3):</div>
                            <div class="list-group list-group-flush">
                                <div v-for="insumo in topRotacion" :key="insumo.insumo" class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                    <span class="small text-truncate" style="max-width: 60%;">{{ insumo.insumo }}</span>
                                    <span class="badge bg-secondary rounded-pill">Índice: {{ insumo.indice_rotacion }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Merma Registrada</span>
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">0 artículos (Módulo en desarrollo)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'BiKpiDashboard',
    props: {
        biData: {
            type: Object,
            required: true,
            default: () => ({})
        }
    },
    computed: {
        topRotacion() {
            if (!this.biData.inventario?.rotacion_insumos) return [];
            // Sort by indice_rotacion descending and take top 3
            return [...this.biData.inventario.rotacion_insumos]
                .sort((a, b) => b.indice_rotacion - a.indice_rotacion)
                .slice(0, 3);
        }
    },
    methods: {
        formatoDinero(valor) {
            if (!valor) return '0';
            return Math.round(valor).toLocaleString('es-CL');
        }
    }
}
</script>

<style scoped>
.bi-dashboard .card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.bi-dashboard .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.1) !important;
}
</style>
