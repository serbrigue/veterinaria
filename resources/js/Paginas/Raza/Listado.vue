<template>
    <Head title="Mascotas" />
    <AuthenticatedLayout>
        <div class="container py-4">
            <div class="card shadow-sm">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h1 class="h5 mb-0">Razas</h1>
                    <button  v-if="esVeterinarioOAdmin"  type="button" class="btn btn-primary" @click="abrirModalCrear">
                        + Nueva Raza
                    </button>
                </div>

                <div class="card-body">
                    <!-- Barra de búsqueda y filtros -->
                    <div class="bg-light p-3 rounded-3 border mb-4 shadow-sm">
                        <div class="row g-3 align-items-end">
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

                            <!-- Limpiar Filtros -->
                            <div class="col-12 col-md-2 col-lg-5 d-flex gap-2 justify-content-md-end">
                                <button class="btn btn-outline-secondary btn-sm" @click="limpiarFiltros()">
                                    Limpiar Filtros
                                </button>
                            </div>
                        </div>
                    </div>
                    <p v-show="!listaVacia" class="text-muted small mb-3">
                        {{ totalRazas }} razas registrada{{ totalRazas === 1 ? '' : 's' }}
                    </p>
                    <div v-if="cargando" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted">Cargando razas...</p>
                    </div>

                    <!-- Estado vacío -->
                    <div v-else-if="listaVacia" class="text-center py-5">
                        <p class="text-muted mb-3">No tienes razas registradas aún.</p>
                        <button type="button" class="btn btn-primary" @click="abrirModalCrear">
                            Registrar tu primera raza
                        </button>
                    </div>

                    <div v-else-if="sinResultadosFiltro" class="text-center py-5">
                        <p class="text-muted mb-3">Ninguna raza coincide con el filtro.</p>
                        <button type="button" class="btn btn-outline-secondary btn-sm" @click="limpiarFiltros()">
                            Quitar filtro
                        </button>
                    </div>

                    
                        <div v-else class="row g-4">
                        <div v-for="raza in razas" :key="raza.id" class="col-12 col-md-6 col-lg-4 col-xl-3">
                            
                                <div 
                                    class="card h-100 border-0 shadow-sm hover-elevate transition-all overflow-hidden group-card cursor-pointer"
                                    
                                >
                                    <!-- Imagen Header -->
                                    <div class="position-relative bg-light" style="height: 160px;">
                                        <Link :href="`/razas/${raza.id}`">
                                            <img 
                                                v-if="raza.imagen_url" 
                                                :src="raza.imagen_url" 
                                                class="w-100 h-100 object-fit-cover hover-zoom" 
                                                alt="Imagen de raza"
                                            >
                                            <div v-else class="w-100 h-100 d-flex align-items-center justify-content-center text-primary bg-primary bg-opacity-10">
                                                <i class="bi bi-bug-fill fs-1"></i>
                                            </div>
                                            <!-- Overlay gradient -->
                                            <div class="position-absolute bottom-0 start-0 w-100 h-50 bg-gradient-dark"></div>
                                            <!-- Title badge overlapping -->
                                            <div class="position-absolute bottom-0 start-0 w-100 p-3 pb-2 text-white">
                                                <h3 class="h5 mb-0 fw-bold text-shadow text-truncate">{{ raza.nombre }}</h3>
                                                <span class="badge bg-white text-dark mt-1 shadow-sm"><i class="bi bi-tag-fill text-primary me-1"></i>{{ raza.especie?.nombre || 'Sin especie' }}</span>
                                            </div>
                                        </Link>
                                    </div>
                                    
                                    <div class="card-body p-3 d-flex flex-column">
                                        <p class="card-text text-muted small mb-3 flex-grow-1 line-clamp-3">
                                            {{ raza.descripcion || 'Sin descripción disponible para esta raza.' }}
                                        </p>
                                        
                                        <div v-if="esVeterinarioOAdmin" class="d-flex gap-2 pt-3 border-top mt-auto justify-content-between">
                                            <button 
                                                class="btn btn-sm btn-light text-primary border border-primary-subtle flex-grow-1 btn-hover-primary transition-all" 
                                                @click.stop="abrirModalEditar(raza)"
                                            >
                                                <i class="bi bi-pencil me-1"></i> Editar
                                            </button>
                                            <button 
                                                class="btn btn-sm btn-light text-danger border border-danger-subtle flex-grow-1 btn-hover-danger transition-all" 
                                                @click.stop="confirmarEliminar(raza)"
                                            >
                                                <i class="bi bi-trash me-1"></i> Eliminar
                                            </button>
                                        </div>
                                    </div>
                                </div>  
                        </div>
                    </div>

                    <!-- TODO: Agregar paginación cuando haya muchas mascotas -->
                </div>
            </div>

            <!-- ========================================== -->
            <!-- MODAL: Crear / Editar Mascota              -->
            <!-- ========================================== -->
            <div v-if="mostrarModal" class="modal fade show d-block" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ tituloModal }}</h5>
                            <button type="button" class="btn-close" @click="cerrarModal"></button>
                        </div>
                        <div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input
                                        id="nombre"
                                        v-model="formulario.nombre"
                                        type="text"
                                        class="form-control"
                                        :class="{ 'is-invalid': formulario.errors.nombre }"
                                        required
                                    />
                                    <div v-if="formulario.errors.nombre" class="invalid-feedback">
                                        {{ formulario.errors.nombre }}
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <textarea
                                        id="descripcion"
                                        v-model="formulario.descripcion"
                                        class="form-control"
                                        :class="{ 'is-invalid': formulario.errors.descripcion }"
                                        rows="3"
                                        required
                                    ></textarea>
                                    <div v-if="formulario.errors.descripcion" class="invalid-feedback">
                                        {{ formulario.errors.descripcion }}
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="especie_id" class="form-label">Especie</label>
                                    <select
                                        id="especie_id"
                                        v-model="formulario.especie_id"
                                        class="form-select"
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
                                    <label for="imagen" class="form-label">Imagen</label>
                                    <input
                                        id="imagen"
                                        v-model="formulario.imagen"
                                        type="text"
                                        class="form-control"
                                        :class="{ 'is-invalid': formulario.errors.imagen }"
                                        placeholder="URL de la imagen"
                                    />
                                    <div v-if="formulario.errors.imagen" class="invalid-feedback">
                                        {{ formulario.errors.imagen }}
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
import { Head, Link, router } from '@inertiajs/vue3';

