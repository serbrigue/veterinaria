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
                    <h1 class="h5 mb-0">Mis Clientes</h1>
                    <button type="button" class="btn btn-primary" @click="abrirModalCrear">
                        + Nuevo Cliente
                    </button>
                </div>

                <div class="card-body">
                    <!-- TODO: Estado de carga -->
                    <div v-if="cargando" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted">Cargando clientes...</p>
                    </div>

                    <!-- TODO: Estado vacío cuando no hay clientes -->
                    <div v-else-if="clientes.length === 0" class="text-center py-5">
                        <p class="text-muted mb-3">No tienes clientes registrados aún.</p>
                        <button type="button" class="btn btn-primary" @click="abrirModalCrear">
                            Registrar tu primer cliente
                        </button>
                    </div>

                    <!-- TODO: Tabla de clientes -->
                    <div v-else class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Dirección</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="cliente in clientes" :key="cliente.id">
                                    <td>{{ cliente.usuario?.name }}</td>
                                    <td>{{ cliente.usuario?.email }}</td>
                                    <td>{{ cliente.telefono }}</td>
                                    <td>{{ cliente.direccion }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- TODO: Agregar paginación cuando haya muchos clientes -->
                </div>
            </div>

            <!-- ========================================== -->
            <!-- MODAL: Crear / Editar Cliente              -->
            <!-- ========================================== -->
            <div v-if="mostrarModal" class="modal fade show d-block" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ modoEdicion ? 'Editar Cliente' : 'Nuevo Cliente' }}</h5>
                            <button type="button" class="btn-close" @click="cerrarModal"></button>
                        </div>
                        <div>
                            <div class="modal-body">
                                <!-- TODO: Campo nombre -->
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input
                                        id="nombre"
                                        v-model="formulario.nombre"
                                        type="text"
                                        class="form-control"
                                        :class="{ 'is-invalid': formulario.errors.nombre }"
                                        required
                                    />
                                    <div v-if="formulario.errors.nombre" class="invalid-feedback">
                                        {{ formulario.errors.nombre }}
                                    </div>
                                </div>

                                <!-- TODO: Campo email -->
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input
                                        id="email"
                                        v-model="formulario.email"
                                        type="email"
                                        class="form-control"
                                        :class="{ 'is-invalid': formulario.errors.email }"
                                        required
                                    />
                                    <div v-if="formulario.errors.email" class="invalid-feedback">
                                        {{ formulario.errors.email }}
                                    </div>
                                </div>

                                <!-- TODO: Campo teléfono -->
                                <div class="mb-3">
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input
                                        id="telefono"
                                        v-model="formulario.telefono"
                                        type="text"
                                        class="form-control"
                                        :class="{ 'is-invalid': formulario.errors.telefono }"
                                        required
                                    />
                                    <div v-if="formulario.errors.telefono" class="invalid-feedback">
                                        {{ formulario.errors.telefono }}
                                    </div>
                                </div>

                                <!-- TODO: Campo dirección -->
                                <div class="mb-3">
                                    <label for="direccion" class="form-label">Dirección</label>
                                    <input
                                        id="direccion"
                                        v-model="formulario.direccion"
                                        type="text"
                                        class="form-control"
                                        :class="{ 'is-invalid': formulario.errors.direccion }"
                                        required
                                    />
                                    <div v-if="formulario.errors.direccion" class="invalid-feedback">
                                        {{ formulario.errors.direccion }}
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" @click="cerrarModal">
                                    Cancelar
                                </button>
                                <button type="button" class="btn btn-primary" :disabled="formulario.processing" @click="guardar">
                                    <span v-if="formulario.processing" class="spinner-border spinner-border-sm me-2"></span>
                                    {{ modoEdicion ? 'Guardar cambios' : 'Crear cliente' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="mostrarModal" class="modal-backdrop fade show"></div>

            <!-- ========================================== -->
            <!-- MODAL: Confirmar Eliminación                -->
            <!-- ========================================== -->
            <div v-if="mostrarConfirmacion" class="modal fade show d-block" tabindex="-1">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirmar eliminación</h5>
                            <button type="button" class="btn-close" @click="mostrarConfirmacion = false"></button>
                        </div>
                        <div class="modal-body">
                            <!-- TODO: Mostrar nombre del cliente a eliminar -->
                            <p>¿Estás seguro de eliminar a <strong>{{ clienteAEliminar?.nombre }}</strong>?</p>
                            <p class="text-muted small mb-0">Esta acción no se puede deshacer.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="mostrarConfirmacion = false">
                                Cancelar
                            </button>
                            <button type="button" class="btn btn-danger" :disabled="eliminando" @click="eliminarCliente">
                                <span v-if="eliminando" class="spinner-border spinner-border-sm me-2"></span>
                                Sí, eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="mostrarConfirmacion" class="modal-backdrop fade show"></div>

            <!-- TODO: Mostrar notificaciones de éxito/error -->
        </div>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from '@/Disenos/LayoutAutenticado.vue';
import { Head } from '@inertiajs/vue3';

export default {
    components: {
        AuthenticatedLayout,
        Head,
    },
    props: {
        clientes: {
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
            // TODO: Agregar los campos del formulario según el modelo Cliente
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
            // TODO: Enviar DELETE al endpoint /api/clientes/{id}
            axios.delete(`/api/clientes/${this.clienteAEliminar.id}`)
                .then(() => {
                    this.mostrarConfirmacion = false
                    this.clienteAEliminar = null
                    window.location.reload()
                })
                .finally(() => {
                    this.eliminando = false
                })
        },
    },
}
</script>
