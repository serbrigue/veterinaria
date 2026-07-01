<template>
    <Head title="Insumos" />
    <AuthenticatedLayout>
        <div class="container py-4">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h1 class="h5 mb-0">Catálogo de Insumos</h1>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <select v-model="filtroSucursal" class="form-select form-select-sm" style="width: auto; min-width: 12rem">
                            <option value="">Todas las sucursales</option>
                            <option v-for="suc in sucursales" :key="suc.id" :value="suc.id">{{ suc.nombre }}</option>
                        </select>
                        <select v-model="filtroCategoria" class="form-select form-select-sm" style="width: auto; min-width: 12rem">
                            <option value="">Todas las categorías</option>
                            <option v-for="cat in categoriasInsumo" :key="cat.id" :value="cat.id">{{ cat.nombre }}</option>
                        </select>
                        <select v-model="filtroEstado" class="form-select form-select-sm" style="width: auto; min-width: 10rem">
                            <option value="">Todos los estados</option>
                            <option value="activo">Activos</option>
                            <option value="inactivo">Inactivos</option>
                        </select>
                        <button type="button" class="btn btn-sm btn-primary" @click="abrirModalCrear">+ Nuevo Insumo</button>
                    </div>
                </div>

                <div class="card-body">
                    <p v-show="!listaVacia" class="text-muted small mb-3">
                        {{ totalInsumos }} insumo{{ totalInsumos === 1 ? '' : 's' }} registrado{{ totalInsumos === 1 ? '' : 's' }}
                    </p>
                    <IndicadorCarga :cargando="cargando" mensaje="insumos" />

                    <EstadoVacio
                        :visible="!cargando && listaVacia"
                        mensaje="No hay insumos registrados en el catálogo."
                        texto-boton="Registrar el primer insumo"
                        icono="bi bi-box-seam"
                        @accion="abrirModalCrear"
                    />

                    <SinResultados
                        :visible="!cargando && sinResultadosFiltro"
                        mensaje="Ningún insumo coincide con la búsqueda."
                        @limpiar="limpiarFiltros()"
                    />

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
                                <tr v-for="insumo in insumosVisibles" :key="insumo.id" :class="{'table-warning': insumo.stock_actual <= insumo.stock_minimo}">
                                    <td class="fw-semibold">
                                        <Link :href="route('insumos.detalle', insumo.id)" class="text-decoration-none">{{ insumo.nombre }}</Link>
                                    </td>
                                    <td>
                                        <span v-if="insumo.categoria_insumo" class="badge rounded-pill px-3" :class="badgeCategoria(insumo.categoria_insumo.nombre)">
                                            {{ insumo.categoria_insumo.nombre }}
                                        </span>
                                        <span v-else class="text-muted">—</span>
                                    </td>
                                    <td><span class="badge bg-info text-dark">{{ insumo.sucursal?.nombre || '—' }}</span></td>
                                    <td>${{ Math.round(insumo.precio_venta).toLocaleString('es-CL') }}</td>
                                    <td>
                                        <span class="badge" :class="insumo.stock_actual <= insumo.stock_minimo ? 'bg-danger' : 'bg-success'">
                                            {{ insumo.stock_actual }}
                                        </span>
                                    </td>
                                    <td>{{ insumo.stock_minimo }}</td>
                                    <td>
                                        <span class="badge" :class="insumo.estado === 'activo' ? 'bg-primary' : 'bg-secondary'">
                                            {{ insumo.estado.toUpperCase() }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-primary" @click="abrirModalEditar(insumo)">
                                                Editar
                                            </button>
                                            <button type="button" class="btn btn-danger" @click="confirmarEliminar(insumo)">
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
                    <select id="categoria_insumo_id" v-model="formulario.categoria_insumo_id" class="form-select bg-light border-0 py-2" :class="{ 'is-invalid': formulario.errors.categoria_insumo_id }">
                        <option :value="null">Sin categoría</option>
                        <option v-for="cat in categoriasInsumo" :key="cat.id" :value="cat.id">{{ cat.nombre }}</option>
                    </select>
                    <div v-if="formulario.errors.categoria_insumo_id" class="invalid-feedback">{{ formulario.errors.categoria_insumo_id }}</div>
                </div>

                <div class="mb-3">
                    <label for="sucursal_id" class="form-label fw-semibold text-secondary small text-uppercase">Sucursal</label>
                    <select id="sucursal_id" v-model="formulario.sucursal_id" class="form-select bg-light border-0 py-2" :class="{ 'is-invalid': formulario.errors.sucursal_id }" required>
                        <option :value="null" disabled>Seleccione una sucursal...</option>
                        <option v-for="suc in sucursales" :key="suc.id" :value="suc.id">
                            {{ suc.nombre }}
                        </option>
                    </select>
                    <div v-if="formulario.errors.sucursal_id" class="invalid-feedback">{{ formulario.errors.sucursal_id }}</div>
                </div>
                
                <div class="mb-3">
                    <label for="nombre" class="form-label fw-semibold text-secondary small text-uppercase">Nombre del Insumo</label>
                    <input id="nombre" v-model="formulario.nombre" type="text" class="form-control bg-light border-0 py-2" placeholder="Ej: Anestesia General" :class="{ 'is-invalid': formulario.errors.nombre }" required />
                    <div v-if="formulario.errors.nombre" class="invalid-feedback">{{ formulario.errors.nombre }}</div>
                </div>
                
                <div class="mb-3">
                    <label for="descripcion" class="form-label fw-semibold text-secondary small text-uppercase">Descripción</label>
                    <textarea id="descripcion" v-model="formulario.descripcion" class="form-control bg-light border-0 py-2" rows="2" placeholder="Opcional" :class="{ 'is-invalid': formulario.errors.descripcion }"></textarea>
                    <div v-if="formulario.errors.descripcion" class="invalid-feedback">{{ formulario.errors.descripcion }}</div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="precio_venta" class="form-label fw-semibold text-secondary small text-uppercase">Precio de Venta ($)</label>
                        <input id="precio_venta" v-model="formulario.precio_venta" type="number" min="0" class="form-control bg-light border-0 py-2" :class="{ 'is-invalid': formulario.errors.precio_venta }" required />
                        <div v-if="formulario.errors.precio_venta" class="invalid-feedback">{{ formulario.errors.precio_venta }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="estado" class="form-label fw-semibold text-secondary small text-uppercase">Estado</label>
                        <select id="estado" v-model="formulario.estado" class="form-select bg-light border-0 py-2" :class="{ 'is-invalid': formulario.errors.estado }" required>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                        <div v-if="formulario.errors.estado" class="invalid-feedback">{{ formulario.errors.estado }}</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="stock_actual" class="form-label fw-semibold text-secondary small text-uppercase">Stock Actual</label>
                        <input id="stock_actual" v-model="formulario.stock_actual" type="number" min="0" class="form-control bg-light border-0 py-2" :class="{ 'is-invalid': formulario.errors.stock_actual }" required />
                        <div v-if="formulario.errors.stock_actual" class="invalid-feedback">{{ formulario.errors.stock_actual }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="stock_minimo" class="form-label fw-semibold text-secondary small text-uppercase">Stock Mínimo (Alerta)</label>
                        <input id="stock_minimo" v-model="formulario.stock_minimo" type="number" min="0" class="form-control bg-light border-0 py-2" :class="{ 'is-invalid': formulario.errors.stock_minimo }" required />
                        <div v-if="formulario.errors.stock_minimo" class="invalid-feedback">{{ formulario.errors.stock_minimo }}</div>
                    </div>
                </div>
            </ModalCrud>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from '@/Disenos/LayoutAutenticado.vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import IndicadorCarga from '@/Componentes/IndicadorCarga.vue';
import EstadoVacio from '@/Componentes/EstadoVacio.vue';
import SinResultados from '@/Componentes/SinResultados.vue';
import ModalCrud from '@/Componentes/ModalCrud.vue';

export default {
    components: {
        AuthenticatedLayout,
        Head,
        Link,
        IndicadorCarga,
        EstadoVacio,
        SinResultados,
        ModalCrud,
    },
    props: {
        insumos: { type: Array, default: () => [] },
        sucursales: { type: Array, default: () => [] },
        categoriasInsumo: { type: Array, default: () => [] },
    },
    data() {
        return {
            cargando: false,
            mostrarModal: false,
            modoEdicion: false,
            insumoEditando: null,
            filtroEstado: '',
            filtroSucursal: '',
            filtroCategoria: '',
            listaInsumos: this.insumos,
            formulario: {
                sucursal_id: null,
                categoria_insumo_id: null,
                nombre: '',
                descripcion: '',
                precio_venta: 0,
                stock_actual: 0,
                stock_minimo: 5,
                estado: 'activo',
                errors: {},
                processing: false,
            },
        }
    },
    computed: {
        insumosVisibles() {
            let visibles = this.listaInsumos;
            if (this.filtroEstado)    visibles = visibles.filter(i => i.estado === this.filtroEstado);
            if (this.filtroSucursal)  visibles = visibles.filter(i => i.sucursal_id === this.filtroSucursal);
            if (this.filtroCategoria) visibles = visibles.filter(i => i.categoria_insumo_id === this.filtroCategoria);
            return visibles;
        },
        totalInsumos() {
            return this.insumosVisibles.length;
        },
        listaVacia() {
            return this.listaInsumos.length === 0;
        },
        sinResultadosFiltro() {
            return this.listaInsumos.length > 0 && this.insumosVisibles.length === 0;
        },
        tituloModal() {
            return this.modoEdicion ? 'Editar Insumo' : 'Nuevo Insumo';
        },
        textoBotonGuardar() {
            return this.modoEdicion ? 'Guardar cambios' : 'Crear insumo';
        },
    },
    methods: {
        limpiarFiltros() {
            this.filtroEstado = '';
            this.filtroSucursal = '';
            this.filtroCategoria = '';
        },
        abrirModalCrear() {
            this.modoEdicion = false;
            this.insumoEditando = null;
            this.formulario.sucursal_id = null;
            this.formulario.categoria_insumo_id = null;
            this.formulario.nombre = '';
            this.formulario.descripcion = '';
            this.formulario.precio_venta = 0;
            this.formulario.stock_actual = 0;
            this.formulario.stock_minimo = 5;
            this.formulario.estado = 'activo';
            this.formulario.errors = {};
            this.mostrarModal = true;
        },

        badgeCategoria(nombre) {
            const mapa = {
                'Medicamento': 'bg-info text-dark',
                'Material Quirúrgico': 'bg-danger',
            };
            return mapa[nombre] || 'bg-secondary';
        },

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
        cerrarModal() {
            this.mostrarModal = false;
            this.insumoEditando = null;
        },
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
        guardar() {
            this.formulario.processing = true;
            this.formulario.errors = {};

            const request = this.modoEdicion
                ? axios.put(`/api/insumos/${this.insumoEditando.id}`, this.datosFormulario())
                : axios.post('/api/insumos', this.datosFormulario());

            request
                .then(() => {
                    this.cerrarModal();
                    this.$alertaExito(this.modoEdicion ? 'Insumo actualizado' : 'Insumo creado', 'Los cambios se guardaron correctamente.');
                    this.obtenerInsumos();
                })
                .catch((error) => {
                    if (error.response?.status === 422) {
                        this.formulario.errors = error.response.data.errors;
                        this.$alertaValidacion(error.response.data.errors);
                    } else {
                        this.$alertaError('Error', 'No se pudo guardar el insumo.');
                    }
                })
                .finally(() => {
                    this.formulario.processing = false;
                });
        },
        obtenerInsumos() {
            // Refrescar desde Inercia (router.reload) o Axios dependiendo de la implementación.
            // Aquí usamos Axios hacia la misma ruta suponiendo que devuelve JSON si explicitly requested.
            axios.get('/insumos', { headers: { 'Accept': 'application/json' } })
                .then((response) => {
                    this.listaInsumos = response.data.insumos || response.data;
                })
                .catch((error) => {
                    console.error("No se pudo recargar la lista:", error);
                });
        },
        confirmarEliminar(insumo) {
            this.$confirmar('¿Eliminar insumo?', `Se eliminará el insumo ${insumo.nombre}.`)
                .then((resultado) => {
                    if (!resultado.isConfirmed) return;
                    axios.delete(`/api/insumos/${insumo.id}`)
                        .then(() => {
                            this.$alertaExito('Eliminado', `${insumo.nombre} fue eliminado.`);
                            this.obtenerInsumos();
                        })
                        .catch(() => this.$alertaError('Error', 'No se pudo eliminar el insumo.'));
                });
        },
    },
    mounted() {
        if (this.listaInsumos.length === 0) {
            this.obtenerInsumos();
        }
    },
}
</script>
