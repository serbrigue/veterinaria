<template>
    <Head title="Mascotas" />
    <AuthenticatedLayout>
        <div class="container py-4">
            <div class="card shadow-sm">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h1 class="h5 mb-0">Razas</h1>
                    <button type="button" class="btn btn-primary" @click="abrirModalCrear">
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

                    <div v-else class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Especie</th>
                                    <th>imagen</th>
                                    <th v-if="esVeterinarioOAdmin" style="width: 180px">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="raza in razas" :key="raza.id">
                                    <td><Link :href="route('razas.detalle',raza.id)">{{ raza.nombre }}</Link></td>  
                                    <td>{{ raza.descripcion }}</td>
                                    <td>
                                        <Link :href="route('especies.detalle',raza.especie_id)">
                                        {{ raza.especie?.nombre }}
                                        </Link>
                                    </td>
                                    <td>
                                        <img :src="raza.imagen_url" :alt="'Imagen de ' + raza.nombre"  class="img-fluid" style="width: 100px; height: 100px;" @click="abrirModalImagen(raza)">
                                    </td>
                                    <td v-if="esVeterinarioOAdmin">
                                        <div class="btn-group btn-group-sm">
                                            <button
                                                type="button"
                                                class="btn btn-primary"
                                                @click="abrirModalEditar(raza)"
                                            >
                                                Editar
                                            </button>
                                            <button
                                                type="button"
                                                class="btn btn-danger"
                                                @click="confirmarEliminar(raza)"
                                            >
                                                Eliminar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
            
            <div v-if="mostrarModalImagen" class="modal fade show d-block" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Imagen de la raza: {{ razaSeleccionada.nombre }}</h5>
                            <button type="button" class="btn-close" @click="cerrarModalImagen"></button>
                        </div>
                        <div class="modal-body">
                            <img :src="razaSeleccionada.imagen_url" alt="Imagen de la raza" class="img-fluid">
                        </div>
                    </div>
                </div>
            </div>



            <div v-if="mostrarModal" class="modal-backdrop fade show"></div>
            <div v-if="mostrarModalImagen" class="modal-backdrop fade show"></div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from '@/Disenos/LayoutAutenticado.vue';
import { Head,Link } from '@inertiajs/vue3';

export default {
    components: {
        AuthenticatedLayout,
        Head,
        Link,
    },
    props: {
        razas: {
            type: Array,
            default: () => [],
        },
        especies:{
            type:Array,
            default:()=> [],
        },
    },
    data() {
        return {
            mostrarModalImagen: false,
            razaSeleccionada:null,
            cargando: false,
            mostrarModal: false,
            modoEdicion: false,
            razaEditando: null,
            mostrarConfirmacion: false,
            razasVisibles:[],
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
            return this.totalRazas === 0
        },
        sinResultadosFiltro() {
            return !this.cargando && this.totalRazas > 0 && this.razasVisibles.length === 0
        },
    },
    methods: {
        abrirModalImagen(raza){
            this.razaSeleccionada=raza;
            this.mostrarModalImagen=true;
        },
        cerrarModalImagen(){
            this.mostrarModalImagen=false;
            this.razaSeleccionada=null;
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
                    this.razasVisibles=response.data;
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
