<template>
    <!-- ================================================================================== -->
    <!-- COMPONENTE: Listado -->
    <!-- ================================================================================== -->
    <Head title="Boxes" />
    <AuthenticatedLayout>
        <div class="container py-4">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header border-0 bg-white p-4 d-flex justify-content-between align-items-center flex-wrap gap-3 rounded-top-4 border-bottom border-light">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary bg-opacity-10 p-2 rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px;">
                            <img src="/images/icon_boxes.png" alt="Icono Boxes" class="w-100 h-100 object-fit-contain" style="transform: scale(1.15);">
                        </div>
                        <h1 class="h4 mb-0 fw-bold text-dark">Boxes de Atención</h1>
                    </div>
                    <div class="d-flex gap-2 flex-wrap align-items-center">
                        <!--Si el usuario es administrador o secretaria, se muestran los botones de importar y exportar-->
                        <template v-if="$isAdmin() || $isSecretaria()">
                            <!--Enlace que permite exportar los boxes-->
                            <a href="/api/export/boxes" class="btn btn-light text-success fw-bold rounded-pill shadow-sm btn-hover-elevate">
                                <i class="bi bi-download me-1"></i> Exportar
                            </a>
                            <!--Evento que permite importar los boxes-->
                            <button type="button" class="btn btn-light text-primary fw-bold rounded-pill shadow-sm btn-hover-elevate" @click="mostrarModalImportar = true">
                                <i class="bi bi-upload me-1"></i> Importar
                            </button>
                        </template>
                        <!--Si el usuario es administrador, se muestra el botón de crear-->
                        <button v-if="$isAdmin()" type="button" class="btn btn-primary fw-bold rounded-pill shadow-sm btn-hover-elevate px-4" @click="abrirModalCrear">
                            <i class="bi bi-plus-lg me-1"></i> Nuevo Box
                        </button>
                    </div>
                </div>

                <div class="card-body p-4">
                    <!-- Barra de búsqueda -->
                    <BarraFiltros 
                        :deshabilitar-limpiar="!filtroTexto && !filtroCategoria" 
                        clase-boton-contenedor="col-12 col-md-4 col-lg-4 d-flex justify-content-md-end"
                        @limpiar="limpiarFiltros"
                    >
                        <div class="col-12 col-md-4 col-lg-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                                <!--Enlace de datos bidireccional con "filtroTexto"-->
                                <input 
                                    type="text" 
                                    v-model="filtroTexto" 
                                    class="form-control border-start-0 ps-0" 
                                    @input="obtenerBoxes()" 
                                    placeholder="Buscar box por nombre..."
                                >
                            </div>
                        </div>
                        <div class="col-12 col-md-4 col-lg-4">
                            <!--Enlace de datos bidireccional con "filtroCategoria"-->
                            <select 
                                v-model="filtroCategoria" 
                                @change="obtenerBoxes()" 
                                class="form-select"
                            >
                                <option value="">Todas las categorías</option>
                                <!--Iteración sobre las categorías de prestación para el filtro-->
                                <option v-for="cat in categoriasPrestacion" :key="cat.id" :value="cat.id">{{ cat.nombre }}</option>
                            </select>
                        </div>
                        <template #texto-limpiar>
                            Limpiar Filtro
                        </template>
                    </BarraFiltros>

                    <!--Si la lista no esta vacia, se muestra la cantidad de boxes-->
                    <p v-show="!listaVacia" class="text-muted small mb-4 fw-medium ms-2">
                        {{ totalBoxes }} box{{ totalBoxes === 1 ? '' : 'es' }} registrado{{ totalBoxes === 1 ? '' : 's' }}
                    </p>

                    <!--Indicador de carga-->
                    <IndicadorCarga :cargando="cargando" mensaje="boxes" />

                    <!--Si la lista esta vacia-->
                    <EstadoVacio
                        :visible="!cargando && listaVacia"
                        mensaje="No tienes boxes registrados aún."
                        :texto-boton="$isAdmin() ? 'Registrar tu primer box' : ''"
                        icono="bi bi-door-open"
                        @accion="abrirModalCrear"
                    />

                    <!--Si no hay resultados para el filtro-->
                    <SinResultados
                        :visible="!cargando && sinResultadosFiltro"
                        mensaje="Ningún box coincide con la búsqueda."
                        @limpiar="limpiarFiltros()"
                    />

                    <!-- Grid de Boxes -->
                    <!--Si no esta cargando, la lista no esta vacia y no hay resultados para el filtro significa que hay boxes para mostrar-->
                    <div v-if="!cargando && !listaVacia && !sinResultadosFiltro" class="row g-4">
                        <!--Iteración sobre los boxes visibles-->
                        <div v-for="box in boxesVisibles" :key="box.id" class="col-12 col-md-6 col-lg-4">
                            <!--Tarjeta de entidad para el renderizado de los boxes-->
                            <TarjetaEntidad
                                :titulo="box.nombre"
                                icono="bi-door-closed"
                                :imagen-url="box.imagen_url || '/images/default_box.png'"
                                :url-detalle="route('boxes.detalle', box.id)"
                                :mostrar-acciones="$isAdmin()"
                                @editar="abrirModalEditar(box)"
                                @eliminar="confirmarEliminar(box)"
                            >
                                <!--Si el box tiene sucursal, se muestra el badge-->
                                <template #header-badge v-if="box.sucursal">
                                    <span class="badge bg-white text-dark mt-1 shadow-sm align-self-start">{{ box.sucursal.nombre }}</span>
                                </template>
                                <template #body>
                                    <!--Para mostrar la descripción del box-->
                                    <p class="card-text text-muted small mb-3 flex-grow-1 line-clamp-3">
                                        {{ box.descripcion || 'Sin descripción disponible para este box.' }}
                                    </p>

                                    <!--Si el box tiene categoría de prestación, se muestra el badge-->
                                    <div class="mb-3" v-if="box.categoria_prestacion">
                                        <span class="badge rounded-pill px-3 py-2 align-self-start" :class="badgeCategoria(box.categoria_prestacion.nombre)">
                                            <i class="bi bi-tag-fill me-1"></i>{{ box.categoria_prestacion.nombre }}
                                        </span>
                                    </div>
                                    <!--Si el box no tiene categoría de prestación, se muestra el badge-->
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

            <!-- MODAL CRUD (INSERTAR/EDITAR BOX) -->
            <!--Este modal se utiliza para insertar y editar boxes, y solo es visible para usuarios administradores-->
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
                    <!-- Enlace de datos bidireccional con "formulario.nombre" -->
                    <input
                        v-model="formulario.nombre"
                        type="text"
                        class="form-control bg-light border-0 py-2"
                        placeholder="Ej: Box 1, Box Cirugía"
                        :class="{ 'is-invalid': formulario.errors.nombre }"
                        required
                    />
                    <!-- Si hay un error en el campo nombre, se muestra el mensaje de error -->
                    <div v-if="formulario.errors.nombre" class="invalid-feedback">{{ formulario.errors.nombre }}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary small text-uppercase">Descripción</label>
                    <!-- Enlace de datos bidireccional con "formulario.descripcion" -->
                    <textarea
                        v-model="formulario.descripcion"
                        class="form-control bg-light border-0 py-2"
                        rows="3"
                        placeholder="Detalles sobre el equipamiento o uso del box..."
                        :class="{ 'is-invalid': formulario.errors.descripcion }"
                    ></textarea>
                    <!-- Si hay un error en el campo descripción, se muestra el mensaje de error -->
                    <div v-if="formulario.errors.descripcion" class="invalid-feedback">{{ formulario.errors.descripcion }}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary small text-uppercase">Tipo de Box</label>
                    <!-- Enlace de datos bidireccional con "formulario.categoria_prestacion_id" -->
                    <select v-model="formulario.categoria_prestacion_id" class="form-select bg-light border-0 py-2"
                        :class="{ 'is-invalid': formulario.errors.categoria_prestacion_id }">
                        <option :value="null">Sin restricción (acepta cualquier tipo)</option>
                        <!-- Renderizado iterativo de lista para asignar categorias existentes -->
                        <option v-for="cat in categoriasPrestacion" :key="cat.id" :value="cat.id">
                            {{ cat.nombre }}
                        </option>
                    </select>   
                    <!-- Si hay un error en el campo categoria_prestacion_id, se muestra el mensaje de error -->
                    <div v-if="formulario.errors.categoria_prestacion_id" class="invalid-feedback">{{ formulario.errors.categoria_prestacion_id }}</div>
                    <div class="form-text text-muted small">Define qué tipo de prestaciones puede atender este box.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary small text-uppercase">Sucursal</label>
                    <!--Enlace de datos bidireccional con "formulario.sucursal_id" -->
                    <select v-model="formulario.sucursal_id" class="form-select bg-light border-0 py-2"
                        :class="{ 'is-invalid': formulario.errors.sucursal_id }">
                        <option :value="null">Seleccionar sucursal</option>
                        <!-- Renderizado iterativo de lista para asignar sucursales existentes -->
                        <option v-for="sucursal in sucursales" :key="sucursal.id" :value="sucursal.id">{{ sucursal.nombre }}</option>
                    </select>
                    <!-- Si hay un error en el campo sucursal_id, se muestra el mensaje de error -->
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
                    <!-- Si hay un error en el campo imagen_url, se muestra el mensaje de error -->
                    <div v-if="formulario.errors.imagen_url" class="invalid-feedback">
                        {{ formulario.errors.imagen_url }}
                    </div>
                    <!-- Si hay una imagen previa o una imagen cargada, se muestra la vista previa -->
                    <div v-if="formulario.imagen_url_preview || formulario.imagen_url" class="mt-2 text-center">
                        <img :src="formulario.imagen_url_preview || formulario.imagen_url" class="img-thumbnail" style="max-height: 120px;" alt="Vista previa" />
                    </div>
                </div>
            </ModalCrud>

            <!-- Modal para importar boxes -->
            <ModalImportarSimple
                :visible="mostrarModalImportar"
                entidad="boxes"
                etiqueta="Boxes"
                @cerrar="mostrarModalImportar = false"
                @importado="obtenerBoxes()"
            />
    </AuthenticatedLayout>
