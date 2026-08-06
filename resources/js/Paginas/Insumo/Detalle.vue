<template>
    <!-- ================================================================================== -->
    <!-- COMPONENTE: Detalle -->
    <!-- ================================================================================== -->
    <Head :title="'Insumo - ' + (insumo.nombre || 'Detalle')" />

    <AuthenticatedLayout>
        <div class="container py-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center gap-3">
                    <Link :href="route('insumos.listado')" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                        <i class="bi bi-arrow-left"></i> Volver
                    </Link>
                    <h1 class="h3 mb-0">Detalle del Insumo</h1>
                </div>
            </div>

            <div class="row g-4">
                <!-- Columna Izquierda: Detalles principales -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-4 bg-light p-3 rounded border">
                                <div>
                                    <span class="badge rounded-pill px-3 py-2 mt-1" :class="insumo.estado === 'activo' ? 'bg-primary' : 'bg-secondary'">
                                        {{ insumo.estado.toUpperCase() }}
                                    </span>
                                </div>
                            </div>

                            <h2 class="h4 fw-bold text-dark mb-3">{{ insumo.nombre }}</h2>

                            <!-- Categoría del insumo -->
                            <div class="mb-3">
                                <h3 class="h6 text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">Categoría</h3>
                                <!--Si el insumo tiene categoría, se muestra la categoría-->
                                <span v-if="insumo.categoria_insumo" class="badge rounded-pill px-3 py-2" :class="badgeCategoria(insumo.categoria_insumo.nombre)">
                                    <i class="bi bi-tag-fill me-1"></i>{{ insumo.categoria_insumo.nombre }}
                                </span>
                                <!--Si el insumo no tiene categoría, se muestra "Sin categoría"-->
                                <span v-else class="badge bg-secondary">Sin categoría</span>
                            </div>

                            <div class="mb-4">
                                <h3 class="h6 text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">Descripción</h3>
                                <p class="text-secondary bg-light p-3 rounded border-start border-primary border-3 mb-0" style="white-space: pre-wrap;">
                                    {{ insumo.descripcion || 'Sin descripción detallada.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Inventario y Finanzas -->
                <div class="col-lg-4">
                    <div class="d-flex flex-column gap-4 h-100">
                        <!-- Tarjeta de Inventario -->
                        <div class="card border-0 shadow-sm border-top border-warning border-4">
                            <div class="card-header bg-transparent border-0 pt-3 px-4 pb-0">
                                <h3 class="h6 mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                                    <i class="bi bi-box-seam-fill text-warning"></i> Inventario
                                </h3>
                            </div>
                            <div class="card-body p-4 pt-3">
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="text-muted fw-medium">Stock Actual:</span>
                                    <span class="fw-bold" :class="insumoLocal.stock_actual <= insumoLocal.stock_minimo ? 'text-danger' : 'text-success'">
                                        {{ insumoLocal.stock_actual }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="text-muted fw-medium">Stock Mínimo:</span>
                                    <span class="fw-bold text-dark">{{ insumoLocal.stock_minimo }}</span>
                                </div>
                                
                                <!--Si el stock actual es menor o igual al stock mínimo, se muestra una alerta de stock crítico-->
                                <div v-if="insumoLocal.stock_actual <= insumoLocal.stock_minimo" class="alert alert-danger mb-0 py-2 mt-3 text-center small">
                                    <i class="bi bi-exclamation-triangle-fill"></i> Nivel de stock crítico
                                </div>
                            </div>
                        </div>

                        <!-- Tarjeta de Finanzas -->
                        <div class="card border-0 shadow-sm border-top border-success border-4 flex-grow-1">
                            <div class="card-header bg-transparent border-0 pt-3 px-4 pb-0">
                                <h3 class="h6 mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                                    <i class="bi bi-currency-dollar text-success"></i> Finanzas
                                </h3>
                            </div>
                            <div class="card-body p-4 pt-3">
                                <div class="text-center mb-3">
                                    <h4 class="display-6 fw-bold text-success mb-0">${{ Math.round(insumo.precio_venta).toLocaleString('es-CL') }}</h4>
                                    <span class="text-muted small">Precio de Venta Unitario</span>
                                </div>
                                <div class="alert alert-info py-2 mb-0 small">
                                    <i class="bi bi-info-circle-fill"></i> Los insumos no generan comisión médica.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Historial de Movimientos (Kardex) -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
                            <h3 class="h5 mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                                <i class="bi bi-clock-history text-primary"></i> Historial de Movimientos (Kardex)
                            </h3>
                            
                            <div class="d-flex gap-2">
                                <!-- Botones para registrar nuevos movimientos -->
                                <!-- Si se hace clic en el boton, se abre el modal con la accion "ingreso" -->
                                <button @click="abrirModal('entrada')" class="btn btn-sm btn-success d-flex align-items-center gap-1">
                                    <i class="bi bi-plus-circle"></i> Ingreso
                                </button>
                                <!-- Si se hace clic en el boton, se abre el modal con la accion "merma" -->
                                <button @click="abrirModal('merma')" class="btn btn-sm btn-danger d-flex align-items-center gap-1">
                                    <i class="bi bi-dash-circle"></i> Merma
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <!-- Accordion para separar los movimientos -->
                            <div class="accordion accordion-flush" id="accordionKardex">
                                <!--Si hay movimientos, se muestra el accordion-->
                                <div v-for="(seccion, index) in seccionesKardex" :key="seccion.id" class="accordion-item border-bottom">
                                    <h2 class="accordion-header">
                                        <!--Si hay movimientos, se muestra el accordion-->
                                        <button class="accordion-button bg-light" :class="{ 'collapsed': index !== 0 }" type="button" data-bs-toggle="collapse" :data-bs-target="'#collapse' + seccion.id">
                                            <i :class="seccion.icono + ' me-2'"></i> <strong class="text-dark">{{ seccion.titulo }}</strong>
                                        </button>
                                    </h2>
                                    <!--Desglose-->
                                    <div :id="'collapse' + seccion.id" class="accordion-collapse collapse" :class="{ 'show': index === 0 }" data-bs-parent="#accordionKardex">
                                        <div class="accordion-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle mb-0">
                                                    <thead class="table-light text-secondary">
                                                        <tr>
                                                            <th class="ps-4">Fecha</th>
                                                            <th>Tipo</th>
                                                            <th>Cantidad</th>
                                                            <th>Motivo</th>
                                                            <th>Usuario</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <!-- Si no hay movimientos, se muestra un mensaje de "No hay movimientos" -->
                                                        <tr v-if="filtrarMovimientos(seccion.tipo).length === 0">
                                                            <td colspan="5" class="text-center py-4 text-muted">
                                                                No hay movimientos registrados en esta categoría.
                                                            </td>
                                                        </tr>
                                                        <!-- Si hay movimientos, se muestra el accordion iterando sobre cada movimiento-->
                                                        <tr v-for="mov in filtrarMovimientos(seccion.tipo)" :key="mov.id"
                                                            :class="{ 'cursor-pointer': mov.cita_id, 'table-active': mov.cita_id }"
                                                            :style="mov.cita_id ? 'cursor: pointer; transition: 0.2s;' : ''"
                                                            @click="irACita(mov.cita_id)"
                                                            :title="mov.cita_id ? 'Ir al detalle de la cita #' + mov.cita_id : ''"
                                                            @mouseover="$event.currentTarget.classList.add('bg-light')"
                                                            @mouseleave="$event.currentTarget.classList.remove('bg-light')">

                                                            <td class="ps-4">{{ formatearFecha(mov.created_at) }}</td>
                                                            <td>
                                                                <span class="badge" :class="badgeTipoMovimiento(mov.tipo)">
                                                                    {{ mov.tipo.toUpperCase() }}
                                                                </span>
                                                            </td>
                                                            <td class="fw-bold" :class="mov.tipo === 'entrada' ? 'text-success' : 'text-danger'">
                                                                {{ mov.tipo === 'entrada' ? '+' : '-' }}{{ mov.cantidad }}
                                                            </td>
                                                            <td>{{ mov.motivo }}</td>
                                                            <td>
                                                                <div class="d-flex align-items-center gap-2">
                                                                    <i class="bi bi-person-circle text-muted"></i>
                                                                    {{ mov.usuario ? mov.usuario.name : 'Sistema' }}
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Modal para Ingreso / Merma -->
            <div class="modal fade" id="modalMovimiento" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                {{ formMovimiento.tipo === 'entrada' ? 'Registrar Ingreso (Compra)' : 'Declarar Merma' }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Cantidad</label>
                                <!-- Almacenamos en esta variable la cantidad a importar-->
                                <input type="number" v-model="formMovimiento.cantidad" class="form-control" min="1" required>
                                <!-- Si hay error, lo mostramos -->
                                <div v-if="errores.cantidad" class="text-danger small mt-1">{{ errores.cantidad[0] }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Motivo (Obligatorio)</label>
                                <!-- Almacenamos en esta variable el motivo -->
                                <textarea v-model="formMovimiento.motivo" class="form-control" rows="3" required placeholder="Justifique este movimiento..."></textarea>
                                <!-- Si hay error, lo mostramos -->
                                <div v-if="errores.motivo" class="text-danger small mt-1">{{ errores.motivo[0] }}</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <!-- Si se hace clic en el boton, se cierra el modal-->
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <!-- Si se hace clic en el boton, se guarda el movimiento-->
                            <button type="button" class="btn btn-primary" @click="guardarMovimiento">
                                Guardar Registro
                            </button>
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
import axios from 'axios';
import * as bootstrap from 'bootstrap';

// ------------------------------------------------------------------------------
// EXPORT DEFAULT: Definición principal del componente
// ------------------------------------------------------------------------------
export default {
    name: 'InsumoDetalle',
    // COMPONENTES: Registro de componentes importados
    components: { AuthenticatedLayout, Head, Link },
    // PROPIEDADES: Datos inyectados desde el componente padre o estado
    props: { insumo: { type: Object, required: true } },
    // ESTADO REACTIVO: Variables locales del componente
    data() {
        return {
            //inicializamos variables
            movimientos: [],
            filtroTipo: 'todos',
            modalInstancia: null,
            //Formulario que guardara los datos del nuevo movimiento
            formMovimiento: {
                tipo: 'entrada',
                cantidad: 1,
                motivo: ''
            },
            errores: {},
            insumoLocal: this.insumo,
            //Secciones que se van a mostrar en el kardex
            seccionesKardex: [
                { id: 'Usos', titulo: '1.- Usos Clínicos', tipo: 'salida', icono: 'bi bi-bandaid text-primary' },
                { id: 'Ingresos', titulo: '2.- Ingresos', tipo: 'entrada', icono: 'bi bi-box-arrow-in-down text-success' },
                { id: 'Mermas', titulo: '3.- Mermas', tipo: 'merma', icono: 'bi bi-trash text-danger' },
            ]
        };
    },
    // CICLO DE VIDA: Se ejecuta al cargar el componente en el DOM
    mounted() {
        this.cargarHistorial();
    },
    // MÉTODOS: Bloque de funciones y eventos
    methods: {
        //Nos redirige a la cita correspondiente
        irACita(citaId) {
            if (citaId) {
                router.visit(route('citas.detalle', citaId));
            }
        },
        //Filtra los movimientos por tipo
        filtrarMovimientos(tipo) {
            return this.movimientos.filter(m => m.tipo === tipo);
        },
        //Badges por categoria
        badgeCategoria(nombre) {
            const mapa = { 'Medicamento': 'bg-danger', 'Material Quirúrgico': 'bg-warning text-dark', 'Vacuna': 'bg-success', 'Consumible General': 'bg-secondary' };
            return mapa[nombre] || 'bg-info text-dark';
        },
        //Badges por tipo de movimiento
        badgeTipoMovimiento(tipo) {
            const mapa = { 'entrada': 'bg-success', 'salida': 'bg-primary', 'merma': 'bg-danger' };
            return mapa[tipo] || 'bg-secondary';
        },
        //Formateo de fechas
        formatearFecha(fechaString) {
            if (!fechaString) return '';
            const fecha = new Date(fechaString);
            return fecha.toLocaleDateString('es-CL', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute:'2-digit' });
        },
        //Carga el historial de movimientos de forma asincrona para evitar bloqueos en la interfaz
        async cargarHistorial() {
            try {
                const response = await axios.get(`/api/insumos/${this.insumoLocal.id}/movimientos`);
                this.movimientos = response.data;
            } catch (error) {
                console.error("Error al cargar historial:", error);
            }
        },
        //Abre el modal de movimiento y limpia los errores
        abrirModal(tipo) {
            this.formMovimiento = { tipo: tipo, cantidad: 1, motivo: '' };
            this.errores = {};
            if (!this.modalInstancia) {
                this.modalInstancia = new bootstrap.Modal(document.getElementById('modalMovimiento'));
            }
            this.modalInstancia.show();
        },
        //Guarda el movimiento y actualiza el stock de forma asincrona para evitar bloqueos en la interfaz
        async guardarMovimiento() {
            this.errores = {};
            //Guarda la url correspondiente segun el tipo de movimiento (compra o merma)
            const url = this.formMovimiento.tipo === 'entrada' ? '/api/insumos/movimientos/compra' : '/api/insumos/movimientos/merma';
            try {
                //Realiza la peticion POST a la url correspondiente
                const response = await axios.post(url, {
                    insumo_id: this.insumoLocal.id,
                    cantidad: this.formMovimiento.cantidad,
                    motivo: this.formMovimiento.motivo
                });
                //Actualiza el stock local y cierra el modal
                this.insumoLocal = response.data.insumo;
                this.cargarHistorial();
                this.modalInstancia.hide();
                
            } catch (error) {
                //Muestra errores de validacion
                if (error.response && error.response.status === 422) {
                    this.errores = error.response.data.errors || { general: [error.response.data.error] };
                    if(error.response.data.error) alert(error.response.data.error);
                } 
                //Muestra error general
                else {
                    console.error("Error guardando movimiento:", error);
                    alert("Error al guardar el movimiento.");
                }
            }
        }
    },
}
</script>