export default {
    components: {
        AuthenticatedLayout,
        Head,
        Link,
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
            eliminando: false,
            formulario: {
                nombre: '',
                descripcion: '',
                especie_id: '',
                imagen: '',
                errors: {},
                processing: false,
            },
        }
    },
    computed: {
        esVeterinarioOAdmin() {
            const user = this.$page.props.auth.user;
            return user && (user.rol?.nombre_interno === 'veterinario' || user.rol?.nombre_interno === 'admin');
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
            this.formulario.errors={};
            this.mostrarModal=true;
        },
        datosFormulario(){
            return {
                nombre: this.formulario.nombre,
                descripcion: this.formulario.descripcion,
                especie_id: this.formulario.especie_id,
                imagen_url: this.formulario.imagen,
            }
        },                                          

        abrirModalEditar(raza) {
            this.modoEdicion=true;
            this.razaEditando=raza;
            this.formulario.nombre=raza.nombre;
            this.formulario.descripcion=raza.descripcion;
            this.formulario.especie_id=raza.especie_id;
            this.formulario.imagen=raza.imagen_url;
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
        limpiarFiltros(){
            this.filtroTexto='';
            this.filtroEspecie='';
            this.obtenerRazas();
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
            axios.put(`/api/razas/${this.razaEditando.id}`, { ...this.datosFormulario() })
                .then(() => { this.cerrarModal(); this.obtenerRazas(); })
                .catch((error) => { if (error.response?.status === 422) this.formulario.errors = error.response.data.errors })
                .finally(() => { this.formulario.processing = false });
        },
        crearRaza(){
            axios.post('/api/razas', { ...this.datosFormulario() })
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
.hover-elevate:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
}
.transition-all {
    transition: all 0.3s ease;
}
.hover-zoom {
    transition: transform 0.5s ease;
}
.group-card:hover .hover-zoom {
    transform: scale(1.08);
}
.bg-gradient-dark {
    background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0) 100%);
}
.text-shadow {
    text-shadow: 0 2px 4px rgba(0,0,0,0.6);
}
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.btn-hover-primary:hover {
    background-color: var(--bs-primary);
    color: white !important;
}
.btn-hover-danger:hover {
    background-color: var(--bs-danger);
    color: white !important;
}
.object-fit-cover {
    object-fit: cover;
}
.cursor-pointer {
    cursor: pointer;
}
</style>
