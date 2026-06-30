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

                    <div v-else>
                        <!-- MENU DE ACCION RAPIDA (CORREO MASIVO) -->
                        <div v-if="esAdmin && selectedClientes.length > 0" class="alert alert-info d-flex justify-content-between align-items-center mb-4 shadow-sm border border-info rounded-3 p-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-people-fill fs-5"></i>
                                <span>Hay <strong>{{ selectedClientes.length }}</strong> cliente(s) seleccionado(s).</span>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-primary btn-sm fw-semibold shadow-sm" @click="abrirModalCorreos">
                                    <i class="bi bi-envelope-fill me-1"></i> Enviar Correo Masivo
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" @click="clearSelection">
                                    Desmarcar todos
                                </button>
                            </div>
                        </div>

                        <!-- Tabla de clientes -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle border">
                            <thead class="table-light">
                                <tr>
                                    <th v-if="esAdmin" class="ps-3" style="width: 45px;">
                                        <input type="checkbox" class="form-check-input" :checked="isAllSelected" @change="toggleSelectAll">
                                    </th>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7" :class="{ 'ps-3': !esAdmin }">Cliente</th>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Contacto</th>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Mascotas</th>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Estado Financiero</th>
                                    <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="cliente in clientesArray" :key="cliente.id" :class="{ 'table-active': esAdmin && selectedClientes.includes(cliente.id) }">
                                    <td v-if="esAdmin" class="ps-3">
                                        <input type="checkbox" class="form-check-input" :value="cliente.id" v-model="selectedClientes">
                                    </td>
                                    <td :class="{ 'ps-3': !esAdmin }">
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
            
            <!-- ========================================== -->
            <!-- MODAL: Crear / Editar Cliente              -->
            <!-- ========================================== -->
            <div v-if="mostrarModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5); z-index: 1050;">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow border-0">
                        <div class="modal-header border-bottom bg-light py-3 px-4">
                            <h5 class="modal-title fw-bold text-dark">{{ modoEdicion ? 'Editar Cliente' : 'Nuevo Cliente' }}</h5>
                            <button type="button" class="btn-close" @click="cerrarModal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label for="nombre" class="form-label fw-semibold text-secondary">Nombre Completo</label>
                                <input id="nombre" v-model="formulario.nombre" type="text" class="form-control" placeholder="Ej: Juan Pérez" :class="{ 'is-invalid': formulario.errors.nombre }" required />
                                <div v-if="formulario.errors.nombre" class="invalid-feedback">{{ formulario.errors.nombre }}</div>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold text-secondary">Correo Electrónico</label>
                                <input id="email" v-model="formulario.email" type="email" class="form-control" placeholder="juan@ejemplo.com" :class="{ 'is-invalid': formulario.errors.email }" required />
                                <div v-if="formulario.errors.email" class="invalid-feedback">{{ formulario.errors.email }}</div>
                            </div>
                            <div class="mb-3">
                                <label for="telefono" class="form-label fw-semibold text-secondary">Teléfono de Contacto</label>
                                <input id="telefono" v-model="formulario.telefono" type="text" class="form-control" placeholder="+56912345678" :class="{ 'is-invalid': formulario.errors.telefono }" />
                                <div v-if="formulario.errors.telefono" class="invalid-feedback">{{ formulario.errors.telefono }}</div>
                            </div>
                            <div class="mb-3">
                                <label for="direccion" class="form-label fw-semibold text-secondary">Dirección</label>
                                <input id="direccion" v-model="formulario.direccion" type="text" class="form-control" placeholder="Av. Siempre Viva 742" :class="{ 'is-invalid': formulario.errors.direccion }" />
                                <div v-if="formulario.errors.direccion" class="invalid-feedback">{{ formulario.errors.direccion }}</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="cerrarModal">Cancelar</button>
                            <button type="button" class="btn btn-primary" :disabled="formulario.processing" @click="guardar">
                                <span v-if="formulario.processing" class="spinner-border spinner-border-sm me-2"></span>
                                {{ modoEdicion ? 'Guardar cambios' : 'Crear cliente' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- MODAL: Enviar Correo Masivo                -->
            <!-- ========================================== -->
            <div v-if="mostrarModalCorreo" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5); z-index: 1055;">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow border-0">
                        <div class="modal-header border-bottom bg-primary text-white py-3 px-4">
                            <h5 class="modal-title fw-bold">
                                <i class="bi bi-envelope-paper-fill me-2"></i>Enviar Correo Masivo
                            </h5>
                            <button type="button" class="btn-close btn-close-white" @click="cerrarModalCorreo"></button>
                        </div>
                        <div class="modal-body p-4">
                            <!-- Selected Clients Count and Pills -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-secondary">Destinatarios ({{ selectedClientes.length }})</label>
                                <div class="border rounded bg-light p-2 d-flex flex-wrap gap-1 align-items-center" style="max-height: 100px; overflow-y: auto;">
                                    <span v-for="cId in selectedClientes" :key="cId" class="badge bg-secondary rounded-pill px-2 py-1 small me-1 mb-1">
                                        {{ getClienteNombre(cId) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Templates / Presets Quick Menu -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-secondary">Seleccionar Plantilla / Tipo</label>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm" :class="emailType === 'deuda' ? 'btn-danger' : 'btn-outline-danger'" @click="setEmailPreset('deuda')">
                                        <i class="bi bi-exclamation-circle-fill me-1"></i>Deudas
                                    </button>
                                    <button type="button" class="btn btn-sm" :class="emailType === 'promocion' ? 'btn-success' : 'btn-outline-success'" @click="setEmailPreset('promocion')">
                                        <i class="bi bi-tag-fill me-1"></i>Promociones
                                    </button>
                                    <button type="button" class="btn btn-sm" :class="emailType === 'personalizado' ? 'btn-primary' : 'btn-outline-primary'" @click="setEmailPreset('personalizado')">
                                        <i class="bi bi-pencil-fill me-1"></i>Personalizado
                                    </button>
                                </div>
                            </div>

                            <!-- Form fields -->
                            <div class="mb-3">
                                <label for="email_asunto" class="form-label fw-semibold text-secondary">Asunto del Correo</label>
                                <input id="email_asunto" v-model="emailAsunto" type="text" class="form-control" placeholder="Ej: Novedades de la clínica" required />
                            </div>

                            <div class="mb-3">
                                <label for="email_mensaje" class="form-label fw-semibold text-secondary">Mensaje</label>
                                <textarea id="email_mensaje" v-model="emailMensaje" class="form-control" rows="5" placeholder="Escribe el mensaje aquí..." required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="cerrarModalCorreo">Cancelar</button>
                            <button type="button" class="btn btn-primary" :disabled="enviandoCorreos || !emailAsunto || !emailMensaje" @click="enviarCorreos">
                                <span v-if="enviandoCorreos" class="spinner-border spinner-border-sm me-2"></span>
                                <i class="bi bi-send me-1"></i> Enviar a {{ selectedClientes.length }} clientes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from '@/Disenos/LayoutAutenticado.vue';
import { Head, Link } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

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
            // Selección y correo masivo
            selectedClientes: [],
            mostrarModalCorreo: false,
            emailType: 'personalizado',
            emailAsunto: '',
            emailMensaje: '',
            enviandoCorreos: false,
        }
    },
    computed: {
        isAllSelected() {
            if (this.clientesArray.length === 0) return false;
            return this.clientesArray.every(c => this.selectedClientes.includes(c.id));
        },
        esAdmin() {
            const user = this.$page.props.auth.user;
            return user && (user.rol_id === 1 || (user.rol && user.rol.nombre_interno === 'admin'));
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
        },
        toggleSelectAll(event) {
            if (event.target.checked) {
                this.clientesArray.forEach(c => {
                    if (!this.selectedClientes.includes(c.id)) {
                        this.selectedClientes.push(c.id);
                    }
                });
            } else {
                this.selectedClientes = [];
            }
        },
        clearSelection() {
            this.selectedClientes = [];
        },
        getClienteNombre(id) {
            const found = this.clientesArray.find(c => c.id === id);
            return found ? (found.usuario?.name || 'Cliente') : 'Cliente';
        },
        abrirModalCorreos() {
            this.emailType = 'personalizado';
            this.emailAsunto = '';
            this.emailMensaje = '';
            this.mostrarModalCorreo = true;
        },
        cerrarModalCorreo() {
            this.mostrarModalCorreo = false;
        },
        setEmailPreset(type) {
            this.emailType = type;
            if (type === 'deuda') {
                this.emailAsunto = 'Recordatorio de Pago Pendiente - Clínica Veterinaria';
                this.emailMensaje = 'Estimado/a cliente,\n\nLe recordamos que presenta saldos pendientes en nuestra clínica. Agradecemos su pronto pago para poder seguir brindándole la mejor atención a sus mascotas.\n\nAtentamente,\nEl equipo de Clínica Veterinaria';
            } else if (type === 'promocion') {
                this.emailAsunto = '¡Promociones Especiales en Vacunas y Alimentos!';
                this.emailMensaje = 'Estimado/a cliente,\n\n¡Tenemos excelentes noticias! Durante este mes contamos con un 20% de descuento en vacunas anuales y en alimentos seleccionados en todas nuestras sucursales.\n\n¡No dejes pasar esta oportunidad de cuidar a tu mascota!\n\nAtentamente,\nEl equipo de Clínica Veterinaria';
            } else {
                this.emailAsunto = '';
                this.emailMensaje = '';
            }
        },
        enviarCorreos() {
            this.enviandoCorreos = true;
            axios.post('/api/clientes/enviar-correo', {
                clientes_ids: this.selectedClientes,
                asunto: this.emailAsunto,
                mensaje: this.emailMensaje
            })
            .then(response => {
                this.cerrarModalCorreo();
                this.selectedClientes = [];
                Swal.fire({
                    icon: 'success',
                    title: 'Correos enviados',
                    text: response.data.mensaje || 'Se han enviado los correos correctamente.',
                    confirmButtonColor: '#3085d6'
                });
            })
            .catch(error => {
                console.error('Error al enviar correos:', error);
                const errMsg = error.response?.data?.error || 'No se pudieron enviar los correos.';
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errMsg,
                    confirmButtonColor: '#3085d6'
                });
            })
            .finally(() => {
                this.enviandoCorreos = false;
            });
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
