<template>
    <Head title="Veterinarios" />
    <AuthenticatedLayout>
        <div class="container py-4">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h1 class="h4 mb-0 text-primary fw-bold">Veterinarios</h1>
                    <button v-if="esVeterinarioOAdmin" type="button" class="btn btn-primary rounded-pill shadow-sm px-4" @click="abrirModalCrear">
                        <i class="bi bi-plus-lg me-1"></i> Nuevo Veterinario
                    </button>
                </div>

                <div class="card-body p-4">
                    <!-- Barra de búsqueda -->
                    <div class="bg-light p-3 rounded-4 border border-light mb-4 shadow-sm">
                        <div class="row g-3 align-items-center">
                            <div class="col-12 col-md-8 col-lg-6">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                                    <input 
                                        type="text" 
                                        v-model="filtroTexto" 
                                        class="form-control border-start-0 ps-0" 
                                        placeholder="Buscar por nombre o especialidad..."
                                    >
                                </div>
                            </div>
                            <div class="col-12 col-md-4 col-lg-6 d-flex justify-content-md-end">
                                <button class="btn btn-outline-secondary rounded-pill px-4" @click="limpiarFiltros()" :disabled="!filtroTexto">
                                    Limpiar Filtro
                                </button>
                            </div>
                        </div>
                    </div>

                    <p v-show="!listaVacia" class="text-muted small mb-4 fw-medium ms-2">
                        {{ totalVeterinarios }} veterinario{{ totalVeterinarios === 1 ? '' : 's' }} registrado{{ totalVeterinarios === 1 ? '' : 's' }}
                    </p>

                    <!-- Estado de carga -->
                    <div v-if="cargando" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-3 text-muted fw-semibold">Cargando veterinarios...</p>
                    </div>

                    <!-- Estado vacío -->
                    <div v-else-if="listaVacia" class="text-center py-5 bg-light rounded-4 border border-dashed">
                        <i class="bi bi-person-badge text-muted display-4 d-block mb-3 opacity-50"></i>
                        <p class="text-muted mb-3 fw-medium">No hay veterinarios registrados aún.</p>
                        <button v-if="esVeterinarioOAdmin" type="button" class="btn btn-primary rounded-pill px-4" @click="abrirModalCrear">
                            Registrar tu primer veterinario
                        </button>
                    </div>

                    <!-- Sin resultados en filtro -->
                    <div v-else-if="sinResultadosFiltro" class="text-center py-5 bg-light rounded-4 border border-dashed">
                        <i class="bi bi-search text-muted display-4 d-block mb-3 opacity-50"></i>
                        <p class="text-muted mb-3 fw-medium">Ningún veterinario coincide con la búsqueda.</p>
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" @click="limpiarFiltros()">
                            Quitar filtro
                        </button>
                    </div>

                    <!-- Grid de Veterinarios -->
                    <div v-else class="row g-4">
                        <div v-for="vet in veterinariosVisibles" :key="vet.id" class="col-12 col-md-6 col-lg-4">
                            <Link :href="route('veterinarios.detalle', vet.id)" class="text-decoration-none text-dark">
                                <div class="card h-100 border-0 shadow-sm hover-elevate transition-all overflow-hidden group-card cursor-pointer rounded-4">
                                    <div class="position-relative bg-light text-center py-4" style="height: 140px;">
                                        <div class="position-absolute bottom-0 start-0 w-100 h-50 bg-gradient-dark pointer-events-none"></div>
                                        <div class="d-flex align-items-center justify-content-center flex-column position-relative z-index-1">
                                            <img v-if="vet.foto_perfil_url" :src="vet.foto_perfil_url" class="rounded-circle shadow-sm mb-2" style="width: 70px; height: 70px; object-fit: cover; border: 3px solid white;" alt="Foto de perfil">
                                            <div v-else class="rounded-circle shadow-sm mb-2 bg-primary text-white d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; border: 3px solid white;">
                                                <i class="bi bi-person-fill fs-3"></i>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body p-4 pt-1 d-flex flex-column bg-white text-center">
                                        <h3 class="h5 mb-0 fw-bold text-dark">{{ vet.usuario?.name || 'Sin Nombre' }}</h3>
                                        <span class="badge bg-primary bg-opacity-10 text-primary mt-2 mb-3 align-self-center rounded-pill px-3">{{ vet.especialidad?.nombre || 'Sin Especialidad' }}</span>
                                        
                                        <div class="text-start mb-3 small flex-grow-1">
                                            <div class="text-muted mb-1 text-truncate"><i class="bi bi-envelope me-2"></i>{{ vet.usuario?.email }}</div>
                                            <div class="text-muted mb-1" v-if="vet.telefono"><i class="bi bi-telephone me-2"></i>{{ vet.telefono }}</div>
                                            <div class="text-muted" v-if="vet.direccion"><i class="bi bi-geo-alt me-2"></i>{{ vet.direccion }}</div>
                                        </div>
                                        
                                        <div v-if="esVeterinarioOAdmin" class="d-flex gap-2 pt-3 border-top mt-auto justify-content-between">
                                            <button 
                                                class="btn btn-sm btn-light text-primary border border-primary-subtle flex-grow-1 btn-hover-primary transition-all rounded-pill" 
                                                @click.prevent="abrirModalEditar(vet)"
                                            >
                                                <i class="bi bi-pencil me-1"></i> Editar
                                            </button>
                                            <button 
                                                class="btn btn-sm btn-light text-danger border border-danger-subtle flex-grow-1 btn-hover-danger transition-all rounded-pill" 
                                                @click.prevent="confirmarEliminar(vet)"
                                            >
                                                <i class="bi bi-trash me-1"></i> Eliminar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- MODAL: Crear / Editar Veterinario          -->
            <!-- ========================================== -->
            <div v-if="mostrarModal" class="modal fade show d-block" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                        <div class="modal-header bg-light border-bottom-0 py-3">
                            <h5 class="modal-title fw-bold text-primary">
                                <i :class="modoEdicion ? 'bi bi-pencil-square' : 'bi bi-plus-circle'" class="me-2"></i>
                                {{ modoEdicion ? 'Editar Veterinario' : 'Nuevo Veterinario' }}
                            </h5>
                            <button type="button" class="btn-close shadow-none" @click="cerrarModal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <form @submit.prevent="guardar" class="row g-3">
                                
                                <!-- Campos de Usuario (Solo en creación) -->
                                <template v-if="!modoEdicion">
                                    <h6 class="text-uppercase text-muted fw-bold small mb-2 border-bottom pb-2">Datos de Acceso</h6>
                                    
                                    <div class="col-12 col-md-6">
                                        <label for="name" class="form-label fw-semibold text-secondary small">Nombre Completo</label>
                                        <input id="name" v-model="formulario.name" type="text" class="form-control bg-light border-0 py-2" :class="{ 'is-invalid': formulario.errors.name }" required placeholder="Ej: Dr. Juan Pérez" />
                                        <div v-if="formulario.errors.name" class="invalid-feedback">{{ formulario.errors.name }}</div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="email" class="form-label fw-semibold text-secondary small">Correo Electrónico</label>
                                        <input id="email" v-model="formulario.email" type="email" class="form-control bg-light border-0 py-2" :class="{ 'is-invalid': formulario.errors.email }" required placeholder="ejemplo@veterinaria.com" />
                                        <div v-if="formulario.errors.email" class="invalid-feedback">{{ formulario.errors.email }}</div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="password" class="form-label fw-semibold text-secondary small">Contraseña</label>
                                        <input id="password" v-model="formulario.password" type="password" class="form-control bg-light border-0 py-2" :class="{ 'is-invalid': formulario.errors.password }" required placeholder="Mínimo 8 caracteres" />
                                        <div v-if="formulario.errors.password" class="invalid-feedback">{{ formulario.errors.password }}</div>
                                    </div>
                                </template>

                                <!-- Campos del Veterinario -->
                                <h6 class="text-uppercase text-muted fw-bold small mb-2 mt-4 border-bottom pb-2">Información Profesional</h6>

                                <div class="col-12 col-md-6">
                                    <label for="especialidad_id" class="form-label fw-semibold text-secondary small">Especialidad</label>
                                    <select id="especialidad_id" v-model="formulario.especialidad_id" class="form-select bg-light border-0 py-2" :class="{ 'is-invalid': formulario.errors.especialidad_id }" required>
                                        <option value="" disabled>Selecciona una especialidad</option>
                                        <option v-for="esp in especialidades" :key="esp.id" :value="esp.id">
                                            {{ esp.nombre }}
                                        </option>
                                    </select>
                                    <div v-if="formulario.errors.especialidad_id" class="invalid-feedback">{{ formulario.errors.especialidad_id }}</div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="sucursal_id" class="form-label fw-semibold text-secondary small">Sucursal Principal</label>
                                    <select id="sucursal_id" v-model="formulario.sucursal_id" class="form-select bg-light border-0 py-2" :class="{ 'is-invalid': formulario.errors.sucursal_id }" required>
                                        <option value="" disabled>Selecciona una sucursal</option>
                                        <option v-for="sucursal in sucursales" :key="sucursal.id" :value="sucursal.id">
                                            {{ sucursal.nombre }}
                                        </option>
                                    </select>
                                    <div v-if="formulario.errors.sucursal_id" class="invalid-feedback">{{ formulario.errors.sucursal_id }}</div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="telefono" class="form-label fw-semibold text-secondary small">Teléfono de Contacto</label>
                                    <input id="telefono" v-model="formulario.telefono" type="text" class="form-control bg-light border-0 py-2" :class="{ 'is-invalid': formulario.errors.telefono }" placeholder="Ej: +56 9 1234 5678" />
                                    <div v-if="formulario.errors.telefono" class="invalid-feedback">{{ formulario.errors.telefono }}</div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="direccion" class="form-label fw-semibold text-secondary small">Dirección</label>
                                    <input id="direccion" v-model="formulario.direccion" type="text" class="form-control bg-light border-0 py-2" :class="{ 'is-invalid': formulario.errors.direccion }" placeholder="Dirección referencial" />
                                    <div v-if="formulario.errors.direccion" class="invalid-feedback">{{ formulario.errors.direccion }}</div>
                                </div>

                                <div class="col-12">
                                    <label for="foto_perfil_url" class="form-label fw-semibold text-secondary small">URL Foto de Perfil (Opcional)</label>
                                    <input id="foto_perfil_url" v-model="formulario.foto_perfil_url" type="url" class="form-control bg-light border-0 py-2" :class="{ 'is-invalid': formulario.errors.foto_perfil_url }" placeholder="https://ejemplo.com/foto.jpg" />
                                    <div v-if="formulario.errors.foto_perfil_url" class="invalid-feedback">{{ formulario.errors.foto_perfil_url }}</div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer bg-light border-top-0 py-3">
                            <button type="button" class="btn btn-secondary rounded-pill px-4" @click="cerrarModal" :disabled="formulario.processing">
                                Cancelar
                            </button>
                            <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" @click="guardar" :disabled="formulario.processing">
                                <span v-if="formulario.processing" class="spinner-border spinner-border-sm me-2"></span>
                                {{ modoEdicion ? 'Guardar Cambios' : 'Registrar Veterinario' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div v-if="mostrarModal" class="modal-backdrop fade show"></div>

            <!-- ========================================== -->
            <!-- MODAL: Confirmar Eliminación                -->
            <!-- ========================================== -->
            <div v-if="mostrarConfirmacion" class="modal fade show d-block" tabindex="-1">
                <div class="modal-dialog modal-sm modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                        <div class="modal-header bg-danger text-white border-bottom-0 py-3">
                            <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle me-2"></i>Confirmar</h5>
                            <button type="button" class="btn-close btn-close-white shadow-none" @click="mostrarConfirmacion = false"></button>
                        </div>
                        <div class="modal-body text-center p-4">
                            <i class="bi bi-trash text-danger display-4 mb-3 d-block opacity-75"></i>
                            <p class="mb-1">¿Estás seguro de eliminar a <strong>{{ vetAEliminar?.usuario?.name || 'este veterinario' }}</strong>?</p>
                            <p class="text-muted small mb-0">Esta acción no se puede deshacer.</p>
                        </div>
                        <div class="modal-footer bg-light border-top-0 py-3 justify-content-center">
                            <button type="button" class="btn btn-secondary rounded-pill px-4" @click="mostrarConfirmacion = false">
                                Cancelar
                            </button>
                            <button type="button" class="btn btn-danger rounded-pill px-4 shadow-sm" :disabled="eliminando" @click="eliminarVeterinario">
                                <span v-if="eliminando" class="spinner-border spinner-border-sm me-2"></span>
                                Sí, eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        
            <div v-if="mostrarConfirmacion" class="modal-backdrop fade show"></div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from '@/Disenos/LayoutAutenticado.vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';

export default {
    components: {
        AuthenticatedLayout,
        Head,
        Link
    },
    props: {
        veterinarios: {
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
    },
    data() {
        return {
            cargando: false,
            mostrarModal: false,
            modoEdicion: false,
            vetEditando: null,
            mostrarConfirmacion: false,
            vetAEliminar: null,
            eliminando: false,
            filtroTexto: '',
            formulario: {
                name: '',
                email: '',
                password: '',
                especialidad_id: '',
                foto_perfil_url: '',
                sucursal_id: '',
                telefono: '',
                direccion: '',
                errors: {},
                processing: false,
            },
        }
    },
    computed: {
        esVeterinarioOAdmin() {
            const role = this.$page.props.auth.user.rol_id;
            return role === 1 || role === 2; // Asumiendo 1=Admin, 2=Veterinario
        },
        listaVacia() {
            return this.veterinarios.length === 0;
        },
        veterinariosVisibles() {
            if (!this.filtroTexto) return this.veterinarios;
            
            const texto = this.filtroTexto.toLowerCase();
            return this.veterinarios.filter(vet => {
                const nombre = vet.usuario?.name?.toLowerCase() || '';
                const especialidad = vet.especialidad?.nombre?.toLowerCase() || '';
                return nombre.includes(texto) || especialidad.includes(texto);
            });
        },
        totalVeterinarios() {
            return this.veterinarios.length;
        },
        sinResultadosFiltro() {
            return !this.listaVacia && this.veterinariosVisibles.length === 0;
        }
    },
    methods: {
        limpiarFiltros() {
            this.filtroTexto = '';
        },
        abrirModalCrear() {
            this.modoEdicion = false;
            this.vetEditando = null;
            this.formulario.name = '';
            this.formulario.email = '';
            this.formulario.password = '';
            this.formulario.especialidad_id = '';
            this.formulario.foto_perfil_url = '';
            this.formulario.sucursal_id = '';
            this.formulario.telefono = '';
            this.formulario.direccion = '';
            this.formulario.errors = {};
            this.mostrarModal = true;
        },
        abrirModalEditar(vet) {
            this.modoEdicion = true;
            this.vetEditando = vet;
            
            this.formulario.especialidad_id = vet.especialidad_id || '';
            this.formulario.foto_perfil_url = vet.foto_perfil_url || '';
            this.formulario.sucursal_id = vet.sucursal_id || '';
            this.formulario.telefono = vet.telefono || '';
            this.formulario.direccion = vet.direccion || '';
            
            this.formulario.errors = {};
            this.mostrarModal = true;
        },
        cerrarModal() {
            this.mostrarModal = false;
            this.formulario.errors = {};
        },
        guardar() {
            this.formulario.processing = true;
            this.formulario.errors = {};

            const request = this.modoEdicion
                ? axios.put(`/api/veterinarios/${this.vetEditando.id}`, this.formulario)
                : axios.post('/api/veterinarios', this.formulario);

            request
                .then(() => {
                    this.cerrarModal();
                    this.$inertia.reload({ only: ['veterinarios'] });
                })
                .catch((error) => {
                    if (error.response?.status === 422) {
                        this.formulario.errors = error.response.data.errors || error.response.data.message;
                    } else if (error.response?.status === 403) {
                        alert("No tienes permisos para realizar esta acción.");
                    } else {
                        alert("Ocurrió un error inesperado al guardar el veterinario.");
                    }
                })
                .finally(() => {
                    this.formulario.processing = false;
                });
        },
        confirmarEliminar(vet) {
            this.vetAEliminar = vet;
            this.mostrarConfirmacion = true;
        },
        eliminarVeterinario() {
            this.eliminando = true;
            axios.delete(`/api/veterinarios/${this.vetAEliminar.id}`)
                .then(() => {
                    this.mostrarConfirmacion = false;
                    this.$inertia.reload({ only: ['veterinarios'] });
                })
                .catch(error => {
                    console.error("Error al eliminar veterinario:", error);
                    alert("No se pudo eliminar el veterinario.");
                })
                .finally(() => {
                    this.eliminando = false;
                });
        },
    },
}
</script>

<style scoped>
.hover-elevate {
    transition: all 0.3s ease;
}
.hover-elevate:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
}
.group-card:hover .hover-zoom {
    transform: scale(1.1);
}
.hover-zoom {
    transition: transform 0.4s ease;
}
.cursor-pointer {
    cursor: pointer;
}
.pointer-events-none {
    pointer-events: none;
}
.bg-gradient-dark {
    background: linear-gradient(to top, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0) 100%);
}
.text-shadow {
    text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
}
</style>
