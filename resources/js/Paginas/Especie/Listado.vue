<template>
    <Head title="Especies" />
    <AuthenticatedLayout>
        <div class="container py-4">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center bg-white py-3">
                    <h1 class="h5 mb-0 fw-bold text-primary">Listado de Especies</h1>
                    <button v-if="esVeterinarioOAdmin" type="button" class="btn btn-sm btn-primary px-3" @click="abrirModalCrear">
                        <i class="bi bi-plus-lg me-1"></i> Nueva Especie
                    </button>
                </div>

                <div class="card-body">
                    <!-- Barra de búsqueda -->
                    <div class="bg-light p-3 rounded border mb-4">
                        <div class="row g-3 align-items-center">
                            <div class="col-12 col-md-8 col-lg-6">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                    <input 
                                        type="text" 
                                        v-model="filtroTexto" 
                                        class="form-control" 
                                        @input="obtenerEspecies()" 
                                        placeholder="Buscar por nombre..."
                                    >
                                </div>
                            </div>
                            <div class="col-12 col-md-4 col-lg-6 d-flex justify-content-md-end">
                                <button class="btn btn-outline-secondary btn-sm" @click="limpiarFiltros()" :disabled="!filtroTexto">
                                    Limpiar Filtro
                                </button>
                            </div>
                        </div>
                    </div>

                    <p v-show="!listaVacia" class="text-muted small mb-3">
                        {{ totalEspecies }} especie{{ totalEspecies === 1 ? '' : 's' }} registrada{{ totalEspecies === 1 ? '' : 's' }}
                    </p>

                    <!-- Estado de carga -->
                    <div v-if="cargando" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted">Cargando especies...</p>
                    </div>

                    <!-- Estado vacío -->
                    <div v-else-if="listaVacia" class="text-center py-5">
                        <i class="bi bi-clipboard2-x text-muted display-4 d-block mb-3"></i>
                        <p class="text-muted mb-3">No tienes especies registradas aún.</p>
                        <button v-if="esVeterinarioOAdmin" type="button" class="btn btn-primary btn-sm px-4" @click="abrirModalCrear">
                            Registrar tu primera especie
                        </button>
                    </div>

                    <!-- Sin resultados en filtro -->
                    <div v-else-if="sinResultadosFiltro" class="text-center py-5">
                        <i class="bi bi-search text-muted display-4 d-block mb-3"></i>
                        <p class="text-muted mb-3">Ninguna especie coincide con la búsqueda.</p>
                        <button type="button" class="btn btn-outline-secondary btn-sm" @click="limpiarFiltros()">
                            Quitar filtro
                        </button>
                    </div>

                    <!-- Grid de especies -->
                    <div v-else class="row g-4">
                        <div v-for="especie in especiesVisibles" :key="especie.id" class="col-12 col-md-6 col-lg-4 col-xl-3">
                            <Link :href="`/especies/${especie.id}`">
                                <div 
                                    class="card h-100 border-0 shadow-sm hover-elevate transition-all overflow-hidden group-card cursor-pointer"
                                    
                                >
                                    <!-- Imagen Header -->
                                    <div class="position-relative bg-light" style="height: 160px;">
                                        <img 
                                            v-if="especie.imagen_url" 
                                            :src="especie.imagen_url" 
                                            class="w-100 h-100 object-fit-cover hover-zoom" 
                                            alt="Imagen de especie"
                                        >
                                        <div v-else class="w-100 h-100 d-flex align-items-center justify-content-center text-primary bg-primary bg-opacity-10">
                                            <i class="bi bi-bug-fill fs-1"></i>
                                        </div>
                                        <!-- Overlay gradient -->
                                        <div class="position-absolute bottom-0 start-0 w-100 h-50 bg-gradient-dark"></div>
                                        <!-- Title badge overlapping -->
                                        <div class="position-absolute bottom-0 start-0 w-100 p-3 pb-2 text-white">
                                            <h3 class="h5 mb-0 fw-bold text-shadow text-truncate">{{ especie.nombre }}</h3>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body p-3 d-flex flex-column">
                                        <p class="card-text text-muted small mb-3 flex-grow-1 line-clamp-3">
                                            {{ especie.descripcion || 'Sin descripción disponible para esta especie.' }}
                                        </p>
                                        
                                        <div v-if="esVeterinarioOAdmin" class="d-flex gap-2 pt-3 border-top mt-auto justify-content-between">
                                            <button 
                                                class="btn btn-sm btn-light text-primary border border-primary-subtle flex-grow-1 btn-hover-primary transition-all" 
                                                @click.stop="abrirModalEditar(especie)"
                                            >
                                                <i class="bi bi-pencil me-1"></i> Editar
                                            </button>
                                            <button 
                                                class="btn btn-sm btn-light text-danger border border-danger-subtle flex-grow-1 btn-hover-danger transition-all" 
                                                @click.stop="confirmarEliminar(especie)"
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

            <!-- MODAL: Crear / Editar Especie -->
            <div v-if="mostrarModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow border-0">
                        <div class="modal-header bg-light py-3">
                            <h5 class="modal-title fw-bold">{{ tituloModal }}</h5>
                            <button type="button" class="btn-close" @click="cerrarModal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <form @submit.prevent="guardar">
                                <div class="mb-3">
                                    <label for="nombre" class="form-label fw-semibold text-secondary">Nombre de la Especie</label>
                                    <input
                                        id="nombre"
                                        v-model="formulario.nombre"
                                        type="text"
                                        class="form-control"
                                        placeholder="Ej: Felinos"
                                        :class="{ 'is-invalid': formulario.errors.nombre }"
                                        required
                                    />
                                    <div v-if="formulario.errors.nombre" class="invalid-feedback">
                                        {{ formulario.errors.nombre }}
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="descripcion" class="form-label fw-semibold text-secondary">Descripción</label>
                                    <textarea
                                        id="descripcion"
                                        v-model="formulario.descripcion"
                                        class="form-control"
                                        rows="3"
                                        placeholder="Descripción corta de la especie..."
                                        :class="{ 'is-invalid': formulario.errors.descripcion }"
                                    ></textarea>
                                    <div v-if="formulario.errors.descripcion" class="invalid-feedback">
                                        {{ formulario.errors.descripcion }}
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="imagen_url" class="form-label fw-semibold text-secondary">URL de la Imagen</label>
                                    <input
                                        id="imagen_url"
                                        v-model="formulario.imagen_url"
                                        type="text"
                                        class="form-control"
                                        placeholder="https://ejemplo.com/foto.jpg"
                                        :class="{ 'is-invalid': formulario.errors.imagen_url }"
                                    />
                                    <div v-if="formulario.errors.imagen_url" class="invalid-feedback">
                                        {{ formulario.errors.imagen_url }}
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary btn-sm" @click="cerrarModal">
                                Cancelar
                            </button>
                            <button type="button" class="btn btn-primary btn-sm" :disabled="formulario.processing" @click="guardar">
                                <span v-if="formulario.processing" class="spinner-border spinner-border-sm me-1"></span>
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
import { Head, Link,} from '@inertiajs/vue3';

