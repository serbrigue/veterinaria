<template>
    <Head title="Sucursales" />
    <AuthenticatedLayout>
        <div class="container py-4">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h1 class="h4 mb-0 text-primary fw-bold">Sucursales</h1>
                    <div class="d-flex gap-2">
                        <template v-if="$isAdmin() || $isSecretaria()">
                            <a href="/api/export/sucursales" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-download me-1"></i> Exportar
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-primary" @click="mostrarModalImportar = true">
                                <i class="bi bi-upload me-1"></i> Importar
                            </button>
                        </template>
                        <button v-if="esAdmin" type="button" class="btn btn-primary rounded-pill shadow-sm px-4" @click="abrirModalCrear">
                            <i class="bi bi-plus-lg me-1"></i> Nueva Sucursal
                        </button>
                    </div>
                </div>

                <div class="card-body p-4">
                    <!-- Barra de búsqueda -->
                    <BarraFiltros 
                        :deshabilitar-limpiar="!filtroTexto" 
                        clase-boton-contenedor="col-12 col-md-4 col-lg-6 d-flex justify-content-md-end"
                        @limpiar="limpiarFiltros"
                    >
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
                        <template #texto-limpiar>
                            Limpiar Filtro
                        </template>
                    </BarraFiltros>

                    <p v-show="!listaVacia" class="text-muted small mb-4 fw-medium ms-2">
                        {{ totalSucursales }} sucursal{{ totalSucursales === 1 ? '' : 'es' }} registrada{{ totalSucursales === 1 ? '' : 's' }}
                    </p>

                    <IndicadorCarga :cargando="cargando" mensaje="sucursales" />

                    <EstadoVacio
                        :visible="!cargando && listaVacia"
                        mensaje="No tienes sucursales registradas aún."
                        :texto-boton="esVeterinarioOAdmin ? 'Registrar tu primera sucursal' : ''"
                        icono="bi bi-shop"
                        @accion="abrirModalCrear"
                    />

                    <SinResultados
                        :visible="!cargando && sinResultadosFiltro"
                        mensaje="Ninguna sucursal coincide con la búsqueda."
                        @limpiar="limpiarFiltros()"
                    />

                    <!-- Grid de Sucursales -->
                    <div v-if="!cargando && !listaVacia && !sinResultadosFiltro" class="row g-4">
                        <div v-for="sucursal in sucursalesVisibles" :key="sucursal.id" class="col-12 col-md-6 col-lg-4">
                            <TarjetaEntidad
                                :titulo="sucursal.nombre"
                                icono="bi-shop"
                                :imagen-url="sucursal.imagen_url || '/images/default_sucursal.png'"
                                :url-detalle="route('sucursales.detalle', sucursal.id)"
                                :mostrar-acciones="esVeterinarioOAdmin"
                                @editar="abrirModalEditar(sucursal)"
                                @eliminar="confirmarEliminar(sucursal)"
                            >
                                <template #body>
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
                texto-crear="Crear Sucursal"
                @cerrar="cerrarModal"
                @guardar="guardar"
            >
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary small text-uppercase">Nombre de la Sucursal</label>
                    <input v-model="formulario.nombre" type="text" class="form-control bg-light border-0 py-2" placeholder="Ej: Sede Central" :class="{ 'is-invalid': formulario.errors.nombre }" required />
                    <div v-if="formulario.errors.nombre" class="invalid-feedback">{{ formulario.errors.nombre }}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary small text-uppercase">Dirección</label>
                    <input v-model="formulario.direccion" type="text" class="form-control bg-light border-0 py-2" placeholder="Ej: Av. Principal 123" :class="{ 'is-invalid': formulario.errors.direccion }" required />
                    <div v-if="formulario.errors.direccion" class="invalid-feedback">{{ formulario.errors.direccion }}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary small text-uppercase">Teléfono</label>
                    <input v-model="formulario.telefono" type="text" class="form-control bg-light border-0 py-2" placeholder="Ej: +56 9 1234 5678" :class="{ 'is-invalid': formulario.errors.telefono }" required />
                    <div v-if="formulario.errors.telefono" class="invalid-feedback">{{ formulario.errors.telefono }}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary small text-uppercase">Foto / Imagen de la Sucursal</label>
                    <input
                        ref="fotoInput"
                        type="file"
                        class="form-control bg-light border-0 py-2"
                        accept="image/*"
                        @change="seleccionarFoto"
                        :class="{ 'is-invalid': formulario.errors.imagen_url }"
                    />
                    <div v-if="formulario.errors.imagen_url" class="invalid-feedback">
                        {{ formulario.errors.imagen_url }}
                    </div>
                    <div v-if="formulario.imagen_url_preview || formulario.imagen_url" class="mt-2 text-center">
                        <img :src="formulario.imagen_url_preview || formulario.imagen_url" class="img-thumbnail" style="max-height: 120px;" alt="Vista previa" />
                    </div>
                </div>
            </ModalCrud>

            <ModalImportarSimple
                :visible="mostrarModalImportar"
                entidad="sucursales"
                etiqueta="Sucursales"
                @cerrar="mostrarModalImportar = false"
                @importado="obtenerSucursales()"
            />
        </div>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from '@/Disenos/LayoutAutenticado.vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
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
            mostrarModalImportar: false,
            formulario: {
                nombre: '',
                direccion: '',
                telefono: '',
                imagen_url: '', // Ruta actual en BD
                imagen_url_file: null, // Archivo a subir
                imagen_url_preview: null, // Previsualización local
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
        esAdmin() {
            const user = this.$page.props.auth.user;
            return user && (user.rol?.nombre_interno === 'admin');
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
        seleccionarFoto(e) {
            const archivos = e.target.files;
            if (archivos && archivos.length > 0) {
                this.formulario.imagen_url_file = archivos[0];
                this.formulario.imagen_url_preview = URL.createObjectURL(archivos[0]);
            }
        },
        datosFormulario() {
            const formData = new FormData();
            formData.append('nombre', this.formulario.nombre);
            formData.append('direccion', this.formulario.direccion);
            formData.append('telefono', this.formulario.telefono);
            
            if (this.formulario.imagen_url_file) {
                formData.append('imagen_url', this.formulario.imagen_url_file);
            }
            return formData;
        },
        obtenerSucursales() {
            this.cargando = true;
            axios.get('/sucursales')
                .then(response => {
                    this.sucursalesVisibles = response.data.sucursales || [];
                    const texto = this.filtroTexto.toLowerCase();
                    if (texto) {
                        this.sucursalesVisibles = this.sucursalesVisibles.filter(s => s.nombre.toLowerCase().includes(texto));
                    }
                })
                .catch(error => {
                    console.error('Error al obtener sucursales:', error);
                })
                .finally(() => {
                    this.cargando = false;
                });
        },
        abrirModalCrear() {
            this.modoEdicion = false;
            this.sucursalEditando = null;
            this.formulario.nombre = '';
            this.formulario.direccion = '';
            this.formulario.telefono = '';
            this.formulario.imagen_url = '';
            this.formulario.imagen_url_file = null;
            this.formulario.imagen_url_preview = null;
            if (this.$refs.fotoInput) {
                this.$refs.fotoInput.value = '';
            }
            this.formulario.errors = {};
            this.mostrarModal = true;
        },
        abrirModalEditar(sucursal) {
            this.modoEdicion = true;
            this.sucursalEditando = sucursal;
            this.formulario.nombre = sucursal.nombre;
            this.formulario.direccion = sucursal.direccion;
            this.formulario.telefono = sucursal.telefono;
            this.formulario.imagen_url = sucursal.imagen_url;
            this.formulario.imagen_url_file = null;
            this.formulario.imagen_url_preview = null;
            if (this.$refs.fotoInput) {
                this.$refs.fotoInput.value = '';
            }
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
            this.formulario.imagen_url = '';
            this.formulario.imagen_url_file = null;
            this.formulario.imagen_url_preview = null;
            this.formulario.errors = {};
            this.mostrarModal = false;
        },
        guardar() {
            this.formulario.processing = true;
            this.formulario.errors = {};
            
            const data = this.datosFormulario();

            if (this.modoEdicion) {
                data.append('_method', 'PUT');
                axios.post(`/api/sucursales/${this.sucursalEditando.id}`, data, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })
                .then(() => { 
                    this.cerrarModal(); 
                    this.obtenerSucursales();
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
                axios.post('/api/sucursales', data, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })
                .then(() => { 
                    this.cerrarModal(); 
                    this.obtenerSucursales();
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
                this.obtenerSucursales();
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
.border-dashed {
    border-style: dashed !important;
}
</style>
