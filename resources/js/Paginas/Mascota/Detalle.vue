<template>
    <Head :title="'Mascota - ' + (mascota.nombre || 'Detalle')" />
    <AuthenticatedLayout>
        <div class="container py-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center gap-3">
                    <Link :href="route('mascotas.listado')" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                        <i class="bi bi-arrow-left"></i> Volver
                    </Link>
                    <h1 class="h3 mb-0 text-dark fw-bold">Perfil del Paciente</h1>
                </div>
                <div class="d-flex gap-2">
                    <button @click="abrirEditar" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1">
                        <i class="bi bi-pencil"></i> Editar
                    </button>
                    <button @click="confirmarEliminar" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1">
                        <i class="bi bi-trash"></i> Eliminar
                    </button>
                </div>
            </div>

            <div class="row g-4">
                
                <div class="col-lg-4 col-md-5">
                    <div class="card border-0 shadow-sm sticky-top" style="top: 2rem;">
                        <div class="card-body text-center py-4">
                            <div v-if="mascota.imagen_url" class="mx-auto mb-3" style="width: 100px; height: 100px;">
                                <img :src="mascota.imagen_url" alt="Foto de la mascota" class="img-fluid rounded-circle object-fit-cover w-100 h-100 border shadow-sm">
                            </div>
                            <div v-else class="mx-auto bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 90px; height: 90px;">
                                <i class="bi bi-heart-pulse-fill fs-1"></i>
                            </div>
                            
                            <h2 class="h5 mb-1 fw-bold text-dark">{{ mascota.nombre }}</h2>
                            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill small mt-2">Paciente Activo</span>
                        </div>
                        
                        <div class="card-body border-top p-4 bg-light bg-opacity-50">
                            <h3 class="h6 text-uppercase text-muted fw-bold mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Información General</h3>
                            
                            <div class="d-flex align-items-start gap-3 mb-4">
                                <div class="bg-white p-2 rounded shadow-sm text-primary">
                                    <i class="bi bi-person-badge fs-5"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-medium mb-1">Dueño (Cliente)</div>
                                        <Link v-if='cliente':href="route('clientes.detalle', cliente.id)" class="text-decoration-none fw-bold text-primary hover-primary">
                                            {{ cliente?.name }} <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                                        </Link>
                                        <span v-else class="text-dark fw-medium">No asignado</span>
                                    </div>
                                    
                            </div>

                            <div class="d-flex align-items-start gap-3 mb-4">
                                <div class="bg-white p-2 rounded shadow-sm text-primary">
                                    <i class="bi bi-clipboard2-pulse fs-5"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-medium mb-1">Especie y Raza</div>
                                    <span class="text-dark fw-medium d-block">
                                        {{ especie?.nombre || 'Especie N/A' }} 
                                        <span class="text-muted mx-1">•</span> 
                                        {{ raza?.nombre || 'Raza N/A' }}
                                    </span>
                                </div>
                            </div>

                            <div class="d-flex align-items-start gap-3">
                                <div class="bg-white p-2 rounded shadow-sm text-primary">
                                    <i class="bi bi-gender-ambiguous fs-5"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-medium mb-1">Sexo</div>
                                    <span class="text-dark fw-medium">{{ mascota.sexo || 'No especificado' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top p-3 text-center">
                            <small class="text-muted"><i class="bi bi-clock-history me-1"></i> Registrado el: {{ formatearFecha(mascota.created_at) }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8 col-md-7">
                    <!-- Citas Próximas -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                            <h3 class="h6 mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                                <i class="bi bi-calendar-event text-primary"></i> Citas Próximas
                            </h3>
                            <button class="btn btn-sm btn-primary d-flex align-items-center gap-1">
                                <i class="bi bi-plus-lg"></i> Nueva Cita
                            </button>
                        </div>
                        <div class="card-body p-3">
                            <div v-if="!proximasCitas || proximasCitas.length === 0" class="py-4 text-center text-muted">
                                <i class="bi bi-calendar-x fs-1 mb-3 d-block" style="color: #dee2e6;"></i>
                                <p class="mb-0">No hay citas programadas próximamente.</p>
                            </div>
                            <div v-else class="d-flex flex-column gap-3">
                                <div
                                    v-for="cita in proximasCitas"
                                    :key="cita.id"
                                    class="border rounded-3 p-3 bg-white shadow-sm cita-card"
                                >
                                    <!-- Cabecera: Título + Badge de Estado -->
                                    <Link :href="route('citas.detalle', cita.id)">
                                        <div class="d-flex align-items-start justify-content-between mb-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-2">
                                                    <i class="bi bi-calendar-event fs-6"></i>
                                                </div>
                                                <span class="fw-bold text-dark">{{ cita.titulo }}</span>
                                            </div>
                                            <span class="badge rounded-pill px-3 py-2" :class="{
                                                'bg-warning text-dark': cita.estado === 'pendiente',
                                                'bg-success':           cita.estado === 'completada',
                                                'bg-danger':            cita.estado === 'cancelada',
                                                'bg-primary':           cita.estado === 'en_curso',
                                            }">
                                                {{ cita.estado ? cita.estado.charAt(0).toUpperCase() + cita.estado.slice(1) : 'Pendiente' }}
                                            </span>
                                        </div>

                                        <!-- Detalles en fila -->
                                        <div class="row g-2 ps-1 mt-1">
                                            <div class="col-12 col-md-6 d-flex align-items-center gap-2 text-muted small">
                                                <i class="bi bi-clock text-primary"></i>
                                                <span>{{ cita.fecha_hora }}</span>
                                            </div>
                                            <div class="col-12 col-md-6 d-flex align-items-center gap-2 text-muted small">
                                                <i class="bi bi-heart-pulse text-danger"></i>
                                                <span>{{ cita.veterinario?.usuario?.name || 'Sin veterinario' }}</span>
                                            </div>
                                            <div class="col-12 col-md-6 d-flex align-items-center gap-2 text-muted small">
                                                <i class="bi bi-building text-secondary"></i>
                                                <span>{{ cita.box?.sucursal?.nombre || 'Sin sucursal' }}</span>
                                            </div>
                                            <div class="col-12 col-md-6 d-flex align-items-center gap-2 text-muted small">
                                                <i class="bi bi-door-open text-secondary"></i>
                                                <span>{{ cita.box?.nombre || 'Sin box' }}</span>
                                            </div>
                                        </div>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Historial Médico -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                      <div class="card-body p-3">
                            <div v-if="!historialClinico || !historialClinico.data || historialClinico.data.length === 0" class="py-4 text-center text-muted">
                                <i class="bi bi-calendar-x fs-1 mb-3 d-block" style="color: #dee2e6;"></i>
                                <p class="mb-0">No hay historial clínico.</p>
                            </div>
                            <div v-else class="d-flex flex-column gap-3">
                                <h3 class="h6 text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">Historial Clínico</h3>
                                <div
                                    v-for="cita in historialClinico.data"
                                    :key="cita.id"
                                    class="border rounded-3 p-3 bg-white shadow-sm cita-card"
                                >
                                    <!-- Cabecera: Título + Badge de Estado -->
                                    <Link :href="route('citas.detalle', cita.id)">
                                        <div class="d-flex align-items-start justify-content-between mb-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-2">
                                                    <i class="bi bi-calendar-event fs-6"></i>
                                                </div>
                                                <span class="fw-bold text-dark">{{ cita.titulo }}</span>
                                            </div>
                                            <span class="badge rounded-pill px-3 py-2" :class="{
                                                'bg-warning text-dark': cita.estado === 'pendiente',
                                                'bg-success':           cita.estado === 'completada',
                                                'bg-danger':            cita.estado === 'cancelada',
                                                'bg-primary':           cita.estado === 'en_curso',
                                            }">
                                                {{ cita.estado ? cita.estado.charAt(0).toUpperCase() + cita.estado.slice(1) : 'Pendiente' }}
                                            </span>
                                        </div>

                                        <!-- Detalles en fila -->
                                        <div class="row g-2 ps-1 mt-1">
                                            <div class="col-12 col-md-6 d-flex align-items-center gap-2 text-muted small">
                                                <i class="bi bi-clock text-primary"></i>
                                                <span>{{ cita.fecha_hora }}</span>
                                            </div>
                                            <div class="col-12 col-md-6 d-flex align-items-center gap-2 text-muted small">
                                                <i class="bi bi-heart-pulse text-danger"></i>
                                                <span>{{ cita.veterinario?.usuario?.name || 'Sin veterinario' }}</span>
                                            </div>
                                            <div class="col-12 col-md-6 d-flex align-items-center gap-2 text-muted small">
                                                <i class="bi bi-building text-secondary"></i>
                                                <span>{{ cita.box?.sucursal?.nombre || 'Sin sucursal' }}</span>
                                            </div>
                                            <div class="col-12 col-md-6 d-flex align-items-center gap-2 text-muted small">
                                                <i class="bi bi-door-open text-secondary"></i>
                                                <span>{{ cita.box?.nombre || 'Sin box' }}</span>
                                            </div>
                                        </div>
                                    </Link>
                                </div>
                                
                                <!-- Controles de Paginación -->
                                <div v-if="historialClinico.last_page > 1" class="d-flex justify-content-between align-items-center mt-3 border-top pt-3">
                                    <div class="text-muted small">
                                        Mostrando {{ historialClinico.from }} a {{ historialClinico.to }} de {{ historialClinico.total }} citas
                                    </div>
                                    <nav aria-label="Navegación de páginas">
                                        <ul class="pagination pagination-sm mb-0">
                                            <li class="page-item" :class="{ disabled: !historialClinico.prev_page_url }">
                                                <Link class="page-link" :href="historialClinico.prev_page_url || '#'">Anterior</Link>
                                            </li>
                                            <li 
                                                v-for="link in historialClinico.links.slice(1, -1)" 
                                                :key="link.label" 
                                                class="page-item" 
                                                :class="{ active: link.active }"
                                            >
                                                <Link class="page-link" :href="link.url || '#'" v-html="link.label"></Link>
                                            </li>
                                            <li class="page-item" :class="{ disabled: !historialClinico.next_page_url }">
                                                <Link class="page-link" :href="historialClinico.next_page_url || '#'">Siguiente</Link>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                      </div>

                        </div>
                    </div>
                </div>            
            </div>
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
                            <label for="cliente_id" class="form-label fw-semibold text-secondary small text-uppercase">Propietario / Cliente</label>
                            <select
                                id="cliente_id"
                                v-model="formulario.cliente_id"
                                class="form-select bg-light border-0 py-2"
                                :class="{ 'is-invalid': formulario.errors.cliente_id }"
                                required
                            >
                                <option value="" disabled>Seleccione un propietario</option>
                                <option
                                    v-for="cliente in clientes"
                                    :key="cliente.id"
                                    :value="cliente.id"
                                >
                                    {{ cliente.usuario?.name || 'Cliente sin nombre' }}
                                </option>
                            </select>
                            <div v-if="formulario.errors.cliente_id" class="invalid-feedback">
                                {{ formulario.errors.cliente_id }}
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
                            <label for="imagen_url" class="form-label fw-semibold text-secondary small text-uppercase">Imagen (URL)</label>
                            <input
                                id="imagen_url"
                                type="text"
                                name="imagen_url"
                                v-model="formulario.imagen_url"
                                class="form-control bg-light border-0 py-2"
                                placeholder="https://ejemplo.com/foto.jpg"
                                :class="{ 'is-invalid': formulario.errors.imagen_url }"
                            />
                            <div v-if="formulario.errors.imagen_url" class="invalid-feedback">
                                {{ formulario.errors.imagen_url }}
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
        </div>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from '@/Disenos/LayoutAutenticado.vue';
import { Head, Link } from '@inertiajs/vue3';
import ModalCrud from '@/Componentes/ModalCrud.vue';

export default {
    name: 'MascotaDetalle',
    components: {
        AuthenticatedLayout,
        Head,
        Link,
        ModalCrud
    },
    props: {
        proximasCitas: {
            type: Array,
            required: true
        },
        historialClinico: {
            type: Object,
            required: true
        },

        mascota: {
            type: Object,
            required: true
        },
        cliente: {
            type: Object,
            required: true
        },
        especie:{
            type:Object,
            required: true
        },
        raza:{
            type: Object,
            required: true
        }
    },
    computed: {
        tituloModal() {
            return this.modoEdicion ? 'Editar Mascota' : 'Nueva Mascota'
        }
    },
    methods: {
        abrirEditar() {
            this.modoEdicion = true;
            this.mascotaEditando = this.mascota;
            this.formulario.nombre = this.mascota.nombre;
            this.formulario.descripcion = this.mascota.descripcion;
            this.formulario.sexo = this.mascota.sexo == 'Hembra' ? 'hembra' : 'macho';
            this.formulario.fecha_nacimiento = this.$fechaInput(this.mascota.fecha_nacimiento);
            this.formulario.especie_id = this.mascota.raza?.especie_id || '';
            this.formulario.raza_id = this.mascota.raza_id ?? '';
            this.formulario.cliente_id = this.mascota.cliente_id ?? '';
            this.formulario.imagen_url = this.mascota.imagen_url ?? '';
            this.formulario.peso_kg = this.mascota.peso_kg ?? '';
            this.formulario.color = this.mascota.color ?? '';
            this.formulario.esterilizado = !!this.mascota.esterilizado;
            this.formulario.errors = {};
            
            this.obtenerEspecies();
            this.obtenerClientes();
            if (this.formulario.especie_id) {
                this.obtenerRazasPorEspecie(this.formulario.especie_id);
            }
            this.mostrarModal = true;
        },
        cerrarModal() {
            this.mostrarModal = false;
            this.mascotaEditando = null;
        },
        obtenerEspecies() {
            axios.get('/especies')
                .then((response) => {
                    this.especies = response.data.especies;
                });
        },
        obtenerRazasPorEspecie(especieId) {
            if (!especieId) {
                this.razas = [];
                return;
            }
            axios.get(`/razas`, {
                params: {
                    especie_id: especieId
                }
            })
                .then((response) => {
                    this.razas = response.data.razas;
                });
        },
        obtenerClientes() {
            const user = this.$page.props.auth.user;
            if (user && (user.rol.nombre_interno === 'admin' || user.rol.nombre_interno === 'veterinario')) {
                axios.get('/api/clientes')
                    .then((response) => {
                        this.clientes = response.data.clientes || response.data;
                    });
            } else {
                this.clientes = [{
                    id: this.mascota.cliente_id,
                    usuario: {
                        name: this.cliente?.name || 'Propietario'
                    }
                }];
            }
        },
        datosFormulario() {
            return {
                nombre: this.formulario.nombre,
                descripcion: this.formulario.descripcion,
                sexo: this.formulario.sexo,
                fecha_nacimiento: this.formulario.fecha_nacimiento || null,
                raza_id: this.formulario.raza_id,
                cliente_id: this.formulario.cliente_id,
                imagen_url: this.formulario.imagen_url,
                peso_kg: this.formulario.peso_kg === '' ? null : this.formulario.peso_kg,
                color: this.formulario.color || null,
                esterilizado: this.formulario.esterilizado,
            };
        },
        guardar() {
            this.formulario.processing = true;
            this.formulario.errors = {};

            axios.put(`/api/mascotas/${this.mascotaEditando.id}`, this.datosFormulario())
                .then(() => {
                    this.cerrarModal();
                    return this.$alertaExito('Mascota actualizada', 'Los cambios se guardaron correctamente.');
                })
                .then(() => {
                    this.$inertia.reload();
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
        },
        confirmarEliminar() {
            this.$confirmar('¿Eliminar mascota?', `Se eliminará a ${this.mascota.nombre}.`)
                .then((resultado) => {
                    if (!resultado.isConfirmed) return;
                    axios.delete(`/api/mascotas/${this.mascota.id}`)
                        .then(() => {
                            this.$alertaExito('Eliminada', `${this.mascota.nombre} fue eliminada.`);
                            this.$inertia.visit(route('mascotas.listado'));
                        })
                        .catch(() => this.$alertaError('Error', 'No se pudo eliminar la mascota.'));
                });
        },
        formatearFecha(fecha) {
            if (!fecha) return 'Sin fecha';
            const date = new Date(fecha);
            return date.toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }
    },
    data() {
        return {
            mostrarModal: false,
            modoEdicion: false,
            mascotaEditando: null,
            especies: [],
            razas: [],
            clientes: [],
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
                peso_kg: '',
                color: '',
                esterilizado: false,
                errors: {},
                processing: false,
            }
        };
    }
}
</script>

<style scoped>
.hover-shadow:hover {
    box-shadow: 0 .25rem .75rem rgba(0,0,0,.05) !important;
}
.transition {
    transition: all 0.2s ease-in-out;
}
.hover-primary:hover {
    color: var(--bs-primary) !important;
}
.border-dashed {
    border-style: dashed !important;
}
/* Para la imagen redonda si la mascota tiene foto */
.object-fit-cover {
    object-fit: cover;
}
/* Hover effect para las tarjetas de cita */
.cita-card {
    transition: box-shadow 0.2s ease-in-out, transform 0.15s ease-in-out;
    cursor: default;
}
.cita-card:hover {
    box-shadow: 0 .35rem 1rem rgba(0,0,0,.08) !important;
    transform: translateY(-1px);
}
</style>