</template>

<script>
// ==================================================================================
// LÓGICA DEL COMPONENTE (VUE 3)
// ==================================================================================

import AuthenticatedLayout from '@/Disenos/LayoutAutenticado.vue';
import { Head, Link } from '@inertiajs/vue3';
import IndicadorCarga from '@/Componentes/IndicadorCarga.vue';
import EstadoVacio from '@/Componentes/EstadoVacio.vue';
import SinResultados from '@/Componentes/SinResultados.vue';
import ModalCrud from '@/Componentes/ModalCrud.vue';
import BarraFiltros from '@/Componentes/BarraFiltros.vue';
import TarjetaEntidad from '@/Componentes/TarjetaEntidad.vue';
import ModalImportarSimple from '@/Componentes/ModalImportarSimple.vue';

// ------------------------------------------------------------------------------
// EXPORT DEFAULT: Definición principal del componente
// ------------------------------------------------------------------------------
export default {
    // COMPONENTES: Registro de componentes importados
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
        ModalImportarSimple,
    },
    // PROPIEDADES: Datos inyectados desde el componente padre o estado
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
    // ESTADO REACTIVO: Variables locales del componente
    data() {
        return {
            cargando: false,
            mostrarModal: false,
            modoEdicion: false,
            boxEditando: null,
            filtroTexto: '',
            filtroCategoria: '',
            boxAEliminar: null,
            mostrarModalImportar: false,
            // Formulario reactivo para el CRUD de boxes
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
            // Lista de boxes visibles según filtros aplicados
            boxesVisibles: this.boxes,
        }
    },
    // OBSERVADORES: Reaccionan a cambios en propiedades o variables
    watch: {
        // Cuando cambia la lista de boxes, se actualiza la lista visible
        boxes(nuevas) {
            this.boxesVisibles = nuevas;
        }
    },
    // PROPIEDADES COMPUTADAS: Variables reactivas que dependen de otras
    computed: {
        // Calcula el texto del botón de guardar según el modo de edición
        textoBotonGuardar() { return this.modoEdicion ? 'Guardar Cambios' : 'Crear Box'; },

        // Calcula el título del modal según el modo de edición
        tituloModal()       { return this.modoEdicion ? 'Editar Box' : 'Nuevo Box'; },

        // Calcula el número total de boxes
        totalBoxes()        { return this.boxesVisibles.length; },

        // Indica si la lista está vacía
        listaVacia()        { return this.boxesVisibles.length === 0 && this.filtroTexto === '' && this.filtroCategoria === ''; },

        // Indica si no hay resultados debido a los filtros
        sinResultadosFiltro() { return this.boxesVisibles.length === 0 && (this.filtroTexto !== '' || this.filtroCategoria !== ''); },
    },
    // MÉTODOS: Bloque de funciones y eventos
    methods: {
        // Badge para mostrar la categoría de la prestación
        badgeCategoria(nombre) {
            const mapa = {
                'Consulta':   'bg-info text-dark',
                'Cirugia':    'bg-danger',
                'Urgencia':   'bg-warning text-dark',
                'Estetica':   'bg-success',
            };
            return mapa[nombre] || 'bg-secondary';
        },
        // Maneja la selección de la foto del box
        seleccionarFoto(e) {
            // Obtiene el archivo seleccionado del evento
            const archivos = e.target.files;
            // Si se selecciona un archivo, se asigna a la variable local "archivo"
            if (archivos && archivos.length > 0) {
                // Asigna el archivo a la variable local "archivo"
                this.formulario.imagen_url_file = archivos[0];
                // Crea una URL temporal para previsualizar la imagen
                this.formulario.imagen_url_preview = URL.createObjectURL(archivos[0]);
            }
        },
        // Prepara los datos del formulario para enviar al servidor
        datosFormulario() {
            // Crea un objeto FormData para enviar los datos al servidor
            const formData = new FormData();
            // Añade los datos del formulario al objeto FormData
            formData.append('nombre', this.formulario.nombre);
            formData.append('descripcion', this.formulario.descripcion || '');
            // Añade la sucursal_id si existe
            if (this.formulario.sucursal_id) formData.append('sucursal_id', this.formulario.sucursal_id);
            // Añade la categoria_prestacion_id si existe
            if (this.formulario.categoria_prestacion_id) formData.append('categoria_prestacion_id', this.formulario.categoria_prestacion_id);
            // Añade la imagen_url_file si existe
            if (this.formulario.imagen_url_file) {
                formData.append('imagen_url', this.formulario.imagen_url_file);
            }
            // Retorna el objeto FormData
            return formData;
        },
        
        //Abre el modal de creación
        abrirModalCrear() {
            // Cambia el modo de edición
            this.modoEdicion = false;
            this.boxEditando = null;
            // Limpia el formulario
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
        //Abre el modal de edición
        abrirModalEditar(box) {
            // Cambia el modo de edición
            this.modoEdicion = true;
            this.boxEditando = box;
            // Carga los datos del box en el formulario
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
        //Obtiene los boxes
        obtenerBoxes() {
            this.cargando = true;
            axios.get('/boxes', { params: { texto: this.filtroTexto, categoria_prestacion_id: this.filtroCategoria } })
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
        //Limpia los filtros
        limpiarFiltros() {
            this.filtroTexto = '';
            this.filtroCategoria = '';
            this.obtenerBoxes();
        },
        //Cierra el modal y lo limpia
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

        //Guarda el box
        guardar() {
            // Procesando
            this.formulario.processing = true;
            this.formulario.errors = {};
            // Datos del formulario
            const data = this.datosFormulario();
            // Si es edicion
            if (this.modoEdicion) {
                // Metodo put
                data.append('_method', 'PUT');
                // Peticion a la api
                axios.post(`/api/boxes/${this.boxEditando.id}`, data, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })
                // Si la peticion es exitosa
                .then(() => { 
                    // Cierra el modal
                    this.cerrarModal(); 
                    // Obtiene los boxes
                    this.obtenerBoxes();
                    // Si existe el alerta de exito
                    if (this.$alertaExito) {
                        // Muestra el alerta de exito
                        this.$alertaExito('Actualizado', 'El box se actualizó correctamente.');
                    }
                })
                // Si la peticion falla
                .catch((error) => { 
                    // Si el codigo de error es 422
                    if (error.response?.status === 422) {
                        // Muestra los errores
                        this.formulario.errors = error.response.data.errors; 
                    }
                })
                // Cuando termina la peticion
                .finally(() => { 
                    this.formulario.processing = false; 
                });
            } else {
                // Peticion a la api
                axios.post('/api/boxes', data, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })
                // Si la peticion es exitosa
                .then(() => { 
                    // Cierra el modal
                    this.cerrarModal(); 
                    // Obtiene los boxes
                    this.obtenerBoxes();
                    // Si existe el alerta de exito
                    if (this.$alertaExito) {
                        // Muestra el alerta de exito
                        this.$alertaExito('Creado', 'El box se creó correctamente.');
                    }
                })
                // Si la peticion falla
                .catch((error) => { 
                    // Si el codigo de error es 422
                    if (error.response?.status === 422) {
                        // Muestra los errores
                        this.formulario.errors = error.response.data.errors; 
                    }
                })
                // Cuando termina la peticion
                .finally(() => { 
                    this.formulario.processing = false; 
                });
            }
        },
        //Confirma la eliminacion del box
        confirmarEliminar(box) {
            // Establece el box a eliminar
            this.boxAEliminar = box;
            // Si existe el confirmar
            if (this.$confirmar) {
                // Muestra el confirmar
                this.$confirmar('¿Eliminar box?', `Se eliminará el box ${box.nombre} permanentemente.`)
                // Si se confirma
                .then((resultado) => {
                    // Si el resultado es confirmado
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
.btn-hover-elevate {
    transition: all 0.2s;
}
.btn-hover-elevate:hover {
    transform: translateY(-2px);
}
</style>
