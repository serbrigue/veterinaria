<template>
    <!-- ================================================================================== -->
    <!-- COMPONENTE: MisPagos -->
    <!-- ================================================================================== -->
    <Head title="Mis Pagos y Honorarios" />

    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="h4 font-weight-bold text-dark mb-0">
                    <i class="bi bi-wallet2 me-2 text-primary"></i> Mis Honorarios y Pagos
                </h2>
                <Link :href="route('perfil.editar')" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm d-print-none">
                    <i class="bi bi-arrow-left me-1"></i> Volver a mi Perfil
                </Link>
            </div>
        </template>

        <div class="container py-4">
            
            <div class="row g-4">
                <!-- COLUMNA RESUMEN MES EN CURSO -->
                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 text-white overflow-hidden position-relative mb-4" :class="estado === 'Pagado' ? 'bg-success' : 'bg-primary'">
                        <div class="position-absolute top-0 end-0 p-4 opacity-25">
                            <i class="bi bi-cash-stack display-1"></i>
                        </div>
                        <div class="card-body p-4 position-relative z-1">
                            <p class="mb-1 fw-medium text-white-50 text-uppercase tracking-wide">Total del Mes ({{ nombreMesActual }} {{ anio_inicial }})</p>
                            <h2 class="display-4 fw-bold mb-0">${{ formatoDinero(total) }}</h2>
                            <div class="mt-3">
                                <span class="badge bg-white text-dark rounded-pill px-3 py-2 shadow-sm">
                                    <i class="me-1" :class="estado === 'Pagado' ? 'bi-check-circle-fill text-success' : 'bi-clock-fill text-warning'"></i>
                                    Estado: {{ estado }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- TARJETA HISTORIAL -->
                    <div class="card border-0 shadow-sm rounded-4 bg-white">
                        <div class="card-header bg-white border-bottom p-4">
                            <h5 class="fw-bold mb-0"><i class="bi bi-clock-history text-secondary me-2"></i>Historial de Pagos</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush rounded-bottom-4">
                                <!-- DIRECTIVA (v-if): Renderizado condicional basado en "historial.length === 0" -->
                                <div v-if="historial.length === 0" class="p-4 text-center text-muted small">
                                    No tienes pagos anteriores registrados.
                                </div>
                                <!-- DIRECTIVA (v-for): Renderizado iterativo de lista -->
                                <!-- EVENTO (@click): Dispara la acción "verMes(pago.mes, pago.anio)" -->
                                <button v-for="pago in historial" :key="pago.id" 
                                     @click="verMes(pago.mes, pago.anio)"
                                     class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-4 text-start transition-all"
                                     :class="{'bg-light border-primary border-start border-4': pago.mes == mes_inicial && pago.anio == anio_inicial}">
                                    <div>
                                        <h6 class="mb-1 fw-bold" :class="{'text-primary': pago.mes == mes_inicial && pago.anio == anio_inicial}">{{ meses[pago.mes] }} {{ pago.anio }}</h6>
                                        <small class="text-success"><i class="bi bi-check-circle-fill me-1"></i> Pagado</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="fs-5 fw-bold text-dark">${{ formatoDinero(pago.monto_total) }}</span>
                                        <i class="bi bi-caret-right-fill text-muted ms-3 opacity-50"></i>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- COLUMNA TABLA DETALLES DEL MES -->
                <div class="col-12 col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
                        <div class="card-header bg-white border-bottom p-4 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                            <h5 class="fw-bold mb-0">Detalle de Servicios</h5>
                            
                            <div class="d-flex flex-wrap align-items-center gap-2 d-print-none">
                                <!-- DIRECTIVA (v-model): Enlace de datos bidireccional con "filtroMes" -->
                                <select v-model="filtroMes" class="form-select form-select-sm rounded-pill shadow-sm" style="width: auto;">
                                    <!-- DIRECTIVA (v-for): Renderizado iterativo de lista -->
                                    <option v-for="(nombre, num) in meses" :key="num" :value="num">{{ nombre }}</option>
                                </select>
                                <!-- DIRECTIVA (v-model): Enlace de datos bidireccional con "filtroAnio" -->
                                <select v-model="filtroAnio" class="form-select form-select-sm rounded-pill shadow-sm" style="width: auto;">
                                    <!-- DIRECTIVA (v-for): Renderizado iterativo de lista -->
                                    <option v-for="anio in aniosDisponibles" :key="anio" :value="anio">{{ anio }}</option>
                                </select>
                                <!-- EVENTO (@click): Dispara la acción "verMes(filtroMes, filtroAnio)" -->
                                <button @click="verMes(filtroMes, filtroAnio)" class="btn btn-sm btn-primary rounded-pill shadow-sm px-3 fw-medium">
                                    <i class="bi bi-search me-1"></i> Filtrar
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-uppercase text-muted small fw-bold py-3 ps-4">Fecha</th>
                                        <th class="text-uppercase text-muted small fw-bold py-3">Paciente</th>
                                        <th class="text-uppercase text-muted small fw-bold py-3">Servicio</th>
                                        <th class="text-uppercase text-muted small fw-bold py-3 text-end">Comisión</th>
                                        <th class="text-uppercase text-muted small fw-bold py-3 text-end pe-4">Ganancia</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- DIRECTIVA (v-if): Renderizado condicional basado en "desglose.length === 0" -->
                                    <tr v-if="desglose.length === 0">
                                        <td colspan="5" class="text-center py-5">
                                            <div class="text-muted mb-0">
                                                <i class="bi bi-emoji-frown fs-3 d-block mb-2 text-black-50"></i>
                                                No has registrado servicios completados y pagados este mes.
                                            </div>
                                        </td>
                                    </tr>
                                    <!-- DIRECTIVA (v-for): Renderizado iterativo de lista -->
                                    <tr v-for="item in desglose" :key="item.id">
                                        <td class="ps-4 text-muted small">{{ formatearFecha(item.fecha) }}</td>
                                        <td>
                                            <span class="fw-bold d-block">{{ item.mascota }}</span>
                                            <span class="text-muted small">{{ item.cliente }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ item.servicio }}</span>
                                        </td>
                                        <td class="text-end text-muted small">
                                            {{ item.comision_porcentaje }}%
                                        </td>
                                        <td class="text-end pe-4 fw-semibold text-success">
                                            ${{ formatoDinero(item.ganancia_personal) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
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
import { Head, Link, router } from '@inertiajs/vue3';

// ------------------------------------------------------------------------------
// EXPORT DEFAULT: Definición principal del componente
// ------------------------------------------------------------------------------
export default {
    // COMPONENTES (COMPONENTS): Registro de componentes importados
    components: {
        AuthenticatedLayout,
        Head,
        Link
    },
    // PROPIEDADES (PROPS): Datos inyectados desde el componente padre o estado
    props: {
        historial: Array,
        desglose_inicial: Array,
        total_inicial: [Number, String],
        estado_inicial: String,
        mes_inicial: [Number, String],
        anio_inicial: [Number, String]
    },
    // ESTADO REACTIVO (DATA): Variables locales del componente
    data() {
        return {
            filtroMes: this.mes_inicial,
            filtroAnio: this.anio_inicial,
            meses: {
                1: 'Enero', 2: 'Febrero', 3: 'Marzo', 4: 'Abril', 5: 'Mayo', 6: 'Junio',
                7: 'Julio', 8: 'Agosto', 9: 'Septiembre', 10: 'Octubre', 11: 'Noviembre', 12: 'Diciembre'
            }
        };
    },
    // OBSERVADORES (WATCH): Reaccionan a cambios en propiedades o variables
    watch: {
        mes_inicial(newVal) {
            this.filtroMes = newVal;
        },
        anio_inicial(newVal) {
            this.filtroAnio = newVal;
        }
    },
    // PROPIEDADES COMPUTADAS (COMPUTED): Variables reactivas que dependen de otras
    computed: {
        desglose() {
            return this.desglose_inicial;
        },
        total() {
            return this.total_inicial;
        },
        estado() {
            return this.estado_inicial;
        },
        nombreMesActual() {
            return this.meses[this.mes_inicial] || '';
        },
        aniosDisponibles() {
            const anios = new Set();
            this.historial.forEach(p => anios.add(p.anio));
            const anioActual = new Date().getFullYear();
            anios.add(anioActual);
            // Agregamos también el anio_inicial por si estamos en uno distinto
            anios.add(parseInt(this.anio_inicial));
            return Array.from(anios).sort((a,b) => b - a);
        }
    },
    // MÉTODOS (METHODS): Bloque de funciones y eventos
    methods: {
        formatoDinero(valor) {
            if (!valor) return '0';
            return Math.round(valor).toLocaleString('es-CL');
        },
        formatearFecha(fechaStr) {
            if (!fechaStr) return 'N/A';
            const f = new Date(fechaStr);
            return f.toLocaleDateString('es-CL', { day: '2-digit', month: 'short' }) + ' ' + f.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' });
        },
        verMes(mes, anio) {
            router.get(route('pagos.mis-pagos'), { mes: mes, anio: anio }, { preserveState: true, preserveScroll: true });
        },
    }
}
</script>

<style scoped>
.transition-all {
    transition: all 0.2s ease-in-out;
}
.list-group-item-action:hover {
    background-color: #f8f9fa;
    cursor: pointer;
    transform: translateX(5px);
}
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
    .container, .container * {
        visibility: visible;
    }
    .container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        padding: 0;
    }
    .d-print-none {
        display: none !important;
    }
    .bg-primary, .bg-success {
        color: black !important;
        background-color: transparent !important;
        border: 1px solid #ccc !important;
    }
    .text-white, .text-white-50 {
        color: black !important;
    }
}
</style>
