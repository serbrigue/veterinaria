<template>
    <Head title="Realizar Pagos" />

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
                    <h5 class="card-title fw-bold text-dark mb-3">Periodo y Filtros de Liquidación</h5>
                    
                    <div class="row g-3">
                        <div class="col-12 col-md-3">
                            <label class="form-label text-muted small fw-semibold text-uppercase tracking-wide">Rol Personal</label>
                            <select 
                                class="form-select form-select-lg rounded-3 shadow-none border-light bg-light" 
                                v-model="filtros.rol_id"
                                @change="aplicarFiltros"
                            >
                                <option v-for="r in roles" :key="r.id" :value="r.id">
                                    {{ r.nombre_legible }}
                                </option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label text-muted small fw-semibold text-uppercase tracking-wide">Mes</label>
                            <select 
                                class="form-select form-select-lg rounded-3 shadow-none border-light bg-light" 
                                v-model="filtros.mes"
                                @change="aplicarFiltros"
                            >
                                <option v-for="(nombre, num) in meses" :key="num" :value="parseInt(num)">
                                    {{ nombre }}
                                </option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label text-muted small fw-semibold text-uppercase tracking-wide">Año</label>
                            <select 
                                class="form-select form-select-lg rounded-3 shadow-none border-light bg-light" 
                                v-model="filtros.anio"
                                @change="aplicarFiltros"
                            >
                                <option v-for="a in anios" :key="a" :value="parseInt(a)">
                                    {{ a }}
                                </option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3 d-flex align-items-end">
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
                    <h2 class="display-5 fw-bold mb-0">${{ totalGeneralComisionesFormatted }}</h2>
                    <p class="mb-0 mt-2 text-white-50 small">Corresponde a la suma de todas las comisiones del rol seleccionado en {{ nombreMesActual }} {{ filtros.anio }}.</p>
                </div>
            </div>

            <!-- LISTADO DE LIQUIDACIONES -->
            <div class="card border-0 shadow-sm rounded-4 bg-white print-table-container">
                <div class="table-responsive">
                    <table class="table table-hover table-borderless align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-uppercase text-muted small fw-bold py-3 ps-4 rounded-start">Personal</th>
                                <th class="text-uppercase text-muted small fw-bold py-3 text-center">Estado</th>
                                <th class="text-uppercase text-muted small fw-bold py-3 text-end">Comisión Total</th>
                                <th class="text-uppercase text-muted small fw-bold py-3 text-center pe-4 rounded-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="cargando">
                                <td colspan="4" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="text-muted mt-2 mb-0">Calculando honorarios...</p>
                                </td>
                            </tr>
                            <tr v-else-if="liquidaciones.length === 0">
                                <td colspan="4" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-piggy-bank display-4 d-block mb-3 opacity-50"></i>
                                        <p class="mb-0 fw-medium">No hay personal registrado para el rol seleccionado.</p>
                                    </div>
                                </td>
                            </tr>
                            <tr v-else-if="liquidacionesFiltradas.length === 0">
                                <td colspan="4" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-piggy-bank display-4 d-block mb-3 opacity-50"></i>
                                        <p class="mb-0 fw-medium">No hay servicios completados ni comisiones generadas en este periodo.</p>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="vet in liquidacionesFiltradas" :key="vet.id" class="border-bottom cursor-pointer hover-bg-light transition-all" @click="irADetalle(vet)">
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3 text-primary">
                                            <i class="bi bi-person-badge-fill fs-5"></i>
                                        </div>
                                        <div>
                                            <span class="fw-bold d-block text-dark">{{ vet.nombre }}</span>
                                            <span class="text-muted small">{{ vet.rol }} (ID: {{ vet.id }})</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-pill px-3 py-2" :class="vet.estado === 'Pagado' ? 'bg-success bg-opacity-10 text-success' : 'bg-warning bg-opacity-10 text-warning'">
                                        <i class="me-1" :class="vet.estado === 'Pagado' ? 'bi-check-circle-fill' : 'bi-clock-fill'"></i>
                                        {{ vet.estado }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold fs-5 text-success">${{ formatoDinero(vet.total_comision) }}</span>
                                </td>
                                <td class="text-center pe-4">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm d-print-none" @click.stop="irADetalle(vet)">
                                            Ver Detalle
                                        </button>
                                        <button v-if="vet.estado === 'Pagado'" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm d-print-none" @click.stop="verComprobante(vet)">
                                            <i class="bi bi-receipt me-1"></i> Comprobante
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginación -->
                <div v-if="liquidacionesData && liquidacionesData.last_page > 1" class="d-flex justify-content-between align-items-center p-4 border-top">
                    <div class="text-muted small">
                        Mostrando {{ liquidacionesData.from }} a {{ liquidacionesData.to }} de {{ liquidacionesData.total }} registros
                    </div>
                    <nav aria-label="Navegación de páginas">
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item" :class="{ disabled: !liquidacionesData.prev_page_url }">
                                <button class="page-link" @click.prevent="aplicarFiltros(liquidacionesData.prev_page_url)">Anterior</button>
                            </li>
                            <li 
                                v-for="link in liquidacionesData.links.slice(1, -1)" 
                                :key="link.label" 
                                class="page-item" 
                                :class="{ active: link.active }"
                            >
                                <button class="page-link" @click.prevent="aplicarFiltros(link.url)" v-html="link.label"></button>
                            </li>
                            <li class="page-item" :class="{ disabled: !liquidacionesData.next_page_url }">
                                <button class="page-link" @click.prevent="aplicarFiltros(liquidacionesData.next_page_url)">Siguiente</button>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>

            <!-- MODAL COMPROBANTE DE LIQUIDACION -->
            <div v-if="mostrarModalComprobante && liquidacionSeleccionada" class="modal fade show d-block d-print-none" tabindex="-1" style="background: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow rounded-4">
                        <div class="modal-header bg-light border-bottom-0 rounded-top-4 p-4">
                            <h5 class="modal-title fw-bold text-dark"><i class="bi bi-receipt me-2 text-primary"></i> Comprobante de Honorarios</h5>
                            <button type="button" class="btn-close" @click="mostrarModalComprobante = false"></button>
                        </div>
                        <div class="modal-body p-4" id="comprobante-vet-imprimir">
                            <div class="text-center mb-4">
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                                <h4 class="mt-2 fw-bold text-success">Honorarios Pagados</h4>
                                <p class="text-muted mb-0">Comprobante LIQ-{{ filtros.anio }}{{ String(filtros.mes).padStart(2, '0') }}-{{ liquidacionSeleccionada.id.toString().padStart(4, '0') }}</p>
                            </div>
                            
                            <div class="card bg-light border-0 rounded-4">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted small">Periodo de Pago:</span>
                                        <span class="fw-medium text-dark">{{ nombreMesActual }} {{ filtros.anio }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted small">Personal:</span>
                                        <span class="fw-medium text-dark">{{ liquidacionSeleccionada.nombre }} ({{ liquidacionSeleccionada.rol }})</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted small">Estado:</span>
                                        <span class="fw-medium text-success"><i class="bi bi-check-circle-fill me-1"></i> Pagado</span>
                                    </div>
                                    <hr class="border-secondary opacity-25">
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <span class="text-uppercase fw-bold text-muted small">Total Comisiones</span>
                                        <span class="fs-4 fw-bold text-success">${{ formatoDinero(liquidacionSeleccionada.total_comision) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mt-4">
                                <small class="text-muted">Documento generado por el Sistema Veterinario.</small>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 p-4">
                            <button type="button" class="btn btn-secondary rounded-pill px-4" @click="mostrarModalComprobante = false">Cerrar</button>
                            <button type="button" class="btn btn-primary rounded-pill px-4" @click="imprimirComprobanteVet"><i class="bi bi-printer me-2"></i>Imprimir</button>
                        </div>
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
        liquidaciones_iniciales: {
            type: Object,
            required: true
        },
        total_general_inicial: {
            type: Number,
            required: true
        },
        roles: {
            type: Array,
            required: true
        },
        rol_id_inicial: {
            type: Number,
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
            liquidacionesData: this.liquidaciones_iniciales,
            liquidaciones: this.liquidaciones_iniciales.data || [],
            totalGeneralAPI: this.total_general_inicial,
            cargando: false,
            mostrarModalComprobante: false,
            liquidacionSeleccionada: null,
            meses: {
                1: 'Enero', 2: 'Febrero', 3: 'Marzo', 4: 'Abril', 5: 'Mayo', 6: 'Junio',
                7: 'Julio', 8: 'Agosto', 9: 'Septiembre', 10: 'Octubre', 11: 'Noviembre', 12: 'Diciembre'
            },
            anios: [2024, 2025, 2026, 2027],
            filtros: {
                mes: this.mes_inicial,
                anio: this.anio_inicial,
                rol_id: this.rol_id_inicial,
            }
        };
    },
    computed: {
        liquidacionesFiltradas() {
            // Mostramos registros que tengan comisión generada > 0 para evitar filas vacías irrelevantes
            return this.liquidaciones.filter(v => parseFloat(v.total_comision) > 0);
        },
        totalGeneralComisionesFormatted() {
            return this.formatoDinero(this.totalGeneralAPI);
        },
        nombreMesActual() {
            return this.meses[this.filtros.mes] || '';
        }
    },
    methods: {
        verComprobante(vet) {
            this.liquidacionSeleccionada = vet;
            this.mostrarModalComprobante = true;
        },
        imprimirComprobanteVet() {
            window.print();
        },
        async aplicarFiltros(url = null) {
            this.cargando = true;
            try {
                const fetchUrl = typeof url === 'string' ? url : route('pagos.personal');
                const response = await axios.get(fetchUrl, {
                    params: typeof url === 'string' ? {} : this.filtros,
                    headers: { 'Accept': 'application/json' }
                });
                
                if (response.data.liquidaciones) {
                    this.liquidacionesData = response.data.liquidaciones;
                    this.liquidaciones = response.data.liquidaciones.data;
                    this.totalGeneralAPI = response.data.totalGeneral;
                } else {
                    this.liquidacionesData = null;
                    this.liquidaciones = response.data;
                }
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
        },
        irADetalle(vet) {
            router.visit(route('pagos.personal.detalle', { usuario: vet.id, mes: this.filtros.mes, anio: this.filtros.anio }));
        }
    }
}
</script>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}
.hover-bg-light:hover {
    background-color: #f8f9fa !important;
}
.transition-all {
    transition: all 0.2s ease-in-out;
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
    #comprobante-vet-imprimir, #comprobante-vet-imprimir * {
        visibility: visible;
    }
    #comprobante-vet-imprimir {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        background-color: white;
    }
}
</style>

