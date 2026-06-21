<template>
    <Head title="Citas" />
    <AuthenticatedLayout>
        <div class="container py-4">
            <div class="card shadow-sm">


                <div class="card-header d-flex justify-content-between align-items-center">
                    <h1 class="h5 mb-0">Mis Citas</h1>

                    <button type="button" class="btn btn-primary" @click="abrirModalCrear">
                        + Nueva Cita
                    </button>
                </div>

                <div class="card-body">
                    <!-- Barra de búsqueda y filtros -->
                    <div class="bg-light p-3 rounded-3 border mb-4 shadow-sm">
                        <div class="row g-3 align-items-end">
                            <div>
                                <label class="form-label small fw-bold text-secondary mb-1" for="filtroTitulo">Buscar por Titulo</label>
                                <input type="text" class="form-control form-control-sm" placeholder="Buscar por titulo" v-model="filtroTitulo" @keyup.enter="obtenerCitas">
                            </div>
                            <!-- Buscar por Mascota -->
                            <div class="col-12 col-md-4 col-lg-3">
                                <label class="form-label small fw-bold text-secondary mb-1" for="filtroMascota">Buscar por Mascota</label>
                                <select 
                                    class="form-select form-select-sm" 
                                    id="filtroMascota"
                                    v-model="filtroMascota"
                                    @change="obtenerCitas()"
                                >
                                    <option value="">Todas las mascotas</option>
                                    <option 
                                        v-for="mascota in mascotas" 
                                        :key="mascota.id" 
                                        :value="mascota.id"
                                    >
                                        {{ mascota.nombre }}
                                    </option>
                                </select>
                            </div>

                            <!-- Buscar por Cliente -->
                            <div class="col-12 col-md-4 col-lg-3">
                                <label class="form-label small fw-bold text-secondary mb-1" for="filtroCliente">Buscar por Cliente</label>
                                <select 
                                    class="form-select form-select-sm" 
                                    id="filtroCliente"
                                    v-model="filtroCliente"
                                    @change="obtenerCitas()"
                                >
                                    <option value="">Todos los clientes</option>
                                    <option 
                                        v-for="cliente in clientes" 
                                        :key="cliente.id" 
                                        :value="cliente.id"
                                    >
                                        {{ cliente.nombre }}
                                    </option>
                                </select>
                            </div>

                            <!-- Buscar por Veterinario -->
                            <div class="col-12 col-md-4 col-lg-3">
                                <label class="form-label small fw-bold text-secondary mb-1" for="filtroVeterinario">Buscar por Veterinario</label>
                                <select 
                                    class="form-select form-select-sm"
                                    id="filtroVeterinario"
                                    v-model="filtroVeterinario"
                                    @change="obtenerCitas()"
                                >
                                    <option value="">Todos los veterinarios</option>
                                    <option 
                                        v-for="veterinario in veterinarios" 
                                        :key="veterinario.id" 
                                        :value="veterinario.id"
                                    >
                                        {{ veterinario.nombre }}
                                    </option>
                                </select>
                            </div>

                            <!-- Limpiar Filtros -->
                            <div class="col-12 col-lg-3 d-flex gap-2 justify-content-lg-end">
                                <button class="btn btn-outline-secondary btn-sm" @click="limpiarFiltros()">
                                    Limpiar Filtros
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-if="cargando" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted">Cargando citas...</p>
                    </div>

                    <div v-else-if="citas.length === 0" class="text-center py-5">
                        <p class="text-muted mb-3">No tienes citas registradas aún.</p>
                        <button type="button" class="btn btn-primary" @click="abrirModalCrear">
                            Registrar tu primera cita
                        </button>
                    </div>

                    <div v-else class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Título</th>
                                    <th>Mascota</th>
                                    <th>Cliente</th>
                                    <th>Fecha y Hora</th>
                                    <th>Hora de Término (Aprox.)</th>
                                    <th>Veterinario</th>
                                    <th style="width: 180px">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="cita in citasVisibles" :key="cita.id">
                                    <td>
                                        <Link :href="route('citas.detalle', cita.id)">
                                        {{ cita.titulo }}
                                        </Link>
                                    </td>
                                    <td>{{ cita.mascota?.nombre }}</td>
                                    <td>{{ cita.cliente?.nombre }}</td>
                                    <td>{{ $formatoFecha(cita.fecha_hora, 'DD/MM/YYYY HH:mm') }}</td>
                                    <td>
                                        {{ cita.hora_termino 
                                            ? $formatoFecha(cita.hora_termino, 'HH:mm') 
                                            : '—'
                                        }}
                                    </td>
                                    <td>{{ cita.veterinario?.nombre }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button
                                                type="button"
                                                class="btn btn-primary"
                                                @click="abrirModalEditar(cita)"
                                            >
                                                Editar
                                            </button>
                                            <button
                                                type="button"
                                                class="btn btn-danger"
                                                @click="confirmarEliminar(cita)"
                                            >
                                                Eliminar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- TODO: Agregar paginación cuando haya muchas citas -->
                </div>
            </div>

            <!-- ========================================== -->
            <!-- MODAL: Crear / Editar Cita                 -->
            <!-- ========================================== -->
            <div v-if="mostrarModal" class="modal fade show d-block" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ modoEdicion ? 'Editar Cita' : 'Nueva Cita' }}</h5>
                            <button type="button" class="btn-close" @click="cerrarModal"></button>
                        </div>
                        <div>
                            <div class="modal-body">
                                <!-- TODO: Campo título -->
                                <div class="mb-3">
                                    <label for="titulo" class="form-label">Título</label>
                                    <input
                                        id="titulo"
                                        v-model="formulario.titulo"
                                        type="text"
                                        class="form-control"
                                        :class="{ 'is-invalid': formulario.errors.titulo }"
                                        required
                                    />
                                    <div v-if="formulario.errors.titulo" class="invalid-feedback">
                                        {{ formulario.errors.titulo }}
                                    </div>
                                </div>

                                <!-- TODO: Campo descripción -->
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

                                <!-- TODO: Campo fecha y hora -->
                                <div class="mb-3">
                                    <label for="fecha_hora" class="form-label">Fecha y Hora</label>
                                    <input
                                        id="fecha_hora"
                                        v-model="formulario.fecha_hora"
                                        type="datetime-local"
                                        class="form-control"
                                        :class="{ 'is-invalid': formulario.errors.fecha_hora }"
                                        required
                                    />
                                    <div v-if="formulario.errors.fecha_hora" class="invalid-feedback">
                                        {{ formulario.errors.fecha_hora }}
                                    </div>
                                </div>
                                 <div class="mb-3">
                                    <label for="cliente_id" class="form-label">Cliente</label>
                                    <select
                                        id="cliente_id"
                                        v-model="formulario.cliente_id"
                                        class="form-select"
                                        :class="{ 'is-invalid': formulario.errors.cliente_id }"
                                        required
                                    >
                                        <option value="" disabled>Selecciona un cliente</option>
                                        <option
                                            v-for="cliente in clientes"
                                            :key="cliente.id"
                                            :value="cliente.id"
                                            @click="obtenerMascotasCliente(cliente.id)"
                                        >
                                            {{ cliente.nombre }}{{ cliente.email ? ` — ${cliente.email}` : '' }}
                                        </option>
                                    </select>
                                    <div v-if="formulario.errors.cliente_id" class="invalid-feedback">
                                        {{ formulario.errors.cliente_id }}
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="mascota_id" class="form-label">Mascota</label>
                                    <select
                                        id="mascota_id"
                                        v-model="formulario.mascota_id"
                                        class="form-select"
                                        :class="{ 'is-invalid': formulario.errors.mascota_id }"
                                        required
                                    >
                                        <option value="" disabled>Selecciona una mascota</option>
                                        <option
                                            v-for="mascota in mascotasUsuario"
                                            :key="mascota.id"
                                            :value="mascota.id"
                                        >
                                            {{ mascota.nombre }} | {{ mascota.especie?.nombre }} | {{ mascota.sexo ? ` (${mascota.sexo})` : '' }} 
                                        </option>
                                    </select>
                                    <div v-if="formulario.errors.mascota_id" class="invalid-feedback">
                                        {{ formulario.errors.mascota_id }}
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="veterinario_id" class="form-label">Veterinario</label>
                                    <select
                                        id="veterinario_id"
                                        v-model="formulario.veterinario_id"
                                        class="form-select"
                                        :class="{ 'is-invalid': formulario.errors.veterinario_id }"
                                        required
                                    >
                                        <option value="" disabled>Selecciona un veterinario</option>
                                        <option
                                            v-for="vet in veterinarios"
                                            :key="vet.id"
                                            :value="vet.id"
                                        >
                                            {{ vet.nombre }} | {{ vet.especie?.nombre }}
                                        </option>
                                    </select>
                                    <div v-if="formulario.errors.veterinario_id" @change="verificarEspecialidad(formulario.mascota_id,formulario.veterinario_id)" class="invalid-feedback">
                                        {{ formulario.errors.veterinario_id }}
                                    </div>
                                </div>  
                                <div v-if="VerificacionEspecialidadInvalida" class="alert alert-danger">
                                    Verifica que el veterinario tenga la especialidad correcta para la mascota
                                </div>   
                            </div>
                        </div>
                        <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" @click="cerrarModal">
                                    Cancelar
                                </button>
                                <button type="button" class="btn btn-primary" :disabled="formulario.processing" @click="guardar">
                                    <span v-if="formulario.processing" class="spinner-border spinner-border-sm me-2"></span>
                                    {{ modoEdicion ? 'Guardar cambios' : 'Crear cita' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="mostrarModal" class="modal-backdrop fade show"></div>

            <!-- ========================================== -->
            <!-- MODAL: Confirmar Eliminación                -->
            <!-- ========================================== -->
            <div v-if="mostrarConfirmacion" class="modal fade show d-block" tabindex="-1">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirmar eliminación</h5>
                            <button type="button" class="btn-close" @click="mostrarConfirmacion = false"></button>
                        </div>
                        <div class="modal-body">
                            <!-- TODO: Mostrar título de la cita a eliminar -->
                            <p>¿Estás seguro de eliminar la cita <strong>{{ citaAEliminar?.titulo }}</strong>?</p>
                            <p class="text-muted small mb-0">Esta acción no se puede deshacer.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="mostrarConfirmacion = false">
                                Cancelar
                            </button>
                            <button type="button" class="btn btn-danger" :disabled="eliminando" @click="eliminarCita">
                                <span v-if="eliminando" class="spinner-border spinner-border-sm me-2"></span>
                                Sí, eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        
            <div v-if="mostrarConfirmacion" class="modal-backdrop fade show"></div>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from '@/Disenos/LayoutAutenticado.vue';
import { Head,Link } from '@inertiajs/vue3';

export default {
    components: {
        AuthenticatedLayout,
        Head,
        Link
    },
    props: {
        citas: {
            type: Array,
            default: () => [],
        },
        mascotas: {
            type: Array,
            default: () => [],
        },
        clientes: {
            type: Array,
            default: () => [],
        },
        veterinarios: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            cargando: false,
            mostrarModal: false,
            modoEdicion: false,
            citaEditando: null,
            mostrarConfirmacion: false,
            citaAEliminar: null,
            eliminando: false,
            filtroMascota:'',
            citasVisibles:this.citas,
            filtroCliente:'',
            filtroVeterinario: '',
            filtroTitulo: '',
            mascotasUsuario:[],
            formulario: {
                titulo: '',
                descripcion: '',
                fecha_hora: '',
                mascota_id: '',
                cliente_id: '',
                veterinario_id: '',
                errors: {},
                processing: false,
            },
        }
    },
    methods: {
        abrirModalCrear() {
            this.modoEdicion = false;
            this.citaEditando = null;
            this.formulario.titulo = '';
            this.formulario.descripcion = '';
            this.formulario.fecha_hora = '';
            this.formulario.mascota_id = '';
            this.formulario.cliente_id = '';
            this.formulario.veterinario_id = '';
            this.formulario.errors = {};
            this.mostrarModal = true;

            // Si hay un solo cliente en la lista, lo seleccionamos automáticamente y cargamos sus mascotas.
            if (this.clientes && this.clientes.length === 1) {
                this.formulario.cliente_id = this.clientes[0].id;
                this.obtenerMascotasCliente(this.clientes[0].id);
            }
        },
        datosFormulario() {
            return {
                titulo: this.formulario.titulo,
                descripcion: this.formulario.descripcion,
                fecha_hora: this.formulario.fecha_hora,
                mascota_id: this.formulario.mascota_id,
                cliente_id: this.formulario.cliente_id,
                veterinario_id: this.formulario.veterinario_id,
            };
        },

        abrirModalEditar(cita) {
            this.modoEdicion = true;
            this.citaEditando = cita;
            this.formulario.titulo = cita.titulo;
            this.formulario.descripcion = cita.descripcion;
            this.formulario.fecha_hora = cita.fecha_hora;
            this.formulario.mascota_id = cita.mascota_id;
            // Obtenemos el ID del cliente mapeado en el accessor o mascota
            this.formulario.cliente_id = cita.cliente?.id || cita.mascota?.cliente_id || '';
            this.formulario.veterinario_id = cita.veterinario_id;
            this.formulario.errors = {};
            this.mostrarModal = true;

            // Cargamos automáticamente la lista de mascotas del cliente asociado al editar
            if (this.formulario.cliente_id) {
                this.obtenerMascotasCliente(this.formulario.cliente_id);
            }
        },
        cerrarModal() {
            this.mostrarModal=false;
            this.modoEdicion=false;
            this.citaEditando=null;
            this.formulario.titulo='';
            this.formulario.descripcion='';
            this.formulario.fecha_hora='';
            this.formulario.mascota_id='';
            this.formulario.cliente_id='';
            this.formulario.errors={};
        },

        obtenerCitas(){
            this.cargando=true;
            axios.get('/citas',{params:{mascota_id:this.filtroMascota,cliente_id:this.filtroCliente,veterinario_id:this.filtroVeterinario,titulo:this.filtroTitulo}})
                .then(response => {
                    this.citasVisibles=response.data.citas;
                })
                .catch(error => {
                    console.error('Error al obtener las citas:', error);
                })
                .finally(() => {
                    this.cargando=false;
                })
        },
        obtenerMascotasCliente(cliente_id){
            this.cargando=true;
            axios.get(`/api/clientes/${cliente_id}/mascotas`)
                .then(response => {
                    this.mascotasUsuario=response.data;
                })
                .catch(error => {
                    console.error('Error al obtener las mascotas:', error);
                })
                .finally(() => {
                    this.cargando=false;
                })
        },
        verificarEspecialidad(mascota,veterinario){
          if (mascota.especie_id !== veterinario.especie_id){
            this.formulario.VerificacionEspecialidad=true;
            return this.formulario.VerificacionEspecialidad;
          }else{
            this.formulario.VerificacionEspecialidad=false;
            return this.formulario.VerificacionEspecialidad;
          }
        },
        validarEspecieRaza(raza){
          if (this.formulario.especie_id !== raza.especie_id){
            this.formulario.especieRaza=true;
            return this.formulario.especieRaza;
          }else{
            this.formulario.especieRaza=false;
            return this.formulario.especieRaza;
          }
        },
        limpiarFiltros(){
            this.filtroMascota='';
            this.filtroCliente='';
            this.filtroVeterinario='';
            this.filtroTitulo='';
            this.obtenerCitas();
        },
        guardar() {
            this.formulario.processing=true;
            this.formulario.errors={};
            if(this.modoEdicion){
                this.actualizarCita();
            }else{
                this.crearCita();
            }
        },
        actualizarCita(){
            axios.put(`/api/citas/${this.citaEditando.id}`, { ...this.datosFormulario() })
                .then(() => { this.cerrarModal(); this.obtenerCitas(); })
                .catch((error) => { if (error.response?.status === 422) this.formulario.errors = error.response.data.errors })
                .finally(() => { this.formulario.processing = false });
        },
        crearCita(){
            axios.post('/api/citas', { ...this.datosFormulario() })
                .then(() => { this.cerrarModal(); this.obtenerCitas(); })
                .catch((error) => { if (error.response?.status === 422) this.formulario.errors = error.response.data.errors })
                .finally(() => { this.formulario.processing = false });
        },
        confirmarEliminar(cita) {
            this.citaAEliminar = cita;
            this.$confirmar('¿Eliminar cita?', `Se eliminará la cita de ${cita.mascota.nombre} con el cliente ${cita.cliente.nombre}.`)
                .then((resultado) => {
                    if (resultado.isConfirmed) return this.eliminarCita();
                })
        },
        eliminarCita() {
            axios.delete(`/api/citas/${this.citaAEliminar.id}`)
            .then(() => { this.cerrarModal(); this.obtenerCitas(); })
            .catch((error) => { if (error.response?.status === 422) this.formulario.errors = error.response.data.errors })
            .finally(() => { this.formulario.processing = false });
        },
    },
    mounted(){
        this.obtenerCitas();
    },
    computed:{

        VerificacionEspecialidadInvalida() {
            if (!this.formulario.mascota_id || !this.formulario.veterinario_id) {
                return false;
            }

            const veterinarioSeleccionado = this.veterinarios.find(
                (veterinario) => Number(veterinario.id) === Number(this.formulario.veterinario_id)
            );
            
            const mascotaSeleccionada = this.mascotas.find(
                (mascota) => Number(mascota.id) === Number(this.formulario.mascota_id)
            );

            if (!veterinarioSeleccionado || !mascotaSeleccionada) {
                return false;
            }

            // Si el veterinario no tiene asociada una especie específica, evitamos disparar la alerta.
            if (!veterinarioSeleccionado.especie_id) {
                return false;
            }

            return Number(veterinarioSeleccionado.especie_id) !== Number(mascotaSeleccionada.especie_id);
        },
    }
}
</script>
