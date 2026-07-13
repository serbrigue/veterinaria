<template>
    <Head title="Boxes" />
    <AuthenticatedLayout>
        <div class="container py-4">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h1 class="h4 mb-0 text-primary fw-bold">Boxes de Atención</h1>
                    <button v-if="esAdmin" type="button" class="btn btn-primary rounded-pill shadow-sm px-4" @click="abrirModalCrear">
                        <i class="bi bi-plus-lg me-1"></i> Nuevo Box
                    </button>
                </div>

                <div class="card-body p-4">
                    <!-- Barra de búsqueda -->
                    <BarraFiltros 
                        :deshabilitar-limpiar="!filtroTexto" 
                        clase-boton-contenedor="col-12 col-md-4 col-lg-6 d-flex justify-content-md-end"
                        @limpiar="limpiarFiltros"
                    >
                        <div class="col-12 col-md-8 col-lg-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                                <input 
                                    type="text" 
                                    v-model="filtroTexto" 
                                    class="form-control border-start-0 ps-0" 
                                    @input="obtenerBoxes()" 
                                    placeholder="Buscar box por nombre..."
                                >
                            </div>
                        </div>
                        <template #texto-limpiar>
                            Limpiar Filtro
                        </template>
                    </BarraFiltros>

                    <p v-show="!listaVacia" class="text-muted small mb-4 fw-medium ms-2">
                        {{ totalBoxes }} box{{ totalBoxes === 1 ? '' : 'es' }} registrado{{ totalBoxes === 1 ? '' : 's' }}
                    </p>

                    <IndicadorCarga :cargando="cargando" mensaje="boxes" />

                    <EstadoVacio
                        :visible="!cargando && listaVacia"
                        mensaje="No tienes boxes registrados aún."
                        :texto-boton="esVeterinarioOAdmin ? 'Registrar tu primer box' : ''"
                        icono="bi bi-door-open"
                        @accion="abrirModalCrear"
                    />

                    <SinResultados
                        :visible="!cargando && sinResultadosFiltro"
                        mensaje="Ningún box coincide con la búsqueda."
                        @limpiar="limpiarFiltros()"
                    />

                    <!-- Grid de Boxes -->
                    <div v-if="!cargando && !listaVacia && !sinResultadosFiltro" class="row g-4">
                        <div v-for="box in boxesVisibles" :key="box.id" class="col-12 col-md-6 col-lg-4">
                            <TarjetaEntidad
                                :titulo="box.nombre"
                                icono="bi-door-closed"
                                :imagen-url="box.imagen_url || '/images/default_box.png'"
                                :url-detalle="route('boxes.detalle', box.id)"
                                :mostrar-acciones="esAdmin"
                                @editar="abrirModalEditar(box)"
                                @eliminar="confirmarEliminar(box)"
                            >
                                <template #header-badge v-if="box.sucursal">
                                    <span class="badge bg-white text-dark mt-1 shadow-sm align-self-start">{{ box.sucursal.nombre }}</span>
                                </template>
                                <template #body>
                                    <p class="card-text text-muted small mb-3 flex-grow-1 line-clamp-3">
                                        {{ box.descripcion || 'Sin descripción disponible para este box.' }}
                                    </p>

                                    <!-- Badge de categoría -->
                                    <div class="mb-3" v-if="box.categoria_prestacion">
                                        <span class="badge rounded-pill px-3 py-2 align-self-start" :class="badgeCategoria(box.categoria_prestacion.nombre)">
                                            <i class="bi bi-tag-fill me-1"></i>{{ box.categoria_prestacion.nombre }}
                                        </span>
                                    </div>
                                    <div class="mb-3" v-else>
                                        <span class="badge bg-secondary bg-opacity-50 rounded-pill px-3 py-2 align-self-start">
                                            <i class="bi bi-tag me-1"></i>Sin restricción
                                        </span>
                                    </div>
                                </template>
                            </TarjetaEntidad>
                        </div>
                    </div>  </div>
                </div>
            </div>

            <ModalCrud
                :visible="mostrarModal"
                :titulo="tituloModal"
                :modo-edicion="modoEdicion"
                :processing="formulario.processing"
                texto-guardar="Guardar Cambios"
                texto-crear="Crear Box"
                @cerrar="cerrarModal"
                @guardar="guardar"
            >
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary small text-uppercase">Nombre del Box</label>
                    <input
                        v-model="formulario.nombre"
                        type="text"
                        class="form-control bg-light border-0 py-2"
                        placeholder="Ej: Box 1, Box Cirugía"
                        :class="{ 'is-invalid': formulario.errors.nombre }"
                        required
                    />
                    <div v-if="formulario.errors.nombre" class="invalid-feedback">{{ formulario.errors.nombre }}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary small text-uppercase">Descripción</label>
                    <textarea
                        v-model="formulario.descripcion"
                        class="form-control bg-light border-0 py-2"
                        rows="3"
                        placeholder="Detalles sobre el equipamiento o uso del box..."
                        :class="{ 'is-invalid': formulario.errors.descripcion }"
                    ></textarea>
                    <div v-if="formulario.errors.descripcion" class="invalid-feedback">{{ formulario.errors.descripcion }}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary small text-uppercase">Tipo de Box</label>
                    <select v-model="formulario.categoria_prestacion_id" class="form-select bg-light border-0 py-2"
                        :class="{ 'is-invalid': formulario.errors.categoria_prestacion_id }">
                        <option :value="null">Sin restricción (acepta cualquier tipo)</option>
                        <option v-for="cat in categoriasPrestacion" :key="cat.id" :value="cat.id">
                            {{ cat.nombre }}
                        </option>
                    </select>
                    <div v-if="formulario.errors.categoria_prestacion_id" class="invalid-feedback">{{ formulario.errors.categoria_prestacion_id }}</div>
                    <div class="form-text text-muted small">Define qué tipo de prestaciones puede atender este box.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary small text-uppercase">Sucursal</label>
                    <select v-model="formulario.sucursal_id" class="form-select bg-light border-0 py-2"
                        :class="{ 'is-invalid': formulario.errors.sucursal_id }">
                        <option :value="null">Seleccionar sucursal</option>
                        <option v-for="sucursal in sucursales" :key="sucursal.id" :value="sucursal.id">{{ sucursal.nombre }}</option>
                    </select>
                    <div v-if="formulario.errors.sucursal_id" class="invalid-feedback">{{ formulario.errors.sucursal_id }}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary small text-uppercase">Foto / Imagen del Box</label>
                    <input
                        ref="fotoInput"
                        type="file"
                        class="form-control bg-light border-0 py-2"
                        accept="image/*"
                        @change="seleccionarFoto"
                        :class="{ 'is-invalid': formulario.errors.imagen_url }"
                    />
                    <div v-if="formulario.errors.imagen_url" class="invalid-feedback">
                        {{ formulario.errors.imagen_url }}
                    </div>
                    <div v-if="formulario.imagen_url_preview || formulario.imagen_url" class="mt-2 text-center">
                        <img :src="formulario.imagen_url_preview || formulario.imagen_url" class="img-thumbnail" style="max-height: 120px;" alt="Vista previa" />
                    </div>
                </div>
            </ModalCrud>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from '@/Disenos/LayoutAutenticado.vue';
