<template>
    <Head title="Clientes" />
    <AuthenticatedLayout>
        <div class="container py-4">
            <div class="card shadow-sm">
                <!--
                ============================================
                MÓDULO 4: CRUD de Clientes
                ============================================
                Backend listo. Completa axios en methods.
                Referencia: Mascota/Listado.vue
                ============================================
                -->

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h1 class="h5 mb-0">Gestión de Clientes</h1>
                    <button type="button" class="btn btn-primary" @click="abrirModalCrear">
                        + Nuevo Cliente
                    </button>
                </div>

                <div class="card-body">
                    <!-- BARRA DE BÚSQUEDA Y FILTROS -->
                    <div class="bg-light p-3 rounded-3 border mb-4 shadow-sm">
                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label small fw-bold text-secondary mb-1">Buscar por Nombre</label>
                                <input type="text" class="form-control form-control-sm" placeholder="Nombre de cliente" v-model="filtroNombre" @keyup.enter="obtenerClientes()">
                            </div>
                            <div class="col-12 col-md-4 col-lg-2">
                                <label class="form-label small fw-bold text-secondary mb-1">Mascota</label>
                                <input type="text" class="form-control form-control-sm" placeholder="Nombre de mascota" v-model="filtroMascota" @keyup.enter="obtenerClientes()">
                            </div>
                            <div class="col-12 col-md-4 col-lg-2">
                                <label class="form-label small fw-bold text-secondary mb-1">Sucursal</label>
                                <select class="form-select form-select-sm" v-model="filtroSucursal" @change="obtenerClientes()">
                                    <option value="">Todas</option>
                                    <option v-for="suc in sucursales" :key="suc.id" :value="suc.id">
                                        {{ suc.nombre }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4 col-lg-3">
                                <label class="form-label small fw-bold text-secondary mb-1">Estado Financiero</label>
                                <select class="form-select form-select-sm" v-model="filtroEstadoPago" @change="obtenerClientes()">
                                    <option value="">Todos</option>
                                    <option value="al_dia">Al Día (Sin deuda)</option>
                                    <option value="moroso">Con Deuda (Moroso)</option>
                                </select>
                            </div>
                            <div class="col-12 col-lg-2 d-flex gap-2 justify-content-lg-end">
                                <button class="btn btn-outline-secondary btn-sm w-100" @click="limpiarFiltros()">
                                    Limpiar
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- TODO: Estado de carga -->
                    <div v-if="cargando" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted">Cargando clientes...</p>
                    </div>

                    <!-- TODO: Estado vacío cuando no hay clientes -->
                    <div v-else-if="!clientes.data || clientes.data.length === 0" class="text-center py-5">
                        <p class="text-muted mb-3">No tienes clientes registrados aún.</p>
                        <button type="button" class="btn btn-primary" @click="abrirModalCrear">
                            Registrar tu primer cliente
                        </button>
                    </div>

                    <!-- Tabla de clientes -->
                    <div v-else class="table-responsive">
                        <table class="table table-hover align-middle border">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">Cliente</th>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Contacto</th>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Mascotas</th>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Estado Financiero</th>
                                    <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="cliente in clientesArray" :key="cliente.id">
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <img v-if="cliente.foto_perfil_url" :src="cliente.foto_perfil_url" class="rounded-circle object-fit-cover" style="width: 40px; height: 40px;">
                                                <i v-else class="bi bi-person-fill fs-5"></i>
                                            </div>
                                            <div>
                                                <Link :href="route('clientes.detalle', cliente.id)" class="text-dark fw-bold text-decoration-none hover-primary">
                                                    {{ cliente.usuario?.name || 'Sin Nombre' }}
                                                </Link>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="text-muted small"><i class="bi bi-telephone-fill me-1"></i> {{ cliente.telefono || 'Sin teléfono' }}</span>
                                            <span class="text-muted small"><i class="bi bi-envelope-fill me-1"></i> {{ cliente.usuario?.email || 'Sin email' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div v-if="cliente.mascotas && cliente.mascotas.length > 0" class="d-flex flex-wrap gap-1">
                                            <span v-for="mascota in cliente.mascotas" :key="mascota.id" class="badge bg-light text-dark border shadow-sm">
                                                <i class="bi bi-heart-fill text-danger me-1" style="font-size: 0.6rem;"></i> {{ mascota.nombre }}
                                            </span>
                                        </div>
                                        <span v-else class="text-muted small">Sin mascotas</span>
                                    </td>
                                    <td>
                                        <div v-if="cliente.transacciones && cliente.transacciones.length > 0">
                                            <span class="badge bg-danger rounded-pill px-3 py-1 shadow-sm">
                                                <i class="bi bi-exclamation-triangle-fill me-1"></i> Deuda Activa ({{ cliente.transacciones.length }})
                                            </span>
                                        </div>
                                        <div v-else>
                                            <span class="badge bg-success rounded-pill px-3 py-1 shadow-sm">
                                                <i class="bi bi-check-circle-fill me-1"></i> Al día
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3 hover-opacity" @click="abrirModalEditar(cliente)">
                                                Editar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación cuando haya muchos clientes -->
                    <!-- Controles de Paginación -->
                    <div v-if="clientesData && clientesData.last_page > 1" class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted small">
                            Mostrando {{ clientesData.from }} a {{ clientesData.to }} de {{ clientesData.total }} clientes
                        </div>
                        <nav aria-label="Navegación de páginas">
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item" :class="{ disabled: !clientesData.prev_page_url }">
                                    <button class="page-link" @click.prevent="obtenerClientes(clientesData.prev_page_url)">Anterior</button>
                                </li>
                                <li 
                                    v-for="link in clientesData.links.slice(1, -1)" 
                                    :key="link.label" 
                                    class="page-item" 
                                    :class="{ active: link.active }"
                                >
                                    <button class="page-link" @click.prevent="obtenerClientes(link.url)" v-html="link.label"></button>
                                </li>
                                <li class="page-item" :class="{ disabled: !clientesData.next_page_url }">
                                    <button class="page-link" @click.prevent="obtenerClientes(clientesData.next_page_url)">Siguiente</button>
                                </li>
                            </ul>
                        </nav>
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
        Link,
    },
    props: {
        clientes: {
            type: Object,
            default: () => ({ data: [] }),
        },
        sucursales: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            cargando: false,
            mostrarModal: false,
            modoEdicion: false,
            clienteEditando: null,
            mostrarConfirmacion: false,
            clienteAEliminar: null,
            eliminando: false,
            filtroNombre: '',
            filtroMascota: '',
            filtroSucursal: '',
            filtroEstadoPago: '',
            clientesData: null,
            clientesArray: [],
            formulario: {
                nombre: '',
                email: '',
                telefono: '',
                direccion: '',
                errors: {},
                processing: false,
            },
        }
    },
    methods: {
        // TODO: Abrir modal para crear un nuevo cliente
        abrirModalCrear() {
            this.modoEdicion = false
            this.clienteEditando = null
            this.formulario.nombre = ''
            this.formulario.email = ''
            this.formulario.telefono = ''
            this.formulario.direccion = ''
            this.formulario.errors = {}
            this.mostrarModal = true
        },
        // TODO: Abrir modal para editar un cliente existente
        abrirModalEditar(cliente) {
            this.modoEdicion = true
            this.clienteEditando = cliente
            this.formulario.nombre = cliente.nombre
            this.formulario.email = cliente.email
            this.formulario.telefono = cliente.telefono
            this.formulario.direccion = cliente.direccion
            this.formulario.errors = {}
            this.mostrarModal = true
        },
        // TODO: Cerrar modal y limpiar estado
        cerrarModal() {
            this.mostrarModal = false
            this.clienteEditando = null
        },
        // TODO: Guardar cliente (crear o actualizar según modoEdicion)
        guardar() {
            this.formulario.processing = true
            this.formulario.errors = {}

            if (this.modoEdicion) {
                // TODO: Enviar PUT al endpoint /api/clientes/{id}
                axios.put(`/api/clientes/${this.clienteEditando.id}`, {
                    nombre: this.formulario.nombre,
                    email: this.formulario.email,
                    telefono: this.formulario.telefono,
                    direccion: this.formulario.direccion,
                })
                .then(() => {
                    this.cerrarModal()
                    window.location.reload()
                })
                .catch((error) => {
                    if (error.response?.status === 422) {
                        this.formulario.errors = error.response.data.errors
                    }
                })
                .finally(() => {
                    this.formulario.processing = false
                })
            } else {
                // TODO: Enviar POST al endpoint /api/clientes
                axios.post('/api/clientes', {
                    nombre: this.formulario.nombre,
                    email: this.formulario.email,
                    telefono: this.formulario.telefono,
                    direccion: this.formulario.direccion,
                })
                .then(() => {
                    this.cerrarModal()
                    window.location.reload()
                })
                .catch((error) => {
                    if (error.response?.status === 422) {
                        this.formulario.errors = error.response.data.errors
                    }
                })
                .finally(() => {
                    this.formulario.processing = false
                })
            }
        },
        // TODO: Confirmar eliminación de un cliente
        confirmarEliminar(cliente) {
            this.clienteAEliminar = cliente
            this.mostrarConfirmacion = true
        },
        // TODO: Eliminar cliente
        eliminarCliente() {
            this.eliminando = true
            axios.delete(`/api/clientes/${this.clienteAEliminar.id}`)
                .then(() => {
                    this.mostrarConfirmacion = false
                    this.clienteAEliminar = null
                    this.obtenerClientes()
                })
                .finally(() => {
                    this.eliminando = false
                })
        },
        obtenerClientes(url = '/clientes') {
            if (!url) return;
            this.cargando = true;
            axios.get(url, {
                params: {
                    nombre: this.filtroNombre,
                    mascota: this.filtroMascota,
                    sucursal_id: this.filtroSucursal,
                    estado_pago: this.filtroEstadoPago
                }
            })
            .then(response => {
                if (response.data.clientes && response.data.clientes.data) {
                    this.clientesData = response.data.clientes;
                    this.clientesArray = response.data.clientes.data;
                } else if (response.data.clientes) {
                    this.clientesData = null;
                    this.clientesArray = response.data.clientes;
                }
            })
            .catch(error => {
                console.error('Error al obtener clientes:', error);
            })
            .finally(() => {
                this.cargando = false;
            });
        },
        limpiarFiltros() {
            this.filtroNombre = '';
            this.filtroMascota = '';
            this.filtroSucursal = '';
            this.filtroEstadoPago = '';
            this.obtenerClientes();
        }
    },
    mounted() {
        this.obtenerClientes();
    }
}
</script>

<style scoped>
.hover-primary:hover {
    color: var(--bs-primary) !important;
}
.hover-opacity:hover {
    opacity: 0.8;
}
</style>