export default {
    components: {
        AuthenticatedLayout,
        Head,
        Link,
    },
    props: {
        especies: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            cargando: false,
            mostrarModal: false,
            modoEdicion: false,
            especieEditando: null,
            filtroTexto: '',
            especieAEliminar: null,
            formulario: {
                nombre: '',
                descripcion: '',
                imagen_url: '',
                errors: {},
                processing: false,
            },
            especiesVisibles: this.especies,
        }
    },
    watch: {
        especies(nuevasEspecies) {
            this.especiesVisibles = nuevasEspecies;
        }
    },
    computed: {
        esVeterinarioOAdmin() {
            const user = this.$page.props.auth.user;
            return user && (user.rol?.nombre_interno === 'veterinario' || user.rol?.nombre_interno === 'admin');
        },
        textoBotonGuardar() {
            return this.modoEdicion ? 'Guardar Cambios' : 'Crear Especie';
        },
        tituloModal() {
            return this.modoEdicion ? 'Editar Especie' : 'Nueva Especie';
        },
        totalEspecies() {
            return this.especiesVisibles.length;
        },
        listaVacia() {
            return this.especiesVisibles.length === 0 && this.filtroTexto === '';
        },
        sinResultadosFiltro() {
            return this.especiesVisibles.length === 0 && this.filtroTexto !== '';
        },
    },
    methods: {
        datosFormulario() {
            return {
                nombre: this.formulario.nombre,
                descripcion: this.formulario.descripcion,
                imagen_url: this.formulario.imagen_url,
            }
        },
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
        abrirModalCrear() {
            this.modoEdicion = false;
            this.especieEditando = null;
            this.formulario.nombre = '';
            this.formulario.descripcion = '';
            this.formulario.imagen_url = '';
            this.formulario.errors = {};
            this.mostrarModal = true;
        },
        abrirModalEditar(especie) {
            this.modoEdicion = true;
            this.especieEditando = especie;
            this.formulario.nombre = especie.nombre;
            this.formulario.descripcion = especie.descripcion;
            this.formulario.imagen_url = especie.imagen_url;
            this.formulario.errors = {};
            this.mostrarModal = true;
        },
        limpiarFiltros() {
            this.filtroTexto = '';
            this.obtenerEspecies();
        },
        cerrarModal() {
            this.modoEdicion = false;
            this.especieEditando = null;
            this.formulario.nombre = '';
            this.formulario.descripcion = '';
            this.formulario.imagen_url = '';
            this.formulario.errors = {};
            this.mostrarModal = false;
        },
        guardar() {
            this.formulario.processing = true;
            this.formulario.errors = {};
            
            if (this.modoEdicion) {
                axios.put(`/api/especies/${this.especieEditando.id}`, this.datosFormulario())
                .then(() => { 
                    this.cerrarModal(); 
                    this.obtenerEspecies(); 
                    if (this.$alertaExito) {
                        this.$alertaExito('Actualizado', 'La especie se actualizó correctamente.');
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
                axios.post('/api/especies', this.datosFormulario())
                .then(() => { 
                    this.cerrarModal(); 
                    this.obtenerEspecies(); 
                    if (this.$alertaExito) {
                        this.$alertaExito('Creado', 'La especie se creó correctamente.');
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
        confirmarEliminar(especie) {
            this.especieAEliminar = especie;
            if (this.$confirmar) {
                this.$confirmar('¿Eliminar especie?', `Se eliminará a ${especie.nombre} permanentemente.`)
                .then((resultado) => {
                    if (resultado.isConfirmed) {
                        this.eliminarEspecie();
                    }
                });
            } else if (confirm(`¿Estás seguro de eliminar la especie ${especie.nombre}?`)) {
                this.eliminarEspecie();
            }
        },
        eliminarEspecie() {
            axios.delete(`/api/especies/${this.especieAEliminar.id}`)
            .then(() => { 
                this.obtenerEspecies(); 
                if (this.$alertaExito) {
                    this.$alertaExito('Eliminado', 'La especie se eliminó correctamente.');
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
