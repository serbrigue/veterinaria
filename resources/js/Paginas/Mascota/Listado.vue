<template>
    <Head title="Mascotas" />
    <AuthenticatedLayout>
        <div class="container py-4">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h1 class="h5 mb-0">Mis Mascotas</h1>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <button type="button" class="btn btn-sm btn-primary" @click="abrirModalCrear">
                            + Nueva Mascota
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Barra de búsqueda y filtros -->
                    <div class="bg-light p-3 rounded-3 border mb-4 shadow-sm">
                        <div class="row g-3 align-items-end">
                            <!-- Buscar por Nombre -->
                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label small fw-bold text-secondary mb-1" for="filtroNombre">Buscar por Nombre</label>
                                <input type="text" class="form-control form-control-sm" id="filtroNombre" placeholder="Ej: Toby" v-model="filtros.nombre" @keyup.enter="obtenerMascotas">
                            </div>
                            
                            <!-- Buscar por Especie -->
                            <div class="col-12 col-md-4 col-lg-2">
                                <label class="form-label small fw-bold text-secondary mb-1" for="filtroEspecie">Especie</label>
                                <select class="form-select form-select-sm" id="filtroEspecie" v-model="filtros.especie_id" @change="alCambiarFiltroEspecie">
                                    <option value="">Todas</option>
                                    <option v-for="especie in especies" :key="especie.id" :value="especie.id">
                                        {{ especie.nombre }}
                                    </option>
                                </select>
                            </div>

                            <!-- Buscar por Raza -->
                            <div class="col-12 col-md-4 col-lg-2">
                                <label class="form-label small fw-bold text-secondary mb-1" for="filtroRaza">Raza</label>
                                <select class="form-select form-select-sm" id="filtroRaza" v-model="filtros.raza_id" :disabled="!filtros.especie_id" @change="obtenerMascotas()">
                                    <option value="">Todas</option>
                                    <option v-for="raza in razasFiltro" :key="raza.id" :value="raza.id">
                                        {{ raza.nombre }}
                                    </option>
                                </select>
                            </div>

                            <!-- Buscar por Sexo -->
                            <div class="col-12 col-md-4 col-lg-1">
                                <label class="form-label small fw-bold text-secondary mb-1" for="filtroSexo">Sexo</label>
                                <select class="form-select form-select-sm" id="filtroSexo" v-model="filtros.sexo" @change="obtenerMascotas()">
                                    <option value="">Todos</option>
                                    <option v-for="op in opcionesSexo" :key="op.value" :value="op.value">{{ op.label }}</option>
                                </select>
                            </div>

                            <!-- Buscar por Esterilizado -->
                            <div class="col-12 col-md-4 col-lg-2">
                                <label class="form-label small fw-bold text-secondary mb-1" for="filtroEsterilizado">Esterilizado</label>
                                <select class="form-select form-select-sm" id="filtroEsterilizado" v-model="filtros.esterilizado" @change="obtenerMascotas()">
                                    <option value="">Todos</option>
                                    <option value="1">Sí</option>
                                    <option value="0">No</option>
                                </select>
                            </div>

                            <!-- Limpiar Filtros -->
                            <div class="col-12 col-lg-2 d-flex gap-2 justify-content-lg-end">
                                <button class="btn btn-outline-secondary btn-sm w-100" @click="limpiarFiltros()">
                                    Limpiar
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <p v-show="consultadoEn" class="text-muted small mb-0">Actualizado: {{ consultadoEn }}</p>
                        <p v-show="!listaVacia" class="text-muted small mb-0 text-end">
                            {{ totalMascotas }} mascota{{ totalMascotas === 1 ? '' : 's' }} encontrada{{ totalMascotas === 1 ? '' : 's' }}
                            <span v-show="mascotasEsterilizadas > 0"> · {{ mascotasEsterilizadas }} esterilizada{{ mascotasEsterilizadas === 1 ? '' : 's' }}</span>
                        </p>
                    </div>
                    <div v-if="cargando" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted">Cargando mascotas...</p>
                    </div>

                    <!-- Estado vacío -->
                    <div v-else-if="listaVacia" class="text-center py-5">
                        <p class="text-muted mb-3">No tienes mascotas registradas aún.</p>
                        <button type="button" class="btn btn-primary" @click="abrirModalCrear">
                            Registrar tu primera mascota
                        </button>
                    </div>

                    <div v-else-if="sinResultadosFiltro" class="text-center py-5">
                        <p class="text-muted mb-3">Ninguna mascota coincide con el filtro.</p>
                        <button type="button" class="btn btn-outline-secondary btn-sm" @click="limpiarFiltros()">
                            Quitar filtro
                        </button>
                    </div>

                    <div v-else class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Sexo</th>
                                    <th>Nacimiento</th>
                                    <th>Raza</th>
                                    <th>Cliente</th>
                                    <th>Peso (kg)</th>
                                    <th>Color</th>
                                    <th>Esterilizado</th>
                                    <th style="width: 180px">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="mascota in mascotasVisibles" :key="mascota.id">
                                    <td>
                                        <Link :href="`/mascotas/${mascota.id}`">
                                            {{ mascota.nombre }}
                                        </Link>
                                    </td>
                                    <td>{{ mascota.sexo }}</td>
                                    <td>
                                        {{ mascota.fecha_nacimiento_formato || $formatoFecha(mascota.fecha_nacimiento) }}
                                        <span v-show="mascota.edad_texto" class="text-muted small d-block">{{ mascota.edad_texto }}</span>
                                        <span v-show="!mascota.edad_texto && edadRelativa(mascota)" class="text-muted small d-block">{{ edadRelativa(mascota) }}</span>
                                    </td>
                                    <td>
                                        <span v-if="mascota.raza">{{ mascota.raza.nombre }}</span>
                                        <span v-else-if="mascota.especie">{{ mascota.especie.nombre }}</span>
                                        <span v-else>—</span>
                                    </td>
                                    <td>{{ mascota.cliente?.usuario?.name || '—' }}</td>
                                    <td>{{ mascota.peso_kg != null ? mascota.peso_kg : '—' }}</td>
                                    <td>{{ mascota.color || '—' }}</td>
                                    <td>{{ mascota.esterilizado ? 'Sí' : 'No' }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button
                                                type="button"
                                                class="btn btn-primary"
                                                @click="abrirModalEditar(mascota)"
                                            >
                                                Editar
                                            </button>
                                            <button
                                                type="button"
                                                class="btn btn-danger"
                                                @click="confirmarEliminar(mascota)"
                                            >
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

            <!-- ========================================== -->
            <!-- MODAL: Crear / Editar Mascota              -->
            <!-- ========================================== -->
            <div v-if="mostrarModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content shadow border-0">
                        <div class="modal-header border-bottom bg-light py-3 px-4">
                            <h5 class="modal-title fw-bold text-dark">{{ tituloModal }}</h5>
                            <button type="button" class="btn-close" @click="cerrarModal"></button>
                        </div>
                        <div>
                            <div class="modal-body p-4">
                                <div class="row g-4">
                                    <!-- Columna Izquierda: Identificación y Clasificación -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nombre" class="form-label fw-semibold text-secondary">Nombre de la Mascota</label>
                                            <input
                                                id="nombre"
                                                v-model="formulario.nombre"
                                                type="text"
                                                class="form-control form-control-lg"
                                                placeholder="Ej: Garfield"
                                                :class="{ 'is-invalid': formulario.errors.nombre }"
                                                required
                                            />
                                            <div v-if="formulario.errors.nombre" class="invalid-feedback">
                                                {{ formulario.errors.nombre }}
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="cliente_id" class="form-label fw-semibold text-secondary">Propietario / Cliente</label>
                                            <select
                                                id="cliente_id"
                                                v-model="formulario.cliente_id"
                                                class="form-select"
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
                                            <label for="especie_id" class="form-label fw-semibold text-secondary">Especie</label>
                                            <select
                                                id="especie_id"
                                                v-model="formulario.especie_id"
                                                class="form-select"
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
                                            <label for="raza_id" class="form-label fw-semibold text-secondary">Raza</label>
                                            <select
                                                id="raza_id"
                                                v-model="formulario.raza_id"
                                                class="form-select"
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
                                            <label for="sexo" class="form-label fw-semibold text-secondary">Sexo</label>
                                            <select
                                                id="sexo"
                                                v-model="formulario.sexo"
                                                class="form-select"
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
                                            <label for="fecha_nacimiento" class="form-label fw-semibold text-secondary">Fecha de Nacimiento</label>
                                            <input
                                                id="fecha_nacimiento"
                                                v-model="formulario.fecha_nacimiento"
                                                type="date"
                                                class="form-control"
                                                :class="{ 'is-invalid': formulario.errors.fecha_nacimiento }"
                                            />
                                            <div v-if="formulario.errors.fecha_nacimiento" class="invalid-feedback">
                                                {{ formulario.errors.fecha_nacimiento }}
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="peso_kg" class="form-label fw-semibold text-secondary">Peso (kg)</label>
                                            <input
                                                id="peso_kg"
                                                v-model="formulario.peso_kg"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                class="form-control"
                                                placeholder="Ej: 5.40"
                                                :class="{ 'is-invalid': formulario.errors.peso_kg }"
                                            />
                                            <div v-if="formulario.errors.peso_kg" class="invalid-feedback">
                                                {{ formulario.errors.peso_kg }}
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="color" class="form-label fw-semibold text-secondary">Color / Pelaje</label>
                                            <input
                                                id="color"
                                                v-model="formulario.color"
                                                type="text"
                                                class="form-control"
                                                placeholder="Ej: Blanco con manchas negras"
                                                :class="{ 'is-invalid': formulario.errors.color }"
                                            />
                                            <div v-if="formulario.errors.color" class="invalid-feedback">
                                                {{ formulario.errors.color }}
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="imagen_url" class="form-label fw-semibold text-secondary">Imagen de la Mascota (URL)</label>
                                            <input
                                                id="imagen_url"
                                                type="text"
                                                name="imagen_url"
                                                v-model="formulario.imagen_url"
                                                class="form-control"
                                                placeholder="https://ejemplo.com/foto.jpg"
                                                :class="{ 'is-invalid': formulario.errors.imagen_url }"
                                            />
                                            <div v-if="formulario.errors.imagen_url" class="invalid-feedback">
                                                {{ formulario.errors.imagen_url }}
                                            </div>
                                        </div>

                                        <div class="mb-3 pt-2">
                                            <div class="form-check form-switch card p-3 border-light shadow-sm d-flex flex-row align-items-center justify-content-between">
                                                <div class="ms-1">
                                                    <label class="form-check-label fw-semibold text-secondary" for="esterilizado">Esterilizado / Castrado</label>
                                                    <span class="d-block text-muted small mt-1">¿Ha sido sometido a cirugía de esterilización?</span>
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
                                            <label for="descripcion" class="form-label fw-semibold text-secondary">Descripción / Antecedentes Médicos</label>
                                            <textarea
                                                id="descripcion"
                                                v-model="formulario.descripcion"
                                                class="form-control"
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
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" @click="cerrarModal">
                                    Cancelar
                                </button>
                                <button type="button" class="btn btn-primary" :disabled="formulario.processing" @click="guardar">
                                    <span v-if="formulario.processing" class="spinner-border spinner-border-sm me-2"></span>
                                    {{ textoBotonGuardar }}
                                </button>
                            </div>
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

export default {
    components: {
        AuthenticatedLayout,
        Head,
        Link,
    },
    props: {
        clientes: {
            type: Array,
            default: () => [],
        },
        consultadoEn: {
            type: String,
            default: '',
        },
    },
    data() {
        return {
            especies: [],
            razas: [], // Para el modal de crear/editar
            razasFiltro: [], // Para el filtro
            cargando: false,
            mostrarModal: false,
            modoEdicion: false,
            mascotaEditando: null,
            filtros: {
                nombre: '',
                especie_id: '',
                raza_id: '',
                sexo: '',
                esterilizado: ''
            },
            opcionesSexo: [
                { value: 'macho', label: 'Macho' },
                { value: 'hembra', label: 'Hembra' },
            ],
            mascotas:[],
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
            },
        }
    },
    computed: {
        mascotasVisibles() {
            return this.mascotas
        },
        totalMascotas() {
            return this.mascotasVisibles.length
        },
        listaVacia() {
            return this.mascotas.length === 0
        },
        sinResultadosFiltro() {
            return this.mascotasVisibles.length === 0 && (this.filtros.nombre || this.filtros.especie_id || this.filtros.sexo || this.filtros.esterilizado !== '')
        },
        mascotasEsterilizadas() {
            return this.mascotasVisibles.filter((m) => m.esterilizado).length
        },
        tituloModal() {
            return this.modoEdicion ? 'Editar Mascota' : 'Nueva Mascota'
        },
        textoBotonGuardar() {
            return this.modoEdicion ? 'Guardar cambios' : 'Crear mascota'
        },
    },
    methods: {
        abrirModalCrear() {
            this.modoEdicion = false
            this.mascotaEditando = null
            this.formulario.nombre = ''
            this.formulario.descripcion = ''
            this.formulario.especie_id = ''
            this.formulario.raza_id = ''
            this.formulario.cliente_id = ''
            this.formulario.sexo = ''
            this.formulario.fecha_nacimiento = ''
            this.formulario.imagen_url = ''
            this.formulario.peso_kg = ''
            this.formulario.color = ''
            this.formulario.esterilizado = false
            this.formulario.errors = {}
            this.mostrarModal = true
        },
        abrirModalEditar(mascota) {
            this.modoEdicion = true
            this.mascotaEditando = mascota
            this.formulario.nombre = mascota.nombre
            this.formulario.descripcion = mascota.descripcion
            this.formulario.sexo = mascota.sexo
            this.formulario.fecha_nacimiento = this.$fechaInput(mascota.fecha_nacimiento)
            this.formulario.especie_id = mascota.especie_id
            this.formulario.raza_id = mascota.raza_id
            this.formulario.cliente_id = mascota.cliente_id
            this.formulario.imagen_url = mascota.imagen_url
            this.formulario.peso_kg = mascota.peso_kg ?? ''
            this.formulario.color = mascota.color ?? ''
            this.formulario.esterilizado = !!mascota.esterilizado
            this.formulario.errors = {}
            this.mostrarModal = true
        },
        cerrarModal() {
            this.mostrarModal = false
            this.mascotaEditando = null
        },
        edadRelativa(mascota) {
            const edad = this.$edadDesde(mascota.fecha_nacimiento)
            return edad ? `${edad}` : ''
        },
        datosFormulario() {
            return {
                nombre: this.formulario.nombre,
                descripcion: this.formulario.descripcion,
                sexo: this.formulario.sexo,
                fecha_nacimiento: this.formulario.fecha_nacimiento || null,
                raza_id: this.formulario.especie_id,
                cliente_id: this.formulario.cliente_id,
                imagen_url: this.formulario.imagen_url,
                peso_kg: this.formulario.peso_kg === '' ? null : this.formulario.peso_kg,
                color: this.formulario.color || null,
                esterilizado: this.formulario.esterilizado,
            }
        },
        guardar() {
            this.formulario.processing = true
            this.formulario.errors = {}

            if (this.modoEdicion) {
                axios.put(`/api/mascotas/${this.mascotaEditando.id}`, this.datosFormulario())
                .then(() => {
                    this.cerrarModal()
                    return this.$alertaExito('Mascota actualizada', 'Los cambios se guardaron correctamente.')
                })
                .then(() => this.obtenerMascotas())
                .catch((error) => {
                    if (error.response?.status === 422) {
                        this.formulario.errors = error.response.data.errors
                        this.$alertaValidacion(error.response.data.errors)
                    } else {
                        this.$alertaError('Error', 'No se pudo guardar la mascota.')
                    }
                })
                .finally(() => {
                    this.formulario.processing = false
                })
            } else {
                axios.post('/api/mascotas', this.datosFormulario())
                .then(() => {
                    this.cerrarModal()
                    return this.$alertaExito('Mascota creada', 'El registro se guardó correctamente.')
                })
                .then(() => this.obtenerMascotas())
                .catch((error) => {
                    if (error.response?.status === 422) {
                        this.formulario.errors = error.response.data.errors
                        this.$alertaValidacion(error.response.data.errors)
                    } else {
                        this.$alertaError('Error', 'No se pudo crear la mascota.')
                    }
                })
                .finally(() => {
                    this.formulario.processing = false
                })
            }
        },
        obtenerMascotas(){
            this.cargando = true
            axios.get(`/mascotas`, {
                params: {
                    ...this.filtros
                }
            }).then((response)=>{
                this.mascotas = response.data.mascotas
            }).catch((error)=>{
                this.$alertaError('Error', 'No se pudo obtener las mascotas.')
            }).finally(() => {
                this.cargando = false
            })
        },
        limpiarFiltros() {
            this.filtros = {
                nombre: '',
                especie_id: '',
                raza_id: '',
                sexo: '',
                esterilizado: ''
            }
            this.razasFiltro = []
            this.obtenerMascotas()
        },
        alCambiarFiltroEspecie() {
            this.filtros.raza_id = ''
            if (this.filtros.especie_id) {
                axios.get(`/razas`, { params: { especie_id: this.filtros.especie_id } })
                    .then(response => {
                        this.razasFiltro = response.data.razas
                    })
            } else {
                this.razasFiltro = []
            }
            this.obtenerMascotas()
        },
        obtenerEspecies() {
            axios.get('/especies')
                .then((response) => {
                    this.especies = response.data.especies
                })
        },
        obtenerRazasPorEspecie(especieId) {
            axios.get(`/razas`, {
                params: {
                    especie_id: especieId
                }
            })
                .then((response) => {
                    this.razas = response.data.razas
                })
        },
        confirmarEliminar(mascota) {
            this.$confirmar('¿Eliminar mascota?', `Se eliminará a ${mascota.nombre}.`)
                .then((resultado) => {
                    if (!resultado.isConfirmed) return
                    axios.delete(`/api/mascotas/${mascota.id}`)
                        .then(() => this.$alertaExito('Eliminada', `${mascota.nombre} fue eliminada.`))
                        .then(() => this.obtenerMascotas())
                        .catch(() => this.$alertaError('Error', 'No se pudo eliminar la mascota.'))
                })
        },
    },
    mounted() {
        this.obtenerEspecies()
        this.obtenerMascotas()
    },
}
</script>
