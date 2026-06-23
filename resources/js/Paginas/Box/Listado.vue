<template>
    <Head title="Boxes" />
    <AuthenticatedLayout>
        <div class="container py-4">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h1 class="h4 mb-0 text-primary fw-bold">Boxes de Atención</h1>
                    <button v-if="esVeterinarioOAdmin" type="button" class="btn btn-primary rounded-pill shadow-sm px-4" @click="abrirModalCrear">
                        <i class="bi bi-plus-lg me-1"></i> Nuevo Box
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
                                        @input="obtenerBoxes()" 
                                        placeholder="Buscar box por nombre..."
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
                        {{ totalBoxes }} box{{ totalBoxes === 1 ? '' : 'es' }} registrado{{ totalBoxes === 1 ? '' : 's' }}
                    </p>

                    <!-- Estado de carga -->
                    <div v-if="cargando" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-3 text-muted fw-semibold">Cargando boxes...</p>
                    </div>

                    <!-- Estado vacío -->
                    <div v-else-if="listaVacia" class="text-center py-5 bg-light rounded-4 border border-dashed">
                        <i class="bi bi-door-open text-muted display-4 d-block mb-3 opacity-50"></i>
                        <p class="text-muted mb-3 fw-medium">No tienes boxes registrados aún.</p>
                        <button v-if="esVeterinarioOAdmin" type="button" class="btn btn-primary rounded-pill px-4" @click="abrirModalCrear">
                            Registrar tu primer box
                        </button>
                    </div>

                    <!-- Sin resultados en filtro -->
                    <div v-else-if="sinResultadosFiltro" class="text-center py-5 bg-light rounded-4 border border-dashed">
                        <i class="bi bi-search text-muted display-4 d-block mb-3 opacity-50"></i>
                        <p class="text-muted mb-3 fw-medium">Ningún box coincide con la búsqueda.</p>
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" @click="limpiarFiltros()">
                            Quitar filtro
                        </button>
                    </div>

                    <!-- Grid de Boxes -->
                    <div v-else class="row g-4">
                        <div v-for="box in boxesVisibles" :key="box.id" class="col-12 col-md-6 col-lg-4">
                            <Link :href="route('boxes.detalle', box.id)" class="text-decoration-none text-dark">
                                <div class="card h-100 border-0 shadow-sm hover-elevate transition-all overflow-hidden group-card cursor-pointer rounded-4">
                                    <div class="position-relative bg-light" style="height: 120px;">
                                        <div class="w-100 h-100 d-flex align-items-center justify-content-center text-primary bg-primary bg-opacity-10 hover-zoom">
                                            <i class="bi bi-door-closed fs-1"></i>
                                        </div>
                                        <div class="position-absolute bottom-0 start-0 w-100 h-50 bg-gradient-dark pointer-events-none"></div>
                                        <div class="position-absolute bottom-0 start-0 w-100 p-3 pb-2 text-white pointer-events-none">
                                            <h3 class="h5 mb-0 fw-bold text-shadow text-truncate">{{ box.nombre }}</h3>
                                            <span class="badge bg-white text-dark mt-1 shadow-sm" v-if="box.sucursal">{{ box.sucursal.nombre }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body p-4 d-flex flex-column bg-white">
                                        <p class="card-text text-muted small mb-3 flex-grow-1 line-clamp-3">
                                            {{ box.descripcion || 'Sin descripción disponible para este box.' }}
                                        </p>
                                        
                                        <div v-if="esVeterinarioOAdmin" class="d-flex gap-2 pt-3 border-top mt-auto justify-content-between">
                                            <button 
                                                class="btn btn-sm btn-light text-primary border border-primary-subtle flex-grow-1 btn-hover-primary transition-all rounded-pill" 
                                                @click.prevent="abrirModalEditar(box)"
                                            >
                                                <i class="bi bi-pencil me-1"></i> Editar
                                            </button>
                                            <button 
                                                class="btn btn-sm btn-light text-danger border border-danger-subtle flex-grow-1 btn-hover-danger transition-all rounded-pill" 
                                                @click.prevent="confirmarEliminar(box)"
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

            <!-- MODAL: Crear / Editar Box -->
            <div v-if="mostrarModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow border-0 rounded-4 overflow-hidden">
                        <div class="modal-header bg-light py-3 border-bottom-0">
                            <h5 class="modal-title fw-bold text-primary">{{ tituloModal }}</h5>
                            <button type="button" class="btn-close" @click="cerrarModal"></button>
                        </div>
                        <div class="modal-body p-4 bg-white">
                            <form @submit.prevent="guardar">
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
                                <div class="mb-4">
                                    <label class="form-label fw-semibold text-secondary small text-uppercase">ID de Sucursal</label>
                                    <select v-model="formulario.sucursal_id" class="form-select">
                                        <option value="">Seleccionar sucursal</option>
                                        <option v-for="sucursal in sucursales" :key="sucursal.id" :value="sucursal.id">{{ sucursal.nombre }}</option>
                                    </select>
                                    <div v-if="formulario.errors.sucursal_id" class="invalid-feedback">{{ formulario.errors.sucursal_id }}</div>
                                    <div class="form-text text-muted small">Ingresa el ID de la sucursal a la que pertenece este box.</div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer bg-light border-top-0 py-3">
                            <button type="button" class="btn btn-light rounded-pill px-4 text-muted fw-medium" @click="cerrarModal">
                                Cancelar
                            </button>
                            <button type="button" class="btn btn-primary rounded-pill px-4 fw-medium shadow-sm" :disabled="formulario.processing" @click="guardar">
                                <span v-if="formulario.processing" class="spinner-border spinner-border-sm me-2"></span>
                                {{ textoBotonGuardar }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Backdrop -->
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
        boxes: {
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
                sucursal_id: '',
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
        esVeterinarioOAdmin() {
            const user = this.$page.props.auth.user;
            return user && (user.rol?.nombre_interno === 'veterinario' || user.rol?.nombre_interno === 'admin');
        },
        textoBotonGuardar() {
            return this.modoEdicion ? 'Guardar Cambios' : 'Crear Box';
        },
        tituloModal() {
            return this.modoEdicion ? 'Editar Box' : 'Nuevo Box';
        },
        totalBoxes() {
            return this.boxesVisibles.length;
        },
        listaVacia() {
            return this.boxesVisibles.length === 0 && this.filtroTexto === '';
        },
        sinResultadosFiltro() {
            return this.boxesVisibles.length === 0 && this.filtroTexto !== '';
        },
    },
    methods: {
        datosFormulario() {
            return {
                nombre: this.formulario.nombre,
                descripcion: this.formulario.descripcion,
                sucursal_id: this.formulario.sucursal_id,
            }
        },
        obtenerSucursales(){
            axios.get(route('sucursales.obtenerTodas'))
                .then(response => {
                    this.sucursales = response.data.sucursales || response.data;
                })
                .catch(error => {
                    console.error('Error al obtener sucursales:', error);
                });
        },
        
        abrirModalCrear() {
            this.modoEdicion = false;
            this.boxEditando = null;
            this.formulario.nombre = '';
            this.formulario.descripcion = '';
            this.formulario.sucursal_id = '';
            this.formulario.errors = {};
            this.mostrarModal = true;
        },
        abrirModalEditar(box) {
            this.modoEdicion = true;
            this.boxEditando = box;
            this.formulario.nombre = box.nombre;
            this.formulario.descripcion = box.descripcion;
            this.formulario.sucursal_id = box.sucursal_id;
            this.formulario.errors = {};
            this.mostrarModal = true;
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
            this.formulario.sucursal_id = '';
            this.formulario.errors = {};
            this.mostrarModal = false;
        },
        guardar() {
            this.formulario.processing = true;
            this.formulario.errors = {};
            
            if (this.modoEdicion) {
                axios.put(`/api/boxes/${this.boxEditando.id}`, this.datosFormulario())
                .then(() => { 
                    this.cerrarModal(); 
                    router.reload();
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
                axios.post('/api/boxes', this.datosFormulario())
                .then(() => { 
                    this.cerrarModal(); 
                    router.reload();
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
                router.reload();
                if (this.$alertaExito) {
                    this.$alertaExito('Eliminado', 'El box se eliminó correctamente.');
                }
            })
            .catch((error) => { 
                console.error(error); 
            });
        },

    },
    mounted() {
        this.obtenerSucursales();
    },   
}
</script>

<style scoped>
.hover-elevate:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
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
    background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0) 100%);
}
.text-shadow {
    text-shadow: 0 2px 4px rgba(0,0,0,0.4);
}
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.border-dashed {
    border-style: dashed !important;
}
.btn-hover-primary:hover {
    background-color: var(--bs-primary);
    color: white !important;
}
.btn-hover-danger:hover {
    background-color: var(--bs-danger);
    color: white !important;
}
.pointer-events-none {
    pointer-events: none;
}
</style>
