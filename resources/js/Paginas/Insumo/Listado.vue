<template>
    <!-- ================================================================================== -->
    <!-- COMPONENTE: Listado -->
    <!-- ================================================================================== -->


    <Head title="Insumos" />
    <AuthenticatedLayout>
        <div class="container py-4">
            <div class="card shadow-sm">
                <div class="card-header border-0 bg-white p-4 d-flex justify-content-between align-items-center flex-wrap gap-3 rounded-top-4 border-bottom border-light">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary bg-opacity-10 p-2 rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px;">
                            <img src="/images/icon_supplies.png" alt="Icono Insumos" class="w-100 h-100 object-fit-contain" style="transform: scale(1.15);">
                        </div>
                        <h1 class="h4 mb-0 fw-bold text-dark">Catálogo de Insumos</h1>
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <!-- Almacenamos el filtro de sucursales -->
                        <select v-model="filtroSucursal" class="form-select border-0 bg-light rounded-pill shadow-sm px-3" style="width: auto; min-width: 12rem">
                            <option value="">Todas las sucursales</option>
                            <!-- Iteramos sobre las sucursales para que el usuario pueda filtrar solo sucursales ya existentes -->
                            <option v-for="suc in sucursales" :key="suc.id" :value="suc.id">{{ suc.nombre }}</option>
                        </select>
                        <!-- Almacenamos el filtro de categorias -->
                        <select v-model="filtroCategoria" class="form-select border-0 bg-light rounded-pill shadow-sm px-3" style="width: auto; min-width: 12rem">
                            <option value="">Todas las categorías</option>
                            <!-- Iteramos sobre las categorias para que el usuario pueda filtrar solo categorias ya existentes -->
                            <option v-for="cat in categoriasInsumo" :key="cat.id" :value="cat.id">{{ cat.nombre }}</option>
                        </select>
                        <!-- Almacenamos el filtro de estados -->
                        <select v-model="filtroEstado" class="form-select border-0 bg-light rounded-pill shadow-sm px-3" style="width: auto; min-width: 10rem">
                            <option value="">Todos los estados</option>
                            <option value="activo">Activos</option>
                            <option value="inactivo">Inactivos</option>
                        </select>
                        <!-- Solo si es admin o secretaria se muestra el boton de exportar e importar -->
                        <template v-if="$isAdmin() || $isSecretaria()">
                            <a href="/api/export/insumos" class="btn btn-light text-success fw-bold rounded-pill shadow-sm btn-hover-elevate">
                                <i class="bi bi-download me-1"></i> Exportar
                            </a>
                            <!-- Se muestra el boton de importar -->
                            <button type="button" class="btn btn-light text-primary fw-bold rounded-pill shadow-sm btn-hover-elevate" @click="mostrarModalImportar = true">
                                <i class="bi bi-upload me-1"></i> Importar
                            </button>
                        </template>
                        <!-- Solo si es admin se muestra el boton de crear -->
                        <button v-if="$isAdmin()" type="button" class="btn btn-primary fw-bold rounded-pill shadow-sm btn-hover-elevate px-4" @click="abrirModalCrear">
                            <i class="bi bi-plus-lg me-1"></i> Nuevo Insumo
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <p v-show="!listaVacia" class="text-muted small mb-3">
                        {{ totalInsumos }} insumo{{ totalInsumos === 1 ? '' : 's' }} registrado{{ totalInsumos === 1 ? '' : 's' }}
                    </p>
                    <IndicadorCarga :cargando="cargando" mensaje="insumos" />

                    <!-- Muestra un mensaje de estado vacio si no hay carga y la lista está vacia -->
                    <EstadoVacio
                        :visible="!cargando && listaVacia"
                        mensaje="No hay insumos registrados en el catálogo."
                        texto-boton="Registrar el primer insumo"
                        icono="bi bi-box-seam"
                        @accion="abrirModalCrear"
                    />

                     <!-- Muestra un mensaje de sin resultados si no hay carga y no hay resultados de filtro -->
                    <SinResultados
                        :visible="!cargando && sinResultadosFiltro"
                        mensaje="Ningún insumo coincide con la búsqueda."
                        @limpiar="limpiarFiltros()"
                    />

                    <!-- si no hay carga, no está vacia y no hay resultados de filtro -->
                    <div v-if="!cargando && !listaVacia && !sinResultadosFiltro" class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Categoría</th>
                                    <th>Sucursal</th>
                                    <th>Precio Venta</th>
                                    <th>Stock Actual</th>
                                    <th>Stock Mínimo</th>
                                    <th>Estado</th>
                                    <th style="width: 180px">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Renderizado iterativo de los insumos -->
                                <tr v-for="insumo in insumosVisibles" :key="insumo.id" :class="{'table-warning': insumo.stock_actual <= insumo.stock_minimo, 'row-hover cursor-pointer transition-all': true}" @click="irADetalle(insumo.id)">
                                    <td class="fw-semibold">
                                        <Link :href="route('insumos.detalle', insumo.id)" class="text-decoration-none">{{ insumo.nombre }}</Link>
                                    </td>
                                    <td>
                                        <!-- Si el insumo tiene categoría, se muestra la categoría -->
                                        <span v-if="insumo.categoria_insumo" class="badge rounded-pill px-3" :class="badgeCategoria(insumo.categoria_insumo.nombre)">
                                            {{ insumo.categoria_insumo.nombre }}
                                        </span>
                                        <!-- Si el insumo no tiene categoría, se muestra "Sin categoría" -->
                                        <span v-else class="text-muted">Sin categoría</span>
                                    </td>
                                    <!-- Si el insumo tiene sucursal, se muestra la sucursal -->
                                    <td><span class="badge bg-info text-dark">{{ insumo.sucursal?.nombre || '—' }}</span></td>
                                    <!-- Si el insumo tiene precio de venta, se muestra el precio de venta -->
                                    <td>${{ Math.round(insumo.precio_venta).toLocaleString('es-CL') }}</td>
                                    <!-- Si el insumo tiene stock actual, se muestra el stock actual -->
                                    <td>
                                        <span class="badge" :class="insumo.stock_actual <= insumo.stock_minimo ? 'bg-danger' : 'bg-success'">
                                            {{ insumo.stock_actual }}
                                        </span>
                                    </td>
                                    <td>{{ insumo.stock_minimo }}</td>
                                    <!-- Si el insumo está activo, se muestra "ACTIVO", si está inactivo, se muestra "INACTIVO" -->
                                    <td>
                                        <span class="badge" :class="insumo.estado === 'activo' ? 'bg-primary' : 'bg-secondary'">
                                            {{ insumo.estado.toUpperCase() }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <!-- Solo si es admin se muestra el boton de editar -->
                                            <button v-if="$isAdmin()" type="button" class="btn btn-primary" @click.stop="abrirModalEditar(insumo)">
                                                Editar
                                            </button>
                                            <!-- Solo si es admin se muestra el boton de eliminar -->
                                            <button v-if="$isAdmin()" type="button" class="btn btn-danger" @click.stop="confirmarEliminar(insumo)">
                                                Eliminar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Modal para crear y editar insumos -->
            <ModalCrud
                :visible="mostrarModal"
                :titulo="tituloModal"
                :modo-edicion="modoEdicion"
                :processing="formulario.processing"
                texto-guardar="Guardar Cambios"
                texto-crear="Crear Insumo"
                @cerrar="cerrarModal"
                @guardar="guardar"
            >
                <div class="mb-3">
                    <label for="categoria_insumo_id" class="form-label fw-semibold text-secondary small text-uppercase">Categoría</label>
                    <!-- Almacena la categoria seleccionada -->
                    <select id="categoria_insumo_id" v-model="formulario.categoria_insumo_id" class="form-select bg-light border-0 py-2" :class="{ 'is-invalid': formulario.errors.categoria_insumo_id }">
                        <option :value="null">Sin categoría</option>
                        <!-- Renderizado iterativo de lista de categorias -->
                        <option v-for="cat in categoriasInsumo" :key="cat.id" :value="cat.id">{{ cat.nombre }}</option>
                    </select>
                    <!-- Muestra el error si hay un error en la categoria seleccionada -->
                    <div v-if="formulario.errors.categoria_insumo_id" class="invalid-feedback">{{ formulario.errors.categoria_insumo_id }}</div>
                </div>

                <div class="mb-3">
                    <label for="sucursal_id" class="form-label fw-semibold text-secondary small text-uppercase">Sucursal</label>
                    <!-- Almacena la sucursal seleccionada -->
                    <select id="sucursal_id" v-model="formulario.sucursal_id" class="form-select bg-light border-0 py-2" :class="{ 'is-invalid': formulario.errors.sucursal_id }" required>
                        <option :value="null" disabled>Seleccione una sucursal...</option>
                        <!-- Renderizado iterativo de lista de sucursales -->
                        <option v-for="suc in sucursales" :key="suc.id" :value="suc.id">
                            {{ suc.nombre }}
                        </option>
                    </select>
                    <!-- Muestra el error si hay un error en la sucursal seleccionada -->
                    <div v-if="formulario.errors.sucursal_id" class="invalid-feedback">{{ formulario.errors.sucursal_id }}</div>
                </div>
                
                <div class="mb-3">
                    <label for="nombre" class="form-label fw-semibold text-secondary small text-uppercase">Nombre del Insumo</label>
                    <!-- Almacena el nombre del insumo -->
                    <input id="nombre" v-model="formulario.nombre" type="text" class="form-control bg-light border-0 py-2" placeholder="Ej: Anestesia General" :class="{ 'is-invalid': formulario.errors.nombre }" required />
                    <!-- Muestra el error si hay un error en el nombre -->
                    <div v-if="formulario.errors.nombre" class="invalid-feedback">{{ formulario.errors.nombre }}</div>
                </div>
                
                <div class="mb-3">
                    <label for="descripcion" class="form-label fw-semibold text-secondary small text-uppercase">Descripción</label>
                    <!-- Almacena la descripción del insumo -->
                    <textarea id="descripcion" v-model="formulario.descripcion" class="form-control bg-light border-0 py-2" rows="2" placeholder="Opcional" :class="{ 'is-invalid': formulario.errors.descripcion }"></textarea>
                    <!-- Muestra el error si hay un error en la descripción -->
                    <div v-if="formulario.errors.descripcion" class="invalid-feedback">{{ formulario.errors.descripcion }}</div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="precio_venta" class="form-label fw-semibold text-secondary small text-uppercase">Precio de Venta ($)</label>
                        <!-- Almacena el precio de venta del insumo -->
                        <input id="precio_venta" v-model="formulario.precio_venta" type="number" min="0" class="form-control bg-light border-0 py-2" :class="{ 'is-invalid': formulario.errors.precio_venta }" required />
                        <!-- Muestra el error si hay un error en el precio de venta -->
                        <div v-if="formulario.errors.precio_venta" class="invalid-feedback">{{ formulario.errors.precio_venta }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="estado" class="form-label fw-semibold text-secondary small text-uppercase">Estado</label>
                        <!-- Almacena el estado del insumo -->
                        <select id="estado" v-model="formulario.estado" class="form-select bg-light border-0 py-2" :class="{ 'is-invalid': formulario.errors.estado }" required>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                        <!-- Muestra el error si hay un error en el estado -->
                        <div v-if="formulario.errors.estado" class="invalid-feedback">{{ formulario.errors.estado }}</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="stock_actual" class="form-label fw-semibold text-secondary small text-uppercase">Stock Actual</label>
                        <!-- Almacena el stock actual del insumo -->
                        <input id="stock_actual" v-model="formulario.stock_actual" type="number" min="0" class="form-control bg-light border-0 py-2" :class="{ 'is-invalid': formulario.errors.stock_actual }" required />
                        <!-- Muestra el error si hay un error en el stock actual -->
                        <div v-if="formulario.errors.stock_actual" class="invalid-feedback">{{ formulario.errors.stock_actual }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="stock_minimo" class="form-label fw-semibold text-secondary small text-uppercase">Stock Mínimo (Alerta)</label>
                        <!-- Almacena el stock mínimo del insumo -->
                        <input id="stock_minimo" v-model="formulario.stock_minimo" type="number" min="0" class="form-control bg-light border-0 py-2" :class="{ 'is-invalid': formulario.errors.stock_minimo }" required />
                        <!-- Muestra el error si hay un error en el stock mínimo -->
                        <div v-if="formulario.errors.stock_minimo" class="invalid-feedback">{{ formulario.errors.stock_minimo }}</div>
                    </div>
                </div>
            </ModalCrud>

            <!-- Modal para importar insumos -->
            <ModalImportarSimple
                :visible="mostrarModalImportar"
                entidad="insumos"
                etiqueta="Insumos"
                @cerrar="mostrarModalImportar = false"
                @importado="obtenerInsumos()"
            />
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
import IndicadorCarga from '@/Componentes/IndicadorCarga.vue';
import EstadoVacio from '@/Componentes/EstadoVacio.vue';
import SinResultados from '@/Componentes/SinResultados.vue';
import ModalCrud from '@/Componentes/ModalCrud.vue';
import ModalImportarSimple from '@/Componentes/ModalImportarSimple.vue';

// ------------------------------------------------------------------------------
// EXPORT DEFAULT: Definición principal del componente
// ------------------------------------------------------------------------------
export default {
    // Componentes importados
    components: {
        AuthenticatedLayout,
        Head,
        Link,
        IndicadorCarga,
        EstadoVacio,
        SinResultados,
        ModalCrud,
        ModalImportarSimple,
    },
    // Datos inyectados desde el componente padre o estado
    props: {
        insumos: { type: Array, default: () => [] },
        sucursales: { type: Array, default: () => [] },
        categoriasInsumo: { type: Array, default: () => [] },
    },
    // Variables locales del componente
    data() {
        return {
            //Inicializamos las variables
            cargando: false,
            mostrarModal: false,
            mostrarModalImportar: false,
            modoEdicion: false,
            insumoEditando: null,
            filtroEstado: '',
            filtroSucursal: '',
            filtroCategoria: '',
            listaInsumos: this.insumos,
            //Inicializamos el formulario
            formulario: {
                sucursal_id: null,
                categoria_insumo_id: null,
                nombre: '',
                descripcion: '',
                precio_venta: null,
                stock_actual: null,
                stock_minimo: null,
                estado: 'activo',
                errors: {},
                processing: false,
            },
        }
    },
    // PROPIEDADES COMPUTADAS: Variables reactivas que dependen de otras
    computed: {
        //Obtiene los insumos visibles
        insumosVisibles() {
            let visibles = this.listaInsumos;
            if (this.filtroEstado)    visibles = visibles.filter(i => i.estado === this.filtroEstado);
            if (this.filtroSucursal)  visibles = visibles.filter(i => i.sucursal_id === this.filtroSucursal);
            if (this.filtroCategoria) visibles = visibles.filter(i => i.categoria_insumo_id === this.filtroCategoria);
            return visibles;
        },
        //Calcula el total de insumos visibles
        totalInsumos() {
            return this.insumosVisibles.length;
        },
        //Verifica si la lista está vacia
        listaVacia() {
            return this.listaInsumos.length === 0;
        },
        //Verifica si no hay resultados de filtro
        sinResultadosFiltro() {
            return this.listaInsumos.length > 0 && this.insumosVisibles.length === 0;
        },
        //Obtiene el título del modal
        tituloModal() {
            return this.modoEdicion ? 'Editar Insumo' : 'Nuevo Insumo';
        },
        //Obtiene el texto del botón guardar
        textoBotonGuardar() {
            return this.modoEdicion ? 'Guardar cambios' : 'Crear insumo';
        },
    },
    // MÉTODOS: Bloque de funciones y eventos
    methods: {
        //Función para limpiar filtros
        limpiarFiltros() {
            this.filtroEstado = '';
            this.filtroSucursal = '';
            this.filtroCategoria = '';
        },
        //Función para abrir modal de creación limpio
        abrirModalCrear() {
            this.modoEdicion = false;
            this.insumoEditando = null;
            this.formulario.sucursal_id = null;
            this.formulario.categoria_insumo_id = null;
            this.formulario.nombre = '';
            this.formulario.descripcion = '';
            this.formulario.precio_venta = null;
            this.formulario.stock_actual = null;
            this.formulario.stock_minimo = null;
            this.formulario.estado = 'activo';
            this.formulario.errors = {};
            this.mostrarModal = true;
        },
        //Función para obtener el color de la categoría
        badgeCategoria(nombre) {
            const mapa = {
                'Medicamento': 'bg-info text-dark',
                'Material Quirúrgico': 'bg-danger',
            };
            return mapa[nombre] || 'bg-secondary';
        },
        //Función para abrir modal de edición con datos del insumo seleccionado
        abrirModalEditar(insumo) {
            this.modoEdicion = true;
            this.insumoEditando = insumo;
            this.formulario.sucursal_id = insumo.sucursal_id;
            this.formulario.categoria_insumo_id = insumo.categoria_insumo_id;
            this.formulario.nombre = insumo.nombre;
            this.formulario.descripcion = insumo.descripcion || '';
            this.formulario.precio_venta = Number(insumo.precio_venta);
            this.formulario.stock_actual = Number(insumo.stock_actual);
            this.formulario.stock_minimo = Number(insumo.stock_minimo);
            this.formulario.estado = insumo.estado;
            this.formulario.errors = {};
            this.mostrarModal = true;
        },
        //Función para cerrar modal
        cerrarModal() {
            this.mostrarModal = false;
            this.insumoEditando = null;
        },
        //Función para obtener los datos del formulario
        datosFormulario() {
            return {
                sucursal_id:       this.formulario.sucursal_id,
                categoria_insumo_id: this.formulario.categoria_insumo_id,
                nombre:            this.formulario.nombre,
                descripcion:       this.formulario.descripcion,
                precio_venta:      this.formulario.precio_venta,
                stock_actual:      this.formulario.stock_actual,
                stock_minimo:      this.formulario.stock_minimo,
                estado:            this.formulario.estado,
            };
        },
        //Función para guardar cambios
        guardar() {
            this.formulario.processing = true;
            this.formulario.errors = {};
            //Se diferencia si es edición o creación
            const request = this.modoEdicion
                ? axios.put(`/api/insumos/${this.insumoEditando.id}`, this.datosFormulario())
                : axios.post('/api/insumos', this.datosFormulario());
            request
                .then(() => {
                    //Se diferencia si es edición o creación para el mensaje de alerta
                    this.cerrarModal();
                    this.$alertaExito(this.modoEdicion ? 'Insumo actualizado' : 'Insumo creado', 'Los cambios se guardaron correctamente.');
                    this.obtenerInsumos();
                })
                .catch((error) => {
                    //Se diferencia si es error de validación
                    if (error.response?.status === 422) {
                        this.formulario.errors = error.response.data.errors;
                        this.$alertaValidacion(error.response.data.errors);
                    } else {
                        //Se muestra el mensaje de error
                        this.$alertaError('Error', 'No se pudo guardar el insumo.');
                    }
                })
                .finally(() => {
                    this.formulario.processing = false;
                });
        },
        //Función para obtener los insumos
        obtenerInsumos() {
            // Refrescar desde Inercia (router.reload) o Axios dependiendo de la implementación.
            // Aquí usamos Axios hacia la misma ruta suponiendo que devuelve JSON si explicitly requested.
            axios.get('/insumos', { headers: { 'Accept': 'application/json' } })
                .then((response) => {
                    this.listaInsumos = response.data.insumos || response.data;
                })
                .catch((error) => {
                    //Se muestra el mensaje de error
                    console.error("No se pudo recargar la lista:", error);
                });
        },
        //Función para confirmar eliminación
        confirmarEliminar(insumo) {
            //Se muestra un mensaje de confirmación
            this.$confirmar('¿Eliminar insumo?', `Se eliminará el insumo ${insumo.nombre}.`)
                .then((resultado) => {
                    //Si no se confirma la eliminación, se retorna
                    if (!resultado.isConfirmed) return;
                    axios.delete(`/api/insumos/${insumo.id}`)
                        .then(() => {
                            this.$alertaExito('Eliminado', `${insumo.nombre} fue eliminado.`);
                            this.obtenerInsumos();
                        })
                        .catch(() => this.$alertaError('Error', 'No se pudo eliminar el insumo.'));
                });
        },
        //Función para ir al detalle del insumo
        irADetalle(id) {
            router.get(route('insumos.detalle', id));
        }
    },
    // Se ejecuta al cargar el componente en el DOM
    mounted() {
        if (this.listaInsumos.length === 0) {
            this.obtenerInsumos();
        }
    },
}
</script>

<style scoped>
.row-hover:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.03) !important;
}
.cursor-pointer {
    cursor: pointer;
}
.transition-all {
    transition: all 0.2s ease-in-out;
}
.btn-hover-elevate {
    transition: all 0.2s;
}
.btn-hover-elevate:hover {
    transform: translateY(-2px);
}
</style>
