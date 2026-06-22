<template>
    <Head title="Sucursales" />
    <AuthenticatedLayout>
        <div class="container py-4">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h1 class="h4 mb-0 text-primary fw-bold">Sucursales</h1>
                    <button v-if="esVeterinarioOAdmin" type="button" class="btn btn-primary rounded-pill shadow-sm px-4" @click="abrirModalCrear">
                        <i class="bi bi-plus-lg me-1"></i> Nueva Sucursal
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
                                        @input="obtenerSucursales()" 
                                        placeholder="Buscar sucursal por nombre..."
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
                        {{ totalSucursales }} sucursal{{ totalSucursales === 1 ? '' : 'es' }} registrada{{ totalSucursales === 1 ? '' : 's' }}
                    </p>

                    <!-- Estado de carga -->
                    <div v-if="cargando" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-3 text-muted fw-semibold">Cargando sucursales...</p>
                    </div>

                    <!-- Estado vacío -->
                    <div v-else-if="listaVacia" class="text-center py-5 bg-light rounded-4 border border-dashed">
                        <i class="bi bi-shop text-muted display-4 d-block mb-3 opacity-50"></i>
                        <p class="text-muted mb-3 fw-medium">No tienes sucursales registradas aún.</p>
                        <button v-if="esVeterinarioOAdmin" type="button" class="btn btn-primary rounded-pill px-4" @click="abrirModalCrear">
                            Registrar tu primera sucursal
                        </button>
                    </div>

                    <!-- Sin resultados en filtro -->
                    <div v-else-if="sinResultadosFiltro" class="text-center py-5 bg-light rounded-4 border border-dashed">
                        <i class="bi bi-search text-muted display-4 d-block mb-3 opacity-50"></i>
                        <p class="text-muted mb-3 fw-medium">Ninguna sucursal coincide con la búsqueda.</p>
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" @click="limpiarFiltros()">
                            Quitar filtro
                        </button>
                    </div>

                    <!-- Grid de Sucursales -->
                    <div v-else class="row g-4">
                        <div v-for="sucursal in sucursalesVisibles" :key="sucursal.id" class="col-12 col-md-6 col-lg-4">
                            <Link :href="route('sucursales.detalle', sucursal.id)" class="text-decoration-none text-dark">
                                <div class="card h-100 border-0 shadow-sm hover-elevate transition-all overflow-hidden group-card cursor-pointer rounded-4">
                                    <div class="position-relative bg-light" style="height: 120px;">
                                        <div class="w-100 h-100 d-flex align-items-center justify-content-center text-primary bg-primary bg-opacity-10 hover-zoom">
                                            <i class="bi bi-shop fs-1"></i>
                                        </div>
                                        <div class="position-absolute bottom-0 start-0 w-100 h-50 bg-gradient-dark pointer-events-none"></div>
                                        <div class="position-absolute bottom-0 start-0 w-100 p-3 pb-2 text-white pointer-events-none">
                                            <h3 class="h5 mb-0 fw-bold text-shadow text-truncate">{{ sucursal.nombre }}</h3>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body p-4 d-flex flex-column bg-white">
                                        <div class="mb-3">
                                            <p class="text-muted small mb-2 d-flex align-items-center gap-2">
                                                <i class="bi bi-geo-alt-fill text-primary opacity-75"></i> 
                                                <span class="text-truncate">{{ sucursal.direccion || 'Sin dirección' }}</span>
                                            </p>
                                            <p class="text-muted small mb-0 d-flex align-items-center gap-2">
                                                <i class="bi bi-telephone-fill text-primary opacity-75"></i> 
                                                <span>{{ sucursal.telefono || 'Sin teléfono' }}</span>
                                            </p>
                                        </div>
                                        
                                        <div v-if="esVeterinarioOAdmin" class="d-flex gap-2 pt-3 border-top mt-auto justify-content-between">
                                            <button 
                                                class="btn btn-sm btn-light text-primary border border-primary-subtle flex-grow-1 btn-hover-primary transition-all rounded-pill" 
                                                @click.prevent="abrirModalEditar(sucursal)"
                                            >
                                                <i class="bi bi-pencil me-1"></i> Editar
                                            </button>
                                            <button 
                                                class="btn btn-sm btn-light text-danger border border-danger-subtle flex-grow-1 btn-hover-danger transition-all rounded-pill" 
                                                @click.prevent="confirmarEliminar(sucursal)"
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

            <!-- MODAL: Crear / Editar Sucursal -->
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
                                    <label class="form-label fw-semibold text-secondary small text-uppercase">Nombre de la Sucursal</label>
                                    <input
                                        v-model="formulario.nombre"
                                        type="text"
                                        class="form-control bg-light border-0 py-2"
                                        placeholder="Ej: Sede Central"
                                        :class="{ 'is-invalid': formulario.errors.nombre }"
                                        required
                                    />
                                    <div v-if="formulario.errors.nombre" class="invalid-feedback">{{ formulario.errors.nombre }}</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-secondary small text-uppercase">Dirección</label>
                                    <input
                                        v-model="formulario.direccion"
                                        type="text"
                                        class="form-control bg-light border-0 py-2"
                                        placeholder="Ej: Av. Principal 123"
                                        :class="{ 'is-invalid': formulario.errors.direccion }"
                                        required
                                    />
                                    <div v-if="formulario.errors.direccion" class="invalid-feedback">{{ formulario.errors.direccion }}</div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-semibold text-secondary small text-uppercase">Teléfono</label>
                                    <input
                                        v-model="formulario.telefono"
                                        type="text"
                                        class="form-control bg-light border-0 py-2"
                                        placeholder="Ej: +56 9 1234 5678"
                                        :class="{ 'is-invalid': formulario.errors.telefono }"
                                        required
                                    />
                                    <div v-if="formulario.errors.telefono" class="invalid-feedback">{{ formulario.errors.telefono }}</div>
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
        sucursales: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            cargando: false,
            mostrarModal: false,
            modoEdicion: false,
            sucursalEditando: null,
            filtroTexto: '',
            sucursalAEliminar: null,
            formulario: {
                nombre: '',
                direccion: '',
                telefono: '',
                errors: {},
                processing: false,
            },
            sucursalesVisibles: this.sucursales,
        }
    },
    watch: {
        sucursales(nuevas) {
            this.sucursalesVisibles = nuevas;
        }
    },
    computed: {
        esVeterinarioOAdmin() {
            const user = this.$page.props.auth.user;
            return user && (user.rol?.nombre_interno === 'veterinario' || user.rol?.nombre_interno === 'admin');
        },
        textoBotonGuardar() {
            return this.modoEdicion ? 'Guardar Cambios' : 'Crear Sucursal';
        },
        tituloModal() {
            return this.modoEdicion ? 'Editar Sucursal' : 'Nueva Sucursal';
        },
        totalSucursales() {
            return this.sucursalesVisibles.length;
        },
        listaVacia() {
            return this.sucursalesVisibles.length === 0 && this.filtroTexto === '';
        },
        sinResultadosFiltro() {
            return this.sucursalesVisibles.length === 0 && this.filtroTexto !== '';
        },
    },
    methods: {
        datosFormulario() {
            return {
                nombre: this.formulario.nombre,
                direccion: this.formulario.direccion,
                telefono: this.formulario.telefono,
            }
        },
        obtenerSucursales() {
            const texto = this.filtroTexto.toLowerCase();
            if(!texto) {
                this.sucursalesVisibles = this.sucursales;
                return;
            }
            this.sucursalesVisibles = this.sucursales.filter(s => s.nombre.toLowerCase().includes(texto));
        },
        abrirModalCrear() {
            this.modoEdicion = false;
            this.sucursalEditando = null;
            this.formulario.nombre = '';
            this.formulario.direccion = '';
            this.formulario.telefono = '';
            this.formulario.errors = {};
            this.mostrarModal = true;
        },
        abrirModalEditar(sucursal) {
            this.modoEdicion = true;
            this.sucursalEditando = sucursal;
            this.formulario.nombre = sucursal.nombre;
            this.formulario.direccion = sucursal.direccion;
            this.formulario.telefono = sucursal.telefono;
            this.formulario.errors = {};
            this.mostrarModal = true;
        },
        limpiarFiltros() {
            this.filtroTexto = '';
            this.obtenerSucursales();
        },
        cerrarModal() {
            this.modoEdicion = false;
            this.sucursalEditando = null;
            this.formulario.nombre = '';
            this.formulario.direccion = '';
            this.formulario.telefono = '';
            this.formulario.errors = {};
            this.mostrarModal = false;
        },
        guardar() {
            this.formulario.processing = true;
            this.formulario.errors = {};
            
            if (this.modoEdicion) {
                axios.put(`/api/sucursales/${this.sucursalEditando.id}`, this.datosFormulario())
                .then(() => { 
                    this.cerrarModal(); 
                    router.reload();
                    if (this.$alertaExito) {
                        this.$alertaExito('Actualizada', 'La sucursal se actualizó correctamente.');
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
                axios.post('/api/sucursales', this.datosFormulario())
                .then(() => { 
                    this.cerrarModal(); 
                    router.reload();
                    if (this.$alertaExito) {
                        this.$alertaExito('Creada', 'La sucursal se creó correctamente.');
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
        confirmarEliminar(sucursal) {
            this.sucursalAEliminar = sucursal;
            if (this.$confirmar) {
                this.$confirmar('¿Eliminar sucursal?', `Se eliminará la sucursal ${sucursal.nombre} permanentemente.`)
                .then((resultado) => {
                    if (resultado.isConfirmed) {
                        this.eliminarSucursal();
                    }
                });
            } else if (confirm(`¿Estás seguro de eliminar la sucursal ${sucursal.nombre}?`)) {
                this.eliminarSucursal();
            }
        },
        eliminarSucursal() {
            axios.delete(`/api/sucursales/${this.sucursalAEliminar.id}`)
            .then(() => { 
                router.reload();
                if (this.$alertaExito) {
                    this.$alertaExito('Eliminada', 'La sucursal se eliminó correctamente.');
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