import { Head, Link } from '@inertiajs/vue3';
import IndicadorCarga from '@/Componentes/IndicadorCarga.vue';
import EstadoVacio from '@/Componentes/EstadoVacio.vue';
import SinResultados from '@/Componentes/SinResultados.vue';
import ModalCrud from '@/Componentes/ModalCrud.vue';
import BarraFiltros from '@/Componentes/BarraFiltros.vue';
import TarjetaEntidad from '@/Componentes/TarjetaEntidad.vue';

export default {
    components: {
        AuthenticatedLayout,
        Head,
        Link,
        IndicadorCarga,
        EstadoVacio,
        SinResultados,
        ModalCrud,
        BarraFiltros,
        TarjetaEntidad,
    },
    props: {
        boxes: {
            type: Array,
            default: () => [],
        },
        categoriasPrestacion: {
            type: Array,
            default: () => [],
        },
        sucursales:{
            type: Array,
            default: () => [],
        }
    },
    data() {
        return {
            cargando: false,
            mostrarModal: false,
            modoEdicion: false,
            boxEditando: null,
            filtroTexto: '',
            boxAEliminar: null,
            formulario: {
                nombre: '',
                descripcion: '',
                sucursal_id: null,
                categoria_prestacion_id: null,
                imagen_url: '',
                imagen_url_file: null,
                imagen_url_preview: null,
                errors: {},
                processing: false,
            },
            boxesVisibles: this.boxes,
        }
    },
    watch: {
        boxes(nuevas) {
            this.boxesVisibles = nuevas;
        }
    },
    computed: {
        esAdmin() {
            const user = this.$page.props.auth.user;
            return user && (user.rol?.nombre_interno === 'admin');
        },
        textoBotonGuardar() { return this.modoEdicion ? 'Guardar Cambios' : 'Crear Box'; },
        tituloModal()       { return this.modoEdicion ? 'Editar Box' : 'Nuevo Box'; },
        totalBoxes()        { return this.boxesVisibles.length; },
        listaVacia()        { return this.boxesVisibles.length === 0 && this.filtroTexto === ''; },
        sinResultadosFiltro() { return this.boxesVisibles.length === 0 && this.filtroTexto !== ''; },
    },
    methods: {
        badgeCategoria(nombre) {
            const mapa = {
                'Consulta':   'bg-info text-dark',
                'Cirugia':    'bg-danger',
                'Urgencia':   'bg-warning text-dark',
                'Estetica':   'bg-success',
            };
            return mapa[nombre] || 'bg-secondary';
        },
        seleccionarFoto(e) {
            const archivos = e.target.files;
            if (archivos && archivos.length > 0) {
                this.formulario.imagen_url_file = archivos[0];
                this.formulario.imagen_url_preview = URL.createObjectURL(archivos[0]);
            }
        },
        datosFormulario() {
            const formData = new FormData();
            formData.append('nombre', this.formulario.nombre);
            formData.append('descripcion', this.formulario.descripcion || '');
            if (this.formulario.sucursal_id) formData.append('sucursal_id', this.formulario.sucursal_id);
            if (this.formulario.categoria_prestacion_id) formData.append('categoria_prestacion_id', this.formulario.categoria_prestacion_id);
            
            if (this.formulario.imagen_url_file) {
                formData.append('imagen_url', this.formulario.imagen_url_file);
            }
            return formData;
        },
        
        abrirModalCrear() {
            this.modoEdicion = false;
            this.boxEditando = null;
            this.formulario.nombre = '';
            this.formulario.descripcion = '';
            this.formulario.sucursal_id = null;
            this.formulario.categoria_prestacion_id = null;
            this.formulario.imagen_url = '';
            this.formulario.imagen_url_file = null;
            this.formulario.imagen_url_preview = null;
            if (this.$refs.fotoInput) {
                this.$refs.fotoInput.value = '';
            }
            this.formulario.errors = {};
            this.mostrarModal = true;
        },
        abrirModalEditar(box) {
            this.modoEdicion = true;
            this.boxEditando = box;
            this.formulario.nombre = box.nombre;
            this.formulario.descripcion = box.descripcion;
            this.formulario.sucursal_id = box.sucursal_id;
            this.formulario.categoria_prestacion_id = box.categoria_prestacion_id;
            this.formulario.imagen_url = box.imagen_url;
            this.formulario.imagen_url_file = null;
            this.formulario.imagen_url_preview = null;
            if (this.$refs.fotoInput) {
                this.$refs.fotoInput.value = '';
            }
            this.formulario.errors = {};
            this.mostrarModal = true;
        },
        obtenerBoxes() {
            this.cargando = true;
            axios.get('/boxes', { params: { texto: this.filtroTexto } })
                .then(response => {
                    this.boxesVisibles = response.data.boxes;
                })
                .catch(error => {
                    console.error('Error al obtener boxes:', error);
                })
                .finally(() => {
                    this.cargando = false;
                });
        },
        limpiarFiltros() {
            this.filtroTexto = '';
            this.obtenerBoxes();
        },
        cerrarModal() {
            this.modoEdicion = false;
            this.boxEditando = null;
            this.formulario.nombre = '';
            this.formulario.descripcion = '';
            this.formulario.sucursal_id = null;
            this.formulario.categoria_prestacion_id = null;
            this.formulario.imagen_url = '';
            this.formulario.imagen_url_file = null;
            this.formulario.imagen_url_preview = null;
            this.formulario.errors = {};
            this.mostrarModal = false;
        },
        guardar() {
            this.formulario.processing = true;
            this.formulario.errors = {};
            
            const data = this.datosFormulario();

            if (this.modoEdicion) {
                data.append('_method', 'PUT');
                axios.post(`/api/boxes/${this.boxEditando.id}`, data, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })
                .then(() => { 
                    this.cerrarModal(); 
                    this.obtenerBoxes();
                    if (this.$alertaExito) {
                        this.$alertaExito('Actualizado', 'El box se actualizó correctamente.');
                    }
                })
                .catch((error) => { 
                    if (error.response?.status === 422) {
                        this.formulario.errors = error.response.data.errors; 
                    }
                })
                .finally(() => { 
                    this.formulario.processing = false; 
                });
            } else {
                axios.post('/api/boxes', data, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })
                .then(() => { 
                    this.cerrarModal(); 
                    this.obtenerBoxes();
                    if (this.$alertaExito) {
                        this.$alertaExito('Creado', 'El box se creó correctamente.');
                    }
                })
                .catch((error) => { 
                    if (error.response?.status === 422) {
                        this.formulario.errors = error.response.data.errors; 
                    }
                })
                .finally(() => { 
                    this.formulario.processing = false; 
                });
            }
        },
        confirmarEliminar(box) {
            this.boxAEliminar = box;
            if (this.$confirmar) {
                this.$confirmar('¿Eliminar box?', `Se eliminará el box ${box.nombre} permanentemente.`)
                .then((resultado) => {
                    if (resultado.isConfirmed) {
                        this.eliminarBox();
                    }
                });
            } else if (confirm(`¿Estás seguro de eliminar el box ${box.nombre}?`)) {
                this.eliminarBox();
            }
        },
        eliminarBox() {
            axios.delete(`/api/boxes/${this.boxAEliminar.id}`)
            .then(() => { 
                this.obtenerBoxes();
                if (this.$alertaExito) {
                    this.$alertaExito('Eliminado', 'El box se eliminó correctamente.');
                }
            })
            .catch((error) => { 
                console.error(error); 
            });
        },

    },
}
</script>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.border-dashed {
    border-style: dashed !important;
}
</style>
