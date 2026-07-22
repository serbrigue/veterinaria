<template>
    <Head title="Panel de Inteligencia de Negocios" />
    <AuthenticatedLayout>
        <div class="container py-4">
            
            <div>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 fw-bold text-dark mb-0">Dashboard Operativo</h1>
                    <div class="d-flex align-items-center gap-2">
                        <Link :href="route('pagos.personal')" class="btn btn-primary rounded-pill shadow-sm px-3 py-2">
                            <i class="bi bi-wallet2 me-1"></i> Realizar Pagos
                        </Link>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill shadow-sm fs-6 border border-secondary">
                            <i class="bi bi-calendar-event me-1"></i> {{ fechaActual }}
                        </span>
                    </div>
                </div>

                <!-- KPI Cards -->
                <div class="row g-4 mb-5">
                    <!-- Ingresos Mensuales -->
                    <div class="col-12 col-sm-6 col-xl-3">
                        <Link :href="route('ingresos.listado')" class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden text-decoration-none hover-elevate transition-all cursor-pointer">
                            <div class="card-body p-4 position-relative">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <p class="text-muted fw-semibold small mb-1 text-uppercase tracking-wide">Ingresos Totales</p>
                                        <h3 class="fw-bold mb-0 text-dark">${{ formatoDinero(estadisticas.financiero?.total) }}</h3>
                                    </div>
                                    <div class="bg-success bg-opacity-10 p-3 rounded-circle d-flex align-items-center justify-content-center text-success" style="width: 50px; height: 50px;">
                                        <i class="bi bi-currency-dollar fs-4"></i>
                                    </div>
                                </div>
                                <div class="small">
                                    <span class="text-secondary">Ingresos del Mes:</span> <span class="fw-bold text-dark">${{ formatoDinero(estadisticas.financiero?.mes) }}</span>
                                </div>
                                <div class="position-absolute bottom-0 start-0 w-100 bg-success" style="height: 4px; opacity: 0.8;"></div>
                            </div>
                        </Link>
                    </div>

                    <!-- Total Citas -->
                    <div class="col-12 col-sm-6 col-xl-3">
                        <Link :href="route('citas.listado')" class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden text-decoration-none hover-elevate transition-all cursor-pointer">
                            <div class="card-body p-4 position-relative">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <p class="text-muted fw-semibold small mb-1 text-uppercase tracking-wide">Citas Globales</p>
                                        <h3 class="fw-bold mb-0 text-dark">{{ estadisticas.operativo?.citas_totales || 0 }}</h3>
                                    </div>
                                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle d-flex align-items-center justify-content-center text-primary" style="width: 50px; height: 50px;">
                                        <i class="bi bi-calendar-check fs-4"></i>
                                    </div>
                                </div>
                                <div class="small d-flex justify-content-between">
                                    <span class="text-success"><i class="bi bi-check-circle me-1"></i>{{ estadisticas.operativo?.citas_completadas }} Completas</span>
                                    <span class="text-danger"><i class="bi bi-x-circle me-1"></i>{{ estadisticas.operativo?.citas_canceladas }} Canceladas</span>
                                </div>
                                <div class="position-absolute bottom-0 start-0 w-100 bg-primary" style="height: 4px; opacity: 0.8;"></div>
                            </div>
                        </Link>
                    </div>

                    <!-- Inventario -->
                    <div class="col-12 col-sm-6 col-xl-3">
                        <Link :href="route('insumos.listado')" class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden text-decoration-none hover-elevate transition-all cursor-pointer">
                            <div class="card-body p-4 position-relative">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <p class="text-muted fw-semibold small mb-1 text-uppercase tracking-wide">Valor de Inventario</p>
                                        <h3 class="fw-bold mb-0 text-dark">${{ formatoDinero(estadisticas.inventario?.valor_total) }}</h3>
                                    </div>
                                    <div class="bg-warning bg-opacity-10 p-3 rounded-circle d-flex align-items-center justify-content-center text-warning" style="width: 50px; height: 50px;">
                                        <i class="bi bi-box-seam fs-4"></i>
                                    </div>
                                </div>
                                <div class="small">
                                    <span :class="{'text-danger fw-bold': estadisticas.inventario?.bajo_stock > 0, 'text-success': estadisticas.inventario?.bajo_stock === 0}">
                                        <i class="bi bi-exclamation-triangle me-1" v-if="estadisticas.inventario?.bajo_stock > 0"></i>
                                        <i class="bi bi-check-circle me-1" v-else></i>
                                        {{ estadisticas.inventario?.bajo_stock || 0 }} insumos con stock bajo
                                    </span>
                                </div>
                                <div class="position-absolute bottom-0 start-0 w-100 bg-warning" style="height: 4px; opacity: 0.8;"></div>
                            </div>
                        </Link>
                    </div>

                    <!-- Clientes & Mascotas -->
                    <div class="col-12 col-sm-6 col-xl-3">
                        <Link :href="route('mascotas.listado')" class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden text-decoration-none hover-elevate transition-all cursor-pointer">
                            <div class="card-body p-4 position-relative">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <p class="text-muted fw-semibold small mb-1 text-uppercase tracking-wide">Pacientes Activos</p>
                                        <h3 class="fw-bold mb-0 text-dark">{{ estadisticas.operativo?.mascotas || 0 }}</h3>
                                    </div>
                                    <div class="bg-info bg-opacity-10 p-3 rounded-circle d-flex align-items-center justify-content-center text-info" style="width: 50px; height: 50px;">
                                        <i class="bi bi-hearts fs-4"></i>
                                    </div>
                                </div>
                                <div class="small text-secondary">
                                    De <span class="fw-bold text-dark">{{ estadisticas.operativo?.clientes || 0 }}</span> clientes registrados
                                </div>
                                <div class="position-absolute bottom-0 start-0 w-100 bg-info" style="height: 4px; opacity: 0.8;"></div>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- Centro de Comando Analítico (Solo Administrador) -->
                <div v-if="$isAdmin()">
                    <!-- Fila 1: Ingresos por Sucursal y Estado de Citas -->
                    <div class="row g-4 mb-5">
                        <!-- Ingresos por Sucursal (Línea) -->
                        <div class="col-12 col-xl-8">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-header bg-white border-bottom-0 p-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h4 class="fw-bold mb-0 text-dark">Ingresos Mensuales por Sucursal</h4>
                                            <p class="text-muted small mb-0">Desglose de transacciones pagadas por sucursal (últimos 6 meses)</p>
                                        </div>
                                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                                            <i class="bi bi-graph-up me-1"></i> Finanzas
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body p-4">
                                    <div style="position: relative; height: 350px;">
                                        <canvas id="chartIngresosSucursales"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Estado de Citas (Dona / Circular) -->
                        <div class="col-12 col-xl-4">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-header bg-white border-bottom-0 p-4">
                                    <h4 class="fw-bold mb-0 text-dark">Estado de Citas</h4>
                                    <p class="text-muted small mb-0">Distribución global de citas agendadas</p>
                                </div>
                                <div class="card-body p-4 d-flex flex-column justify-content-between">
                                    <div style="position: relative; height: 230px;">
                                        <canvas id="chartEstadoCitas"></canvas>
                                    </div>
                                    <div class="d-flex justify-content-around mt-3 pt-3 border-top">
                                        <div class="text-center">
                                            <span class="d-block small text-muted">Completadas</span>
                                            <span class="fw-bold text-success">{{ porcentajeCitas('completadas') }}%</span>
                                        </div>
                                        <div class="text-center">
                                            <span class="d-block small text-muted">Pendientes</span>
                                            <span class="fw-bold text-warning">{{ porcentajeCitas('agendadas') }}%</span>
                                        </div>
                                        <div class="text-center">
                                            <span class="d-block small text-muted">Canceladas</span>
                                            <span class="fw-bold text-danger">{{ porcentajeCitas('canceladas') }}%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fila 2: Rendimiento y Comisiones por Veterinario -->
                    <div class="row g-4 mb-5">
                        <!-- Rendimiento por Veterinario (Citas Completadas) -->
                        <div class="col-12 col-lg-6">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-header bg-white border-bottom-0 p-4">
                                    <h4 class="fw-bold mb-0 text-dark">Rendimiento por Veterinario</h4>
                                    <p class="text-muted small mb-0">Cantidad de citas completadas por profesional</p>
                                </div>
                                <div class="card-body p-4">
                                    <div style="position: relative; height: 300px;">
                                        <canvas id="chartRendimientoVeterinarios"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Comisiones Acumuladas por Veterinario -->
                        <div class="col-12 col-lg-6">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-header bg-white border-bottom-0 p-4">
                                    <h4 class="fw-bold mb-0 text-dark">Comisiones Acumuladas</h4>
                                    <p class="text-muted small mb-0">Comisión total generada y acumulada por profesional</p>
                                </div>
                                <div class="card-body p-4">
                                    <div style="position: relative; height: 300px;">
                                        <canvas id="chartComisionesVeterinarios"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fila 3: Servicios e Insumos con Mayor Rotación -->
                    <div class="row g-4 mb-5">
                        <!-- Top 5 Servicios Más Solicitados -->
                        <div class="col-12 col-lg-6">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-header bg-white border-bottom-0 p-4">
                                    <h4 class="fw-bold mb-0 text-dark">Top 5 Servicios Más Solicitados</h4>
                                    <p class="text-muted small mb-0">Prestaciones médicas más demandadas</p>
                                </div>
                                <div class="card-body p-4">
                                    <div style="position: relative; height: 300px;">
                                        <canvas id="chartTopPrestaciones"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Top 5 Insumos con Mayor Rotación -->
                        <div class="col-12 col-lg-6">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-header bg-white border-bottom-0 p-4">
                                    <h4 class="fw-bold mb-0 text-dark">Top 5 Insumos con Mayor Rotación</h4>
                                    <p class="text-muted small mb-0">Artículos consumidos con mayor frecuencia</p>
                                </div>
                                <div class="card-body p-4">
                                    <div style="position: relative; height: 300px;">
                                        <canvas id="chartTopInsumos"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Panel Avanzado de BI KPIs -->
                    <BiKpiDashboard v-if="estadisticas.bi_kpis" :biData="estadisticas.bi_kpis" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from '@/Disenos/LayoutAutenticado.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Chart } from 'chart.js';
