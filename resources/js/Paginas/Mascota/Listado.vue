<template>
    <Head title="Mascotas" />
    <AuthenticatedLayout>
        <div class="container py-4">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h1 class="h5 mb-0">Mis Mascotas</h1>


                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <template v-if="$isAdmin() || $isSecretaria()">
                            <a href="/api/export/mascotas" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-download me-1"></i> Exportar
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-primary" @click="mostrarModalImportar = true">
                                <i class="bi bi-upload me-1"></i> Importar Consolidado
                            </button>
                        </template>
                        <button v-if="$isCliente() || $isAdmin() || $isSecretaria()" type="button" class="btn btn-sm btn-primary" @click="abrirModalCrear">
                            + Nueva Mascota
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Barra de búsqueda y filtros -->
                    <BarraFiltros 
                        :deshabilitar-limpiar="!filtros.nombre && !filtros.especie_id && !filtros.raza_id && !filtros.sexo && !filtros.esterilizado" 
                        clase-boton-contenedor="col-12 col-lg-2 d-flex gap-2 justify-content-lg-end"
                        @limpiar="limpiarFiltros()"
                    >
                        <!-- Buscar por Nombre -->
                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label small fw-bold text-secondary mb-1" for="filtroNombre">Buscar por Nombre</label>
                            <input type="text" class="form-control form-control-sm" id="filtroNombre" placeholder="Ej: Toby" v-model="filtros.nombre" @keyup.enter="obtenerMascotas()">
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
                        <template #texto-limpiar>
                            Limpiar
                        </template>
                    </BarraFiltros>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <p v-show="consultadoEn" class="text-muted small mb-0">Actualizado: {{ consultadoEn }}</p>
                        <p v-show="!listaVacia" class="text-muted small mb-0 text-end">
                            {{ totalMascotas }} mascota{{ totalMascotas === 1 ? '' : 's' }} encontrada{{ totalMascotas === 1 ? '' : 's' }}
                            <span v-show="mascotasEsterilizadas > 0"> · {{ mascotasEsterilizadas }} esterilizada{{ mascotasEsterilizadas === 1 ? '' : 's' }}</span>
                        </p>
                    </div>
                    <IndicadorCarga :cargando="cargando" mensaje="mascotas" />

                    <EstadoVacio
                        :visible="!cargando && listaVacia"
                        mensaje="No tienes mascotas registradas aún."
                        texto-boton="Registrar tu primera mascota"
                        icono="bi bi-heart-pulse"
                        @accion="abrirModalCrear"
                    />

                    <SinResultados
                        :visible="!cargando && sinResultadosFiltro"
                        mensaje="Ninguna mascota coincide con la búsqueda."
                        @limpiar="limpiarFiltros()"
                    />

                    <div v-if="!cargando && !listaVacia && !sinResultadosFiltro" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4 mb-4">
                        <div v-for="mascota in mascotasVisibles" :key="mascota.id" class="col">
                            <div class="card h-100 border-0 shadow-sm rounded-4 hover-elevate transition-all cursor-pointer overflow-hidden" @click="irADetalle(mascota.id)">
                                <!-- Encabezado de la tarjeta con color/imagen -->
                                <div class="card-img-top bg-light position-relative" style="height: 100px;">
                                    <div class="w-100 h-100 bg-primary bg-opacity-10 d-flex align-items-center justify-content-center">
                                        <!-- Patrón decorativo de fondo -->
                                        <i class="bi bi-heart-pulse-fill text-primary opacity-25" style="font-size: 3rem; transform: rotate(15deg);"></i>
                                    </div>
                                    <div class="position-absolute top-0 end-0 m-2">
                                        <span v-if="mascota.sexo === 'macho'" class="badge bg-primary bg-opacity-75 rounded-pill p-2 px-3 shadow-sm" title="Macho"><i class="bi bi-gender-male me-1"></i> Macho</span>
                                        <span v-else-if="mascota.sexo === 'hembra'" class="badge bg-danger bg-opacity-75 rounded-pill p-2 px-3 shadow-sm" title="Hembra"><i class="bi bi-gender-female me-1"></i> Hembra</span>
                                    </div>
                                </div>
                                
                                <div class="card-body position-relative pt-4">
                                    <div class="position-absolute top-0 start-50 translate-middle" style="z-index: 2;">
                                        <!-- Avatar circular -->
                                        <div class="bg-white p-1 rounded-circle shadow-sm" style="width: 72px; height: 72px;">
                                            <div class="w-100 h-100 rounded-circle bg-primary bg-gradient text-white d-flex align-items-center justify-content-center fw-bold fs-3 overflow-hidden">
                                                <img v-if="mascota.imagen_url" :src="mascota.imagen_url" class="w-100 h-100 object-fit-cover" />
                                                <span v-else>{{ mascota.nombre.charAt(0).toUpperCase() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="text-center mb-3 mt-3">
                                        <h5 class="fw-bold mb-0 text-dark">{{ mascota.nombre }}</h5>
                                        <small class="text-primary fw-semibold d-block">
                                            <span v-if="mascota.raza">{{ mascota.raza.nombre }}</span>
                                            <span v-else-if="mascota.especie">{{ mascota.especie.nombre }}</span>
                                            <span v-else>Especie N/A</span>
                                        </small>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center small mb-3 bg-light rounded-4 p-2 px-3 border border-light">
                                        <div class="text-center border-end border-2 w-50 border-secondary border-opacity-10 pe-2">
                                            <span class="d-block text-muted" style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.5px;">Edad</span>
                                            <span class="fw-bold text-dark">{{ mascota.edad_texto || edadRelativa(mascota) || '—' }}</span>
                                        </div>
                                        <div class="text-center w-50 ps-2">
                                            <span class="d-block text-muted" style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.5px;">Peso</span>
                                            <span class="fw-bold text-dark">{{ mascota.peso_kg ? mascota.peso_kg + ' kg' : '—' }}</span>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-center gap-2 mb-2 p-2">
                                        <div class="rounded-circle shadow-sm overflow-hidden border border-2 border-white" style="width: 32px; height: 32px;">
                                            <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(mascota.cliente?.usuario?.name || 'S P')}&background=e2e8f0&color=475569&bold=true`" class="w-100 h-100 object-fit-cover" alt="Avatar Cliente" />
                                        </div>
                                        <span class="text-truncate small text-secondary fw-semibold">
                                            {{ mascota.cliente?.usuario?.name || 'Sin propietario' }}
                                        </span>
                                    </div>
                                </div>
                                <div v-if="$isCliente() || $isAdmin() || $isSecretaria()" class="card-footer bg-white border-top border-light pt-3 pb-3 d-flex gap-2">
                                    <button class="btn btn-sm btn-light text-primary flex-grow-1 fw-bold rounded-pill btn-hover-elevate" @click.stop="abrirModalEditar(mascota)">
                                        <i class="bi bi-pencil-square me-1"></i> Editar
                                    </button>
                                    <button class="btn btn-sm btn-light text-danger flex-grow-1 fw-bold rounded-pill btn-hover-elevate" @click.stop="confirmarEliminar(mascota)">
                                        <i class="bi bi-trash3 me-1"></i> Borrar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <Paginador :data="mascotasData" entidad="mascotas" @cambiar-pagina="obtenerMascotas" />
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
                                @change="alCambiarEspecieFormulario"
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

                        <div class="mb-3" v-if="formulario.especie_id">
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
        </div>

        <ModalImportarConsolidado
            :visible="mostrarModalImportar"
            @cerrar="mostrarModalImportar = false"
            @importado="obtenerMascotas()"
        />

    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from '@/Disenos/LayoutAutenticado.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import IndicadorCarga from '@/Componentes/IndicadorCarga.vue';
import EstadoVacio from '@/Componentes/EstadoVacio.vue';
import SinResultados from '@/Componentes/SinResultados.vue';
import ModalImportarConsolidado from '@/Componentes/ModalImportarConsolidado.vue';
import Paginador from '@/Componentes/Paginador.vue';
import ModalCrud from '@/Componentes/ModalCrud.vue';
import BarraFiltros from '@/Componentes/BarraFiltros.vue';

export default {
    components: {
        AuthenticatedLayout,
        Head,
        Link,
        IndicadorCarga,
        EstadoVacio,
        SinResultados,
        ModalImportarConsolidado,
        Paginador,
        ModalCrud,
        BarraFiltros,
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
            mostrarModalImportar: false,
            mostrarModalHistorial: false,
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
            mascotasData: null,
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
                foto: null,
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
            return !this.filtros.nombre && !this.filtros.especie_id && !this.filtros.raza_id && !this.filtros.sexo && this.filtros.esterilizado === '' && this.mascotas.length === 0
        },
        sinResultadosFiltro() {
            return (this.filtros.nombre || this.filtros.especie_id || this.filtros.raza_id || this.filtros.sexo || this.filtros.esterilizado !== '') && this.mascotas.length === 0
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
            this.formulario.foto = null
            if (this.$refs.fotoInput) {
                this.$refs.fotoInput.value = ''
            }
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
            
            const especieId = mascota.especie_id || mascota.raza?.especie_id || ''
            this.formulario.especie_id = especieId
            this.formulario.raza_id = mascota.raza_id || ''
            if (especieId) {
                this.obtenerRazasPorEspecie(especieId)
            } else {
                this.razas = []
            }

            this.formulario.cliente_id = mascota.cliente_id
            this.formulario.imagen_url = mascota.imagen_url
            this.formulario.foto = null
            if (this.$refs.fotoInput) {
                this.$refs.fotoInput.value = ''
            }
            this.formulario.peso_kg = mascota.peso_kg ?? ''
            this.formulario.color = mascota.color ?? ''
            this.formulario.esterilizado = !!mascota.esterilizado
            this.formulario.errors = {}
            this.mostrarModal = true
        },
        alCambiarEspecieFormulario() {
            this.formulario.raza_id = ''
            if (this.formulario.especie_id) {
                this.obtenerRazasPorEspecie(this.formulario.especie_id)
            } else {
                this.razas = []
            }
        },
        cerrarModal() {
            this.mostrarModal = false
            this.mascotaEditando = null
        },
        edadRelativa(mascota) {
            const edad = this.$edadDesde(mascota.fecha_nacimiento)
            return edad ? `${edad}` : ''
        },
        seleccionarFoto(e) {
            const archivos = e.target.files
            if (archivos && archivos.length > 0) {
                this.formulario.foto = archivos[0]
            }
        },
        datosFormulario() {
            const formData = new FormData()
            formData.append('nombre', this.formulario.nombre)
            formData.append('descripcion', this.formulario.descripcion)
            formData.append('sexo', this.formulario.sexo)
            if (this.formulario.fecha_nacimiento) {
                formData.append('fecha_nacimiento', this.formulario.fecha_nacimiento)
            }
            formData.append('raza_id', this.formulario.raza_id)
            formData.append('cliente_id', this.formulario.cliente_id)
            if (this.formulario.peso_kg !== '') {
                formData.append('peso_kg', this.formulario.peso_kg)
            }
            if (this.formulario.color) {
                formData.append('color', this.formulario.color)
            }
            formData.append('esterilizado', this.formulario.esterilizado ? '1' : '0')
            
            if (this.formulario.foto) {
                formData.append('foto', this.formulario.foto)
            } else if (this.formulario.imagen_url) {
                formData.append('imagen_url', this.formulario.imagen_url)
            }
            return formData
        },
        guardar() {
            this.formulario.processing = true
            this.formulario.errors = {}

            const data = this.datosFormulario()

            if (this.modoEdicion) {
                data.append('_method', 'PUT')
                axios.post(`/api/mascotas/${this.mascotaEditando.id}`, data, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                })
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
                axios.post('/api/mascotas', data, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                })
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
        obtenerMascotas(url = '/mascotas'){
            this.cargando = true
            axios.get(url, {
                params: {
                    ...this.filtros
                }
            }).then((response)=>{
                if (response.data.mascotas.data) {
                    this.mascotasData = response.data.mascotas;
                    this.mascotas = response.data.mascotas.data;
                } else {
                    this.mascotasData = null;
                    this.mascotas = response.data.mascotas;
                }
            }).catch((error)=>{
                this.$alertaError('Error', 'No se pudo obtener las mascotas.')
                console.error(error)
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
        irADetalle(id) {
            router.get(`/mascotas/${id}`);
        }
    },
    mounted() {
        this.obtenerEspecies()
        this.obtenerMascotas()
    },
}
</script>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}
.transition-all {
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}
.hover-elevate:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
}
.btn-hover-elevate {
    transition: all 0.2s;
}
.btn-hover-elevate:hover {
    transform: translateY(-2px);
}
</style>
