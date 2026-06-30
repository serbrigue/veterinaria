<template>
    <Head :title="`Liquidación - ${personal.nombre}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="h4 font-weight-bold text-dark mb-0">
                    <i class="bi bi-person-lines-fill me-2 text-primary"></i> Detalle de Liquidación
                </h2>
                <Link :href="route('pagos.personal')" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm d-print-none">
                    <i class="bi bi-arrow-left me-1"></i> Volver a Pagos
                </Link>
            </div>
        </template>

        <div class="container py-4">
            
            <!-- HEADER INFO -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-1">{{ personal.nombre }}</h4>
                        <p class="text-muted mb-0">
                            Rol: <span class="fw-semibold text-dark me-2">{{ personal.rol }}</span> | Periodo: <span class="fw-semibold text-dark">{{ nombreMesActual }} {{ anio_inicial }}</span>
                        </p>
                    </div>
                    <div>
                        <span class="badge rounded-pill px-4 py-2 fs-6 shadow-sm border" 
                              :class="estado === 'Pagado' ? 'bg-success bg-opacity-10 text-success border-success' : 'bg-warning bg-opacity-10 text-warning border-warning'">
                            <i class="me-1" :class="estado === 'Pagado' ? 'bi-check-circle-fill' : 'bi-clock-fill'"></i>
                            {{ estado }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- COLUMNA TABLA DETALLES -->
                <div class="col-12 col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
                        <div class="card-header bg-white border-bottom p-4">
                            <h5 class="fw-bold mb-0">Servicios Realizados</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-uppercase text-muted small fw-bold py-3 ps-4">Fecha</th>
                                        <th class="text-uppercase text-muted small fw-bold py-3">Paciente / Cliente</th>
                                        <th class="text-uppercase text-muted small fw-bold py-3">Servicio</th>
                                        <th class="text-uppercase text-muted small fw-bold py-3 text-end">Comisión</th>
                                        <th class="text-uppercase text-muted small fw-bold py-3 text-end pe-4">Ganancia</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="desglose.length === 0">
                                        <td colspan="5" class="text-center py-5">
                                            <p class="text-muted mb-0">No hay servicios que generen comisión este mes.</p>
                                        </td>
                                    </tr>
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
                                            {{ item.comision_porcentaje }}% (de ${{ formatoDinero(item.ingreso_clinica) }})
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

                <!-- COLUMNA PAGO -->
                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 bg-primary text-white overflow-hidden position-relative mb-4">
                        <div class="position-absolute top-0 end-0 p-4 opacity-25">
                            <i class="bi bi-wallet2 display-1"></i>
                        </div>
                        <div class="card-body p-4 position-relative z-1">
                            <p class="mb-1 fw-medium text-white-50 text-uppercase tracking-wide">Total a Pagar</p>
                            <h2 class="display-4 fw-bold mb-0">${{ formatoDinero(total) }}</h2>
                            <p class="mb-0 mt-3 text-white-50 small">
                                Total de ganancias por servicios prestados y pagados por el cliente.
                            </p>
                        </div>
                    </div>

                    <div class="d-grid gap-3 d-print-none">
                        <button 
                            v-if="estado === 'Pendiente'"
                            @click="procesarPago" 
                            class="btn btn-success btn-lg rounded-3 shadow-sm d-flex align-items-center justify-content-center"
                            :disabled="cargando || total <= 0"
                        >
                            <span v-if="cargando" class="spinner-border spinner-border-sm me-2" role="status"></span>
                            <i v-else class="bi bi-check2-circle fs-4 me-2"></i>
                            Confirmar y Realizar Pago
                        </button>

                        <button 
                            @click="imprimirComprobante" 
                            class="btn btn-outline-primary btn-lg rounded-3 shadow-sm d-flex align-items-center justify-content-center bg-white"
                        >
                            <i class="bi bi-printer fs-4 me-2"></i> Imprimir Comprobante
                        </button>
                    </div>

                    <div v-if="mensajeExito" class="alert alert-success border-0 shadow-sm rounded-3 mt-4 d-flex align-items-center d-print-none">
                        <i class="bi bi-check-circle-fill me-2 fs-5"></i> {{ mensajeExito }}
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from '@/Disenos/LayoutAutenticado.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';

export default {
    components: {
        AuthenticatedLayout,
        Head,
        Link
    },
    props: {
        personal: Object,
        desglose_inicial: Array,
        total_inicial: [Number, String],
        estado_inicial: String,
        mes_inicial: [Number, String],
        anio_inicial: [Number, String]
    },
    data() {
        return {
            desglose: this.desglose_inicial,
            total: this.total_inicial,
            estado: this.estado_inicial,
            cargando: false,
            mensajeExito: '',
            meses: {
                1: 'Enero', 2: 'Febrero', 3: 'Marzo', 4: 'Abril', 5: 'Mayo', 6: 'Junio',
                7: 'Julio', 8: 'Agosto', 9: 'Septiembre', 10: 'Octubre', 11: 'Noviembre', 12: 'Diciembre'
            }
        };
    },
    computed: {
        nombreMesActual() {
            return this.meses[this.mes_inicial] || '';
        }
    },
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
        async procesarPago() {
            if (!confirm(`¿Estás seguro de registrar el pago de $${this.formatoDinero(this.total)} a ${this.personal.nombre}?`)) {
                return;
            }

            this.cargando = true;
            this.mensajeExito = '';

            try {
                const response = await axios.post(route('pagos.personal.pagar', this.personal.id), {
                    mes: this.mes_inicial,
                    anio: this.anio_inicial,
                    monto_total: this.total
                });
                
                this.estado = 'Pagado';
                this.mensajeExito = response.data.mensaje || 'Pago registrado con éxito.';
            } catch (error) {
                console.error(error);
                alert(error.response?.data?.error || 'Ocurrió un error al procesar el pago.');
            } finally {
                this.cargando = false;
            }
        },
        imprimirComprobante() {
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
    .shadow-sm {
        box-shadow: none !important;
    }
}
</style>
