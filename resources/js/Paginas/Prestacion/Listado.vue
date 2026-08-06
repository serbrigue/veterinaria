<template>
    <!-- ================================================================================== -->
    <!-- COMPONENTE: Listado -->
    <!-- ================================================================================== -->
    <Head title="Prestaciones" />
    <AuthenticatedLayout>

        <div class="container py-4">
            <div class="card shadow-sm">
                <div class="card-header border-0 bg-white p-4 d-flex justify-content-between align-items-center flex-wrap gap-3 rounded-top-4 border-bottom border-light">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary bg-opacity-10 p-2 rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px;">
                            <img src="/images/icon_services.png" alt="Icono Prestaciones" class="w-100 h-100 object-fit-contain" style="transform: scale(1.15);">
                        </div>
                        <h1 class="h4 mb-0 fw-bold text-dark">Catálogo de Prestaciones</h1>
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <!-- DIRECTIVA (v-if): Renderizado condicional basado en "$isAdmin() || $isSecretaria()" -->
                        <template v-if="$isAdmin() || $isSecretaria()">
                            <a href="/api/export/prestaciones" class="btn btn-light text-success fw-bold rounded-pill shadow-sm btn-hover-elevate">
                                <i class="bi bi-download me-1"></i> Exportar
                            </a>
                            <!-- EVENTO (@click): Dispara la acción "mostrarModalImportar = true" -->
                            <button type="button" class="btn btn-light text-primary fw-bold rounded-pill shadow-sm btn-hover-elevate" @click="mostrarModalImportar = true">
                                <i class="bi bi-upload me-1"></i> Importar
                            </button>
                        </template>
                        <!-- DIRECTIVA (v-if): Renderizado condicional basado en "esAdmin" -->
                        <!-- EVENTO (@click): Dispara la acción "abrirModalCrear" -->
                        <button v-if="$isAdmin()" type="button" class="btn btn-primary fw-bold rounded-pill shadow-sm btn-hover-elevate px-4" @click="abrirModalCrear">
                            <i class="bi bi-plus-lg me-1"></i> Nueva Prestación
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Barra de búsqueda y filtros -->
                    <BarraFiltros
                        :deshabilitar-limpiar="!filtros.especialidad_id && !filtros.sucursal_id && !filtros.orden_precio"
                        clase-boton-contenedor="col-12 col-lg-3 d-flex gap-2 justify-content-lg-end"
                        @limpiar="limpiarFiltros"
                    >
                        <div class="col-12 col-md-4 col-lg-3">
                            <label class="form-label small fw-bold text-secondary mb-1">Especialidad</label>
                            <!-- DIRECTIVA (v-model): Enlace de datos bidireccional con "filtros.especialidad_id" -->
                            <select class="form-select form-select-sm" v-model="filtros.especialidad_id" @change="obtenerPrestaciones()">
                                <option value="">Todas</option>
                                <option value="general">Medicina General</option>
                                <!-- DIRECTIVA (v-for): Renderizado iterativo de lista -->
                                <option v-for="esp in especialidades" :key="esp.id" :value="esp.id">{{ esp.nombre }}</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4 col-lg-3">
                            <label class="form-label small fw-bold text-secondary mb-1">Sucursal</label>
                            <!-- DIRECTIVA (v-model): Enlace de datos bidireccional con "filtros.sucursal_id" -->
                            <select class="form-select form-select-sm" v-model="filtros.sucursal_id" @change="obtenerPrestaciones()">
                                <option value="">Todas</option>
                                <!-- DIRECTIVA (v-for): Renderizado iterativo de lista -->
                                <option v-for="suc in sucursales" :key="suc.id" :value="suc.id">{{ suc.nombre }}</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4 col-lg-3">
                            <label class="form-label small fw-bold text-secondary mb-1">Orden Precio</label>
                            <!-- DIRECTIVA (v-model): Enlace de datos bidireccional con "filtros.orden_precio" -->
                            <select class="form-select form-select-sm" v-model="filtros.orden_precio" @change="obtenerPrestaciones()">
                                <option value="">Sin orden</option>
                                <option value="desc">Mayor a menor</option>
                                <option value="asc">Menor a mayor</option>
                            </select>
                        </div>

                        <template #texto-limpiar>
                            Limpiar
                        </template>
                    </BarraFiltros>

                    <p v-show="totalPrestaciones > 0" class="text-muted small mb-3">
                        {{ totalPrestaciones }} prestación{{ totalPrestaciones === 1 ? '' : 'es' }} encontrada{{ totalPrestaciones === 1 ? '' : 's' }}
                    </p>

                    <IndicadorCarga :cargando="cargando" mensaje="prestaciones" />

                    <EstadoVacio
                        :visible="!cargando && listaVacia"
                        mensaje="No hay prestaciones registradas en el catálogo."
                        :texto-boton="$isAdmin() ? 'Registrar la primera prestación' : ''"
                        icono="bi bi-box-seam"
                        @accion="abrirModalCrear"
                    />

                    <SinResultados
                        :visible="!cargando && sinResultadosFiltro"
                        mensaje="Ninguna prestación coincide con la búsqueda."
                        @limpiar="limpiarFiltros()"
                    />

                    <!-- DIRECTIVA (v-if): Renderizado condicional basado en "!cargando && !listaVacia && !sinResultadosFiltro" -->

                    <div v-if="!cargando && !listaVacia && !sinResultadosFiltro" class="row g-4">
                        <!-- DIRECTIVA (v-for): Renderizado iterativo de lista -->
                        <div v-for="prestacion in prestacionesVisibles" :key="prestacion.id" class="col-md-6 col-lg-4">
                            <Link :href="route('prestaciones.detalle', prestacion.id)" class="text-decoration-none">
                            <div class="card h-100 border-0 shadow-sm rounded-4 hover-elevate transition-all overflow-hidden position-relative">
                                <!-- Línea superior decorativa -->
                                <div class="position-absolute top-0 start-0 w-100 bg-primary" style="height: 4px;"></div>
                                
                                <div class="card-body p-4 d-flex flex-column pt-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3 gap-2">
                                        <h3 class="h5 fw-bold mb-0 text-dark">
                                            {{ prestacion.nombre }}
                                        </h3>
                                        <div class="d-flex flex-column align-items-end gap-1">
                                            <span class="badge rounded-pill bg-light text-primary border shadow-sm px-3 py-2">
                                                {{ prestacion.especialidad_id ? prestacion.especialidad.nombre : 'Medicina General' }}
                                            </span>
                                            <!-- DIRECTIVA (v-if): Renderizado condicional basado en "prestacion.categoria_prestacion" -->
                                            <span v-if="prestacion.categoria_prestacion" class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 shadow-sm px-3 py-1">
                                                {{ prestacion.categoria_prestacion.nombre }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3 text-muted small d-flex align-items-center gap-2">
                                        <div class="bg-danger bg-opacity-10 p-1 rounded-circle text-danger d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">
                                            <i class="bi bi-geo-alt-fill"></i>
                                        </div>
                                        <span class="fw-medium">{{ prestacion.sucursal?.nombre || 'Sin sucursal asignada' }}</span>
                                    </div>
                                    
                                    <p class="card-text text-secondary mb-4 flex-grow-1" style="font-size: 0.9rem; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                        {{ prestacion.descripcion || 'Sin descripción detallada.' }}
                                    </p>
                                    
                                    <div class="mt-auto bg-light rounded-4 p-3 d-flex justify-content-between align-items-center border border-light shadow-sm">
                                        <div>
                                            <span class="d-block small text-muted fw-bold mb-1 text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">Valor Cliente</span>
                                            <span class="fs-4 fw-bold text-success">${{ Math.round(prestacion.precio_base).toLocaleString('es-CL') }}</span>
                                        </div>
                                        <!-- DIRECTIVA (v-if): Renderizado condicional basado en "esAdmin" -->
                                        <div v-if="$isAdmin()" class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-light text-primary fw-bold rounded-pill shadow-sm btn-hover-elevate px-3" @click.prevent.stop="abrirModalEditar(prestacion)">
                                                <i class="bi bi-pencil-fill me-1"></i> Editar
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light text-danger fw-bold rounded-pill shadow-sm btn-hover-elevate px-3" @click.prevent.stop="confirmarEliminar(prestacion)">
                                                <i class="bi bi-trash3-fill me-1"></i> Eliminar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MODAL: Crear / Editar Prestación -->
            <!-- DIRECTIVA (v-if): Renderizado condicional basado en "mostrarModal" -->
            <div v-if="mostrarModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow border-0">
                        <div class="modal-header border-bottom bg-light py-3 px-4">
                            <h5 class="modal-title fw-bold text-dark">{{ tituloModal }}</h5>
                            <!-- EVENTO (@click): Dispara la acción "cerrarModal" -->
                            <button type="button" class="btn-close" @click="cerrarModal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label for="sucursal_id" class="form-label fw-semibold text-secondary">Sucursal</label>
                                <!-- DIRECTIVA (v-model): Enlace de datos bidireccional con "formulario.sucursal_id" -->
                                <select id="sucursal_id" v-model="formulario.sucursal_id" class="form-select" :class="{ 'is-invalid': formulario.errors.sucursal_id }" required>
                                    <option :value="null" disabled>Seleccione una sucursal...</option>
                                    <!-- DIRECTIVA (v-for): Renderizado iterativo de lista -->
                                    <option v-for="suc in sucursales" :key="suc.id" :value="suc.id">
                                        {{ suc.nombre }}
                                    </option>
                                </select>
                                <!-- DIRECTIVA (v-if): Renderizado condicional basado en "formulario.errors.sucursal_id" -->
                                <div v-if="formulario.errors.sucursal_id" class="invalid-feedback">{{ formulario.errors.sucursal_id }}</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="nombre" class="form-label fw-semibold text-secondary">Nombre de la Prestación</label>
                                <!-- DIRECTIVA (v-model): Enlace de datos bidireccional con "formulario.nombre" -->
                                <input id="nombre" v-model="formulario.nombre" type="text" class="form-control" placeholder="Ej: Consulta Especialista" :class="{ 'is-invalid': formulario.errors.nombre }" required />
                                <!-- DIRECTIVA (v-if): Renderizado condicional basado en "formulario.errors.nombre" -->
                                <div v-if="formulario.errors.nombre" class="invalid-feedback">{{ formulario.errors.nombre }}</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="descripcion" class="form-label fw-semibold text-secondary">Descripción</label>
                                <!-- DIRECTIVA (v-model): Enlace de datos bidireccional con "formulario.descripcion" -->
                                <textarea id="descripcion" v-model="formulario.descripcion" class="form-control" rows="2" placeholder="Opcional" :class="{ 'is-invalid': formulario.errors.descripcion }"></textarea>
                                <!-- DIRECTIVA (v-if): Renderizado condicional basado en "formulario.errors.descripcion" -->
                                <div v-if="formulario.errors.descripcion" class="invalid-feedback">{{ formulario.errors.descripcion }}</div>
                            </div>

                            <div class="mb-3">
                                <label for="especialidad_id" class="form-label fw-semibold text-secondary">Especialidad Requerida</label>
                                <!-- DIRECTIVA (v-model): Enlace de datos bidireccional con "formulario.especialidad_id" -->
                                <select id="especialidad_id" v-model="formulario.especialidad_id" class="form-select" :class="{ 'is-invalid': formulario.errors.especialidad_id }">
                                    <option :value="null">Ninguna (Medicina General)</option>
                                    <!-- DIRECTIVA (v-for): Renderizado iterativo de lista -->
                                    <option v-for="esp in especialidades" :key="esp.id" :value="esp.id">
                                        {{ esp.nombre }}
                                    </option>
                                </select>
                                <div class="form-text text-muted small">Si seleccionas una especialidad, solo los veterinarios con ella podrán realizarla.</div>
                                <!-- DIRECTIVA (v-if): Renderizado condicional basado en "formulario.errors.especialidad_id" -->
                                <div v-if="formulario.errors.especialidad_id" class="invalid-feedback">{{ formulario.errors.especialidad_id }}</div>
                            </div>

                            <div class="mb-3">
                                <label for="categoria_prestacion_id" class="form-label fw-semibold text-secondary">Categoría de Prestación</label>
                                <!-- DIRECTIVA (v-model): Enlace de datos bidireccional con "formulario.categoria_prestacion_id" -->
                                <select id="categoria_prestacion_id" v-model="formulario.categoria_prestacion_id" class="form-select" :class="{ 'is-invalid': formulario.errors.categoria_prestacion_id }">
                                    <option :value="null">Seleccione una categoría...</option>
                                    <!-- DIRECTIVA (v-for): Renderizado iterativo de lista -->
                                    <option v-for="cat in categoriasPrestaciones" :key="cat.id" :value="cat.id">
                                        {{ cat.nombre }}
                                    </option>
                                </select>
                                <!-- DIRECTIVA (v-if): Renderizado condicional basado en "formulario.errors.categoria_prestacion_id" -->
                                <div v-if="formulario.errors.categoria_prestacion_id" class="invalid-feedback">{{ formulario.errors.categoria_prestacion_id }}</div>
                            </div>


                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="precio_base" class="form-label fw-semibold text-secondary">Precio Base ($)</label>
                                    <!-- DIRECTIVA (v-model): Enlace de datos bidireccional con "formulario.precio_base" -->
                                    <input id="precio_base" v-model="formulario.precio_base" type="number" min="0" class="form-control" :class="{ 'is-invalid': formulario.errors.precio_base }" required />
                                    <!-- DIRECTIVA (v-if): Renderizado condicional basado en "formulario.errors.precio_base" -->
                                    <div v-if="formulario.errors.precio_base" class="invalid-feedback">{{ formulario.errors.precio_base }}</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="comision_vet" class="form-label fw-semibold text-secondary">Comisión Médico (%)</label>
                                    <div class="input-group">
                                        <!-- DIRECTIVA (v-model): Enlace de datos bidireccional con "formulario.comision_vet" -->
                                        <input id="comision_vet" v-model="formulario.comision_vet" type="number" min="0" max="100" class="form-control" :class="{ 'is-invalid': formulario.errors.comision_vet }" />
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <!-- DIRECTIVA (v-if): Renderizado condicional basado en "formulario.errors.comision_vet" -->
                                    <div v-if="formulario.errors.comision_vet" class="invalid-feedback d-block">{{ formulario.errors.comision_vet }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <!-- EVENTO (@click): Dispara la acción "cerrarModal" -->
                            <button type="button" class="btn btn-secondary" @click="cerrarModal">Cancelar</button>
                            <!-- EVENTO (@click): Dispara la acción "guardar" -->
                            <button type="button" class="btn btn-primary" :disabled="formulario.processing" @click="guardar">
                                <!-- DIRECTIVA (v-if): Renderizado condicional basado en "formulario.processing" -->
                                <span v-if="formulario.processing" class="spinner-border spinner-border-sm me-2"></span>
                                {{ textoBotonGuardar }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- DIRECTIVA (v-if): Renderizado condicional basado en "mostrarModal" -->
            <div v-if="mostrarModal" class="modal-backdrop fade show"></div>

            <ModalImportarSimple
                :visible="mostrarModalImportar"
                entidad="prestaciones"
                etiqueta="Prestaciones"
                @cerrar="mostrarModalImportar = false"
                @importado="obtenerPrestaciones()"
            />
        </div>
    </AuthenticatedLayout>
</template>

<script>
// ==================================================================================
// LÓGICA DEL COMPONENTE (VUE 3)
// ==================================================================================

import AuthenticatedLayout from '@/Disenos/LayoutAutenticado.vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import BarraFiltros from '@/Componentes/BarraFiltros.vue';
import IndicadorCarga from '@/Componentes/IndicadorCarga.vue';
import EstadoVacio from '@/Componentes/EstadoVacio.vue';
import SinResultados from '@/Componentes/SinResultados.vue';
import ModalImportarSimple from '@/Componentes/ModalImportarSimple.vue';

// ------------------------------------------------------------------------------
// EXPORT DEFAULT: Definición principal del componente
// ------------------------------------------------------------------------------
export default {
    // COMPONENTES (COMPONENTS): Registro de componentes importados
    components: {
        AuthenticatedLayout,
        Head,
        Link,
        BarraFiltros,
        IndicadorCarga,
        EstadoVacio,
        SinResultados,
        ModalImportarSimple,
    },
    // PROPIEDADES (PROPS): Datos inyectados desde el componente padre o estado
    props: {
        prestaciones: {
            type: Array,
            default: () => [],
        },
        sucursales: {
            type: Array,
            default: () => [],
        },
        especialidades: {
            type: Array,
            default: () => [],
        },
        categoriasPrestaciones: {
            type: Array,
            default: () => [],
        }
    },
    // ESTADO REACTIVO (DATA): Variables locales del componente
    data() {
        return {
            cargando: false,
            mostrarModal: false,
            mostrarModalImportar: false,
            modoEdicion: false,
            prestacionEditando: null,
            filtros: {
                especialidad_id: '',
                sucursal_id: '',
                orden_precio: ''
            },
            listaPrestaciones: this.prestaciones,
            formulario: {
                sucursal_id: null,
                nombre: '',
                descripcion: '',
                precio_base: 0,
                especialidad_id: null,
                comision_vet: 0,
                categoria_prestacion_id: null,
                errors: {},
                processing: false,
            },
        }
    },
    // PROPIEDADES COMPUTADAS (COMPUTED): Variables reactivas que dependen de otras
    computed: {

        prestacionesVisibles() {
            return this.listaPrestaciones;
        },
        totalPrestaciones() {
            return this.prestacionesVisibles.length;
        },
        listaVacia() {
            return this.listaPrestaciones.length === 0 && !this.hayFiltrosActivos;
        },
        sinResultadosFiltro() {
            return this.listaPrestaciones.length === 0 && this.hayFiltrosActivos;
        },
        hayFiltrosActivos() {
            return !!(
                this.filtros.especialidad_id ||
                this.filtros.sucursal_id ||
                this.filtros.orden_precio
            );
        },
        tituloModal() {
            return this.modoEdicion ? 'Editar Prestación' : 'Nueva Prestación';
        },
        textoBotonGuardar() {
            return this.modoEdicion ? 'Guardar cambios' : 'Crear prestación';
        },
        
    },
    // MÉTODOS (METHODS): Bloque de funciones y eventos
    methods: {
        isAdmin() {
            return this.$page.props.auth.user.rol === 'admin';
        },
        abrirModalCrear() {
            this.modoEdicion = false;
            this.prestacionEditando = null;
            this.formulario.sucursal_id = null;
            this.formulario.nombre = '';
            this.formulario.descripcion = '';
            this.formulario.precio_base = 0;
            this.formulario.especialidad_id = null;
            this.formulario.comision_vet = 0;
            this.formulario.categoria_prestacion_id = null;
            this.formulario.errors = {};
            this.mostrarModal = true;
        },
        abrirModalEditar(prestacion) {
            this.modoEdicion = true;
            this.prestacionEditando = prestacion;
            this.formulario.sucursal_id = prestacion.sucursal_id;
            this.formulario.nombre = prestacion.nombre;
            this.formulario.descripcion = prestacion.descripcion || '';
            this.formulario.precio_base = Number(prestacion.precio_base);
            this.formulario.especialidad_id = prestacion.especialidad_id || null;
            this.formulario.comision_vet = Number(prestacion.comision_vet) || 0;
            this.formulario.categoria_prestacion_id = prestacion.categoria_prestacion_id || null;
            this.formulario.errors = {};
            this.mostrarModal = true;
        },
        cerrarModal() {
            this.mostrarModal = false;
            this.prestacionEditando = null;
        },
        datosFormulario() {
            return {
                sucursal_id: this.formulario.sucursal_id,
                nombre: this.formulario.nombre,
                descripcion: this.formulario.descripcion,
                precio_base: this.formulario.precio_base,
                especialidad_id: this.formulario.especialidad_id,
                comision_vet: this.formulario.comision_vet === 0 ? null : this.formulario.comision_vet,
                categoria_prestacion_id: this.formulario.categoria_prestacion_id,
            };
        },
        guardar() {
            this.formulario.processing = true;
            this.formulario.errors = {};

            const request = this.modoEdicion
                ? axios.put(`/api/prestaciones/${this.prestacionEditando.id}`, this.datosFormulario())
                : axios.post('/api/prestaciones', this.datosFormulario());

            request
                .then(() => {
                    this.cerrarModal();
                    this.$alertaExito(this.modoEdicion ? 'Prestación actualizada' : 'Prestación creada', 'Los cambios se guardaron correctamente.');
                    this.obtenerPrestaciones();
                })
                .catch((error) => {
                    if (error.response?.status === 422) {
                        this.formulario.errors = error.response.data.errors;
                        this.$alertaValidacion(error.response.data.errors);
                    } else {
                        this.$alertaError('Error', 'No se pudo guardar la prestación.');
                    }
                })
                .finally(() => {
                    this.formulario.processing = false;
                });
        },
        obtenerPrestaciones() {
            this.cargando = true;
            axios.get('/prestaciones', { 
                params: this.filtros,
                headers: { 'Accept': 'application/json' } 
            })
                .then((response) => {
                    this.listaPrestaciones = response.data.prestaciones || response.data;
                })
                .catch((error) => console.error("No se pudo recargar la lista:", error))
                .finally(() => this.cargando = false);
        },
        limpiarFiltros() {
            this.filtros = {
                especialidad_id: '',
                sucursal_id: '',
                orden_precio: ''
            };
            this.obtenerPrestaciones();
        },
        confirmarEliminar(prestacion) {
            this.$confirmar('¿Eliminar prestación?', `Se eliminará la prestación ${prestacion.nombre}.`)
                .then((resultado) => {
                    if (!resultado.isConfirmed) return;
                    axios.delete(`/api/prestaciones/${prestacion.id}`)
                        .then(() => {
                            this.$alertaExito('Eliminada', `${prestacion.nombre} fue eliminada.`);
                            this.obtenerPrestaciones();
                        })
                        .catch(() => this.$alertaError('Error', 'No se pudo eliminar la prestación.'));
                });
        },
    },
}
</script>

<style scoped>
.hover-elevate {
    transition: transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1), box-shadow 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
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
.transition-all {
    transition: all 0.3s ease-in-out;
}
</style>
