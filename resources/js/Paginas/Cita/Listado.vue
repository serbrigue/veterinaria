<template>
    <Head title="Citas" />
    <AuthenticatedLayout>
        <div class="container py-4">
            <div class="card shadow-sm">
                <!--
                ============================================
                MÓDULO 5: CRUD de Citas
                ============================================
                Props: citas, mascotas, clientes (desde CitaController).
                Completa axios y v-for en selects.
                Referencia: Mascota/Listado.vue
                ============================================
                -->

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h1 class="h5 mb-0">Mis Citas</h1>
                    <button type="button" class="btn btn-primary" @click="abrirModalCrear">
                        + Nueva Cita
                    </button>
                </div>

                <div class="card-body">
                    <!-- TODO: Estado de carga -->
                    <div v-if="cargando" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted">Cargando citas...</p>
                    </div>

                    <!-- TODO: Estado vacío cuando no hay citas -->
                    <div v-else-if="citas.length === 0" class="text-center py-5">
                        <p class="text-muted mb-3">No tienes citas registradas aún.</p>
                        <button type="button" class="btn btn-primary" @click="abrirModalCrear">
                            Registrar tu primera cita
                        </button>
                    </div>

                    <!-- TODO: Tabla de citas -->
                    <div v-else class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Título</th>
                                    <th>Mascota</th>
                                    <th>Cliente</th>
                                    <th>Fecha y Hora</th>
                                    <th style="width: 180px">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="cita in citas" :key="cita.id">
                                    <td>{{ cita.titulo }}</td>
                                    <td>{{ cita.mascota?.nombre }}</td>
                                    <td>{{ cita.cliente?.nombre }}</td>
                                    <td>{{ $formatoFecha(cita.fecha_hora, 'DD/MM/YYYY HH:mm') }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button
                                                type="button"
                                                class="btn btn-primary"
                                                @click="abrirModalEditar(cita)"
                                            >
                                                Editar
                                            </button>
                                            <button
                                                type="button"
                                                class="btn btn-danger"
                                                @click="confirmarEliminar(cita)"
                                            >
                                                Eliminar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- TODO: Agregar paginación cuando haya muchas citas -->
                </div>
            </div>

            <!-- ========================================== -->
            <!-- MODAL: Crear / Editar Cita                 -->
            <!-- ========================================== -->
            <div v-if="mostrarModal" class="modal fade show d-block" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ modoEdicion ? 'Editar Cita' : 'Nueva Cita' }}</h5>
                            <button type="button" class="btn-close" @click="cerrarModal"></button>
                        </div>
                        <div>
                            <div class="modal-body">
                                <!-- TODO: Campo título -->
                                <div class="mb-3">
                                    <label for="titulo" class="form-label">Título</label>
                                    <input
                                        id="titulo"
                                        v-model="formulario.titulo"
                                        type="text"
                                        class="form-control"
                                        :class="{ 'is-invalid': formulario.errors.titulo }"
                                        required
                                    />
                                    <div v-if="formulario.errors.titulo" class="invalid-feedback">
                                        {{ formulario.errors.titulo }}
                                    </div>
                                </div>

                                <!-- TODO: Campo descripción -->
                                <div class="mb-3">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <textarea
                                        id="descripcion"
                                        v-model="formulario.descripcion"
                                        class="form-control"
                                        :class="{ 'is-invalid': formulario.errors.descripcion }"
                                        rows="3"
                                        required
                                    ></textarea>
                                    <div v-if="formulario.errors.descripcion" class="invalid-feedback">
                                        {{ formulario.errors.descripcion }}
                                    </div>
                                </div>

                                <!-- TODO: Campo fecha y hora -->
                                <div class="mb-3">
                                    <label for="fecha_hora" class="form-label">Fecha y Hora</label>
                                    <input
                                        id="fecha_hora"
                                        v-model="formulario.fecha_hora"
                                        type="datetime-local"
                                        class="form-control"
                                        :class="{ 'is-invalid': formulario.errors.fecha_hora }"
                                        required
                                    />
                                    <div v-if="formulario.errors.fecha_hora" class="invalid-feedback">
                                        {{ formulario.errors.fecha_hora }}
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="mascota_id" class="form-label">Mascota</label>
                                    <select
                                        id="mascota_id"
                                        v-model="formulario.mascota_id"
                                        class="form-select"
                                        :class="{ 'is-invalid': formulario.errors.mascota_id }"
                                        required
                                    >
                                        <option value="" disabled>Selecciona una mascota</option>
                                        <option
                                            v-for="mascota in mascotas"
                                            :key="mascota.id"
                                            :value="mascota.id"
                                        >
                                            {{ mascota.nombre }}{{ mascota.sexo ? ` (${mascota.sexo})` : '' }}
                                        </option>
                                    </select>
                                    <div v-if="formulario.errors.mascota_id" class="invalid-feedback">
                                        {{ formulario.errors.mascota_id }}
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="cliente_id" class="form-label">Cliente</label>
                                    <select
                                        id="cliente_id"
                                        v-model="formulario.cliente_id"
                                        class="form-select"
                                        :class="{ 'is-invalid': formulario.errors.cliente_id }"
                                        required
                                    >
                                        <option value="" disabled>Selecciona un cliente</option>
                                        <option
                                            v-for="cliente in clientes"
                                            :key="cliente.id"
                                            :value="cliente.id"
                                        >
                                            {{ cliente.nombre }}{{ cliente.email ? ` — ${cliente.email}` : '' }}
                                        </option>
                                    </select>
                                    <div v-if="formulario.errors.cliente_id" class="invalid-feedback">
                                        {{ formulario.errors.cliente_id }}
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" @click="cerrarModal">
                                    Cancelar
                                </button>
                                <button type="button" class="btn btn-primary" :disabled="formulario.processing" @click="guardar">
                                    <span v-if="formulario.processing" class="spinner-border spinner-border-sm me-2"></span>
                                    {{ modoEdicion ? 'Guardar cambios' : 'Crear cita' }}
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
                            <!-- TODO: Mostrar título de la cita a eliminar -->
                            <p>¿Estás seguro de eliminar la cita <strong>{{ citaAEliminar?.titulo }}</strong>?</p>
                            <p class="text-muted small mb-0">Esta acción no se puede deshacer.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="mostrarConfirmacion = false">
                                Cancelar
                            </button>
                            <button type="button" class="btn btn-danger" :disabled="eliminando" @click="eliminarCita">
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
        citas: {
            type: Array,
            default: () => [],
        },
        mascotas: {
            type: Array,
            default: () => [],
        },
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
            citaEditando: null,
            mostrarConfirmacion: false,
            citaAEliminar: null,
            eliminando: false,
            // TODO: Agregar los campos del formulario según el modelo Cita
            formulario: {
                titulo: '',
                descripcion: '',
                fecha_hora: '',
                mascota_id: '',
                cliente_id: '',
                errors: {},
                processing: false,
            },
        }
    },
    methods: {
        // TODO: Abrir modal para crear una nueva cita
        abrirModalCrear() {
            this.modoEdicion = false
            this.citaEditando = null
            this.formulario.titulo = ''
            this.formulario.descripcion = ''
            this.formulario.fecha_hora = ''
            this.formulario.mascota_id = ''
            this.formulario.cliente_id = ''
            this.formulario.errors = {}
            this.mostrarModal = true
        },
        // TODO: Abrir modal para editar una cita existente
        abrirModalEditar(cita) {
            this.modoEdicion = true
            this.citaEditando = cita
            this.formulario.titulo = cita.titulo
            this.formulario.descripcion = cita.descripcion
            this.formulario.fecha_hora = this.$fechaHoraInput(cita.fecha_hora)
            this.formulario.mascota_id = cita.mascota_id
            this.formulario.cliente_id = cita.cliente_id
            this.formulario.errors = {}
            this.mostrarModal = true
        },
        // TODO: Cerrar modal y limpiar estado
        cerrarModal() {
            this.mostrarModal = false
            this.citaEditando = null
        },
        // TODO: Guardar cita (crear o actualizar según modoEdicion)
        guardar() {
            this.formulario.processing = true
            this.formulario.errors = {}

            if (this.modoEdicion) {
                // TODO: Enviar PUT al endpoint /api/citas/{id}
                axios.put(`/api/citas/${this.citaEditando.id}`, {
                    titulo: this.formulario.titulo,
                    descripcion: this.formulario.descripcion,
                    fecha_hora: this.formulario.fecha_hora,
                    mascota_id: this.formulario.mascota_id,
                    cliente_id: this.formulario.cliente_id,
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
                // TODO: Enviar POST al endpoint /api/citas
                axios.post('/api/citas', {
                    titulo: this.formulario.titulo,
                    descripcion: this.formulario.descripcion,
                    fecha_hora: this.formulario.fecha_hora,
                    mascota_id: this.formulario.mascota_id,
                    cliente_id: this.formulario.cliente_id,
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
        // TODO: Confirmar eliminación de una cita
        confirmarEliminar(cita) {
            this.citaAEliminar = cita
            this.mostrarConfirmacion = true
        },
        // TODO: Eliminar cita
        eliminarCita() {
            this.eliminando = true
            // TODO: Enviar DELETE al endpoint /api/citas/{id}
            axios.delete(`/api/citas/${this.citaAEliminar.id}`)
                .then(() => {
                    this.mostrarConfirmacion = false
                    this.citaAEliminar = null
                    window.location.reload()
                })
                .finally(() => {
                    this.eliminando = false
                })
        },
    },
}
</script>
