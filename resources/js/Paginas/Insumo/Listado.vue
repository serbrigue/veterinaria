<template>
    <Head title="Insumos" />
    <AuthenticatedLayout>
        <div class="container py-4">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h1 class="h5 mb-0">Catálogo de Insumos</h1>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <select
                            v-model="filtroSucursal"
                            class="form-select form-select-sm"
                            style="width: auto; min-width: 12rem"
                        >
                            <option value="">Todas las sucursales</option>
                            <option v-for="suc in sucursales" :key="suc.id" :value="suc.id">
                                {{ suc.nombre }}
                            </option>
                        </select>
                        <select
                            v-model="filtroEstado"
                            class="form-select form-select-sm"
                            style="width: auto; min-width: 10rem"
                        >
                            <option value="">Todos los estados</option>
                            <option value="activo">Activos</option>
                            <option value="inactivo">Inactivos</option>
                        </select>
                        <button type="button" class="btn btn-sm btn-primary" @click="abrirModalCrear">
                            + Nuevo Insumo
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <p v-show="!listaVacia" class="text-muted small mb-3">
                        {{ totalInsumos }} insumo{{ totalInsumos === 1 ? '' : 's' }} registrado{{ totalInsumos === 1 ? '' : 's' }}
                    </p>
                    <div v-if="cargando" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted">Cargando insumos...</p>
                    </div>

                    <!-- Estado vacío -->
                    <div v-else-if="listaVacia" class="text-center py-5">
                        <p class="text-muted mb-3">No hay insumos registrados en el catálogo.</p>
                        <button type="button" class="btn btn-primary" @click="abrirModalCrear">
                            Registrar el primer insumo
                        </button>
                    </div>

                    <div v-else-if="sinResultadosFiltro" class="text-center py-5">
                        <p class="text-muted mb-3">Ningún insumo coincide con el filtro.</p>
                        <button type="button" class="btn btn-outline-secondary btn-sm" @click="filtroEstado = ''">
                            Quitar filtro
                        </button>
                    </div>

                    <div v-else class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Sucursal</th>
                                    <th>Descripción</th>
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
                                        <Link :href="route('insumos.detalle', insumo.id)" class="text-decoration-none">
                                            {{ insumo.nombre }}
                                        </Link>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-dark">{{ insumo.sucursal?.nombre || '—' }}</span>
                                    </td>
                                    <td>{{ insumo.descripcion || '—' }}</td>
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

            <!-- MODAL: Crear / Editar Insumo -->
            <div v-if="mostrarModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow border-0">
                        <div class="modal-header border-bottom bg-light py-3 px-4">
                            <h5 class="modal-title fw-bold text-dark">{{ tituloModal }}</h5>
                            <button type="button" class="btn-close" @click="cerrarModal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label for="sucursal_id" class="form-label fw-semibold text-secondary">Sucursal</label>
                                <select id="sucursal_id" v-model="formulario.sucursal_id" class="form-select" :class="{ 'is-invalid': formulario.errors.sucursal_id }" required>
                                    <option :value="null" disabled>Seleccione una sucursal...</option>
                                    <option v-for="suc in sucursales" :key="suc.id" :value="suc.id">
                                        {{ suc.nombre }}
                                    </option>
                                </select>
                                <div v-if="formulario.errors.sucursal_id" class="invalid-feedback">{{ formulario.errors.sucursal_id }}</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="nombre" class="form-label fw-semibold text-secondary">Nombre del Insumo</label>
                                <input id="nombre" v-model="formulario.nombre" type="text" class="form-control" placeholder="Ej: Anestesia General" :class="{ 'is-invalid': formulario.errors.nombre }" required />
                                <div v-if="formulario.errors.nombre" class="invalid-feedback">{{ formulario.errors.nombre }}</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="descripcion" class="form-label fw-semibold text-secondary">Descripción</label>
                                <textarea id="descripcion" v-model="formulario.descripcion" class="form-control" rows="2" placeholder="Opcional" :class="{ 'is-invalid': formulario.errors.descripcion }"></textarea>
                                <div v-if="formulario.errors.descripcion" class="invalid-feedback">{{ formulario.errors.descripcion }}</div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="precio_venta" class="form-label fw-semibold text-secondary">Precio de Venta ($)</label>
                                    <input id="precio_venta" v-model="formulario.precio_venta" type="number" min="0" class="form-control" :class="{ 'is-invalid': formulario.errors.precio_venta }" required />
                                    <div v-if="formulario.errors.precio_venta" class="invalid-feedback">{{ formulario.errors.precio_venta }}</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="estado" class="form-label fw-semibold text-secondary">Estado</label>
                                    <select id="estado" v-model="formulario.estado" class="form-select" :class="{ 'is-invalid': formulario.errors.estado }" required>
                                        <option value="activo">Activo</option>
                                        <option value="inactivo">Inactivo</option>
                                    </select>
                                    <div v-if="formulario.errors.estado" class="invalid-feedback">{{ formulario.errors.estado }}</div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="stock_actual" class="form-label fw-semibold text-secondary">Stock Actual</label>
                                    <input id="stock_actual" v-model="formulario.stock_actual" type="number" min="0" class="form-control" :class="{ 'is-invalid': formulario.errors.stock_actual }" required />
                                    <div v-if="formulario.errors.stock_actual" class="invalid-feedback">{{ formulario.errors.stock_actual }}</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="stock_minimo" class="form-label fw-semibold text-secondary">Stock Mínimo (Alerta)</label>
                                    <input id="stock_minimo" v-model="formulario.stock_minimo" type="number" min="0" class="form-control" :class="{ 'is-invalid': formulario.errors.stock_minimo }" required />
                                    <div v-if="formulario.errors.stock_minimo" class="invalid-feedback">{{ formulario.errors.stock_minimo }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="cerrarModal">Cancelar</button>
                            <button type="button" class="btn btn-primary" :disabled="formulario.processing" @click="guardar">
                                <span v-if="formulario.processing" class="spinner-border spinner-border-sm me-2"></span>
                                {{ textoBotonGuardar }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="mostrarModal" class="modal-backdrop fade show"></div>
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
        Link,
    },
    props: {
        insumos: {
            type: Array,
            default: () => [],
        },
        sucursales: {
            type: Array,
            default: () => [],
        }
    },
    data() {
        return {
            cargando: false,
            mostrarModal: false,
            modoEdicion: false,
            insumoEditando: null,
            filtroEstado: '',
            filtroSucursal: '',
            listaInsumos: this.insumos, // Copia local para reactividad si viene por prop
            formulario: {
                sucursal_id: null,
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
            if (this.filtroEstado) {
                visibles = visibles.filter((i) => i.estado === this.filtroEstado);
            }
            if (this.filtroSucursal) {
                visibles = visibles.filter((i) => i.sucursal_id === this.filtroSucursal);
            }
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
        abrirModalCrear() {
            this.modoEdicion = false;
            this.insumoEditando = null;
            this.formulario.sucursal_id = null;
            this.formulario.nombre = '';
            this.formulario.descripcion = '';
            this.formulario.precio_venta = 0;
            this.formulario.stock_actual = 0;
            this.formulario.stock_minimo = 5;
            this.formulario.estado = 'activo';
            this.formulario.errors = {};
            this.mostrarModal = true;
        },
        abrirModalEditar(insumo) {
            this.modoEdicion = true;
            this.insumoEditando = insumo;
            this.formulario.sucursal_id = insumo.sucursal_id;
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
                sucursal_id: this.formulario.sucursal_id,
                nombre: this.formulario.nombre,
                descripcion: this.formulario.descripcion,
                precio_venta: this.formulario.precio_venta,
                stock_actual: this.formulario.stock_actual,
                stock_minimo: this.formulario.stock_minimo,
                estado: this.formulario.estado,
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
