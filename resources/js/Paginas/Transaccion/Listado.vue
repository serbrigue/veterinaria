<template>
    <Head title="Desglose de Ingresos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="h4 font-weight-bold text-dark mb-0">
                    <i class="bi bi-wallet2 me-2 text-success"></i> Desglose de Ingresos
                </h2>
                <Link :href="route('panel')" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Volver al Panel
                </Link>
            </div>
        </template>

        <div class="container py-4">
            <!-- PANEL DE FILTROS -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-body p-4">
                    <h5 class="card-title fw-bold text-dark mb-3">Filtros de Búsqueda</h5>
                    
                    <div class="row g-3">
                        <div class="col-12 col-md-3">
                            <label class="form-label text-muted small fw-semibold text-uppercase tracking-wide">Mes</label>
                            <select 
                                class="form-select form-select-lg rounded-3 shadow-none border-light bg-light" 
                                v-model="filtros.mes"
                                @change="aplicarFiltros"
                            >
                                <option value="">Todos los Meses</option>
                                <option v-for="(nombre, num) in meses" :key="num" :value="num">
                                    {{ nombre }}
                                </option>
                            </select>
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label text-muted small fw-semibold text-uppercase tracking-wide">Año</label>
                            <select 
                                class="form-select form-select-lg rounded-3 shadow-none border-light bg-light" 
                                v-model="filtros.anio"
                                @change="aplicarFiltros"
                            >
                                <option value="">Todos</option>
                                <option v-for="a in anios" :key="a" :value="a">
                                    {{ a }}
                                </option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label text-muted small fw-semibold text-uppercase tracking-wide">Sucursal</label>
                            <select 
                                class="form-select form-select-lg rounded-3 shadow-none border-light bg-light" 
                                v-model="filtros.sucursal_id" 
                                @change="aplicarFiltros"
                            >
                                <option value="">Todas las Sucursales</option>
                                <option v-for="suc in sucursales" :key="suc.id" :value="suc.id">
                                    {{ suc.nombre }}
                                </option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3 d-flex align-items-end">
                            <button 
                                class="btn btn-primary btn-lg rounded-3 w-100 shadow-sm"
                                @click="aplicarFiltros"
                                :disabled="cargando"
                            >
                                <span v-if="cargando" class="spinner-border spinner-border-sm me-2" role="status"></span>
                                <i v-else class="bi bi-funnel me-2"></i> Filtrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RESUMEN MÉTRICA -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-success text-white overflow-hidden position-relative">
                <div class="position-absolute top-0 end-0 p-4 opacity-25">
                    <i class="bi bi-currency-dollar display-1"></i>
                </div>
                <div class="card-body p-4 position-relative z-1">
                    <p class="mb-1 fw-medium text-white-50 text-uppercase tracking-wide">Total Filtrado</p>
                    <h2 class="display-5 fw-bold mb-0">${{ totalFiltrado }}</h2>
                    <p class="mb-0 mt-2 text-white-50 small">Corresponde a la suma de todas las transacciones pagadas listadas abajo.</p>
                </div>
            </div>

            <!-- LISTADO DE TRANSACCIONES -->
            <div class="card border-0 shadow-sm rounded-4 bg-white">
                <div class="table-responsive">
                    <table class="table table-hover table-borderless align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-uppercase text-muted small fw-bold py-3 ps-4 rounded-start">Fecha de Pago</th>
                                <th class="text-uppercase text-muted small fw-bold py-3">Cliente / Mascota</th>
                                <th class="text-uppercase text-muted small fw-bold py-3">Sucursal</th>
                                <th class="text-uppercase text-muted small fw-bold py-3">Método</th>
                                <th class="text-uppercase text-muted small fw-bold py-3 text-end pe-4 rounded-end">Monto Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="cargando">
                                <td colspan="5" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="text-muted mt-2 mb-0">Cargando ingresos...</p>
                                </td>
                            </tr>
                            <tr v-else-if="transacciones.length === 0">
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-wallet2 display-4 d-block mb-3 opacity-50"></i>
                                        <p class="mb-0 fw-medium">No se encontraron ingresos pagados para los filtros seleccionados.</p>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="t in transacciones" :key="t.id" v-else class="border-bottom">
                                <td class="ps-4">
                                    <span class="fw-bold text-dark">{{ formatearFecha(t.fecha_pago) }}</span>
                                    <span class="d-block text-muted small"><i class="bi bi-clock me-1"></i> {{ formatearHora(t.fecha_pago) }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                                            <i class="bi bi-person-fill text-primary"></i>
                                        </div>
                                        <div>
                                            <span class="fw-bold d-block text-dark">{{ t.cliente?.usuario?.name || 'Cliente Desconocido' }}</span>
                                            <span class="text-muted small"><i class="bi bi-bug-fill me-1"></i> Paciente: {{ t.cita?.mascota?.nombre || 'N/A' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi bi-building me-1"></i> {{ t.cita?.box?.sucursal?.nombre || 'Sin sucursal' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge rounded-pill" :class="badgeMetodoPago(t.metodo_pago)">
                                        <i class="me-1" :class="iconoMetodoPago(t.metodo_pago)"></i>
                                        {{ formatearMetodo(t.metodo_pago) }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <span class="fw-bold fs-5 text-success">${{ formatoDinero(t.monto_pagado) }}</span>
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
        transacciones_iniciales: {
            type: Array,
            required: true
        },
        sucursales: {
            type: Array,
            required: true
        }
    },
    data() {
        return {
            transacciones: this.transacciones_iniciales,
            cargando: false,
            meses: {
                1: 'Enero', 2: 'Febrero', 3: 'Marzo', 4: 'Abril', 5: 'Mayo', 6: 'Junio',
                7: 'Julio', 8: 'Agosto', 9: 'Septiembre', 10: 'Octubre', 11: 'Noviembre', 12: 'Diciembre'
            },
            anios: [2024, 2025, 2026, 2027],
            filtros: {
                mes: '',
                anio: '',
                sucursal_id: ''
            }
        };
    },
    computed: {
        totalFiltrado() {
            const total = this.transacciones.reduce((sum, t) => sum + (parseFloat(t.monto_pagado) || 0), 0);
            return this.formatoDinero(total);
        }
    },
    methods: {
        async aplicarFiltros() {
            this.cargando = true;
            try {
                // Hacemos petición a la misma ruta pero pidiendo JSON
                const response = await axios.get(route('ingresos.listado'), {
                    params: this.filtros,
                    headers: { 'Accept': 'application/json' }
                });
                this.transacciones = response.data;
            } catch (error) {
                console.error('Error al filtrar ingresos:', error);
            } finally {
                this.cargando = false;
            }
        },
        formatearFecha(fechaStr) {
            if (!fechaStr) return 'N/A';
            const f = new Date(fechaStr);
            return f.toLocaleDateString('es-CL', { day: '2-digit', month: 'long', year: 'numeric' });
        },
        formatearHora(fechaStr) {
            if (!fechaStr) return '';
            const f = new Date(fechaStr);
            return f.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' });
        },
        formatoDinero(valor) {
            if (!valor) return '0';
            return Math.round(valor).toLocaleString('es-CL');
        },
        formatearMetodo(metodo) {
            if (!metodo) return 'No registrado';
            return metodo.charAt(0).toUpperCase() + metodo.slice(1);
        },
        badgeMetodoPago(metodo) {
            switch(metodo) {
                case 'tarjeta': return 'bg-info bg-opacity-10 text-info';
                case 'efectivo': return 'bg-success bg-opacity-10 text-success';
                case 'transferencia': return 'bg-primary bg-opacity-10 text-primary';
                default: return 'bg-secondary bg-opacity-10 text-secondary';
            }
        },
        iconoMetodoPago(metodo) {
            switch(metodo) {
                case 'tarjeta': return 'bi-credit-card';
                case 'efectivo': return 'bi-cash-coin';
                case 'transferencia': return 'bi-bank';
                default: return 'bi-wallet';
            }
        }
    },
    mounted() {
        // Inicializar el input month con el mes actual si se desea
        // const hoy = new Date();
        // this.filtros.mes_anio = `${hoy.getFullYear()}-${String(hoy.getMonth() + 1).padStart(2, '0')}`;
        // Para que se inicie viendo todos, lo dejamos vacío inicialmente.
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
</style>
