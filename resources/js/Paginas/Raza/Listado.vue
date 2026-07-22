<template>
    <Head title="Mascotas" />
    <AuthenticatedLayout>
        <div class="container py-4">
            <div class="card shadow-sm">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h1 class="h5 mb-0">Razas</h1>
                    <div class="d-flex gap-2">
                        <template v-if="$isAdmin() || $isSecretaria()">
                            <a href="/api/export/razas" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-download me-1"></i> Exportar
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-primary" @click="mostrarModalImportar = true">
                                <i class="bi bi-upload me-1"></i> Importar
                            </button>
                        </template>
                        <button v-if="esVeterinario" type="button" class="btn btn-primary" @click="abrirModalCrear">
                            + Nueva Raza
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Barra de búsqueda y filtros -->
                    <BarraFiltros 
                        :deshabilitar-limpiar="!filtroTexto && !filtroEspecie" 
                        clase-boton-contenedor="col-12 col-md-2 col-lg-5 d-flex gap-2 justify-content-md-end"
                        @limpiar="limpiarFiltros"
                    >
                        <!-- Búsqueda de texto -->
                        <div class="col-12 col-md-6 col-lg-4">
                            <label class="form-label small fw-bold text-secondary mb-1">Buscar por nombre</label>
                            <input 
                                type="text" 
                                v-model="filtroTexto" 
                                class="form-control form-control-sm" 
                                @input="obtenerRazas()" 
                                placeholder="Buscar por nombre..."
                            >
                        </div>

                        <!-- Especie -->
                        <div class="col-12 col-md-4 col-lg-3">
                            <label class="form-label small fw-bold text-secondary mb-1">Especie</label>
                            <select 
                                v-model="filtroEspecie" 
                                @change="obtenerRazas()" 
                                class="form-select form-select-sm"
                            >
                                <option value="">Todas las especies</option>
                                <option
                                    v-for="op in especies"
                                    :key="op.id"
                                    :value="op.id"
                                >
                                    {{ op.nombre }}
                                </option>
                            </select>
                        </div>
                    </BarraFiltros>
                    <p v-show="!listaVacia" class="text-muted small mb-3">
                        {{ totalRazas }} razas registrada{{ totalRazas === 1 ? '' : 's' }}
                    </p>
                    <IndicadorCarga :cargando="cargando" mensaje="razas" />

                    <EstadoVacio
                        :visible="!cargando && listaVacia"
                        mensaje="No tienes razas registradas aún."
                        :texto-boton="esVeterinario ? 'Registrar tu primera raza' : ''"
                        icono="bi bi-bug-fill"
                        @accion="abrirModalCrear"
                    />

                    <SinResultados
                        :visible="!cargando && sinResultadosFiltro"
                        mensaje="Ninguna raza coincide con la búsqueda."
                        @limpiar="limpiarFiltros()"
                    />

                    <div v-if="!cargando && !listaVacia && !sinResultadosFiltro" class="row g-4">
                        <div v-for="raza in razas" :key="raza.id" class="col-12 col-md-6 col-lg-4 col-xl-3">
                            <TarjetaEntidad
                                :titulo="raza.nombre"
                                :imagen-url="raza.imagen_url"
                                icono="bi-bug-fill"
                                :url-detalle="`/razas/${raza.id}`"
                                :mostrar-acciones="esVeterinario"
                                @editar="abrirModalEditar(raza)"
                                @eliminar="confirmarEliminar(raza)"
                            >
                                <template #header-badge>
                                    <span class="badge bg-white text-dark mt-1 shadow-sm align-self-start">
                                        <i class="bi bi-tag-fill text-primary me-1"></i>{{ raza.especie?.nombre || 'Sin especie' }}
                                    </span>
                                </template>
                                <template #body>
                                    <p class="card-text text-muted small mb-3 flex-grow-1 line-clamp-3">
                                        {{ raza.descripcion || 'Sin descripción disponible para esta raza.' }}
                                    </p>
                                </template>
                            </TarjetaEntidad>
                        </div>
                    </div>

                    <!-- TODO: Agregar paginación cuando haya muchas mascotas -->
                </div>
            </div>

            <ModalCrud
                :visible="mostrarModal"
                :titulo="tituloModal"
                :modo-edicion="modoEdicion"
                :processing="formulario.processing"
                texto-guardar="Guardar Cambios"
                texto-crear="Crear Raza"
                @cerrar="cerrarModal"
                @guardar="guardar"
            >
                <div class="mb-3">
                    <label for="nombre" class="form-label fw-semibold text-secondary small text-uppercase">Nombre</label>
                    <input
                        id="nombre"
                        v-model="formulario.nombre"
                        type="text"
                        class="form-control bg-light border-0 py-2"
                        placeholder="Ej: Golden Retriever"
                        :class="{ 'is-invalid': formulario.errors.nombre }"
                        required
                    />
                    <div v-if="formulario.errors.nombre" class="invalid-feedback">
                        {{ formulario.errors.nombre }}
                    </div>
                </div>
                <div class="mb-3">
                    <label for="descripcion" class="form-label fw-semibold text-secondary small text-uppercase">Descripción</label>
                    <textarea
                        id="descripcion"
                        v-model="formulario.descripcion"
                        class="form-control bg-light border-0 py-2"
                        rows="3"
                        placeholder="Detalles sobre características o comportamiento..."
                        :class="{ 'is-invalid': formulario.errors.descripcion }"
                        required
                    ></textarea>
                    <div v-if="formulario.errors.descripcion" class="invalid-feedback">
                        {{ formulario.errors.descripcion }}
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
                <div class="mb-3">
                    <label for="foto" class="form-label fw-semibold text-secondary small text-uppercase">Foto de la Raza</label>
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
                    <div v-if="formulario.imagen" class="mt-2 text-center">
                        <img :src="formulario.imagen" class="img-thumbnail" style="max-height: 120px;" alt="Vista previa de la raza" />
                    </div>
                </div>
            </ModalCrud>

            <ModalImportarSimple
                :visible="mostrarModalImportar"
                entidad="razas"
                etiqueta="Razas"
                @cerrar="mostrarModalImportar = false"
                @importado="obtenerRazas()"
            />
        </div>
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
import ModalImportarSimple from '@/Componentes/ModalImportarSimple.vue';

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
        ModalImportarSimple,
    },
    props: {
        especies:{
            type:Array,
            default:()=> [],
        },
    },
    data() {
        return {
            cargando: false,
            mostrarModal: false,
            modoEdicion: false,
            razaEditando: null,
            mostrarConfirmacion: false,
            razas:[],
            filtroEspecie:'',
            filtroTexto:'',
            razaAEliminar: null,
            mostrarModalImportar: false,
            eliminando: false,
            formulario: {
                nombre: '',
                descripcion: '',
                especie_id: '',
                imagen: '',
                foto: null,
                errors: {},
                processing: false,
            },
        }
    },
    computed: {
        esVeterinario() {
            const user = this.$page.props.auth.user;
            return user && (user.rol?.nombre_interno === 'veterinario');
        },
        textoBotonGuardar() {
            return this.modoEdicion ? 'Guardar cambios' : 'Crear raza';
        },
        textoBotonCancelar() {
            return this.modoEdicion ? 'Cancelar' : 'Cerrar';
        },
        tituloModal() {
            return this.modoEdicion ? 'Editar raza' : 'Crear raza';
        },
        totalRazas() {
            return this.razas.length
        },
        listaVacia() {
            return !this.cargando && this.razas.length === 0 && !this.filtroTexto && !this.filtroEspecie;
        },
        sinResultadosFiltro() {
            return !this.cargando && this.razas.length === 0 && (this.filtroTexto || this.filtroEspecie);
        },
    },
    methods: {
        seleccionarFoto(e) {
            const archivos = e.target.files;
            if (archivos && archivos.length > 0) {
                this.formulario.foto = archivos[0];
            }
        },
        limpiarFiltros(){
            this.filtroTexto='';
            this.filtroEspecie='';
            this.obtenerRazas();
        },
        abrirModalCrear() {
            this.modoEdicion=false;
            this.razaEditando=null;
            this.formulario.nombre='';
            this.formulario.descripcion='';
            this.formulario.especie_id='';
            this.formulario.imagen='';
            this.formulario.foto=null;
            if (this.$refs.fotoInput) {
                this.$refs.fotoInput.value = '';
            }
            this.formulario.errors={};
            this.mostrarModal=true;
        },
        datosFormulario(){
            const formData = new FormData();
            formData.append('nombre', this.formulario.nombre);
            if (this.formulario.descripcion) {
                formData.append('descripcion', this.formulario.descripcion);
            }
            formData.append('especie_id', this.formulario.especie_id);
            if (this.formulario.foto) {
                formData.append('foto', this.formulario.foto);
            } else if (this.formulario.imagen) {
                formData.append('imagen_url', this.formulario.imagen);
            }
            return formData;
        },                                          

        abrirModalEditar(raza) {
            this.modoEdicion=true;
            this.razaEditando=raza;
            this.formulario.nombre=raza.nombre;
            this.formulario.descripcion=raza.descripcion;
            this.formulario.especie_id=raza.especie_id;
            this.formulario.imagen=raza.imagen_url;
            this.formulario.foto=null;
            if (this.$refs.fotoInput) {
                this.$refs.fotoInput.value = '';
            }
            this.formulario.errors={};
            this.mostrarModal=true;
        },

        cerrarModal() {
            this.mostrarModal=false;
            this.modoEdicion=false;
            this.razaEditando=null;
            this.formulario.nombre='';
            this.formulario.descripcion='';
            this.formulario.especie_id='';
            this.formulario.imagen='';
            this.formulario.foto=null;
            if (this.$refs.fotoInput) {
                this.$refs.fotoInput.value = '';
            }
            this.formulario.errors={};
        },

        validarEspecieRaza(){
          if(this.filtroEspecie){
            return this.raza.especie_id===this.filtroEspecie;
          }
          return true;
        },

        obtenerRazas(){
            this.cargando=true;
            axios.get('/razas',{params:{texto:this.filtroTexto,especie_id:this.filtroEspecie}})
                .then(response => {
                    this.razas=response.data.razas;
                })
                .catch(error => {
                    console.error('Error al obtener las razas:', error);
                })
                .finally(() => {
                    this.cargando=false;
                })
        },
        guardar() {
            this.formulario.processing=true;
            this.formulario.errors={};
            if(this.modoEdicion){
                this.actualizarRaza();
            }else{
                this.crearRaza();
            }
        },
        actualizarRaza(){
            const data = this.datosFormulario();
            data.append('_method', 'PUT');
            axios.post(`/api/razas/${this.razaEditando.id}`, data, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
            .then(() => { this.cerrarModal(); this.obtenerRazas(); })
            .catch((error) => { if (error.response?.status === 422) this.formulario.errors = error.response.data.errors })
            .finally(() => { this.formulario.processing = false });
        },
        crearRaza(){
            axios.post('/api/razas', this.datosFormulario(), {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
            .then(() => { this.cerrarModal(); this.obtenerRazas(); })
            .catch((error) => { if (error.response?.status === 422) this.formulario.errors = error.response.data.errors })
            .finally(() => { this.formulario.processing = false });
        },
        confirmarEliminar(raza) {
            this.razaAEliminar = raza;
            this.$confirmar('¿Eliminar raza?', `Se eliminará a ${raza.nombre}.`)
                .then((resultado) => {
                    if (resultado.isConfirmed) return this.eliminarRaza();
                })
        },
        eliminarRaza() {
            axios.delete(`/api/razas/${this.razaAEliminar.id}`)
            .then(() => { this.cerrarModal(); this.obtenerRazas(); })
            .catch((error) => { if (error.response?.status === 422) this.formulario.errors = error.response.data.errors })
            .finally(() => { this.formulario.processing = false });
        },
    },
    mounted(){
        this.obtenerRazas();
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
</style>
