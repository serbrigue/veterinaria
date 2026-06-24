<template>
    <Head title="Panel de Inteligencia de Negocios" />
    <AuthenticatedLayout>
        <div class="container py-4">
            
            <div>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 fw-bold text-dark mb-0">Dashboard Operativo</h1>
                    <div class="d-flex align-items-center gap-2">
                        <Link :href="route('pagos.veterinarios')" class="btn btn-primary rounded-pill shadow-sm px-3 py-2">
                            <i class="bi bi-wallet2 me-1"></i> Pago a Veterinarios
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

                <div class="row g-4">
                    <!-- Columna Izquierda -->
                    <div class="col-12 col-lg-7">
                        <!-- Próximas Citas -->
                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                                <h2 class="h5 fw-bold mb-0">Próximas Citas (Agenda)</h2>
                                <Link :href="route('citas.listado')" class="btn btn-sm btn-outline-primary rounded-pill px-3">Ver agenda completa</Link>
                            </div>
                            <div class="card-body p-0">
                                <div v-if="!estadisticas.proximas_citas || estadisticas.proximas_citas.length === 0" class="p-5 text-center text-muted">
                                    <i class="bi bi-calendar2-x display-4 d-block mb-3 opacity-50"></i>
                                    <p class="mb-0 fw-medium">No hay citas agendadas próximas.</p>
                                </div>
                                <div v-else class="list-group list-group-flush">
                                    <div v-for="cita in estadisticas.proximas_citas" :key="cita.id" class="list-group-item p-4 border-bottom-0 border-top transition-all hover-bg-light">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded-3 p-3 text-center me-4" style="min-width: 80px;">
                                                <span class="d-block fw-bold fs-5 text-primary">{{ formatearDia(cita.fecha_hora) }}</span>
                                                <span class="d-block small text-uppercase fw-semibold text-secondary">{{ formatearMes(cita.fecha_hora) }}</span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h4 class="h6 fw-bold mb-1">{{ cita.titulo || 'Cita Programada' }}</h4>
                                                <div class="text-muted small mb-2">
                                                    <i class="bi bi-clock me-1"></i> {{ formatearHora(cita.fecha_hora) }}
                                                    <span class="mx-2">|</span>
                                                    <i class="bi bi-person me-1"></i> {{ cita.veterinario?.usuario?.name || 'Dr. Asignado' }}
                                                </div>
                                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-1"><i class="bi bi-bug me-1"></i> {{ cita.mascota?.nombre }}</span>
                                            </div>
                                            <div>
                                                <Link :href="route('citas.detalle', cita.id)" class="btn btn-light btn-sm rounded-circle shadow-sm" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="bi bi-chevron-right"></i>
                                                </Link>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Columna Derecha -->
                    <div class="col-12 col-lg-5">
                        <!-- Top Prestaciones -->
                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-header bg-white border-bottom p-4">
                                <h2 class="h5 fw-bold mb-0">Servicios Más Solicitados</h2>
                            </div>
                            <div class="card-body p-4">
                                <div v-if="!estadisticas.top_prestaciones || estadisticas.top_prestaciones.length === 0" class="text-center text-muted py-4">
                                    <p class="mb-0">Aún no hay datos de servicios prestados.</p>
                                </div>
                                <div v-else>
                                    <div v-for="(prestacion, index) in estadisticas.top_prestaciones" :key="index" class="mb-3 last-mb-0">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-semibold text-dark">{{ prestacion.nombre }}</span>
                                            <span class="badge bg-light text-dark border">{{ prestacion.cantidad }} veces</span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-primary rounded-pill" role="progressbar" :style="'width: ' + ((prestacion.cantidad / (estadisticas.operativo.citas_totales || 1)) * 100) + '%'" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Distribución de Citas -->
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-header bg-white border-bottom p-4">
                                <h2 class="h5 fw-bold mb-0">Estado de Citas</h2>
                            </div>
                            <div class="card-body p-4">
                                <div class="d-flex flex-column gap-3">
                                    <div>
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="fw-semibold text-success"><i class="bi bi-check-circle-fill me-1"></i> Completadas</span>
                                            <span class="fw-bold">{{ porcentajeCitas('completadas') }}%</span>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-success rounded-pill" role="progressbar" :style="'width: ' + porcentajeCitas('completadas') + '%'"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="fw-semibold text-warning"><i class="bi bi-clock-fill me-1"></i> Pendientes</span>
                                            <span class="fw-bold">{{ porcentajeCitas('agendadas') }}%</span>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-warning rounded-pill" role="progressbar" :style="'width: ' + porcentajeCitas('agendadas') + '%'"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="fw-semibold text-danger"><i class="bi bi-x-circle-fill me-1"></i> Canceladas</span>
                                            <span class="fw-bold">{{ porcentajeCitas('canceladas') }}%</span>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-danger rounded-pill" role="progressbar" :style="'width: ' + porcentajeCitas('canceladas') + '%'"></div>
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
    components: {
        AuthenticatedLayout,
        Head,
        Link
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
            // Capitalizar la primera letra del dia
            const str = new Date().toLocaleDateString('es-CL', opciones);
            return str.charAt(0).toUpperCase() + str.slice(1);
        }
    },
    methods: {
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
