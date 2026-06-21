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

                    <!-- Tabla de especies -->
                    <div v-else class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Imagen</th>
                                    <th v-if="esVeterinarioOAdmin" style="width: 180px" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="especie in especiesVisibles" :key="especie.id">
                                    <td class="fw-bold">
                                        <Link :href="route('especies.detalle', especie.id)" class="text-decoration-none">
                                            {{ especie.nombre }}
                                        </Link>
                                    </td>
                                    <td>{{ especie.descripcion || 'Sin descripción' }}</td>
                                    <td>
                                        <img 
                                            v-if="especie.imagen_url" 
                                            :src="especie.imagen_url" 
                                            alt="Imagen especie" 
                                            class="img-thumbnail cursor-pointer" 
                                            style="width: 50px; height: 50px; object-fit: cover; cursor: pointer;" 
                                            @click="abrirModalImagen(especie)"
                                        >
                                        <span v-else class="text-muted small">—</span>
                                    </td>
                                    <td v-if="esVeterinarioOAdmin">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary"
                                                @click="abrirModalEditar(especie)"
                                            >
                                                Editar
                                            </button>
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                @click="confirmarEliminar(especie)"
                                            >
                                                Eliminar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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

            <!-- MODAL: Ampliar Imagen -->
            <div v-if="mostrarModalImagen" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);" @click.self="cerrarModalImagen">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow border-0">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">Imagen de {{ especieSeleccionada?.nombre }}</h5>
                            <button type="button" class="btn-close" @click="cerrarModalImagen"></button>
                        </div>
                        <div class="modal-body text-center p-4">
                            <img :src="especieSeleccionada?.imagen_url" alt="Imagen de la especie" class="img-fluid rounded shadow-sm" style="max-height: 400px; object-fit: contain;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Backdrop -->
            <div v-if="mostrarModal || mostrarModalImagen" class="modal-backdrop fade show"></div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from '@/Disenos/LayoutAutenticado.vue';
import { Head, Link } from '@inertiajs/vue3';

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
            mostrarModalImagen: false,
            especieSeleccionada: null,
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
        abrirModalImagen(especie) {
            this.especieSeleccionada = especie;
            this.mostrarModalImagen = true;
        },
        cerrarModalImagen() {
            this.mostrarModalImagen = false;
            this.especieSeleccionada = null;
        },
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
