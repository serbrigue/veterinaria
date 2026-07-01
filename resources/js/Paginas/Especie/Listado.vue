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
                    <BarraFiltros 
                        :deshabilitar-limpiar="!filtroTexto" 
                        clase-boton-contenedor="col-12 col-md-4 col-lg-6 d-flex justify-content-md-end"
                        @limpiar="limpiarFiltros"
                    >
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
                        <template #texto-limpiar>
                            Limpiar Filtro
                        </template>
                    </BarraFiltros>

                    <p v-show="!listaVacia" class="text-muted small mb-3">
                        {{ totalEspecies }} especie{{ totalEspecies === 1 ? '' : 's' }} registrada{{ totalEspecies === 1 ? '' : 's' }}
                    </p>

                    <IndicadorCarga :cargando="cargando" mensaje="especies" />

                    <EstadoVacio
                        :visible="!cargando && listaVacia"
                        mensaje="No tienes especies registradas aún."
                        :texto-boton="esVeterinarioOAdmin ? 'Registrar tu primera especie' : ''"
                        icono="bi bi-clipboard2-x"
                        @accion="abrirModalCrear"
                    />

                    <SinResultados
                        :visible="!cargando && sinResultadosFiltro"
                        mensaje="Ninguna especie coincide con la búsqueda."
                        @limpiar="limpiarFiltros()"
                    />

                    <!-- Grid de especies -->
                    <div v-if="!cargando && !listaVacia && !sinResultadosFiltro" class="row g-4">
                        <div v-for="especie in especiesVisibles" :key="especie.id" class="col-12 col-md-6 col-lg-4 col-xl-3">
                            <TarjetaEntidad
                                :titulo="especie.nombre"
                                :imagen-url="especie.imagen_url"
                                icono="bi-bug-fill"
                                :url-detalle="`/especies/${especie.id}`"
                                :mostrar-acciones="esVeterinarioOAdmin"
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
                <div class="mb-3">
                    <label for="nombre" class="form-label fw-semibold text-secondary small text-uppercase">Nombre de la Especie</label>
                    <input
                        id="nombre"
                        v-model="formulario.nombre"
                        type="text"
                        class="form-control bg-light border-0 py-2"
                        placeholder="Ej: Felinos"
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
                        placeholder="Descripción corta de la especie..."
                        :class="{ 'is-invalid': formulario.errors.descripcion }"
                    ></textarea>
                    <div v-if="formulario.errors.descripcion" class="invalid-feedback">
                        {{ formulario.errors.descripcion }}
                    </div>
                </div>
                <div class="mb-3">
                    <label for="imagen_url" class="form-label fw-semibold text-secondary small text-uppercase">URL de la Imagen</label>
                    <input
                        id="imagen_url"
                        v-model="formulario.imagen_url"
                        type="text"
                        class="form-control bg-light border-0 py-2"
                        placeholder="https://ejemplo.com/foto.jpg"
                        :class="{ 'is-invalid': formulario.errors.imagen_url }"
                    />
                    <div v-if="formulario.errors.imagen_url" class="invalid-feedback">
                        {{ formulario.errors.imagen_url }}
                    </div>
                </div>
            </ModalCrud>
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
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