import BiKpiDashboard from '@/Paginas/App/Partials/BiKpiDashboard.vue';

export default {
    components: {
        AuthenticatedLayout,
        Head,
        Link,
        BiKpiDashboard
    },
    props: {
        estadisticas: {
            type: Object,
            default: () => ({}),
        },
        ultimasMascotas: {
            type: Array,
            default: () => [],
        },
    },
    computed: {
        fechaActual() {
            const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const str = new Date().toLocaleDateString('es-CL', opciones);
            return str.charAt(0).toUpperCase() + str.slice(1);
        }
    },
    mounted() {
        this.renderCharts();
    },
    beforeUnmount() {
        this.destroyCharts();
    },
    methods: {
        renderCharts() {
            this.destroyCharts();
            this.charts = [];

            if (!this.$isAdmin()) {
                return;
            }

            // 1. CHART INGRESOS MENSUALES POR SUCURSAL (Línea)
            const ctxIngresos = document.getElementById('chartIngresosSucursales')?.getContext('2d');
            if (ctxIngresos && this.estadisticas.ingresos_sucursales) {
                const meses = this.estadisticas.ingresos_sucursales.meses || [];
                const datosSucursales = this.estadisticas.ingresos_sucursales.datos_sucursales || [];
                
                const colors = [
                    { border: '#4f46e5', background: 'rgba(79, 70, 229, 0.05)' }, // Indigo
                    { border: '#06b6d4', background: 'rgba(6, 182, 212, 0.05)' }, // Cyan
                    { border: '#10b981', background: 'rgba(16, 185, 129, 0.05)' }, // Emerald
                    { border: '#f59e0b', background: 'rgba(245, 158, 11, 0.05)' }   // Amber
                ];

                const datasets = datosSucursales.map((ds, idx) => {
                    const color = colors[idx % colors.length];
                    return {
                        label: ds.sucursal,
                        data: ds.data,
                        borderColor: color.border,
                        backgroundColor: color.background,
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3,
                        pointBackgroundColor: color.border,
                        pointHoverRadius: 7
                    };
                });

                this.charts.push(new Chart(ctxIngresos, {
                    type: 'line',
                    data: { labels: meses, datasets },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top', labels: { boxWidth: 12, usePointStyle: true } },
                            tooltip: {
                                callbacks: {
                                    label: (context) => ` ${context.dataset.label}: $${this.formatoDinero(context.raw)}`
                                }
                            }
                        },
                        scales: {
                            y: {
                                ticks: {
                                    callback: (value) => `$${this.formatoDinero(value)}`
                                }
                            }
                        }
                    }
                }));
            }

            // 2. CHART ESTADO CITAS (Dona)
            const ctxEstado = document.getElementById('chartEstadoCitas')?.getContext('2d');
            if (ctxEstado && this.estadisticas.operativo) {
                const completadas = this.estadisticas.operativo.citas_completadas || 0;
                const pendientes = this.estadisticas.operativo.citas_agendadas || 0;
                const canceladas = this.estadisticas.operativo.citas_canceladas || 0;

                this.charts.push(new Chart(ctxEstado, {
                    type: 'doughnut',
                    data: {
                        labels: ['Completadas', 'Pendientes', 'Canceladas'],
                        datasets: [{
                            data: [completadas, pendientes, canceladas],
                            backgroundColor: [
                                'rgba(16, 185, 129, 0.85)',
                                'rgba(245, 158, 11, 0.85)',
                                'rgba(239, 68, 68, 0.85)'
                            ],
                            borderColor: [
                                '#10b981',
                                '#f59e0b',
                                '#ef4444'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        cutout: '70%'
                    }
                }));
            }

            // 3. CHART RENDIMIENTO VETERINARIOS (Barras)
            const ctxRendimiento = document.getElementById('chartRendimientoVeterinarios')?.getContext('2d');
            if (ctxRendimiento && this.estadisticas.veterinarios_estadisticas) {
                const nombres = this.estadisticas.veterinarios_estadisticas.map(v => v.nombre);
                const citas = this.estadisticas.veterinarios_estadisticas.map(v => v.citas_completadas);

                this.charts.push(new Chart(ctxRendimiento, {
                    type: 'bar',
                    data: {
                        labels: nombres,
                        datasets: [{
                            label: 'Citas Completadas',
                            data: citas,
                            backgroundColor: 'rgba(16, 185, 129, 0.85)',
                            borderColor: '#10b981',
                            borderWidth: 1,
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: { beginAtZero: true, ticks: { stepSize: 1 } }
                        }
                    }
                }));
            }

            // 4. CHART COMISIONES VETERINARIOS (Barras)
            const ctxComisiones = document.getElementById('chartComisionesVeterinarios')?.getContext('2d');
            if (ctxComisiones && this.estadisticas.veterinarios_estadisticas) {
                const nombres = this.estadisticas.veterinarios_estadisticas.map(v => v.nombre);
                const comisiones = this.estadisticas.veterinarios_estadisticas.map(v => v.comisiones_acumuladas);

                this.charts.push(new Chart(ctxComisiones, {
                    type: 'bar',
                    data: {
                        labels: nombres,
                        datasets: [{
                            label: 'Comisiones ($)',
                            data: comisiones,
                            backgroundColor: 'rgba(99, 102, 241, 0.85)',
                            borderColor: '#6366f1',
                            borderWidth: 1,
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: (context) => ` Comisiones: $${this.formatoDinero(context.raw)}`
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: (value) => `$${this.formatoDinero(value)}`
                                }
                            }
                        }
                    }
                }));
            }

            // 5. CHART TOP PRESTACIONES (Barras Horizontales)
            const ctxPrestaciones = document.getElementById('chartTopPrestaciones')?.getContext('2d');
            if (ctxPrestaciones && this.estadisticas.top_prestaciones) {
                const nombres = this.estadisticas.top_prestaciones.map(p => p.nombre);
                const cantidades = this.estadisticas.top_prestaciones.map(p => p.cantidad);

                this.charts.push(new Chart(ctxPrestaciones, {
                    type: 'bar',
                    data: {
                        labels: nombres,
                        datasets: [{
                            label: 'Solicitudes',
                            data: cantidades,
                            backgroundColor: 'rgba(6, 182, 212, 0.85)',
                            borderColor: '#06b6d4',
                            borderWidth: 1,
                            borderRadius: 6
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
                    }
                }));
            }

            // 6. CHART TOP INSUMOS (Barras Horizontales)
            const ctxInsumos = document.getElementById('chartTopInsumos')?.getContext('2d');
            if (ctxInsumos && this.estadisticas.top_insumos) {
                const nombres = this.estadisticas.top_insumos.map(i => i.nombre);
                const cantidades = this.estadisticas.top_insumos.map(i => i.cantidad);

                this.charts.push(new Chart(ctxInsumos, {
                    type: 'bar',
                    data: {
                        labels: nombres,
                        datasets: [{
                            label: 'Unidades Rotadas',
                            data: cantidades,
                            backgroundColor: 'rgba(245, 158, 11, 0.85)',
                            borderColor: '#f59e0b',
                            borderWidth: 1,
                            borderRadius: 6
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
                    }
                }));
            }
        },
        destroyCharts() {
            if (this.charts && this.charts.length > 0) {
                this.charts.forEach(chart => {
                    if (chart && typeof chart.destroy === 'function') {
                        chart.destroy();
                    }
                });
            }
            this.charts = [];
        },
        formatoDinero(valor) {
            if (!valor) return '0';
            return Math.round(valor).toLocaleString('es-CL');
        },
        formatearDia(fechaStr) {
            if(!fechaStr) return '';
            const f = new Date(fechaStr);
            return f.getDate().toString().padStart(2, '0');
        },
        formatearMes(fechaStr) {
            if(!fechaStr) return '';
            const f = new Date(fechaStr);
            return f.toLocaleDateString('es-CL', { month: 'short' });
        },
        formatearHora(fechaStr) {
            if(!fechaStr) return '';
            const f = new Date(fechaStr);
            return f.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' });
        },
        porcentajeCitas(tipo) {
            const total = this.estadisticas.operativo?.citas_totales || 0;
            if (total === 0) return 0;
            const cantidad = this.estadisticas.operativo?.[`citas_${tipo}`] || 0;
            return Math.round((cantidad / total) * 100);
        }
    }
}
</script>

<style scoped>
.hover-elevate:hover {
    transform: translateY(-5px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
}
.transition-all {
    transition: all 0.3s ease;
}
.tracking-wide {
    letter-spacing: 0.05em;
}
.hover-bg-light:hover {
    background-color: #f8f9fa;
}
.last-mb-0:last-child {
    margin-bottom: 0 !important;
}
</style>
