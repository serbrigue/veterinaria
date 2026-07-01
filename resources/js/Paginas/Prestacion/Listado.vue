<template>
    <Head title="Prestaciones" />
    <AuthenticatedLayout>

        <div class="container py-4">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h1 class="h5 mb-0">Catálogo de Prestaciones</h1>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <button v-if="isAdmin()" type="button" class="btn btn-sm btn-primary" @click="abrirModalCrear">
                            + Nueva Prestación
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
                            <select class="form-select form-select-sm" v-model="filtros.especialidad_id" @change="obtenerPrestaciones()">
                                <option value="">Todas</option>
                                <option value="general">Medicina General</option>
                                <option v-for="esp in especialidades" :key="esp.id" :value="esp.id">{{ esp.nombre }}</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4 col-lg-3">
                            <label class="form-label small fw-bold text-secondary mb-1">Sucursal</label>
                            <select class="form-select form-select-sm" v-model="filtros.sucursal_id" @change="obtenerPrestaciones()">
                                <option value="">Todas</option>
                                <option v-for="suc in sucursales" :key="suc.id" :value="suc.id">{{ suc.nombre }}</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4 col-lg-3">
                            <label class="form-label small fw-bold text-secondary mb-1">Orden Precio</label>
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
                        :texto-boton="isAdmin() ? 'Registrar la primera prestación' : ''"
                        icono="bi bi-box-seam"
                        @accion="abrirModalCrear"
                    />

                    <SinResultados
                        :visible="!cargando && sinResultadosFiltro"
                        mensaje="Ninguna prestación coincide con la búsqueda."
                        @limpiar="limpiarFiltros()"
                    />

                    <div v-if="!cargando && !listaVacia && !sinResultadosFiltro" class="row g-4">
                        <div v-for="prestacion in prestacionesVisibles" :key="prestacion.id" class="col-md-6 col-lg-4">
                            <Link :href="route('prestaciones.detalle', prestacion.id)" class="text-decoration-none">
                            <div class="card h-100 shadow-sm border-0 border-top border-primary border-4" style="transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 .5rem 1rem rgba(0,0,0,.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 .125rem .25rem rgba(0,0,0,.075)';">
                                <div class="card-body p-4 d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
                                        <h3 class="h5 fw-bold mb-0 text-dark">
                                            {{ prestacion.nombre }}
                                        </h3>
                                        <div class="d-flex flex-column align-items-end gap-1">
                                            <span class="badge rounded-pill text-bg-light border shadow-sm">
                                                {{ prestacion.especialidad_id ? prestacion.especialidad.nombre : 'Medicina General' }}
                                            </span>
                                            <span v-if="prestacion.categoria_prestacion" class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 shadow-sm small">
                                                {{ prestacion.categoria_prestacion.nombre }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3 text-muted small d-flex align-items-center gap-1">
                                        <i class="bi bi-geo-alt-fill text-danger"></i>
                                        {{ prestacion.sucursal?.nombre || 'Sin sucursal asignada' }}
                                    </div>
                                    
                                    <p class="card-text text-secondary mb-4 flex-grow-1" style="font-size: 0.9rem; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                        {{ prestacion.descripcion || 'Sin descripción detallada.' }}
                                    </p>
                                    
                                    <div class="mt-auto bg-light rounded p-3 d-flex justify-content-between align-items-center border">
                                        <div>
                                            <span class="d-block small text-muted fw-semibold mb-1 text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Valor Cliente</span>
                                            <span class="fs-5 fw-bold text-success">${{ Math.round(prestacion.precio_base).toLocaleString('es-CL') }}</span>
                                        </div>
                                        <div v-if="isAdmin()"   class="btn-group btn-group-sm bg-white shadow-sm rounded">
                                            <button type="button" class="btn btn-outline-primary border-0" @click="abrirModalEditar(prestacion)" title="Editar Prestación">
                                                <i class="bi bi-pencil-fill"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger border-0" @click="confirmarEliminar(prestacion)" title="Eliminar Prestación">
                                                <i class="bi bi-trash-fill"></i>
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
                                <label for="nombre" class="form-label fw-semibold text-secondary">Nombre de la Prestación</label>
                                <input id="nombre" v-model="formulario.nombre" type="text" class="form-control" placeholder="Ej: Consulta Especialista" :class="{ 'is-invalid': formulario.errors.nombre }" required />
                                <div v-if="formulario.errors.nombre" class="invalid-feedback">{{ formulario.errors.nombre }}</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="descripcion" class="form-label fw-semibold text-secondary">Descripción</label>
                                <textarea id="descripcion" v-model="formulario.descripcion" class="form-control" rows="2" placeholder="Opcional" :class="{ 'is-invalid': formulario.errors.descripcion }"></textarea>
                                <div v-if="formulario.errors.descripcion" class="invalid-feedback">{{ formulario.errors.descripcion }}</div>
                            </div>

                            <div class="mb-3">
                                <label for="especialidad_id" class="form-label fw-semibold text-secondary">Especialidad Requerida</label>
                                <select id="especialidad_id" v-model="formulario.especialidad_id" class="form-select" :class="{ 'is-invalid': formulario.errors.especialidad_id }">
                                    <option :value="null">Ninguna (Medicina General)</option>
                                    <option v-for="esp in especialidades" :key="esp.id" :value="esp.id">
                                        {{ esp.nombre }}
                                    </option>
                                </select>
                                <div class="form-text text-muted small">Si seleccionas una especialidad, solo los veterinarios con ella podrán realizarla.</div>
                                <div v-if="formulario.errors.especialidad_id" class="invalid-feedback">{{ formulario.errors.especialidad_id }}</div>
                            </div>

                            <div class="mb-3">
                                <label for="categoria_prestacion_id" class="form-label fw-semibold text-secondary">Categoría de Prestación</label>
                                <select id="categoria_prestacion_id" v-model="formulario.categoria_prestacion_id" class="form-select" :class="{ 'is-invalid': formulario.errors.categoria_prestacion_id }">
                                    <option :value="null">Seleccione una categoría...</option>
                                    <option v-for="cat in categoriasPrestaciones" :key="cat.id" :value="cat.id">
                                        {{ cat.nombre }}
                                    </option>
                                </select>
                                <div v-if="formulario.errors.categoria_prestacion_id" class="invalid-feedback">{{ formulario.errors.categoria_prestacion_id }}</div>
                            </div>


                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="precio_base" class="form-label fw-semibold text-secondary">Precio Base ($)</label>
                                    <input id="precio_base" v-model="formulario.precio_base" type="number" min="0" class="form-control" :class="{ 'is-invalid': formulario.errors.precio_base }" required />
                                    <div v-if="formulario.errors.precio_base" class="invalid-feedback">{{ formulario.errors.precio_base }}</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="comision_vet" class="form-label fw-semibold text-secondary">Comisión Médico (%)</label>
                                    <div class="input-group">
                                        <input id="comision_vet" v-model="formulario.comision_vet" type="number" min="0" max="100" class="form-control" :class="{ 'is-invalid': formulario.errors.comision_vet }" />
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <div v-if="formulario.errors.comision_vet" class="invalid-feedback d-block">{{ formulario.errors.comision_vet }}</div>
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
import BarraFiltros from '@/Componentes/BarraFiltros.vue';
import IndicadorCarga from '@/Componentes/IndicadorCarga.vue';
import EstadoVacio from '@/Componentes/EstadoVacio.vue';
import SinResultados from '@/Componentes/SinResultados.vue';

export default {
    components: {
        AuthenticatedLayout,
        Head,
        Link,
        BarraFiltros,
        IndicadorCarga,
        EstadoVacio,
        SinResultados,
    },
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
    data() {
        return {
            cargando: false,
            mostrarModal: false,
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
