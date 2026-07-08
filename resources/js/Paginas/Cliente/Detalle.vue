<template>
    <Head :title="'Cliente - ' + (cliente.usuario?.name || 'Detalle')" />

    <AuthenticatedLayout>
        <div class="container py-4">
            <!-- Encabezado con botón de volver -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center gap-3">
                    <Link :href="route('clientes.listado')" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                        <i class="bi bi-arrow-left"></i> Volver a Clientes
                    </Link>
                    <h1 class="h3 mb-0 text-dark fw-bold">Expediente del Cliente</h1>
                </div>
            </div>

            <div class="row g-4">
                <!-- COLUMNA IZQUIERDA: PERFIL DEL CLIENTE -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 h-100">
                        <div class="card-header bg-primary bg-opacity-10 border-0 pt-4 pb-0 text-center">
                            <div class="position-relative d-inline-block mb-3">
                                <img v-if="cliente.foto_perfil_url" :src="cliente.foto_perfil_url" :alt="cliente.usuario?.name" class="rounded-circle object-fit-cover shadow border border-3 border-white" style="width: 120px; height: 120px;">
                                <div v-else class="bg-primary text-white rounded-circle shadow d-flex align-items-center justify-content-center border border-3 border-white" style="width: 120px; height: 120px;">
                                    <i class="bi bi-person-fill" style="font-size: 4rem;"></i>
                                </div>
                            </div>
                            <h2 class="h5 fw-bold text-dark mb-1">{{ cliente.usuario?.name || 'Cliente sin nombre' }}</h2>
                            <p class="text-muted small mb-3">
                                <i class="bi bi-person-badge me-1"></i> ID: #{{ cliente.id.toString().padStart(4, '0') }}
                            </p>
                            
                            <!-- Badge de Deuda Global -->
                            <div class="mb-4">
                                <div v-if="transaccionesPendientesCount > 0" class="badge bg-danger rounded-pill px-3 py-2 fs-6 shadow-sm">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Deuda Activa: {{ formatearDinero(deudaTotal) }}
                                </div>
                                <div v-else class="badge bg-success rounded-pill px-3 py-2 fs-6 shadow-sm">
                                    <i class="bi bi-check-circle-fill me-1"></i> Cuenta al día
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4 pt-2">
                            <h3 class="h6 text-uppercase fw-bold text-muted mb-3" style="letter-spacing: 0.5px;">Información de Contacto</h3>
                            
                            <ul class="list-unstyled mb-0">
                                <li class="mb-3 d-flex align-items-center gap-3">
                                    <div class="bg-light rounded p-2 text-secondary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="bi bi-envelope-fill fs-5"></i>
                                    </div>
                                    <div>
                                        <span class="d-block small text-muted">Correo Electrónico</span>
                                        <span class="fw-medium text-dark">{{ cliente.usuario?.email || 'No registrado' }}</span>
                                    </div>
                                </li>
                                <li class="mb-3 d-flex align-items-center gap-3">
                                    <div class="bg-light rounded p-2 text-secondary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="bi bi-telephone-fill fs-5"></i>
                                    </div>
                                    <div>
                                        <span class="d-block small text-muted">Teléfono</span>
                                        <span class="fw-medium text-dark">{{ cliente.telefono || 'No registrado' }}</span>
                                    </div>
                                </li>
                                <li class="d-flex align-items-center gap-3">
                                    <div class="bg-light rounded p-2 text-secondary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="bi bi-geo-alt-fill fs-5"></i>
                                    </div>
                                    <div>
                                        <span class="d-block small text-muted">Dirección</span>
                                        <span class="fw-medium text-dark">{{ cliente.direccion || 'No registrada' }}</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- COLUMNA DERECHA: MASCOTAS Y TRANSACCIONES -->
                <div class="col-lg-8">
                    
                    <!-- SECCIÓN: MASCOTAS DEL CLIENTE -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                            <h3 class="h5 mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                                <i class="bi bi-hearts text-danger"></i> Pacientes (Mascotas)
                            </h3>
                            <div class="d-flex align-items-center gap-2">
                                <button
                                    v-if="$isAdmin() || $isSecretaria()"
                                    type="button"
                                    class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm"
                                    @click="abrirModalCrear"
                                >
                                    <i class="bi bi-plus-lg"></i> Registrar Mascota
                                </button>
                                <span class="badge bg-secondary rounded-pill">{{ cliente.mascotas?.length || 0 }} Registros</span>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div v-if="!cliente.mascotas || cliente.mascotas.length === 0" class="text-center py-4 bg-light rounded-3">
                                <i class="bi bi-info-circle text-muted fs-2 d-block mb-2"></i>
                                <span class="text-muted small">Este cliente aún no tiene mascotas registradas.</span>
                            </div>
                            <div v-else class="row g-3">
                                <div v-for="mascota in cliente.mascotas" :key="mascota.id" class="col-md-6">
                                    <div class="d-flex align-items-center gap-3 p-3 border rounded-3 hover-shadow transition-all bg-white h-100 position-relative">
                                        <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 50px; height: 50px;">
                                            <img v-if="mascota.imagen_url" :src="mascota.imagen_url" class="rounded-circle object-fit-cover w-100 h-100">
                                            <i v-else class="bi bi-heart-fill fs-5"></i>
                                        </div>
                                        <div class="overflow-hidden">
                                            <h4 class="h6 mb-1 fw-bold text-dark text-truncate">{{ mascota.nombre }}</h4>
                                            <p class="text-muted small mb-0 text-truncate">
                                                {{ mascota.raza?.especie?.nombre || 'Especie N/A' }} - {{ mascota.raza?.nombre || 'Raza N/A' }}
                                            </p>
                                        </div>
                                        <div class="ms-auto d-flex align-items-center gap-1">
                                            <button
                                                v-if="$isAdmin() || $isSecretaria() || ($isCliente() && $page.props.auth.user.cliente?.id === cliente.id)"
                                                type="button"
                                                class="btn btn-sm btn-outline-primary p-0 rounded-circle d-flex align-items-center justify-content-center"
                                                style="width: 28px; height: 28px;"
                                                @click.prevent="abrirModalEditar(mascota)"
                                                title="Editar"
                                            >
                                                <i class="bi bi-pencil small">Editar</i>
                                            </button>
                                            <button
                                                v-if="$isAdmin() || $isSecretaria() || ($isCliente() && $page.props.auth.user.cliente?.id === cliente.id)"
                                                type="button"
                                                class="btn btn-sm btn-outline-danger p-0 rounded-circle d-flex align-items-center justify-content-center"
                                                style="width: 28px; height: 28px;"
                                                @click.prevent="confirmarEliminar(mascota)"
                                                title="Eliminar"
                                            >
                                                <i class="bi bi-trash small">Eliminar</i>
                                            </button>
                                            <Link :href="route('mascotas.detalle', mascota.id)" class="btn btn-sm btn-link text-muted p-0 ms-1" title="Ver detalle">
                                                <i class="bi bi-chevron-right fs-5"></i>
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN: HISTORIAL DE PAGOS / TRANSACCIONES -->
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                            <h3 class="h5 mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                                <i class="bi bi-receipt-cutoff text-success"></i> Historial de Transacciones
                            </h3>
                        </div>
                        <div class="card-body p-4">
                            <div v-if="!transacciones.data || transacciones.data.length === 0" class="text-center py-4 bg-light rounded-3">
                                <i class="bi bi-wallet2 text-muted fs-2 d-block mb-2"></i>
                                <span class="text-muted small">No hay registro de transacciones para este cliente.</span>
                            </div>
                            <div v-else class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-secondary small fw-bold text-uppercase border-0 rounded-start">Fecha</th>
                                            <th class="text-secondary small fw-bold text-uppercase border-0">Concepto</th>
                                            <th class="text-secondary small fw-bold text-uppercase border-0 text-end">Monto</th>
                                            <th class="text-secondary small fw-bold text-uppercase border-0 text-center rounded-end">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="tx in transacciones.data" :key="tx.id">
                                            <td>
                                                <span class="d-block fw-medium text-dark small">{{ formatearFechaCorta(tx.created_at) }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-bold text-dark small">Atención Médica</span>
                                                    <Link v-if="tx.cita" :href="route('citas.detalle', tx.cita.id)" class="text-muted small text-decoration-none hover-primary text-truncate d-inline-block" style="max-width: 200px;">
                                                        <i class="bi bi-link-45deg"></i> {{ tx.cita.titulo || 'Cita #' + tx.cita.id }}
                                                    </Link>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <span class="fw-bold text-dark">{{ formatearDinero(tx.monto_total) }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge rounded-pill px-3 py-1" :class="{
                                                    'bg-success': tx.estado === 'pagado',
                                                    'bg-danger': tx.estado === 'pendiente',
                                                    'bg-warning text-dark': tx.estado === 'abonado',
                                                    'bg-secondary': tx.estado === 'anulado',
                                                }">
                                                    {{ tx.estado.charAt(0).toUpperCase() + tx.estado.slice(1) }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Controles de Paginación -->
                            <div v-if="transacciones.last_page > 1" class="d-flex justify-content-between align-items-center mt-4">
                                <div class="text-muted small">
                                    Mostrando {{ transacciones.from }} a {{ transacciones.to }} de {{ transacciones.total }}
                                </div>
                                <nav aria-label="Navegación de páginas">
                                    <ul class="pagination pagination-sm mb-0">
                                        <li class="page-item" :class="{ disabled: !transacciones.prev_page_url }">
                                            <Link class="page-link" :href="transacciones.prev_page_url || '#'" preserve-scroll>Anterior</Link>
                                        </li>
                                        <li 
                                            v-for="link in transacciones.links.slice(1, -1)" 
                                            :key="link.label" 
                                            class="page-item" 
                                            :class="{ active: link.active }"
                                        >
                                            <Link class="page-link" :href="link.url || '#'" v-html="link.label" preserve-scroll></Link>
                                        </li>
                                        <li class="page-item" :class="{ disabled: !transacciones.next_page_url }">
                                            <Link class="page-link" :href="transacciones.next_page_url || '#'" preserve-scroll>Siguiente</Link>
                                        </li>
                                    </ul>
                                </nav>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- MODAL CRUD PARA MASCOTAS -->
        <ModalCrud
            :visible="mostrarModal"
            :titulo="tituloModal"
            :modo-edicion="modoEdicion"
            :processing="formulario.processing"
            tamanio="lg"
            texto-guardar="Guardar Cambios"
            texto-crear="Registrar Mascota"
            @cerrar="cerrarModal"
            @guardar="guardar"
        >
            <div class="row g-4">
                <!-- Columna Izquierda: Identificación y Clasificación -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="nombre" class="form-label fw-semibold text-secondary small text-uppercase">Nombre de la Mascota</label>
                        <input
                            id="nombre"
                            v-model="formulario.nombre"
                            type="text"
                            class="form-control bg-light border-0 py-2"
                            placeholder="Ej: Garfield"
                            :class="{ 'is-invalid': formulario.errors.nombre }"
                            required
                        />
                        <div v-if="formulario.errors.nombre" class="invalid-feedback">
                            {{ formulario.errors.nombre }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="especie_id" class="form-label fw-semibold text-secondary small text-uppercase">Especie</label>
                        <select
                            id="especie_id"
                            v-model="formulario.especie_id"
                            class="form-select bg-light border-0 py-2"
                            :class="{ 'is-invalid': formulario.errors.especie_id }"
                            required
                            @change="obtenerRazasPorEspecie(formulario.especie_id)"
                        >
                            <option value="" disabled>Seleccione una especie</option>
                            <option
                                v-for="especie in especies"
                                :key="especie.id"
                                :value="especie.id"
                            >
                                {{ especie.nombre }}
                            </option>
                        </select>
                        <div v-if="formulario.errors.especie_id" class="invalid-feedback">
                            {{ formulario.errors.especie_id }}
                        </div>
                    </div>

                    <div class="mb-3" v-if="formulario.especie_id && razas.length > 0">
                        <label for="raza_id" class="form-label fw-semibold text-secondary small text-uppercase">Raza</label>
                        <select
                            id="raza_id"
                            v-model="formulario.raza_id"
                            class="form-select bg-light border-0 py-2"
                            :class="{ 'is-invalid': formulario.errors.raza_id }"
                            required
                        >
                            <option value="" disabled>Seleccione una raza</option>
                            <option
                                v-for="raza in razas"
                                :key="raza.id"
                                :value="raza.id"
                            >
                                {{ raza.nombre }}
                            </option>
                        </select>
                        <div v-if="formulario.errors.raza_id" class="invalid-feedback">
                            {{ formulario.errors.raza_id }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="sexo" class="form-label fw-semibold text-secondary small text-uppercase">Sexo</label>
                        <select
                            id="sexo"
                            v-model="formulario.sexo"
                            class="form-select bg-light border-0 py-2"
                            :class="{ 'is-invalid': formulario.errors.sexo }"
                            required
                        >
                            <option value="" disabled>Seleccione el sexo</option>
                            <option
                                v-for="op in opcionesSexo"
                                :key="op.value"
                                :value="op.value"
                            >
                                {{ op.label }}
                            </option>
                        </select>
                        <div v-if="formulario.errors.sexo" class="invalid-feedback">
                            {{ formulario.errors.sexo }}
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Información Física y Médica -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="fecha_nacimiento" class="form-label fw-semibold text-secondary small text-uppercase">Fecha de Nacimiento</label>
                        <input
                            id="fecha_nacimiento"
                            v-model="formulario.fecha_nacimiento"
                            type="date"
                            class="form-control bg-light border-0 py-2"
                            :class="{ 'is-invalid': formulario.errors.fecha_nacimiento }"
                        />
                        <div v-if="formulario.errors.fecha_nacimiento" class="invalid-feedback">
                            {{ formulario.errors.fecha_nacimiento }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="peso_kg" class="form-label fw-semibold text-secondary small text-uppercase">Peso (kg)</label>
                        <input
                            id="peso_kg"
                            v-model="formulario.peso_kg"
                            type="number"
                            step="0.01"
                            min="0"
                            class="form-control bg-light border-0 py-2"
                            placeholder="Ej: 5.40"
                            :class="{ 'is-invalid': formulario.errors.peso_kg }"
                        />
                        <div v-if="formulario.errors.peso_kg" class="invalid-feedback">
                            {{ formulario.errors.peso_kg }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="color" class="form-label fw-semibold text-secondary small text-uppercase">Color / Pelaje</label>
                        <input
                            id="color"
                            v-model="formulario.color"
                            type="text"
                            class="form-control bg-light border-0 py-2"
                            placeholder="Ej: Blanco con manchas negras"
                            :class="{ 'is-invalid': formulario.errors.color }"
                        />
                        <div v-if="formulario.errors.color" class="invalid-feedback">
                            {{ formulario.errors.color }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="foto" class="form-label fw-semibold text-secondary small text-uppercase">Foto de la Mascota</label>
                        <input
                            id="foto"
                            ref="fotoInput"
                            type="file"
                            class="form-control bg-light border-0 py-2"
                            accept="image/*"
                            @change="seleccionarFoto"
                            :class="{ 'is-invalid': formulario.errors.foto }"
                        />
                        <div v-if="formulario.errors.foto" class="invalid-feedback">
                            {{ formulario.errors.foto }}
                        </div>
                        <div v-if="formulario.imagen_url" class="mt-2 text-center">
                            <img :src="formulario.imagen_url" class="img-thumbnail" style="max-height: 120px;" alt="Vista previa de la mascota" />
                        </div>
                    </div>

                    <div class="mb-3 pt-2">
                        <div class="form-check form-switch card p-3 border-light shadow-sm d-flex flex-row align-items-center justify-content-between bg-light border-0">
                            <div class="ms-1">
                                <label class="form-check-label fw-semibold text-secondary small text-uppercase" for="esterilizado">Esterilizado / Castrado</label>
                                <span class="d-block text-muted small mt-1" style="font-size: 0.75rem;">¿Ha sido sometido a cirugía de esterilización?</span>
                            </div>
                            <input
                                id="esterilizado"
                                v-model="formulario.esterilizado"
                                type="checkbox"
                                class="form-check-input ms-0 float-none"
                                role="switch"
                                style="width: 2.8em; height: 1.5em; cursor: pointer;"
                                :class="{ 'is-invalid': formulario.errors.esterilizado }"
                            />
                        </div>
                        <div v-if="formulario.errors.esterilizado" class="invalid-feedback d-block mt-1">
                            {{ formulario.errors.esterilizado }}
                        </div>
                    </div>
                </div>

                <!-- Sección de Ancho Completo al Final: Descripción/Notas Médicas -->
                <div class="col-12 mt-2">
                    <hr class="text-muted opacity-25 my-3">
                    <div class="mb-2">
                        <label for="descripcion" class="form-label fw-semibold text-secondary small text-uppercase">Descripción / Antecedentes Médicos</label>
                        <textarea
                            id="descripcion"
                            v-model="formulario.descripcion"
                            class="form-control bg-light border-0 py-2"
                            :class="{ 'is-invalid': formulario.errors.descripcion }"
                            rows="3"
                            placeholder="Registra condiciones previas, alergias, comportamiento o detalles relevantes de la mascota."
                            required
                        ></textarea>
                        <div v-if="formulario.errors.descripcion" class="invalid-feedback">
                            {{ formulario.errors.descripcion }}
                        </div>
                    </div>
                </div>
            </div>
        </ModalCrud>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from '@/Disenos/LayoutAutenticado.vue';
import { Head, Link } from '@inertiajs/vue3';
import ModalCrud from '@/Componentes/ModalCrud.vue';

export default {
    components: {
        AuthenticatedLayout,
        Head,
        Link,
        ModalCrud
    },
    props: {
        cliente: {
            type: Object,
            required: true
        },
        transacciones: {
            type: Object,
            required: true
        },
        deudaTotal: {
            type: Number,
            default: 0
        },
        transaccionesPendientesCount: {
            type: Number,
            default: 0
        }
    },
    data() {
        return {
            especies: [],
            razas: [],
            mostrarModal: false,
            modoEdicion: false,
            mascotaEditando: null,
            opcionesSexo: [
                { value: 'macho', label: 'Macho' },
                { value: 'hembra', label: 'Hembra' },
            ],
            formulario: {
                nombre: '',
                descripcion: '',
                sexo: '',
                fecha_nacimiento: '',
                especie_id: '',
                raza_id: '',
                cliente_id: '',
                imagen_url: '',
                foto: null,
                peso_kg: '',
                color: '',
                esterilizado: false,
                errors: {},
                processing: false,
            }
        };
    },
    computed: {
        tituloModal() {
            return this.modoEdicion ? 'Editar Mascota' : 'Nueva Mascota';
        }
    },
    methods: {
        formatearDinero(monto) {
            return '$' + Math.round(monto).toLocaleString('es-CL');
        },
        formatearFechaCorta(fechaStr) {
            if (!fechaStr) return 'N/A';
            const f = new Date(fechaStr);
            return f.toLocaleDateString('es-CL', { day: '2-digit', month: 'short', year: 'numeric' });
        },
        obtenerEspecies() {
            axios.get('/especies')
                .then((response) => {
                    this.especies = response.data.especies;
                });
        },
        obtenerRazasPorEspecie(especieId) {
            axios.get(`/razas`, {
                params: {
                    especie_id: especieId
                }
            })
                .then((response) => {
                    this.razas = response.data.razas;
                });
        },
        abrirModalCrear() {
            this.modoEdicion = false;
            this.mascotaEditando = null;
            this.formulario.nombre = '';
            this.formulario.descripcion = '';
            this.formulario.especie_id = '';
            this.formulario.raza_id = '';
            this.formulario.cliente_id = this.cliente.id;
            this.formulario.sexo = '';
            this.formulario.fecha_nacimiento = '';
            this.formulario.imagen_url = '';
            this.formulario.foto = null;
            if (this.$refs.fotoInput) {
                this.$refs.fotoInput.value = '';
            }
            this.formulario.peso_kg = '';
            this.formulario.color = '';
            this.formulario.esterilizado = false;
            this.formulario.errors = {};
            this.mostrarModal = true;
        },
        abrirModalEditar(mascota) {
            this.modoEdicion = true;
            this.mascotaEditando = mascota;
            this.formulario.nombre = mascota.nombre;
            this.formulario.descripcion = mascota.descripcion;
            this.formulario.sexo = mascota.sexo;
            this.formulario.fecha_nacimiento = this.$fechaInput(mascota.fecha_nacimiento);
            this.formulario.especie_id = mascota.raza?.especie_id || '';
            this.formulario.raza_id = mascota.raza_id;
            this.formulario.cliente_id = this.cliente.id;
            this.formulario.imagen_url = mascota.imagen_url;
            this.formulario.foto = null;
            if (this.$refs.fotoInput) {
                this.$refs.fotoInput.value = '';
            }
            this.formulario.peso_kg = mascota.peso_kg ?? '';
            this.formulario.color = mascota.color ?? '';
            this.formulario.esterilizado = !!mascota.esterilizado;
            this.formulario.errors = {};
            if (this.formulario.especie_id) {
                this.obtenerRazasPorEspecie(this.formulario.especie_id);
            }
            this.mostrarModal = true;
        },
        cerrarModal() {
            this.mostrarModal = false;
            this.mascotaEditando = null;
        },
        seleccionarFoto(e) {
            const archivos = e.target.files;
            if (archivos && archivos.length > 0) {
                this.formulario.foto = archivos[0];
            }
        },
        datosFormulario() {
            const formData = new FormData();
            formData.append('nombre', this.formulario.nombre);
            formData.append('descripcion', this.formulario.descripcion);
            formData.append('sexo', this.formulario.sexo);
            if (this.formulario.fecha_nacimiento) {
                formData.append('fecha_nacimiento', this.formulario.fecha_nacimiento);
            }
            formData.append('raza_id', this.formulario.raza_id);
            formData.append('cliente_id', this.formulario.cliente_id);
            if (this.formulario.peso_kg !== '') {
                formData.append('peso_kg', this.formulario.peso_kg);
            }
            if (this.formulario.color) {
                formData.append('color', this.formulario.color);
            }
            formData.append('esterilizado', this.formulario.esterilizado ? '1' : '0');
            
            if (this.formulario.foto) {
                formData.append('foto', this.formulario.foto);
            } else if (this.formulario.imagen_url) {
                formData.append('imagen_url', this.formulario.imagen_url);
            }
            return formData;
        },
        guardar() {
            this.formulario.processing = true;
            this.formulario.errors = {};

            const data = this.datosFormulario();

            if (this.modoEdicion) {
                data.append('_method', 'PUT');
                axios.post(`/api/mascotas/${this.mascotaEditando.id}`, data, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                })
                .then(() => {
                    this.cerrarModal();
                    this.$alertaExito('Mascota actualizada', 'Los cambios se guardaron correctamente.');
                    this.$inertia.reload({ preserveScroll: true });
                })
                .catch((error) => {
                    if (error.response?.status === 422) {
                        this.formulario.errors = error.response.data.errors;
                        this.$alertaValidacion(error.response.data.errors);
                    } else {
                        this.$alertaError('Error', 'No se pudo guardar la mascota.');
                    }
                })
                .finally(() => {
                    this.formulario.processing = false;
                });
            } else {
                axios.post('/api/mascotas', data, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                })
                .then(() => {
                    this.cerrarModal();
                    this.$alertaExito('Mascota creada', 'El registro se guardó correctamente.');
                    this.$inertia.reload({ preserveScroll: true });
                })
                .catch((error) => {
                    if (error.response?.status === 422) {
                        this.formulario.errors = error.response.data.errors;
                        this.$alertaValidacion(error.response.data.errors);
                    } else {
                        this.$alertaError('Error', 'No se pudo crear la mascota.');
                    }
                })
                .finally(() => {
                    this.formulario.processing = false;
                });
            }
        },
        confirmarEliminar(mascota) {
            this.$confirmar('¿Eliminar mascota?', `Se eliminará a ${mascota.nombre}.`)
                .then((resultado) => {
                    if (!resultado.isConfirmed) return;
                    axios.delete(`/api/mascotas/${mascota.id}`)
                        .then(() => {
                            this.$alertaExito('Eliminada', `${mascota.nombre} fue eliminada.`);
                            this.$inertia.reload({ preserveScroll: true });
                        })
                        .catch(() => this.$alertaError('Error', 'No se pudo eliminar la mascota.'));
                });
        }
    },
    mounted() {
        this.obtenerEspecies();
    }
}
</script>

<style scoped>
.hover-shadow:hover {
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    transform: translateY(-2px);
}
.hover-primary:hover {
    color: var(--bs-primary) !important;
}
.transition-all {
    transition: all 0.3s ease;
}
</style>
