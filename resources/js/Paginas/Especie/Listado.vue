<template>
    <!-- ================================================================================== -->
    <!-- COMPONENTE: Listado -->
    <!-- ================================================================================== -->
    <Head title="Especies" />
    <AuthenticatedLayout>
        <div class="container py-4">
            <div class="card shadow-sm">
                <div class="card-header border-0 bg-white p-4 d-flex justify-content-between align-items-center flex-wrap gap-3 rounded-top-4 border-bottom border-light">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary bg-opacity-10 p-2 rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px;">
                            <img src="/images/icon_species.png" alt="Icono Especies" class="w-100 h-100 object-fit-contain" style="transform: scale(1.15);">
                        </div>
                        <h1 class="h4 mb-0 fw-bold text-dark">Gestión de Especies</h1>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <!-- Si es admin o secretaria, muestra los botones de importar y exportar -->
                        <template v-if="$isAdmin() || $isSecretaria()">
                            <!-- Exportar especies -->
                            <a href="/api/export/especies" class="btn btn-light text-success fw-bold rounded-pill shadow-sm btn-hover-elevate">
                                <i class="bi bi-download me-1"></i> Exportar
                            </a>
                            <!-- Importar especies -->
                            <button type="button" class="btn btn-light text-primary fw-bold rounded-pill shadow-sm btn-hover-elevate" @click="mostrarModalImportar = true">
                                <i class="bi bi-upload me-1"></i> Importar
                            </button>
                        </template>
                        <!-- Si es veterinario, muestra el botón de crear especie -->
                        <button v-if="$isVeterinario()" type="button" class="btn btn-primary fw-bold rounded-pill shadow-sm btn-hover-elevate px-4" @click="abrirModalCrear">
                            <i class="bi bi-plus-lg me-1"></i> Nueva Especie
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Barra de búsqueda -->
                    <BarraFiltros 
                        :deshabilitar-limpiar="!filtroTexto" 
                        clase-boton-contenedor="col-12 col-md-4 col-lg-6 d-flex justify-content-md-end"
                        @limpiar="limpiarFiltros"
                    >
                        <div class="col-12 col-md-8 col-lg-6">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                <!-- Almacenamos el texto de filtro -->
                                <input 
                                    type="text" 
                                    v-model="filtroTexto" 
                                    class="form-control" 
                                    @input="obtenerEspecies()" 
                                    placeholder="Buscar por nombre..."
                                >
                            </div>
                        </div>
                        <template #texto-limpiar>
                            Limpiar Filtro
                        </template>
                    </BarraFiltros>

                    <!-- Total de especies -->
                    <p v-show="!listaVacia" class="text-muted small mb-3">
                        {{ totalEspecies }} especie{{ totalEspecies === 1 ? '' : 's' }} registrada{{ totalEspecies === 1 ? '' : 's' }}
                    </p>

                    <!-- Indicador de carga -->
                    <IndicadorCarga :cargando="cargando" mensaje="especies" />

                    <!-- Estado vacío (sin especies) -->
                    <EstadoVacio
                        :visible="!cargando && listaVacia"
                        mensaje="No tienes especies registradas aún."
                        :texto-boton="$isVeterinario() ? 'Registrar tu primera especie' : ''"
                        icono="bi bi-clipboard2-x"
                        @accion="abrirModalCrear"
                    />

                    <!-- Sin resultados (sin resultado filtros)-->
                    <SinResultados
                        :visible="!cargando && sinResultadosFiltro"
                        mensaje="Ninguna especie coincide con la búsqueda."
                        @limpiar="limpiarFiltros()"
                    />

                    <!-- Grid de especies -->
                    <!-- Si no esta cargando, no esta vacio y no hay resultados de filtro se muestra el grid -->
                    <div v-if="!cargando && !listaVacia && !sinResultadosFiltro" class="row g-4">
                        <!-- Itera sobre las especies visibles -->
                        <div v-for="especie in especiesVisibles" :key="especie.id" class="col-12 col-md-6 col-lg-4 col-xl-3">
                            <!-- Tarjeta de entidad -->
                            <TarjetaEntidad
                                :titulo="especie.nombre"
                                :imagen-url="especie.imagen_url"
                                icono="bi-bug-fill"
                                :url-detalle="`/especies/${especie.id}`"
                                :mostrar-acciones="$isVeterinario()"
                                @editar="abrirModalEditar(especie)"
                                @eliminar="confirmarEliminar(especie)"
                            >
                                <template #body>
                                    <p class="card-text text-muted small mb-3 flex-grow-1 line-clamp-3">
                                        {{ especie.descripcion || 'Sin descripción disponible para esta especie.' }}
                                    </p>
                                </template>
                            </TarjetaEntidad>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de creación/edición -->
            <ModalCrud
                :visible="mostrarModal"
                :titulo="tituloModal"
                :modo-edicion="modoEdicion"
                :processing="formulario.processing"
                texto-guardar="Guardar Cambios"
                texto-crear="Crear Especie"
                @cerrar="cerrarModal"
                @guardar="guardar"
            >
                <!-- Campos del formulario -->
                <div class="mb-3">
                    <label for="nombre" class="form-label fw-semibold text-secondary small text-uppercase">Nombre de la Especie</label>
                    <!-- Almacenamos el nombre de la especie -->
                    <input
                        id="nombre"
                        v-model="formulario.nombre"
                        type="text"
                        class="form-control bg-light border-0 py-2"
                        placeholder="Ej: Felinos"
                        :class="{ 'is-invalid': formulario.errors.nombre }"
                        required
                    />
                    <!-- Si hay un error en el nombre se muestra el mensaje de error -->
                    <div v-if="formulario.errors.nombre" class="invalid-feedback">
                        {{ formulario.errors.nombre }}
                    </div>
                </div>
                <div class="mb-3">
                    <label for="descripcion" class="form-label fw-semibold text-secondary small text-uppercase">Descripción</label>
                    <!-- Almacenamos la descripción de la especie -->
                    <textarea
                        id="descripcion"
                        v-model="formulario.descripcion"
                        class="form-control bg-light border-0 py-2"
                        rows="3"
                        placeholder="Descripción corta de la especie..."
                        :class="{ 'is-invalid': formulario.errors.descripcion }"
                    ></textarea>
                    <!-- Si hay un error en la descripción se muestra el mensaje de error -->
                    <div v-if="formulario.errors.descripcion" class="invalid-feedback">
                        {{ formulario.errors.descripcion }}
                    </div>
                </div>
                <div class="mb-3">
                    <label for="foto" class="form-label fw-semibold text-secondary small text-uppercase">Foto de la Especie</label>
                    <input
                        id="foto"
                        ref="fotoInput"
                        type="file"
                        class="form-control bg-light border-0 py-2"
                        accept="image/*"
                        @change="seleccionarFoto"
                        :class="{ 'is-invalid': formulario.errors.foto }"
                    />
                    <!-- Si hay un error en la foto se muestra el mensaje de error -->
                    <div v-if="formulario.errors.foto" class="invalid-feedback">
                        {{ formulario.errors.foto }}
                    </div>
                    <!-- Si hay una imagen de la especie se muestra la vista previa -->
                    <div v-if="formulario.imagen_url" class="mt-2 text-center">
                        <img :src="formulario.imagen_url" class="img-thumbnail" style="max-height: 120px;" alt="Vista previa de la especie" />
                    </div>
                </div>
            </ModalCrud>

            <!-- Modal de importar simple -->
            <ModalImportarSimple
                :visible="mostrarModalImportar"
                entidad="especies"
                etiqueta="Especies"
                @cerrar="mostrarModalImportar = false"
                @importado="obtenerEspecies()"
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
        especies: {
            type: Array,
            default: () => [],
        },
    },
    // ESTADO REACTIVO: Variables locales del componente
    data() {
        return {
            //inicializamos variables
            cargando: false,
            mostrarModal: false,
            modoEdicion: false,
            especieEditando: null,
            filtroTexto: '',
            especieAEliminar: null,
            mostrarModalImportar: false,
            //inicializamos el formulario
            formulario: {
                nombre: '',
                descripcion: '',
                imagen_url: '',
                foto: null,
                errors: {},
                processing: false,
            },
            // lista de especies visibles para filtrar y mostrar
            especiesVisibles: this.especies,
        }
    },
    // OBSERVADORES: Reaccionan a cambios en propiedades o variables
    watch: {
        // Actualiza las especies visibles cuando cambian las especies o el filtro de busqueda 
        especies(nuevasEspecies) {
            this.especiesVisibles = nuevasEspecies;
        }
    },
    // PROPIEDADES COMPUTADAS: Variables reactivas que dependen de otras
    computed: {
        //Alterna el texto del boton dependiendo si es edicion o creacion   
        textoBotonGuardar() {
            return this.modoEdicion ? 'Guardar Cambios' : 'Crear Especie';
        },
        //Alterna el titulo del modal dependiendo si es edicion o creacion
        tituloModal() {
            return this.modoEdicion ? 'Editar Especie' : 'Nueva Especie';
        },
        //Total de especies visibles
        totalEspecies() {
            return this.especiesVisibles.length;
        },
        //Lista vacia
        listaVacia() {
            return this.especiesVisibles.length === 0 && this.filtroTexto === '';
        },
        //Sin resultados debido al filtro de busqueda
        sinResultadosFiltro() {
            return this.especiesVisibles.length === 0 && this.filtroTexto !== '';
        },
    },
    // METODOS: Bloque de funciones y eventos
    methods: {
        //Almacena la foto seleccionada
        seleccionarFoto(e) {
            const archivos = e.target.files;
            if (archivos && archivos.length > 0) {
                this.formulario.foto = archivos[0];
            }
        },
        //Obtiene los datos del formulario
        datosFormulario() {
            //Lo pasamos a FormData para poder enviar archivos
            const formData = new FormData();
            formData.append('nombre', this.formulario.nombre);
            //Si existe descripcion la pasamos a FormData
            if (this.formulario.descripcion) {
                formData.append('descripcion', this.formulario.descripcion);
            }
            //Si existe foto la pasamos a FormData
            if (this.formulario.foto) {
                formData.append('foto', this.formulario.foto);
            } else if (this.formulario.imagen_url) {
                formData.append('imagen_url', this.formulario.imagen_url);
            }
            return formData;
        },
        //Obtiene todas las especies de la base de datos
        obtenerEspecies() {

            this.cargando = true;
            axios.get('/especies', {
                params: { texto: this.filtroTexto }
            })
            .then((response) => { 
                this.especiesVisibles = response.data.especies; 
            })
            .catch((error) => { 
                console.error(error); 
            })
            .finally(() => { 
                this.cargando = false; 
            });
        },
        //Abre el modal para crear una especie
        abrirModalCrear() {
            this.modoEdicion = false;
            this.especieEditando = null;
            this.formulario.nombre = '';
            this.formulario.descripcion = '';
            this.formulario.imagen_url = '';
            this.formulario.foto = null;
            if (this.$refs.fotoInput) {
                this.$refs.fotoInput.value = '';
            }
            this.formulario.errors = {};
            this.mostrarModal = true;
        },
        //Abre el modal para editar una especie
        abrirModalEditar(especie) {
            this.modoEdicion = true;
            this.especieEditando = especie;
            this.formulario.nombre = especie.nombre;
            this.formulario.descripcion = especie.descripcion;
            this.formulario.imagen_url = especie.imagen_url;
            this.formulario.foto = null;
            if (this.$refs.fotoInput) {
                this.$refs.fotoInput.value = '';
            }
            this.formulario.errors = {};
            this.mostrarModal = true;
        },
        //Limpia los filtros
        limpiarFiltros() {
            this.filtroTexto = '';
            this.obtenerEspecies();
        },
        //Cierra el modal
        cerrarModal() {
            this.modoEdicion = false;
            this.especieEditando = null;
            this.formulario.nombre = '';
            this.formulario.descripcion = '';
            this.formulario.imagen_url = '';
            this.formulario.foto = null;
            if (this.$refs.fotoInput) {
                this.$refs.fotoInput.value = '';
            }
            this.formulario.errors = {};
            this.mostrarModal = false;
        },
        //Guarda la especie, ya sea creando una nueva o editando una existente
        guardar() {
            this.formulario.processing = true;
            this.formulario.errors = {};
            //Se prepara el formulario para enviar los datos
           const data = this.datosFormulario();
           //Si es edicion, se agrega el metodo PUT
            if (this.modoEdicion) {
                //Agrega el metodo PUT al formulario
                data.append('_method', 'PUT');
                //Se envian los datos al servidor
                axios.post(`/api/especies/${this.especieEditando.id}`, data, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                })
                //Si la respuesta es exitosa
                .then(() => { 
                    //Cierra el modal
                    this.cerrarModal(); 
                    //Obtiene las especies
                    this.obtenerEspecies(); 
                    //Muestra alerta de exito
                    if (this.$alertaExito) {
                        this.$alertaExito('Actualizado', 'La especie se actualizó correctamente.');
                    }
                })
                //Si hay error
                .catch((error) => { 
                    if (error.response?.status === 422) {
                        this.formulario.errors = error.response.data.errors; 
                    }
                })
                //Al finalizar
                .finally(() => { 
                    //Finaliza el procesamiento
                    this.formulario.processing = false; 
                });
            } else {
                //Se envian los datos al servidor
                axios.post('/api/especies', data, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                })
                //Si la respuesta es exitosa
                .then(() => { 
                    //Cierra el modal
                    this.cerrarModal(); 
                    //Obtiene las especies
                    this.obtenerEspecies(); 
                    //Muestra alerta de exito
                    if (this.$alertaExito) {
                        this.$alertaExito('Creado', 'La especie se creó correctamente.');
                    }
                }) 
                //Si hay error
                .catch((error) => { 
                    if (error.response?.status === 422) {
                        this.formulario.errors = error.response.data.errors; 
                    }
                })
                //Al finalizar
                .finally(() => { 
                    //Finaliza el procesamiento
                    this.formulario.processing = false; 
                });
            }
        },
        //Confirma la eliminacion de una especie
        confirmarEliminar(especie) {
            //Establece la especie a eliminar
            this.especieAEliminar = especie;
            //Si existe el confirmador
            if (this.$confirmar) {
                //Muestra el confirmador
                this.$confirmar('¿Eliminar especie?', `Se eliminará a ${especie.nombre} permanentemente.`)
                .then((resultado) => {
                    //Si se confirma la eliminacion
                    if (resultado.isConfirmed) {
                        //Elimina la especie
                        this.eliminarEspecie();
                    }
                });
                //Si no existe el confirmador
            } else if (confirm(`¿Estás seguro de eliminar la especie ${especie.nombre}?`)) {
                //Elimina la especie
                this.eliminarEspecie();
            }
        },
        //Elimina una especie
        eliminarEspecie() {
            //Se envia la peticion de eliminacion
            axios.delete(`/api/especies/${this.especieAEliminar.id}`)
            //Si la respuesta es exitosa
            .then(() => { 
                //Obtiene las especies
                this.obtenerEspecies(); 
                //Muestra alerta de exito
                if (this.$alertaExito) {
                    this.$alertaExito('Eliminado', 'La especie se eliminó correctamente.');
                }
            })
            //Si hay error
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
.btn-hover-elevate {
    transition: all 0.2s;
}
.btn-hover-elevate:hover {
    transform: translateY(-2px);
}
</style>
