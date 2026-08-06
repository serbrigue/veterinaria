<template>
    <!-- ================================================================================== -->
    <!-- COMPONENTE: Listado -->
    <!-- ================================================================================== -->
    <Head title="Clientes" />
    <AuthenticatedLayout>
        <div class="container py-4">
            <div class="card shadow-sm">

                <!-- Encabezado -->
                <div class="card-header border-0 bg-white p-4 d-flex justify-content-between align-items-center flex-wrap gap-3 rounded-top-4 border-bottom border-light">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary bg-opacity-10 p-2 rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px;">
                            <img src="/images/icon_clients.png" alt="Icono Clientes" class="w-100 h-100 object-fit-contain" style="transform: scale(1.15);">
                        </div>
                        <h1 class="h4 mb-0 fw-bold text-dark">Gestión de Clientes</h1>
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <!-- Si el usuario es administrador o secretaria puede acceder a los modulos de exportacion, importación y creación de clientes -->
                        <template v-if="$isAdmin() || $isSecretaria()">
                            <!-- EXPORTACIÓN: Botón para exportar los clientes -->
                            <a href="/api/export/clientes" class="btn btn-light text-success fw-bold rounded-pill shadow-sm btn-hover-elevate">
                                <i class="bi bi-download me-1"></i> Exportar
                            </a>
                            <!-- IMPORTACIÓN: Botón para importar clientes -->
                            <button type="button" class="btn btn-light text-primary fw-bold rounded-pill shadow-sm btn-hover-elevate" @click="mostrarModalImportar = true">
                                <i class="bi bi-upload me-1"></i> Importar Consolidado
                            </button>
                        </template>
                        <!-- CREACIÓN: Botón para crear un nuevo cliente -->
                        <button v-if="$isAdmin() || $isSecretaria()" type="button" class="btn btn-primary fw-bold rounded-pill shadow-sm btn-hover-elevate px-4" @click="abrirModalCrear">
                            <i class="bi bi-person-plus me-1"></i> Nuevo Cliente
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <!-- BARRA DE BÚSQUEDA Y FILTROS -->
                    <BarraFiltros 
                        :deshabilitar-limpiar="!filtroNombre && !filtroMascota && !filtroSucursal && !filtroEstadoPago" 
                        clase-boton-contenedor="col-12 col-lg-2 d-flex gap-2 justify-content-lg-end"
                        @limpiar="limpiarFiltros"
                    >
                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label small fw-bold text-secondary mb-1">Buscar por Nombre</label>
                            <!-- Almacenamos el nombre del cliente en la variable filtroNombre -->
                            <input type="text" class="form-control form-control-sm" placeholder="Nombre de cliente" v-model="filtroNombre" @keyup.enter="obtenerClientes()">
                        </div>
                        <div class="col-12 col-md-4 col-lg-2">
                            <label class="form-label small fw-bold text-secondary mb-1">Mascota</label>
                            <!-- Almacenamos el nombre de la mascota en la variable filtroMascota -->
                            <input type="text" class="form-control form-control-sm" placeholder="Nombre de mascota" v-model="filtroMascota" @keyup.enter="obtenerClientes()">
                        </div>
                        <div class="col-12 col-md-4 col-lg-2">
                            <label class="form-label small fw-bold text-secondary mb-1">Sucursal</label>
                            <!-- Almacenamos la sucursal en la variable filtroSucursal -->
                            <select class="form-select form-select-sm" v-model="filtroSucursal" @change="obtenerClientes()">
                                <option value="">Todas</option>
                                <!-- Renderizamos las sucursales iterando sobre ellas -->
                                <option v-for="suc in sucursales" :key="suc.id" :value="suc.id">
                                    {{ suc.nombre }}
                                </option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4 col-lg-3">
                            <label class="form-label small fw-bold text-secondary mb-1">Estado Financiero</label>
                            <!-- Almacenamos el estado de pago en la variable filtroEstadoPago -->
                            <select class="form-select form-select-sm" v-model="filtroEstadoPago" @change="obtenerClientes()">
                                <option value="">Todos</option>
                                <option value="al_dia">Al Día (Sin deuda)</option>
                                <option value="moroso">Con Deuda (Moroso)</option>
                            </select>
                        </div>
                        <!-- Boton para limpiar los filtros -->
                        <template #texto-limpiar>
                            Limpiar
                        </template>
                    </BarraFiltros>
                    <!-- Indicador de carga -->
                    <IndicadorCarga :cargando="cargando" mensaje="clientes" />

                    <!-- Mensaje de estado vacio -->
                    <EstadoVacio
                        :visible="!cargando && listaVacia"
                        mensaje="No tienes clientes registrados aún."
                        texto-boton="Registrar tu primer cliente"
                        icono="bi bi-people"
                        @accion="abrirModalCrear"
                    />

                    <!-- Mensaje de sin resultados -->
                    <SinResultados
                        :visible="cargando && sinResultadosFiltro"
                        mensaje="Ningún cliente coincide con la búsqueda."
                        @limpiar="limpiarFiltros()"
                    />

                    <!-- Si no está cargando, no está vacia y no hay resultados de filtro, renderizamos la lista de clientes -->
                    <div v-if="!cargando && !listaVacia && !sinResultadosFiltro">
                        <!-- MENU DE ACCION RAPIDA (CORREO MASIVO) -->
                        <!-- Si el usuario es administrador o secretaria y hay clientes seleccionados, mostramos el menu de accion rapida -->
                        <div v-if="($isAdmin() || $isSecretaria()) && selectedClientes.length > 0" class="alert alert-info d-flex justify-content-between align-items-center mb-4 shadow-sm border border-info rounded-3 p-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-people-fill fs-5"></i>
                                <span>Hay <strong>{{ selectedClientes.length }}</strong> cliente(s) seleccionado(s).</span>
                            </div>
                            <div class="d-flex gap-2">
                                <!-- Boton para enviar correo masivo -->
                                <button class="btn btn-primary btn-sm fw-semibold shadow-sm" @click="abrirModalCorreos">
                                    <i class="bi bi-envelope-fill me-1"></i> Enviar Correo Masivo
                                </button>
                                <!-- Boton para limpiar la seleccion -->
                                <button class="btn btn-outline-secondary btn-sm" @click="clearSelection">
                                    Desmarcar todos
                                </button>
                            </div>
                        </div>

                        <!-- Tabla de clientes -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle border">
                            <thead class="table-light">
                                <tr>
                                    <!-- Si el usuario es administrador o secretaria, mostramos la columna de seleccion -->
                                    <th v-if="$isAdmin() || $isSecretaria()" class="ps-3" style="width: 45px;">
                                        <input type="checkbox" class="form-check-input" :checked="isAllSelected" @change="toggleSelectAll">
                                    </th>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7" :class="{ 'ps-3': !($isAdmin() || $isSecretaria()) }">Cliente</th>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Contacto</th>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Mascotas</th>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Estado Financiero</th>
                                    <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Renderizado iterativo de los clientes -->
                                <tr v-for="cliente in clientesArray" :key="cliente.id" 
                                    :class="{ 'table-active': ($isAdmin() || $isSecretaria()) && selectedClientes.includes(cliente.id) }"
                                    @click="irADetalle(cliente.id)"
                                    style="cursor: pointer;"
                                    class="row-hover"
                                >
                                    <!-- Si el usuario es administrador o secretaria, mostramos la columna de seleccion -->
                                    <td v-if="$isAdmin() || $isSecretaria()" class="ps-3">
                                        <input type="checkbox" class="form-check-input" :value="cliente.id" v-model="selectedClientes" @click.stop>
                                    </td>
                                    <td :class="{ 'ps-3': !($isAdmin() || $isSecretaria()) }">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <!-- Si el cliente tiene foto de perfil, la mostramos, si no, mostramos un icono de persona -->
                                                <img v-if="cliente.foto_perfil_url" :src="cliente.foto_perfil_url" class="rounded-circle object-fit-cover" style="width: 40px; height: 40px;">
                                                <i v-else class="bi bi-person-fill fs-5"></i>
                                            </div>
                                            <div>
                                                <!-- Enlace a la pagina de detalle del cliente -->
                                                <Link :href="route('clientes.detalle', cliente.id)" class="text-dark fw-bold text-decoration-none hover-primary">
                                                    {{ cliente.usuario?.name || 'Sin Nombre' }}
                                                </Link>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="text-muted small"><i class="bi bi-telephone-fill me-1"></i> {{ cliente.telefono || 'Sin teléfono' }}</span>
                                            <span class="text-muted small"><i class="bi bi-envelope-fill me-1"></i> {{ cliente.usuario?.email || 'Sin email' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <!-- Si el cliente tiene mascotas, las mostramos, si no, mostramos un mensaje -->
                                        <div v-if="cliente.mascotas && cliente.mascotas.length > 0" class="d-flex flex-wrap gap-1">
                                            <!-- Renderizado iterativo de las mascotas del cliente -->
                                            <span v-for="mascota in cliente.mascotas" :key="mascota.id" class="badge bg-light text-dark border shadow-sm">
                                                <i class="bi bi-heart-fill text-danger me-1" style="font-size: 0.6rem;"></i> {{ mascota.nombre }}
                                            </span>
                                        </div>
                                        <span v-else class="text-muted small">Sin mascotas</span>
                                    </td>
                                    <td>
                                        <!-- Si el cliente tiene transacciones pendientes, mostramos una alerta de deuda activa, si no, una alerta de cuenta al día -->
                                        <div v-if="cliente.transacciones && cliente.transacciones.length > 0">
                                            <span class="badge bg-danger rounded-pill px-3 py-1 shadow-sm">
                                                <i class="bi bi-exclamation-triangle-fill me-1"></i> Deuda Activa ({{ cliente.transacciones.length }})
                                            </span>
                                        </div>
                                        <div v-else>
                                            <span class="badge bg-success rounded-pill px-3 py-1 shadow-sm">
                                                <i class="bi bi-check-circle-fill me-1"></i> Al día
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2 align-items-center">
                                            <!-- Si el usuario es administrador o secretaria, mostramos el boton de editar -->
                                            <TieneRol :rol="['admin', 'secretaria']">
                                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3 hover-opacity" @click.stop="abrirModalEditar(cliente)">
                                                    Editar
                                                </button>
                                            </TieneRol>
                                            <i class="bi bi-caret-right-fill text-muted fs-5 ms-2"></i>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>


                    <Paginador :data="clientesData" entidad="clientes" @cambiar-pagina="obtenerClientes" />
                </div>
            </div>
        </div>

        <ModalImportarConsolidado
            :visible="mostrarModalImportar"
            @cerrar="mostrarModalImportar = false"
            @importado="obtenerClientes()"
        />
            
            <ModalCrud
                :visible="mostrarModal"
                :titulo="modoEdicion ? 'Editar Cliente' : 'Nuevo Cliente'"
                :modo-edicion="modoEdicion"
                :processing="formulario.processing"
                texto-guardar="Guardar Cambios"
                texto-crear="Crear Cliente"
                @cerrar="cerrarModal"
                @guardar="guardar"
            >
                <div class="mb-3">
                    <label for="nombre" class="form-label fw-semibold text-secondary small text-uppercase">Nombre Completo</label>
                    <!-- Almacenamos el nombre del cliente en la propiedad "formulario.nombre" -->
                    <input id="nombre" v-model="formulario.nombre" type="text" class="form-control bg-light border-0 py-2" placeholder="Ej: Juan Pérez" :class="{ 'is-invalid': formulario.errors.nombre }" required />
                    <!-- Si surge un error, lo mostramos en un mensaje -->
                    <div v-if="formulario.errors.nombre" class="invalid-feedback">{{ formulario.errors.nombre }}</div>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold text-secondary small text-uppercase">Correo Electrónico</label>
                    <!-- Almacenamos el correo del cliente en la propiedad "formulario.email" -->
                    <input id="email" v-model="formulario.email" type="email" class="form-control bg-light border-0 py-2" placeholder="juan@ejemplo.com" :class="{ 'is-invalid': formulario.errors.email }" required />
                    <!-- Si surge un error, lo mostramos en un mensaje -->
                    <div v-if="formulario.errors.email" class="invalid-feedback">{{ formulario.errors.email }}</div>
                </div>
                <div class="mb-3">
                    <label for="telefono" class="form-label fw-semibold text-secondary small text-uppercase">Teléfono de Contacto</label>
                    <!-- Almacenamos el teléfono del cliente en la propiedad "formulario.telefono" -->
                    <input id="telefono" v-model="formulario.telefono" type="text" class="form-control bg-light border-0 py-2" placeholder="+56912345678" :class="{ 'is-invalid': formulario.errors.telefono }" />
                    <!-- Si surge un error, lo mostramos en un mensaje -->
                    <div v-if="formulario.errors.telefono" class="invalid-feedback">{{ formulario.errors.telefono }}</div>
                </div>
                <div class="mb-3">
                    <label for="direccion" class="form-label fw-semibold text-secondary small text-uppercase">Dirección</label>
                    <!-- Almacenamos la dirección del cliente en la propiedad "formulario.direccion" -->
                    <input id="direccion" v-model="formulario.direccion" type="text" class="form-control bg-light border-0 py-2" placeholder="Av. Siempre Viva 742" :class="{ 'is-invalid': formulario.errors.direccion }" />
                    <!-- Si surge un error, lo mostramos en un mensaje -->
                    <div v-if="formulario.errors.direccion" class="invalid-feedback">{{ formulario.errors.direccion }}</div>
                </div>
            </ModalCrud>

            <!-- ========================================== -->
            <!-- MODAL: Enviar Correo Masivo                -->
            <!-- ========================================== -->
            <!-- Si mostrarModalCorreo es true, se muestra el modal -->
            <div v-if="mostrarModalCorreo" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5); z-index: 1055;">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow border-0">
                        <div class="modal-header border-bottom bg-primary text-white py-3 px-4">
                            <h5 class="modal-title fw-bold">
                                <i class="bi bi-envelope-paper-fill me-2"></i>Enviar Correo Masivo
                            </h5>
                            <!-- Boton para cerrar el modal -->
                            <button type="button" class="btn-close btn-close-white" @click="cerrarModalCorreo"></button>
                        </div>
                        <div class="modal-body p-4">
                            <!-- Muestra la cantidad de destinatarios -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-secondary">Destinatarios ({{ selectedClientes.length }})</label>
                                <div class="border rounded bg-light p-2 d-flex flex-wrap gap-1 align-items-center" style="max-height: 100px; overflow-y: auto;">
                                    <!-- Renderizado de destinatarios -->
                                    <span v-for="cId in selectedClientes" :key="cId" class="badge bg-secondary rounded-pill px-2 py-1 small me-1 mb-1">
                                        {{ getClienteNombre(cId) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Plantillas -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-secondary">Seleccionar Plantilla / Tipo</label>
                                <div class="d-flex gap-2">
                                    <!-- Al dar click se ejecuta la funcion setEmailPreset -->
                                    <button type="button" class="btn btn-sm" :class="emailType === 'deuda' ? 'btn-danger' : 'btn-outline-danger'" @click="setEmailPreset('deuda')">
                                        <i class="bi bi-exclamation-circle-fill me-1"></i>Deudas
                                    </button>
                                    <!-- Al dar click se ejecuta la funcion setEmailPreset -->
                                    <button type="button" class="btn btn-sm" :class="emailType === 'promocion' ? 'btn-success' : 'btn-outline-success'" @click="setEmailPreset('promocion')">
                                        <i class="bi bi-tag-fill me-1"></i>Promociones
                                    </button>
                                    <!-- Al dar click se ejecuta la funcion setEmailPreset -->
                                    <button type="button" class="btn btn-sm" :class="emailType === 'personalizado' ? 'btn-primary' : 'btn-outline-primary'" @click="setEmailPreset('personalizado')">
                                        <i class="bi bi-pencil-fill me-1"></i>Personalizado
                                    </button>
                                </div>
                            </div>

                            <!-- Campos del formulario -->
                            <div class="mb-3">
                                <label for="email_asunto" class="form-label fw-semibold text-secondary">Asunto del Correo</label>
                                <!-- Almacenamos el asunto del correo en la propiedad "emailAsunto" -->
                                <input id="email_asunto" v-model="emailAsunto" type="text" class="form-control" placeholder="Ej: Novedades de la clínica" required />
                            </div>

                            <div class="mb-3">
                                <label for="email_mensaje" class="form-label fw-semibold text-secondary">Mensaje</label>
                                <!-- Almacenamos el mensaje del correo en la propiedad "emailMensaje" -->
                                <textarea id="email_mensaje" v-model="emailMensaje" class="form-control" rows="5" placeholder="Escribe el mensaje aquí..." required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <!-- Al dar click se ejecuta la funcion cerrarModalCorreo -->
                            <button type="button" class="btn btn-secondary" @click="cerrarModalCorreo">Cancelar</button>
                            <!-- Al dar click se ejecuta la funcion enviarCorreos -->
                            <button type="button" class="btn btn-primary" :disabled="enviandoCorreos || !emailAsunto || !emailMensaje" @click="enviarCorreos">
                                <!-- Si enviandoCorreos es true, se muestra un spinner -->
                                <span v-if="enviandoCorreos" class="spinner-border spinner-border-sm me-2"></span>
                                <i class="bi bi-send me-1"></i> Enviar a {{ selectedClientes.length }} clientes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>

<script>
// ==================================================================================
// LÓGICA DEL COMPONENTE (VUE 3)
// ==================================================================================

import AuthenticatedLayout from '@/Disenos/LayoutAutenticado.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import IndicadorCarga from '@/Componentes/IndicadorCarga.vue';
import EstadoVacio from '@/Componentes/EstadoVacio.vue';
import SinResultados from '@/Componentes/SinResultados.vue';
import ModalImportarConsolidado from '@/Componentes/ModalImportarConsolidado.vue';
import Paginador from '@/Componentes/Paginador.vue';
import ModalCrud from '@/Componentes/ModalCrud.vue';
import BarraFiltros from '@/Componentes/BarraFiltros.vue';

// ------------------------------------------------------------------------------
// EXPORT DEFAULT: Definición principal del componente
// ------------------------------------------------------------------------------
export default {
    // COMPONENTES: Registro de componentes importados
    components: {
        AuthenticatedLayout,
        Head,
        Link,
        IndicadorCarga,
        EstadoVacio,
        SinResultados,
        ModalImportarConsolidado,
        Paginador,
        ModalCrud,
        BarraFiltros,
    },
    // PROPIEDADES: Datos inyectados desde el componente padre o estado
    props: {
        clientes: {
            type: Object,
            default: () => ({ data: [] }),
        },
        sucursales: {
            type: Array,
            default: () => [],
        },
    },
    // ESTADO REACTIVO: Variables locales del componente
    data() {
        return {
            //Inicializamos las variables
            cargando: false,
            mostrarModalImportar: false,
            clientesLocal: this.clientes.data,
            mostrarModal: false,
            modoEdicion: false,
            clienteEditando: null,
            mostrarConfirmacion: false,
            clienteAEliminar: null,
            eliminando: false,
            filtroNombre: '',
            filtroMascota: '',
            filtroSucursal: '',
            filtroEstadoPago: '',
            clientesData: this.clientes,
            clientesArray: this.clientes?.data || [],
            // Inicializamos las variables del formulario
            formulario: {
                nombre: '',
                email: '',
                telefono: '',
                direccion: '',
                errors: {},
                processing: false,
            },
            // Selección y correo masivo
            selectedClientes: [],
            mostrarModalCorreo: false,
            emailType: 'personalizado',
            emailAsunto: '',
            emailMensaje: '',
            enviandoCorreos: false,
        }
    },
    // PROPIEDADES COMPUTADAS: Variables reactivas que dependen de otras
    computed: {
        //Si todos los clientes están seleccionados
        isAllSelected() {
            if (this.clientesArray.length === 0) return false;
            return this.clientesArray.every(c => this.selectedClientes.includes(c.id));
        },
        //Si la lista está vacía
        listaVacia() {
            return !this.filtroNombre && !this.filtroMascota && !this.filtroSucursal && !this.filtroEstadoPago && this.clientesArray.length === 0;
        },
        //Si no hay resultados de los filtros
        sinResultadosFiltro() {
            return (this.filtroNombre || this.filtroMascota || this.filtroSucursal || this.filtroEstadoPago) && this.clientesArray.length === 0;
        },
    },
    // MÉTODOS: Bloque de funciones y eventos
    methods: {
        // Navega a la vista de detalle del cliente
        irADetalle(id) {
            router.visit(route('clientes.detalle', id));
        },
        //Abre el modal para crear un nuevo cliente
        abrirModalCrear() {
            this.modoEdicion = false
            this.clienteEditando = null
            this.formulario.nombre = ''
            this.formulario.email = ''
            this.formulario.telefono = ''
            this.formulario.direccion = ''
            this.formulario.errors = {}
            this.mostrarModal = true
        },
        //Abre el modal para editar un cliente existente
        abrirModalEditar(cliente) {
            this.modoEdicion = true
            this.clienteEditando = cliente
            this.formulario.nombre = cliente.usuario?.name || cliente.nombre || ''
            this.formulario.email = cliente.usuario?.email || cliente.email || ''
            this.formulario.telefono = cliente.telefono || ''
            this.formulario.direccion = cliente.direccion || ''
            this.formulario.errors = {}
            this.mostrarModal = true
        },
        //Cierra el modal y limpia el estado
        cerrarModal() {
            this.mostrarModal = false
            this.clienteEditando = null
        },
        //Guarda el cliente (crear o actualizar según modoEdicion)
        guardar() {
            this.formulario.processing = true;
            this.formulario.errors = {};

            //Edita el cliente
            if (this.modoEdicion) {
                //Hacemos la petición put para actualizar el cliente
                axios.put(`/api/clientes/${this.clienteEditando.id}`, {
                    //payload de la petición
                    nombre: this.formulario.nombre,
                    email: this.formulario.email,
                    telefono: this.formulario.telefono,
                    direccion: this.formulario.direccion,
                })
                //Si se actualiza correctamente
                .then(() => {
                    //Cierra el modal
                    this.cerrarModal();
                    //Muestra un mensaje de exito
                    this.$alertaExito('Actualizado', 'El cliente ha sido actualizado correctamente.');
                    //Obtiene los clientes de nuevo
                    this.obtenerClientes();
                })
                //Si hay un error
                .catch((error) => {
                    //Si hay un error de validación
                    if (error.response?.status === 422) {
                        //Muestra los errores de validación
                        this.formulario.errors = error.response.data.errors;
                        this.$alertaValidacion(error.response.data.errors);
                    } else {
                        //Muestra un mensaje de error
                        this.$alertaError('Error', 'No se pudo actualizar el cliente.');
                    }
                })
                //Al finalizar
                .finally(() => {
                    //Finaliza el procesamiento
                    this.formulario.processing = false;
                });
            } else {
                //crea el cliente
                axios.post('/api/clientes', {
                    nombre: this.formulario.nombre,
                    email: this.formulario.email,
                    telefono: this.formulario.telefono,
                    direccion: this.formulario.direccion,
                })
                //Si se crea correctamente
                .then(() => {
                    //Cierra el modal
                    this.cerrarModal();
                    //Muestra un mensaje de exito
                    this.$alertaExito('Creado', 'El cliente ha sido registrado correctamente.');
                    //Obtiene los clientes de nuevo
                    this.obtenerClientes();
                })
                //Si hay un error
                .catch((error) => {
                    //Si hay un error de validación
                    if (error.response?.status === 422) {
                        //Muestra los errores de validación
                        this.formulario.errors = error.response.data.errors;
                        this.$alertaValidacion(error.response.data.errors);
                    } else {
                        //Muestra un mensaje de error
                        this.$alertaError('Error', 'No se pudo crear el cliente.');
                    }
                })
                //Al finalizar
                .finally(() => {
                    //Finaliza el procesamiento
                    this.formulario.processing = false;
                });
            }
        },
        //Confirma la eliminación del cliente
        confirmarEliminar(cliente) {
            this.$confirmar('¿Eliminar Cliente?', `Se eliminará el cliente ${cliente.usuario?.name || ''} y todas sus asociaciones.`)
                .then((resultado) => {
                    //Si no se confirma la eliminación
                    if (!resultado.isConfirmed) return;
                    //Inicia el proceso de eliminación
                    this.eliminando = true;
                    //Hace la petición delete para eliminar el cliente
                    axios.delete(`/api/clientes/${cliente.id}`)
                        //Si se elimina correctamente
                        .then(() => {
                            //Muestra un mensaje de exito
                            this.$alertaExito('Eliminado', 'El cliente ha sido eliminado.');
                            //Obtiene los clientes de nuevo
                            this.obtenerClientes();
                        })
                        //Si hay un error
                        .catch(() => {
                            //Muestra un mensaje de error
                            this.$alertaError('Error', 'No se pudo eliminar el cliente.');
                        })
                        //Al finalizar
                        .finally(() => {
                            //Finaliza el procesamiento
                            this.eliminando = false;
                        });
                });
        },
        //Obtiene los clientes
        obtenerClientes(url = '/clientes') {
            //Si no hay url, retorna
            if (!url) return;
            //Inicia el procesamiento
            this.cargando = true;
            //Hace la petición get para obtener los clientes
            axios.get(url, {
                params: {
                    nombre: this.filtroNombre,
                    mascota: this.filtroMascota,
                    sucursal_id: this.filtroSucursal,
                    estado_pago: this.filtroEstadoPago
                }
            })
            //Si la petición es exitosa
            .then(response => {
                //Si hay datos paginados
                if (response.data.clientes && response.data.clientes.data) {
                    this.clientesData = response.data.clientes;
                    this.clientesArray = response.data.clientes.data;
                } else if (response.data.clientes) {
                    this.clientesData = null;
                    this.clientesArray = response.data.clientes;
                }
            })
            //Si hay un error
            .catch(error => {
                console.error('Error al obtener clientes:', error);
            })
            //Al finalizar
            .finally(() => {
                //Finaliza el procesamiento
                this.cargando = false;
            });
        },
        //Limpia los filtros
        limpiarFiltros() {
            //Limpia los filtros
            this.filtroNombre = '';
            this.filtroMascota = '';
            this.filtroSucursal = '';
            this.filtroEstadoPago = '';
            this.obtenerClientes();
        },
        //Alterna la selección de todos los clientes
        toggleSelectAll(event) {
            //Si se selecciona todos los clientes
            if (event.target.checked) {
                //Selecciona todos los clientes
                this.clientesArray.forEach(c => {
                    //Si no está seleccionado, lo selecciona
                    if (!this.selectedClientes.includes(c.id)) {
                        this.selectedClientes.push(c.id);
                    }
                });
            } else {
                //Desmarca todos los clientes
                this.selectedClientes = [];
            }
        },
        //Limpia la selección
        clearSelection() {
            //Limpia la selección
            this.selectedClientes = [];
        },
        //Obtiene el nombre del cliente
        getClienteNombre(id) {
            //Busca el cliente
            const found = this.clientesArray.find(c => c.id === id);
            //Si lo encuentra, retorna el nombre
            return found ? (found.usuario?.name || 'Cliente') : 'Cliente';
        },
        //Abre el modal de correos
        abrirModalCorreos() {
            //Inicia el procesamiento
            this.emailType = 'personalizado';
            this.emailAsunto = '';
            this.emailMensaje = '';
            this.mostrarModalCorreo = true;
        },
        //Cierra el modal de correos
        cerrarModalCorreo() {
            this.mostrarModalCorreo = false;
        },
        //Establece un preset para el correo
        setEmailPreset(type) {
            //Establece el tipo de preset
            this.emailType = type;
            //Si es un preset de deuda
            if (type === 'deuda') {
                this.emailAsunto = 'Recordatorio de Pago Pendiente - Clínica Veterinaria';
                this.emailMensaje = 'Estimado/a cliente,\n\nLe recordamos que presenta saldos pendientes en nuestra clínica. Agradecemos su pronto pago para poder seguir brindándole la mejor atención a sus mascotas.\n\nAtentamente,\nEl equipo de Clínica Veterinaria';
            } else if (type === 'promocion') {
                this.emailAsunto = '¡Promociones Especiales en Vacunas y Alimentos!';
                this.emailMensaje = 'Estimado/a cliente,\n\n¡Tenemos excelentes noticias! Durante este mes contamos con un 20% de descuento en vacunas anuales y en alimentos seleccionados en todas nuestras sucursales.\n\n¡No dejes pasar esta oportunidad de cuidar a tu mascota!\n\nAtentamente,\nEl equipo de Clínica Veterinaria';
            } else {
                this.emailAsunto = '';
                this.emailMensaje = '';
            }
        },
        //Envía los correos
        enviarCorreos() {
            //Inicia el procesamiento
            this.enviandoCorreos = true;
            //Hace la petición post para enviar los correos
            axios.post('/api/clientes/enviar-correo', {
                clientes_ids: this.selectedClientes,
                asunto: this.emailAsunto,
                mensaje: this.emailMensaje
            })
            //Si la petición es exitosa
            .then(response => {
                //Cierra el modal de correos
                this.cerrarModalCorreo();
                //Limpia la selección
                this.selectedClientes = [];
                //Muestra un mensaje de exito
                Swal.fire({
                    icon: 'success',
                    title: 'Correos enviados',
                    text: response.data.mensaje || 'Se han enviado los correos correctamente.',
                    confirmButtonColor: '#3085d6'
                });
            })
            //Si hay un error
            .catch(error => {
                console.error('Error al enviar correos:', error);
                const errMsg = error.response?.data?.error || 'No se pudieron enviar los correos.';
                //Muestra un mensaje de error
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errMsg,
                    confirmButtonColor: '#3085d6'
                });
            })
            //Al finalizar
            .finally(() => {
                //Finaliza el procesamiento
                this.enviandoCorreos = false;
            });
        }
    },
    // Se ejecuta al cargar el componente en el DOM
    mounted() {
        this.obtenerClientes();
    }
}
</script>

<style scoped>
.hover-primary:hover {
    color: var(--bs-primary) !important;
}
.hover-opacity:hover {
    opacity: 0.8;
}
.row-hover:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.03) !important;
    transition: background-color 0.2s ease-in-out;
}
.btn-hover-elevate {
    transition: all 0.2s;
}
.btn-hover-elevate:hover {
    transform: translateY(-2px);
}
</style>
