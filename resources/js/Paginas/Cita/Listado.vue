<template>
    <!-- ================================================================================== -->
    <!-- COMPONENTE: Listado -->
    <!-- ================================================================================== -->
    <Head title="Citas" />
    <AuthenticatedLayout>
        <div class="container py-4">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h1 class="h5 mb-0">Mis Citas</h1>

                    <div class="d-flex gap-2">
                        <!-- Si el usuario es administrador o secretaria, se muestran los botones de exportar e importar -->
                        <template v-if="$isAdmin() || $isSecretaria()">
                            <a href="/api/export/citas" class="btn btn-outline-success">
                                <i class="bi bi-download me-1"></i> Exportar
                            </a>
                            <button type="button" class="btn btn-outline-primary" @click="mostrarModalImportar = true">
                                <i class="bi bi-upload me-1"></i> Importar Consolidado
                            </button>
                        </template>
                        <!-- Si el usuario es cliente, administrador o veterinario, se muestra el boton de nueva cita -->
                        <button v-if="$isCliente() || $isAdmin() || $isSecretaria()" type="button" class="btn btn-primary" @click="abrirModalCrear">
                            + Nueva Cita
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Barra de búsqueda y filtros -->
                    <BarraFiltros
                        :deshabilitar-limpiar="!filtroTitulo && !filtroSucursal && !filtroMascota && !filtroVeterinario && !filtroEstado && !filtroCliente"
                        clase-boton-contenedor="col-12 col-lg-2 d-flex gap-2 justify-content-lg-end"
                        @limpiar="limpiarFiltros"
                    >
                        <!-- Buscar por Título -->
                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label small fw-bold text-secondary mb-1" for="filtroTitulo">Buscar por Título</label>
                            <!-- vincula el input con la variable filtroTitulo -->
                            <input type="text" class="form-control form-control-sm" id="filtroTitulo" placeholder="Ej: Control mensual" v-model="filtroTitulo" @keyup.enter="obtenerCitas()">
                        </div>
                        <!-- Buscar por Sucursal -->
                        <!-- Si el usuario no es veterinario, se muestra el filtro de sucursal -->
                        <div v-if="!$isVeterinario()" class="col-12 col-md-4 col-lg-2">
                            <label class="form-label small fw-bold text-secondary mb-1" for="filtroSucursal">Sucursal</label>
                            <!-- v-model vincula el select con la variable filtroSucursal -->
                            <select 
                                class="form-select form-select-sm" 
                                id="filtroSucursal"
                                v-model="filtroSucursal"
                                @change="cambiarFiltroSucursal"
                            >
                                <option value="">Todas</option>
                                <!-- Recorre la lista de sucursales para mostrar las opciones del filtro -->
                                <option 
                                    v-for="sucursal in sucursales" 
                                    :key="sucursal.id" 
                                    :value="sucursal.id"
                                >
                                    {{ sucursal.nombre }}
                                </option>
                            </select>
                        </div>
                        
                        <!-- Buscar por Cliente -->
                        <!-- Si el usuario es administrador, secretaria o veterinario, se muestra el filtro de cliente -->
                        <div v-if="$isAdmin() || $isSecretaria() || $isVeterinario()" class="col-12 col-md-4 col-lg-2">
                            <label class="form-label small fw-bold text-secondary mb-1" for="filtroCliente">Buscar por Cliente</label>
                            <!-- v-model vincula el input con la variable filtroCliente -->
                            <input type="text" class="form-control form-control-sm" id="filtroCliente" placeholder="Ej: Juan Pérez" v-model="filtroCliente" @keyup.enter="obtenerCitas()">
                        </div>

                        <!-- Buscar por Mascota -->
                        <div class="col-12 col-md-4 col-lg-2">
                            <label class="form-label small fw-bold text-secondary mb-1" for="filtroMascota">Buscar por Mascota</label>
                            <!-- v-model vincula el input con la variable filtroMascota -->
                            <input type="text" class="form-control form-control-sm" id="filtroMascota" placeholder="Nombre de mascota..." v-model="filtroMascota" @keyup.enter="obtenerCitas()">
                        </div>
                        
                        <!-- Buscar por Veterinario -->
                        <!-- Si el usuario no es veterinario, se muestra el filtro de veterinario -->
                        <div v-if="!$isVeterinario()" class="col-12 col-md-4 col-lg-2">
                            <label class="form-label small fw-bold text-secondary mb-1" for="filtroVeterinario">Buscar por Veterinario</label>
                            <!-- v-model vincula el select con la variable filtroVeterinario -->
                            <select 
                                class="form-select form-select-sm"
                                id="filtroVeterinario"
                                v-model="filtroVeterinario"
                                @change="obtenerCitas()"
                            >
                                <option value="">Todos los veterinarios</option>
                                <!-- Recorre la lista de veterinarios para mostrar las opciones del filtro -->
                                <option 
                                    v-for="veterinario in veterinariosFiltradosPorSucursal" 
                                    :key="veterinario.id" 
                                    :value="veterinario.id"
                                >
                                    {{ veterinario.nombre }}
                                </option>
                            </select>
                        </div>

                        <!-- Buscar por Estado -->
                        <div class="col-12 col-md-4 col-lg-1">
                            <label class="form-label small fw-bold text-secondary mb-1" for="filtroEstado">Estado</label>
                            <!-- v-model vincula el select con la variable filtroEstado -->
                            <select 
                                class="form-select form-select-sm"
                                id="filtroEstado"
                                v-model="filtroEstado"
                                @change="obtenerCitas()"
                            >
                                <option value="">Activas (Oculta canceladas)</option>
                                <option value="todos">Mostrar Todas</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="en_curso">En curso</option>
                                <option value="completada">Completada</option>
                                <option value="cancelada">Cancelada</option>
                            </select>
                        </div>

                        <!-- Botón para limpiar filtros -->
                        <template #texto-limpiar>
                            Limpiar
                        </template>
                    </BarraFiltros>

                    <IndicadorCarga :cargando="cargando" mensaje="citas" />

                    <EstadoVacio
                        :visible="!cargando && listaVacia"
                        mensaje="No tienes citas registradas aún."
                        :texto-boton="$isCliente() || $isAdmin() || $isSecretaria() ? 'Registrar primera cita' : ''"
                        icono="bi bi-calendar-x"
                        @accion="abrirModalCrear"
                    />

                    <SinResultados
                        :visible="!cargando && sinResultadosFiltro"
                        mensaje="Ninguna cita coincide con la búsqueda."
                        @limpiar="limpiarFiltros()"
                    />

                    <!-- Muestra la tabla de citas si no está cargando, no está vacía y no hay resultados de búsqueda -->
                    <div v-if="!cargando && !listaVacia && !sinResultadosFiltro" class="table-responsive">
                        <table class="table table-hover align-middle border">
                            <thead class="table-light">
                                <!-- Encabezados de la tabla -->
                                <tr>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">Detalle de la Cita</th>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Paciente y Propietario</th>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Atención</th>
                                    <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-7" style="width: 150px">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Recorre la lista de citas para mostrar las filas de la tabla -->
                                <tr v-for="cita in citas" :key="cita.id" @click="irADetalle(cita.id)" style="cursor: pointer;" class="row-hover transition-all">
                                    <td class="ps-3">
                                        <div class="d-flex flex-column">
                                            <Link :href="route('citas.detalle', cita.id)" class="text-dark fw-bold text-decoration-none mb-1">
                                                {{ cita.titulo }}
                                            </Link>
                                            <span class="text-muted small mb-1">
                                                <i class="bi bi-calendar-event me-1"></i> {{ $formatoFecha(cita.fecha_hora, 'DD/MM/YYYY HH:mm') }}
                                                <!-- Si la cita tiene hora de término, la muestra -->
                                                <span v-if="cita.hora_termino" class="ms-1">- {{ $formatoFecha(cita.hora_termino, 'HH:mm') }}</span>
                                            </span>
                                            <div>
                                                <!-- Dependiendo del estado de la cita, se muestra un badge de diferente color -->
                                                <span class="badge" :class="{
                                                    'bg-warning text-dark': cita.estado === 'pendiente',
                                                    'bg-success': cita.estado === 'completada',
                                                    'bg-danger': cita.estado === 'cancelada',
                                                    'bg-primary': cita.estado === 'en_curso'
                                                }">
                                                    {{ cita.estado ? cita.estado.charAt(0).toUpperCase() + cita.estado.slice(1) : 'Pendiente' }}
                                                </span>
                                            </div>
                                            <!-- Si la cita tiene alertas, las muestra -->
                                            <div v-if="cita.alertas_secretaria?.length" class="d-flex flex-wrap gap-2 mt-2">
                                                <!-- Recorre la lista de alertas y muestra un badge por cada una -->
                                                <span
                                                    v-for="alerta in cita.alertas_secretaria"
                                                    :key="alerta.tipo"
                                                    class="badge rounded-pill d-inline-flex align-items-center shadow-sm border"
                                                    :class="{
                                                        'bg-danger bg-opacity-10 text-danger border-danger border-opacity-25': alerta.tipo === 'sin_equipo',
                                                        'bg-warning bg-opacity-10 text-dark border-warning border-opacity-50': alerta.tipo === 'sin_box'
                                                    }"
                                                    style="font-size: 0.7rem; padding: 0.35rem 0.65rem;"
                                                >
                                                    <i class="bi me-1" :class="alerta.icono"></i>
                                                    {{ alerta.mensaje }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <!-- Muestra la información de la mascota y el propietario -->
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold text-dark">{{ cita.mascota?.nombre || 'Sin Mascota' }} <span class="text-muted fw-normal small" v-if="cita.mascota?.edad_texto">({{ cita.mascota.edad_texto }})</span></span>
                                            <span class="text-muted small"><i class="bi bi-person me-1"></i> {{ cita.cliente?.nombre || 'Sin Propietario' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <!-- Muestra la información del veterinario y el box -->
                                        <div class="d-flex flex-column">
                                            <span class="fw-medium text-dark"><i class="bi bi-heart-pulse text-danger me-1"></i> {{ cita.veterinario?.nombre || 'Sin Asignar' }}</span>
                                            <span class="text-muted small"><i class="bi bi-door-open me-1"></i> {{ cita.box?.nombre || 'Sin Box' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <!-- Botones de acción dependiendo del estado de la cita -->
                                        <div class="d-flex justify-content-center gap-2 align-items-center">
                                            <template v-if="cita.estado === 'completada'">
                                                <!-- Si la cita está completada y tiene una transacción, muestra el total y el comprobante -->
                                                <div v-if="cita.transaccion" class="d-flex flex-column align-items-center gap-1">
                                                    <span class="fw-bold text-success small">Total: ${{ Math.round(cita.transaccion.monto_total).toLocaleString('es-CL') }}</span>
                                                    <button v-if="cita.transaccion.estado === 'pagado'" class="btn btn-outline-primary btn-sm rounded-pill px-2 py-0 shadow-sm" style="font-size: 0.75rem" @click.stop="verComprobante(cita.transaccion, cita)">
                                                        <i class="bi bi-receipt me-1"></i> Comprobante
                                                    </button>
                                                </div>
                                                <!-- Si la cita está completada pero no tiene una transacción, muestra un mensaje -->
                                                <div v-else class="text-muted small fw-medium">
                                                    Sin registro de pago
                                                </div>
                                            </template>
                                            <template v-else-if="cita.estado === 'pendiente'">
                                                <!-- Si la cita está pendiente, permite editarla siempre y cuando sea admin, secretaria o cliente -->
                                                <button
                                                    v-if="$isAdmin() || $isSecretaria() || $isCliente()"
                                                    type="button"
                                                    class="btn btn-sm btn-outline-primary rounded-pill px-3 transition-all hover-opacity"
                                                    @click.stop="abrirModalEditar(cita)"
                                                >
                                                    Editar
                                                </button>
                                                <!-- Si la cita está pendiente, permite cancelarla siempre y cuando sea admin, secretaria o cliente -->
                                                <button
                                                    v-if="$isAdmin() || $isSecretaria() || $isCliente()"
                                                    type="button"
                                                    class="btn btn-sm btn-outline-warning rounded-pill px-3 transition-all hover-opacity"
                                                    @click.stop="confirmarCancelar(cita)"
                                                >
                                                    <i class="bi bi-x-circle-fill me-1"></i> Cancelar
                                                </button>
                                            </template>
                                            <i class="bi bi-caret-right-fill text-muted fs-5 ms-2 d-none d-md-block"></i>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!--Paginador de citas-->
                    <Paginador :data="citasData" entidad="citas" @cambiar-pagina="obtenerCitas" />
                </div>
            </div>

            <!-- MODAL COMPROBANTE DE PAGO -->
        <!-- Si mostrarModalComprobante es verdadero y transaccionSeleccionada es verdadero, se muestra el modal -->
            <div v-if="mostrarModalComprobante && transaccionSeleccionada" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); z-index: 1055;">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow rounded-4">
                        <div class="modal-header bg-light border-bottom-0 rounded-top-4 p-4">
                            <h5 class="modal-title fw-bold text-dark"><i class="bi bi-receipt me-2 text-primary"></i> Comprobante de Pago</h5>
                            <!-- Cerrar modal -->
                            <button type="button" class="btn-close" @click="mostrarModalComprobante = false"></button>
                        </div>
                        <!--Comprobante de pago -->
                        <div class="modal-body p-4" id="comprobante-imprimir">
                            <div class="text-center mb-4">
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                                <h4 class="mt-2 fw-bold text-success">¡Pago Exitoso!</h4>
                                <p class="text-muted mb-0">Comprobante #{{ transaccionSeleccionada.id.toString().padStart(6, '0') }}</p>
                            </div>
                            
                            <div class="card bg-light border-0 rounded-4">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted small">Fecha de pago:</span>
                                        <span class="fw-medium text-dark">{{ formatearFecha(transaccionSeleccionada.fecha_pago) }} {{ formatearHora(transaccionSeleccionada.fecha_pago) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted small">Cliente:</span>
                                        <span class="fw-medium text-dark">{{ citaSeleccionadaParaComprobante?.cliente?.nombre || citaSeleccionadaParaComprobante?.mascota?.cliente?.nombre || 'Desconocido' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted small">Paciente:</span>
                                        <span class="fw-medium text-dark">{{ citaSeleccionadaParaComprobante?.mascota?.nombre || 'N/A' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted small">Método de pago:</span>
                                        <span class="fw-medium text-dark">{{ formatearMetodo(transaccionSeleccionada.metodo_pago) }}</span>
                                    </div>
                                    <hr class="border-secondary opacity-25">
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <span class="text-uppercase fw-bold text-muted small">Total Pagado</span>
                                        <span class="fs-4 fw-bold text-success">${{ Math.round(transaccionSeleccionada.monto_pagado).toLocaleString('es-CL') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mt-4">
                                <small class="text-muted">Gracias por confiar en nuestra clínica veterinaria.</small>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 p-4">
                            <!-- Cerrar modal -->
                            <button type="button" class="btn btn-secondary rounded-pill px-4" @click="mostrarModalComprobante = false">Cerrar</button>
                            <!-- Imprimir comprobante -->
                            <button type="button" class="btn btn-primary rounded-pill px-4" @click="imprimirComprobante"><i class="bi bi-printer me-2"></i>Imprimir</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- MODAL: Crear / Editar Cita                 -->
            <!-- ========================================== -->
            <ModalCrud
                :visible="mostrarModal"
                :titulo="modoEdicion ? 'Editar Cita' : 'Nueva Cita'"
                :modo-edicion="modoEdicion"
                :processing="formulario.processing"
                tamanio="lg"
                texto-crear="Crear cita"
                texto-guardar="Guardar cambios"
                @cerrar="cerrarModal"
                @guardar="guardar"
            >
                <!-- Contenedor principal del formulario con altura mínima y diseño de dos columnas -->
                <div class="row g-0" style="min-height: 65vh;">

                                    <!-- Columna izquierda: datos de la cita -->
                                    <div class="col-md-5 p-3 border-end">
                                        <div class="row g-3">
                                            <!-- Si hay un error general, mostrarlo -->
                                            <div v-if="errorGeneral" class="col-12">
                                                <div class="alert alert-danger d-flex align-items-center mb-0 border-0 shadow-sm" role="alert">
                                                    <i class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2"></i>
                                                    <div>{{ errorGeneral }}</div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <label for="titulo" class="form-label fw-semibold text-secondary small text-uppercase">Título</label>
                                                <!-- Almacenamos el valor de "titulo" en "formulario.titulo" -->
                                                <input id="titulo" v-model="formulario.titulo" type="text" class="form-control bg-light border-0 py-2" :class="{ 'is-invalid': formulario.errors.titulo }" required placeholder="Ej: Control general" />
                                                <!-- Si existe el error, lo mostramos -->
                                                <div v-if="formulario.errors.titulo" class="invalid-feedback">{{ formulario.errors.titulo }}</div>
                                            </div>
                                            <div class="col-12">
                                                <label for="descripcion" class="form-label fw-semibold text-secondary small text-uppercase">Descripción</label>
                                                <!-- Almacenamos el valor de "descripcion" en "formulario.descripcion" -->
                                                <textarea id="descripcion" v-model="formulario.descripcion" class="form-control bg-light border-0 py-2" :class="{ 'is-invalid': formulario.errors.descripcion }" rows="2" required placeholder="Motivo de la cita..."></textarea>
                                                <!-- Si existe el error, lo mostramos -->
                                                <div v-if="formulario.errors.descripcion" class="invalid-feedback">{{ formulario.errors.descripcion }}</div>
                                            </div>
                                            <!-- Si el usuario es secretario, mostramos el campo de cliente -->
                                            <div v-if="$isSecretaria()" class="col-12 position-relative">
                                                <label class="form-label fw-semibold text-secondary small text-uppercase mb-1">Cliente</label>
                                                <div class="dropdown">
                                                    <div 
                                                        class="form-select bg-light border-0 py-2 d-flex justify-content-between align-items-center"
                                                        :class="{ 'is-invalid': formulario.errors.cliente_id }"
                                                        @click="mostrarDropdownCliente = !mostrarDropdownCliente"
                                                        style="cursor: pointer;"
                                                    >
                                                        <span>{{ nombreClienteSeleccionado || 'Selecciona un cliente' }}</span>
                                                    </div>
                                                    <!-- Si existe el error, lo mostramos -->
                                                    <div v-if="formulario.errors.cliente_id" class="invalid-feedback d-block">{{ formulario.errors.cliente_id }}</div>
                                                    <!-- Backdrop transparente para cerrar al hacer click fuera -->
                                                    <div 
                                                        v-if="mostrarDropdownCliente" 
                                                        class="position-fixed top-0 start-0 w-100 h-100" 
                                                        style="z-index: 1040; background: transparent;" 
                                                        @click.stop="mostrarDropdownCliente = false"
                                                    ></div>
                                                    <!-- Controlamos el despegable-->
                                                    <div 
                                                        v-if="mostrarDropdownCliente" 
                                                        class="dropdown-menu show w-100 p-2 shadow border-0 mt-1 bg-white" 
                                                        style="max-height: 350px; overflow-y: auto; z-index: 1050; display: block;"
                                                    >
                                                        <!-- Busqueda de clientes-->
                                                        <input 
                                                            type="text" 
                                                            class="form-control form-control-sm mb-2" 
                                                            v-model="busquedaCliente" 
                                                            placeholder="Escribe para buscar cliente..."
                                                            @click.stop
                                                        />
                                                        <ul class="list-unstyled mb-0">
                                                            <!-- Bucle para obtener los clientes segun la busqueda-->
                                                            <li v-for="cliente in clientesFiltradosPorBusqueda" :key="cliente.id">
                                                                 <!-- Si existe el error, lo mostramos -->
                                                                 <button 
                                                                    type="button" 
                                                                    class="dropdown-item py-2 rounded text-start"
                                                                    :class="{ 'active bg-primary text-white': formulario.cliente_id === cliente.id }"
                                                                    @click="seleccionarClienteDropdown(cliente)"
                                                                 >
                                                                     {{ cliente.nombre }} ({{ cliente.email }})
                                                                 </button>
                                                            </li>
                                                            <!-- Mostramos este mensaje si no se encontraron resultados -->
                                                            <li v-if="clientesFiltradosPorBusqueda.length === 0" class="text-muted small p-2 text-center">
                                                                No se encontraron resultados
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <label for="mascota_id" class="form-label fw-semibold text-secondary small text-uppercase">Mascota</label>
                                                <!-- Almacenamos la mascota seleccionada en "formulario.mascota_id". Si es secretaria y no hay cliente seleccionado, se deshabilita el campo -->
                                                <select id="mascota_id" v-model="formulario.mascota_id" class="form-select bg-light border-0 py-2" :class="{ 'is-invalid': formulario.errors.mascota_id }" required :disabled="$isSecretaria() && !formulario.cliente_id">
                                                    <!-- Si es secretaria y no hay cliente seleccionado, se deshabilita el campo -->
                                                    <option value="" disabled>{{ ($isSecretaria() && !formulario.cliente_id) ? 'Debe seleccionar un cliente primero' : 'Selecciona una mascota' }}</option>
                                                    <!-- Bucle para obtener las mascotas segun el cliente -->
                                                    <option v-for="mascota in mascotasFiltradas" :key="mascota.id" :value="mascota.id">
                                                        {{ mascota.nombre }} {{ mascota.sexo ? `(${mascota.sexo})` : '' }}
                                                    </option>
                                                </select>
                                                <!-- Si existe el error, lo mostramos -->
                                                <div v-if="formulario.errors.mascota_id" class="invalid-feedback">{{ formulario.errors.mascota_id }}</div>
                                            </div>
                                            <!-- Si existe la mascota seleccionada, mostramos el campo de prestacion o servicio -->
                                            <div v-if="formulario.mascota_id" class="col-12 position-relative">
                                                <label class="form-label fw-semibold text-secondary small text-uppercase mb-1">Prestación o Servicio</label>
                                                <!-- Desplegable para seleccionar la prestacion o servicio -->
                                                <div class="dropdown">
                                                    <div 
                                                        class="form-select bg-light border-0 py-2 d-flex justify-content-between align-items-center"
                                                        :class="{ 'is-invalid': formulario.errors.prestacion_id }"
                                                        @click="mostrarDropdownPrestacion = !mostrarDropdownPrestacion"
                                                        style="cursor: pointer;"
                                                    >
                                                        <span>{{ nombrePrestacionSeleccionada || 'Selecciona una prestación o servicio' }}</span>
                                                    </div>
                                                    <!-- Si existe el error, lo mostramos -->
                                                    <div v-if="formulario.errors.prestacion_id" class="invalid-feedback d-block">{{ formulario.errors.prestacion_id }}</div>
                                                    
                                                    <!-- Backdrop transparente para cerrar al hacer click fuera -->
                                                    <div 
                                                        v-if="mostrarDropdownPrestacion" 
                                                        class="position-fixed top-0 start-0 w-100 h-100" 
                                                        style="z-index: 1040; background: transparent;" 
                                                        @click.stop="mostrarDropdownPrestacion = false"
                                                    ></div>
                                                    <!-- Si se muestra el despegable, obtenemos las prestaciones disponibles -->
                                                    <div 
                                                        v-if="mostrarDropdownPrestacion" 
                                                        class="dropdown-menu show w-100 p-2 shadow border-0 mt-1 bg-white" 
                                                        style="max-height: 350px; overflow-y: auto; z-index: 1050; display: block;"
                                                    >
                                                        <!-- DIRECTIVA (v-model): Enlace de datos bidireccional con "busquedaPrestacion" -->
                                                        <input 
                                                            type="text" 
                                                            class="form-control form-control-sm mb-2" 
                                                            v-model="busquedaPrestacion" 
                                                            placeholder="Escribe para buscar..."
                                                            @click.stop
                                                        />
                                                        <ul class="list-unstyled mb-0">
                                                            <!-- Iteramos las prestaciones disponibles para mostrar el desplegable-->
                                                            <li v-for="prestacion in prestacionesFiltradasPorBusqueda" :key="prestacion.id">
                                                                <!-- Al hacer clic en la prestacion, se selecciona -->
                                                                <button 
                                                                    type="button" 
                                                                    class="dropdown-item py-2 rounded text-start"
                                                                    :class="{ 'active bg-primary text-white': formulario.prestacion_id === prestacion.id }"
                                                                    @click="seleccionarPrestacionDropdown(prestacion)"
                                                                >
                                                                    {{ prestacion.nombre }} ({{ prestacion.sucursal?.nombre }})
                                                                </button>
                                                            </li>
                                                            <!-- Si no se encontraron resultados, se muestra un mensaje -->
                                                            <li v-if="prestacionesFiltradasPorBusqueda.length === 0" class="text-muted small p-2 text-center">
                                                                No se encontraron resultados
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Si se selecciona una prestacion, se muestra la sucursal asociada a la prestacion (deshabilitado) -->
                                            <div v-if="formulario.prestacion_id" class="col-12">
                                                <label class="form-label fw-semibold text-secondary small text-uppercase">Sucursal</label>
                                                <!-- Almacenamos la sucursal asociada a la prestacion seleccionada -->
                                                <p class="form-control-plaintext bg-light px-3 py-2 rounded">
                                                    {{ sucursal.nombre}}
                                                </p>
                                                <!-- Si existe el error, lo mostramos -->
                                                <div v-if="formulario.errors.sucursal_id" class="invalid-feedback">{{ formulario.errors.sucursal_id }}</div>
                                            </div>

                                            <!-- Si hay una sucursal mostramos los veterinarios aptos para la prestacion seleccionada y de esa sucursal -->
                                            <template v-if="formulario.sucursal_id">
                                                <div class="col-12">
                                                    <label class="form-label fw-semibold text-secondary small text-uppercase">Veterinario (Aptos)</label>
                                                    <!-- Almacenamos el veterinario seleccionado -->
                                                    <select id="veterinario_id" v-model="formulario.veterinario_id" class="form-select bg-light border-0 py-2" :class="{ 'is-invalid': formulario.errors.veterinario_id }">
                                                        <option value="">Cualquier veterinario (opcional)</option>
                                                        <!-- Iteramos sobre los veterinarios aptos para la prestacion seleccionada y de esa sucursal-->
                                                        <option v-for="vet in veterinariosFiltrados" :key="vet.id" :value="vet.id">
                                                            {{ vet.usuario.name }}
                                                        </option>
                                                    </select>
                                                    <!-- Si existe el error, lo mostramos -->
                                                    <div v-if="formulario.errors.veterinario_id" class="invalid-feedback">{{ formulario.errors.veterinario_id }}</div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Columna derecha: fecha y horarios -->
                                    <div class="col-md-7 p-3 bg-light bg-opacity-50">
                                        <!-- Mostramos los campos de fecha y horarios si hay una sucursal seleccionada -->
                                        <template v-if="formulario.sucursal_id">
                                            <div class="mb-3">
                                                <label for="fecha_seleccionada" class="form-label fw-semibold text-secondary small text-uppercase">Fecha</label>
                                                <!-- Almacenamos la fecha seleccionada -->
                                                <input
                                                    id="fecha_seleccionada"
                                                    type="date"
                                                    v-model="formulario.fecha_seleccionada"
                                                    class="form-control bg-white border-0 py-2 shadow-sm"
                                                    :class="{ 'is-invalid': formulario.errors.fecha_hora }"
                                                    :min="hoy"
                                                    @change="cargarHorarios"
                                                />
                                                <!-- Si existe el error, lo mostramos -->
                                                <div v-if="formulario.errors.fecha_hora" class="invalid-feedback">{{ formulario.errors.fecha_hora }}</div>
                                            </div>

                                            <!-- Si esta cargando horarios, lo mostramos -->
                                            <div v-if="cargandoHorarios" class="text-center py-4">
                                                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                                <span class="ms-2 text-muted small">Consultando disponibilidad...</span>
                                            </div>

                                            <template v-else-if="formulario.fecha_seleccionada">
                                                <!-- CASO 1: Veterinario seleccionado -->
                                                <template v-if="formulario.veterinario_id">
                                                    <!-- Horarios normales -->
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold text-secondary small text-uppercase">Horarios disponibles</label>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            <!-- Iteramos sobre los horarios disponibles-->
                                                            <template v-for="slot in horariosNormales" :key="slot.hora">
                                                                <!-- Mostramos los horarios disponibles -->
                                                                <!-- Al hacer clic en el horario, se selecciona -->
                                                                <button
                                                                    v-if="slot.disponible"
                                                                    type="button" 
                                                                    :class="[
                                                                        'btn btn-sm rounded-pill px-3',
                                                                        formulario.fecha_hora === slot.fecha_hora
                                                                            ? 'btn-primary'
                                                                            : 'btn-outline-primary'
                                                                    ]"
                                                                    @click="seleccionarHorario(slot)"
                                                                >
                                                                    {{ slot.hora }}
                                                                </button>
                                                            </template>
                                                            <!-- Si no hay horarios disponibles, lo mostramos -->
                                                            <div v-if="horariosNormales.filter(s => s.disponible).length === 0" class="text-muted small">
                                                                No hay horarios normales disponibles.
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Horarios de urgencia -->
                                                    <div>
                                                        <label class="form-label fw-semibold text-warning small text-uppercase">
                                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                                            Urgencia (fuera de horario)
                                                        </label>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            <!-- Iteramos sobre los horarios de urgencia-->
                                                            <template v-for="slot in horariosUrgencia" :key="slot.hora">
                                                                <!-- Mostramos los horarios de urgencia -->
                                                                <!-- Al hacer clic en el horario, se selecciona -->
                                                                <button
                                                                    v-if="slot.disponible"
                                                                    type="button"
                                                                    :class="[
                                                                        'btn btn-sm rounded-pill px-3',
                                                                        formulario.fecha_hora === slot.fecha_hora
                                                                            ? 'btn-warning text-dark'
                                                                            : 'btn-outline-warning'
                                                                    ]"
                                                                    @click="seleccionarHorario(slot)"
                                                                >
                                                                    {{ slot.hora }}
                                                                </button>
                                                            </template>
                                                            <!-- Si no hay horarios de urgencia disponibles, lo mostramos -->
                                                            <div v-if="horariosUrgencia.filter(s => s.disponible).length === 0" class="text-muted small">
                                                                No hay horarios de urgencia disponibles.
                                                            </div>
                                                        </div>
                                                        <small class="text-muted mt-2 d-block">Las atenciones fuera de horario tienen un costo adicional.</small>
                                                    </div>
                                                </template>

                                                <!-- CASO 2: Veterinario no seleccionado (Cualquier veterinario) -> Acordeón -->
                                                <template v-else>
                                                    <div class="mb-2 text-secondary small fw-semibold text-uppercase">Selecciona un veterinario y horario</div>

                                                    <div class="accordion border border-light rounded-3 overflow-hidden shadow-sm" id="vetSchedulesAccordion">
                                                        <!-- Iteramos sobre los veterinarios filtrados -->
                                                        <div v-for="vet in veterinariosFiltrados" :key="vet.id" class="accordion-item border-0 border-bottom border-light">
                                                            <h4 class="accordion-header mb-0">
                                                                <!-- Al hacer clic en el veterinario, se expande el acordeón -->
                                                                <button 
                                                                    class="accordion-button d-flex align-items-center justify-content-between w-100 py-3 px-4 text-start border-0 fw-semibold"
                                                                    :class="vetAcordeonAbiertoId === vet.id ? 'bg-primary bg-opacity-10 text-primary' : 'bg-white text-dark'"
                                                                    type="button" 
                                                                    @click="toggleAcordeon(vet.id)"
                                                                >
                                                                    <span class="d-flex align-items-center gap-2">
                                                                        <i class="bi bi-person-badge-fill" :class="vetAcordeonAbiertoId === vet.id ? 'text-primary' : 'text-secondary'"></i>
                                                                        {{ vet.usuario.name }}
                                                                    </span>
                                                                    <span class="badge rounded-pill bg-light text-secondary border border-light small px-2 py-1">
                                                                        {{ obtenerCantSlotsDisponibles(vet.id) }} horarios disponibles
                                                                        <i class="bi ms-1" :class="vetAcordeonAbiertoId === vet.id ? 'bi-caret-up-fill' : 'bi-caret-down-fill'"></i>
                                                                    </span>
                                                                </button>
                                                            </h4>
                                                            <!-- Si esta abierto el acordeón, lo mostramos -->
                                                            <div 
                                                                v-if="vetAcordeonAbiertoId === vet.id" 
                                                                class="accordion-body p-4 bg-light bg-opacity-25"
                                                            >
                                                                <!-- Horarios del veterinario -->
                                                                <div v-if="horariosPorVeterinario[vet.id]">
                                                                    <!-- Horarios normales -->
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-semibold text-secondary small text-uppercase">Horarios disponibles</label>
                                                                        <div class="d-flex flex-wrap gap-2">
                                                                            <!-- Iteramos sobre los horarios normales del veterinario -->
                                                                            <template v-for="slot in horariosPorVeterinario[vet.id].normal" :key="slot.hora">
                                                                                <!-- Mostramos los horarios normales -->
                                                                                <!-- Al hacer clic en el horario, se selecciona -->
                                                                                <button
                                                                                    v-if="slot.disponible"
                                                                                    type="button"
                                                                                    :class="[
                                                                                        'btn btn-sm rounded-pill px-3',
                                                                                        formulario.fecha_hora === slot.fecha_hora
                                                                                            ? 'btn-primary'
                                                                                            : 'btn-outline-primary'
                                                                                    ]"
                                                                                    @click="seleccionarHorarioAcordeon(slot, vet.id)"
                                                                                >
                                                                                    {{ slot.hora }}
                                                                                </button>
                                                                            </template>
                                                                            <!-- Si no hay horarios normales disponibles, lo mostramos -->
                                                                            <div v-if="horariosPorVeterinario[vet.id].normal.filter(s => s.disponible).length === 0" class="text-muted small">
                                                                                No hay horarios normales disponibles.
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <!-- Horarios de urgencia -->
                                                                    <div>
                                                                        <label class="form-label fw-semibold text-warning small text-uppercase">
                                                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                                                            Urgencia (fuera de horario)
                                                                        </label>
                                                                        <div class="d-flex flex-wrap gap-2">
                                                                            <!-- Iteramos sobre los horarios de urgencia del veterinario -->
                                                                            <template v-for="slot in horariosPorVeterinario[vet.id].urgencia" :key="slot.hora">
                                                                                <!-- Mostramos los horarios de urgencia -->
                                                                                <!-- Al hacer clic en el horario, se selecciona -->
                                                                                <button
                                                                                    v-if="slot.disponible"
                                                                                    type="button"
                                                                                    :class="[
                                                                                        'btn btn-sm rounded-pill px-3',
                                                                                        formulario.fecha_hora === slot.fecha_hora
                                                                                            ? 'btn-warning text-dark'
                                                                                            : 'btn-outline-warning'
                                                                                    ]"
                                                                                    @click="seleccionarHorarioAcordeon(slot, vet.id)"
                                                                                >
                                                                                    {{ slot.hora }}
                                                                                </button>
                                                                            </template>
                                                                            <!-- Si no hay horarios de urgencia disponibles, lo mostramos -->
                                                                            <div v-if="horariosPorVeterinario[vet.id].urgencia.filter(s => s.disponible).length === 0" class="text-muted small">
                                                                                No hay horarios de urgencia disponibles.
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <!-- Mostrar indicador de carga mientras se obtienen los horarios -->
                                                                <div v-else class="text-center py-3">
                                                                    <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>

                                            </template>

                                            <!-- Placeholder cuando aún no se elige fecha -->
                                            <div v-else class="text-center text-muted py-5">
                                                <i class="bi bi-calendar2-week fs-1 d-block mb-2" style="color: #dee2e6;"></i>
                                                <small>Selecciona una fecha para ver los horarios disponibles</small>
                                            </div>
                                        </template>

                                        <!-- Placeholder cuando aún no se elige sucursal -->
                                        <div v-else class="text-center text-muted py-5">
                                            <i class="bi bi-clock-history fs-1 d-block mb-2" style="color: #dee2e6;"></i>
                                            <small>Selecciona una prestación para ver la disponibilidad</small>
                                        </div>
                                    </div>

                                </div>
            </ModalCrud>
            </div>

            <!-- ========================================== -->
            <!-- MODAL: Confirmar Eliminación                -->
            <!-- ========================================== -->
            <!-- Modal para confirmar la eliminación de una cita -->
            <div v-if="mostrarConfirmacion" class="modal fade show d-block" tabindex="-1">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirmar eliminación</h5>
                            <!-- Al hacer clic en el botón de cerrar, se cierra el modal -->
                            <button type="button" class="btn-close" @click="mostrarConfirmacion = false"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Se muestra el título de la cita a eliminar -->
                            <p>¿Estás seguro de eliminar la cita <strong>{{ citaAEliminar?.titulo }}</strong>?</p>
                            <p class="text-muted small mb-0">Esta acción no se puede deshacer.</p>
                        </div>
                        <div class="modal-footer">
                            <!-- Al hacer clic en el botón de cancelar, se cierra el modal -->
                            <button type="button" class="btn btn-secondary" @click="mostrarConfirmacion = false">
                                Cancelar
                            </button>
                            <!-- Al hacer clic en el botón de eliminar, se elimina la cita -->
                            <button type="button" class="btn btn-danger" :disabled="eliminando" @click="eliminarCita">
                                <!-- Se muestra un indicador de carga mientras se elimina la cita -->
                                <span v-if="eliminando" class="spinner-border spinner-border-sm me-2"></span>
                                Sí, eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        
            <!-- Fondo oscuro para el modal de confirmación -->
            <div v-if="mostrarConfirmacion" class="modal-backdrop fade show"></div>

            <!-- Modal para importar citas consolidadas -->
            <ModalImportarConsolidado
                :visible="mostrarModalImportar"
                @cerrar="mostrarModalImportar = false"
                @importado="obtenerCitas()"
            />
    </AuthenticatedLayout>
</template>

<script>
// ==================================================================================
// LÓGICA DEL COMPONENTE (VUE 3)
// ==================================================================================

import AuthenticatedLayout from '@/Disenos/LayoutAutenticado.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Paginador from '@/Componentes/Paginador.vue';
import BarraFiltros from '@/Componentes/BarraFiltros.vue';
import IndicadorCarga from '@/Componentes/IndicadorCarga.vue';
import EstadoVacio from '@/Componentes/EstadoVacio.vue';
import SinResultados from '@/Componentes/SinResultados.vue';
import ModalCrud from '@/Componentes/ModalCrud.vue';
import ModalImportarConsolidado from '@/Componentes/ModalImportarConsolidado.vue';

// ------------------------------------------------------------------------------
// EXPORT DEFAULT: Definición principal del componente
// ------------------------------------------------------------------------------
export default {
    // Registro de componentes importados
    components: {
        AuthenticatedLayout,
        Head,
        Link,
        Paginador,
        BarraFiltros,
        IndicadorCarga,
        EstadoVacio,
        SinResultados,
        ModalCrud,
        ModalImportarConsolidado,
    },
    // Datos inyectados desde el componente padre o estado
    props: {
        mascotas: {
            type: Array,
            default: () => [],
        },
        veterinarios: {
            type: Array,
            default: () => [],
        },
        sucursales: {
            type: Array,
            default: () => [],
        },
        prestaciones: {
            type: Array,
            default: () => [],
        },
    },
    // Variables locales del componente
    data() {
        return {
            // Inicializacion de variables
            cargando: false,
            busquedaPrestacion: '',
            mostrarDropdownPrestacion: false,
            busquedaCliente: '',
            mostrarDropdownCliente: false,
            mostrarModal: false,
            mostrarModalImportar: false,
            modoEdicion: false,
            citaEditando: null,
            mostrarConfirmacion: false,
            citaAEliminar: null,
            eliminando: false,
            filtroMascota:'',
            citasData: null,
            citas:[],
            filtroCliente:'',
            filtroVeterinario: '',
            filtroTitulo: '',
            filtroEstado: '',
            filtroSucursal: '',
            veterinariosSucursal: [],
            boxesSucursal: [],
            cargandoDetallesSucursal: false,
            errorGeneral: null,
            horariosNormales: [],
            horariosUrgencia: [],
            cargandoHorarios: false,
            mostrarModalComprobante: false,
            horariosPorVeterinario: {},
            vetAcordeonAbiertoId: null,
            evitarResetHorario: false,
            transaccionSeleccionada: null,
            citaSeleccionadaParaComprobante: null,
            // Inicializacion de formulario
            formulario: {
                titulo: '',
                descripcion: '',
                fecha_hora: '',
                fecha_seleccionada: '',
                tipo: 'normal',
                mascota_id: '',
                veterinario_id: '',
                sucursal_id: '',
                box_id: '',
                prestacion_id: '',
                cliente_id: '',
                errors: {},
                processing: false,
            },
        }
    },
    // Variables reactivas que dependen de otras
    computed: {
        // Fecha actual
        hoy() {
            return new Date().toISOString().split('T')[0];
        },
        // Clientes según mascotas
        clientes() {
            // Se crea un mapa para evitar duplicados
            const map = new Map();
            // Recorremos las mascotas
            this.mascotas.forEach(mascota => {
                // Si la mascota tiene un cliente y un usuario
                if (mascota.cliente && mascota.cliente.usuario) {
                    // Agregamos el cliente al mapa
                    map.set(mascota.cliente.id, {
                        id: mascota.cliente.id,
                        nombre: mascota.cliente.usuario.name,
                        email: mascota.cliente.usuario.email
                    });
                }
            });
            // Retornamos el mapa convertido en array y ordenado por nombre
            return Array.from(map.values()).sort((a, b) => a.nombre.localeCompare(b.nombre));
        },
        // Mascotas según cliente
        mascotasFiltradas() {
            // Si es secretaria y tiene cliente seleccionado
            if (this.$isSecretaria()) {
                if (!this.formulario.cliente_id) {
                    // Si no tiene cliente seleccionado, retorna array vacío
                    return [];
                }
                // Filtra las mascotas según el cliente seleccionado
                return this.mascotas.filter(m => m.cliente_id === this.formulario.cliente_id);
            }
            // Si no es secretaria, retorna todas las mascotas
            return this.mascotas;
        },
        // Sucursales según prestación
        sucursalesFiltradas() {
            // Si no tiene prestación seleccionada, retorna array vacío
            if (!this.formulario.prestacion_id) return [];
            // Busca la prestación seleccionada
            const prestacion = this.prestaciones.find(p => p.id === this.formulario.prestacion_id);
            // Si no encuentra la prestación, retorna array vacío
            if (!prestacion) return [];
            // Filtra las sucursales según la prestación seleccionada
            return this.sucursales.filter(s => s.id === prestacion.sucursal_id);
        },
        // Veterinarios según sucursal y prestación
        veterinariosFiltrados() {
            // Si no tiene sucursal o prestación seleccionada, retorna array vacío
            if (!this.formulario.sucursal_id || !this.formulario.prestacion_id) return [];
            // Busca la sucursal seleccionada
            const sucursal = this.sucursales.find(s => s.id === this.formulario.sucursal_id);
            // Si no encuentra la sucursal, retorna array vacío
            if (!sucursal) return [];
            // Busca la prestación seleccionada
            const prestacion = this.prestaciones.find(p => p.id === this.formulario.prestacion_id);
            // Filtra los veterinarios según la prestación seleccionada
            return sucursal.veterinarios.filter(vet => {
                // Si no tiene especialidad, retorna true
                if (!prestacion.especialidad_id) return true;
                // Si tiene especialidad, retorna true si es igual a la especialidad de la prestación
                return vet.especialidad_id === prestacion.especialidad_id;
            });
        },
        // Boxes según sucursal y prestación
        boxesFiltrados() {
            // Si no tiene sucursal, retorna array vacío
            if (!this.formulario.sucursal_id) return [];
            // Busca la sucursal seleccionada
            const sucursal = this.sucursales.find(s => s.id === this.formulario.sucursal_id);
            if (!sucursal) return [];
            // Busca la prestación seleccionada
            const prestacion = this.prestaciones.find(p => p.id === this.formulario.prestacion_id);
            // Obtiene la categoría de la prestación
            const catPrestId = prestacion?.categoria_prestacion_id ?? null;
            // Filtra los boxes según la categoría de la prestación
            return sucursal.boxes.filter(box => {
                // Box sin restricción: acepta cualquier tipo de prestación
                if (!box.categoria_prestacion_id) return true;
                // Box con restricción: solo coincide si la categoría es la misma
                return box.categoria_prestacion_id === catPrestId;
            });
        },
        // Verifica si hay filtros activos
        hayFiltrosActivos() {   
            return !!(
                this.filtroMascota ||
                this.filtroVeterinario ||
                this.filtroTitulo ||
                this.filtroEstado ||
                this.filtroSucursal
            );
        },
        // Verifica si la lista está vacía
        listaVacia() {
            return this.citas.length === 0 && !this.hayFiltrosActivos;
        },
        // Verifica si no hay resultados de filtro
        sinResultadosFiltro() {
            return this.citas.length === 0 && this.hayFiltrosActivos;
        },
        // Prestaciones filtradas por búsqueda
        prestacionesFiltradasPorBusqueda() {
            // Si no hay búsqueda, retorna todas las prestaciones
            if (!this.busquedaPrestacion) {
                return this.prestaciones;
            }
            // Convierte la búsqueda a minúsculas
            const term = this.busquedaPrestacion.toLowerCase();
            // Filtra las prestaciones por búsqueda
            return this.prestaciones.filter(p => {
                if (p.id === this.formulario.prestacion_id) return true;
                return p.nombre.toLowerCase().includes(term) || (p.sucursal?.nombre && p.sucursal.nombre.toLowerCase().includes(term));
            });
        },
        // Nombre de la prestación seleccionada
        nombrePrestacionSeleccionada() {
            // Si no tiene prestación seleccionada, retorna cadena vacía
            if (!this.formulario.prestacion_id) return '';
            // Busca la prestación seleccionada
            const prestacion = this.prestaciones.find(p => p.id === this.formulario.prestacion_id);
            // Retorna el nombre de la prestación y su sucursal
            return prestacion ? `${prestacion.nombre} (${prestacion.sucursal?.nombre || ''})` : '';
        },
        // Clientes filtrados por búsqueda
        clientesFiltradosPorBusqueda() {
            // Si no hay búsqueda, retorna todos los clientes
            if (!this.busquedaCliente) {
                return this.clientes;
            }
            // Convierte la búsqueda a minúsculas
            const term = this.busquedaCliente.toLowerCase();
            // Filtra los clientes por búsqueda
            return this.clientes.filter(c => {
                if (c.id === this.formulario.cliente_id) return true;
                return c.nombre.toLowerCase().includes(term) || (c.email && c.email.toLowerCase().includes(term));
            });
        },
        // Nombre del cliente seleccionado
        nombreClienteSeleccionado() {
            // Si no tiene cliente seleccionado, retorna cadena vacía
            if (!this.formulario.cliente_id) return '';
            // Busca el cliente seleccionado
            const cliente = this.clientes.find(c => c.id === this.formulario.cliente_id);
            // Retorna el nombre del cliente y su email
            return cliente ? `${cliente.nombre} (${cliente.email})` : '';
        },
    },
    // OBSERVADORES (WATCH): Reaccionan a cambios en propiedades o variables
    watch: {
        // Al cambiar la prestación seleccionada actualizamos el box y el veterinario
        'formulario.prestacion_id'(newVal, oldVal) {
            // Si la prestación cambió (no es la primera vez) y no estamos editando,
            if (newVal) {
                // Busca la prestación seleccionada
                const prestacion = this.prestaciones.find(p => p.id === newVal);
                // Si la prestación tiene una sucursal diferente a la seleccionada, la actualiza
                if (prestacion && this.formulario.sucursal_id !== prestacion.sucursal_id) {
                    this.formulario.sucursal_id = prestacion.sucursal_id;
                }
                // Si la prestación cambió (no es la primera vez) y no estamos editando
                if (oldVal && newVal !== oldVal && !this.modoEdicion) {
                    this.formulario.box_id = '';
                    this.formulario.veterinario_id = '';
                    this.formulario.fecha_seleccionada = '';
                    this.formulario.fecha_hora = '';
                }

            } else {
                this.formulario.sucursal_id = '';
                this.formulario.box_id = '';
                this.formulario.veterinario_id = '';
            }
        },
        // Al cambiar la sucursal seleccionada actualizamos el veterinario y el box
        'formulario.sucursal_id'(newVal, oldVal) {
            if (oldVal && newVal !== oldVal && !this.modoEdicion) {
                this.formulario.veterinario_id = '';
                this.formulario.box_id = '';
            }
        },
        // Al cambiar el veterinario seleccionado actualizamos el box
        'formulario.veterinario_id'(newVal, oldVal) { 
            if (oldVal && newVal !== oldVal && !this.modoEdicion && !this.evitarResetHorario) {
                this.formulario.fecha_seleccionada = '';
                this.formulario.fecha_hora = '';
            }
            this.cargarHorarios(); 
        },
    },
    // MÉTODOS (METHODS): Bloque de funciones y eventos
    methods: {
        // Redirige a la página de detalle de la cita
        irADetalle(id) {
            router.visit(route('citas.detalle', id));
        },
        // Cambia el filtro de sucursal
        cambiarFiltroSucursal() {
            // Si hay filtro de sucursal y veterinario y no coinciden, limpia el filtro de veterinario
            if (this.filtroSucursal && this.filtroVeterinario) {
                const vet = this.veterinarios.find(v => v.id === this.filtroVeterinario);
                if (vet && Number(vet.sucursal_id) !== Number(this.filtroSucursal)) {
                    this.filtroVeterinario = '';
                }
            }
            this.obtenerCitas();
        },
        // Selecciona una prestación del dropdown
        seleccionarPrestacionDropdown(prestacion) {
            this.formulario.prestacion_id = prestacion.id;
            this.mostrarDropdownPrestacion = false;
            this.busquedaPrestacion = '';
        },
        // Selecciona un cliente del dropdown
        seleccionarClienteDropdown(cliente) {
            this.formulario.cliente_id = cliente.id;
            this.formulario.mascota_id = '';
            this.mostrarDropdownCliente = false;
            this.busquedaCliente = '';
        },
        // Abre el modal de creación de citas vacio
        abrirModalCrear() {
            this.modoEdicion = false;
            this.citaEditando = null;
            this.errorGeneral = null;
            this.formulario.titulo = '';
            this.formulario.descripcion = '';
            this.formulario.fecha_hora = '';
            this.formulario.fecha_seleccionada = '';
            this.formulario.tipo = 'normal';
            this.formulario.mascota_id = '';
            this.formulario.prestacion_id = '';
            this.formulario.sucursal_id = '';
            this.formulario.veterinario_id = '';
            this.formulario.box_id = '';
            this.formulario.cliente_id = '';
            this.busquedaPrestacion = '';
            this.mostrarDropdownPrestacion = false;
            this.busquedaCliente = '';
            this.mostrarDropdownCliente = false;
            this.formulario.errors = {};
            this.horariosNormales = [];
            this.horariosUrgencia = [];
            this.horariosPorVeterinario = {};
            this.vetAcordeonAbiertoId = null;
            this.evitarResetHorario = false;
            this.mostrarModal = true;
        },
        // Retorna los datos del formulario
        datosFormulario() {
            return {
                titulo: this.formulario.titulo,
                descripcion: this.formulario.descripcion,
                fecha_hora: this.formulario.fecha_hora,
                tipo: this.formulario.tipo,
                mascota_id: this.formulario.mascota_id,
                prestacion_id: this.formulario.prestacion_id,
                veterinario_id: this.formulario.veterinario_id,
                box_id: this.formulario.box_id,
            };
        },
        // Abre el modal de edición de citas llenandolo con los datos de la cita
        abrirModalEditar(cita) {
            this.evitarResetHorario = true;
            this.modoEdicion = true;
            this.citaEditando = cita;
            this.errorGeneral = null;
            this.formulario.titulo = cita.titulo;
            this.formulario.descripcion = cita.descripcion;
            this.formulario.fecha_hora = cita.fecha_hora;
            // Seteamos la fecha seleccionada para que se muestre el horario en el acordeon
            if (cita.fecha_hora) {
                this.formulario.fecha_seleccionada = cita.fecha_hora.substring(0, 10);
            }
            this.formulario.mascota_id = cita.mascota_id;
            // Obtenemos el ID del cliente mapeado en el accessor o mascota
            this.formulario.cliente_id = cita.cliente?.id || cita.mascota?.cliente_id || '';
            this.formulario.prestacion_id = cita.prestacion_id || '';
            this.busquedaPrestacion = '';
            this.mostrarDropdownPrestacion = false;
            this.busquedaCliente = '';
            this.mostrarDropdownCliente = false;
            
            // Obtenemos la prestacion para obtener la sucursal
            const prestacion = this.prestaciones.find(p => p.id === cita.prestacion_id);
            const sucursalId = prestacion ? prestacion.sucursal_id : (cita.box?.sucursal_id || '');
            
            this.formulario.sucursal_id = sucursalId;
            this.formulario.veterinario_id = cita.veterinario_id || '';
            this.formulario.box_id = cita.box_id || '';

            // Cargamos los horarios una vez que el modal está abierto
            this.$nextTick(() => {
                this.cargarHorarios();
                this.$nextTick(() => {
                    this.evitarResetHorario = false;
                });
            });

            // Limpiamos los horarios
            this.horariosPorVeterinario = {};
            this.vetAcordeonAbiertoId = null;
            this.evitarResetHorario = false;
            this.formulario.errors = {};
            this.mostrarModal = true;

            // Cargamos automáticamente la lista de mascotas del cliente asociado al editar
            if (this.formulario.cliente_id && typeof this.obtenerMascotasCliente === 'function') {
                this.obtenerMascotasCliente(this.formulario.cliente_id);
            }
        },
        // Cierra el modal de edición de citas y limpia el formulario
        cerrarModal() {
            this.mostrarModal=false;
            this.modoEdicion=false;
            this.citaEditando=null;
            this.errorGeneral=null;
            this.formulario.titulo='';
            this.formulario.descripcion='';
            this.formulario.fecha_hora='';
            this.formulario.fecha_seleccionada='';
            this.formulario.tipo='normal';
            this.formulario.prestacion_id='';
            this.formulario.sucursal_id='';
            this.formulario.veterinario_id='';
            this.formulario.box_id='';
            this.formulario.mascota_id='';
            this.formulario.cliente_id='';
            this.busquedaPrestacion='';
            this.mostrarDropdownPrestacion=false;
            this.busquedaCliente='';
            this.mostrarDropdownCliente=false;
            this.formulario.errors={};
            this.horariosNormales=[];
            this.horariosUrgencia=[];
            this.horariosPorVeterinario = {};
            this.vetAcordeonAbiertoId = null;
            this.evitarResetHorario = false;
        },
        // Obtiene las citas de la API y actualiza la tabla
        obtenerCitas(url = '/citas'){
            // Si no hay URL, no hacemos nada
            if (!url) return;
            this.cargando=true;
            // Hacemos la petición GET con los filtros
            axios.get(url,{params:{
                mascota:this.filtroMascota,
                veterinario_id:this.filtroVeterinario,
                titulo:this.filtroTitulo,
                estado:this.filtroEstado,
                sucursal_id:this.filtroSucursal,
                cliente:this.filtroCliente
            }})
            // Si la petición es exitosa
                .then(response => {
                    // Si la respuesta contiene data, actualizamos las citas y la data
                    if (response.data.citas.data) {
                        this.citasData = response.data.citas;
                        this.citas = response.data.citas.data;
                    } else {
                        // En caso de que se pase data sin paginar por algún motivo
                        this.citasData = null;
                        this.citas = response.data.citas;
                    }
                })
                // En caso de error, mostramos un mensaje de error
                .catch(error => {
                    console.error('Error al obtener las citas:', error);
                })
                // Al finalizar la petición, ocultamos el cargando
                .finally(() => {
                    this.cargando=false;
                })
        },
        // Limpia los filtros y obtiene las citas
        limpiarFiltros(){
            this.filtroMascota='';
            this.filtroCliente='';
            this.filtroVeterinario='';
            this.filtroTitulo='';
            this.filtroEstado='';
            this.filtroSucursal='';
            this.obtenerCitas();
        },
        // Guarda la cita (crea o actualiza)
        guardar() {
            this.formulario.processing=true;
            this.formulario.errors={};
            this.errorGeneral = null;
            if(this.modoEdicion){
                this.actualizarCita();
            }else{
                this.crearCita();
            }
        },
        // Actualiza una cita existente
        actualizarCita(){
            axios.put(`/api/citas/${this.citaEditando.id}`, { ...this.datosFormulario() })
                .then(() => { this.cerrarModal(); this.obtenerCitas(); })
                .catch((error) => { 
                    if (error.response?.status === 422) {
                        this.formulario.errors = error.response.data.errors;
                    } else if (error.response?.status === 409) {
                        this.errorGeneral = error.response.data.error;
                    }
                })
                .finally(() => { this.formulario.processing = false });
        },
        // Crea una nueva cita
        crearCita(){
            axios.post('/api/citas', { ...this.datosFormulario() })
                .then(() => { this.cerrarModal(); this.obtenerCitas(); })
                .catch((error) => { 
                    // Si hay errores 422, los mostramos
                    if (error.response?.status === 422) {
                        this.formulario.errors = error.response.data.errors;
                    } else if (error.response?.status === 409) {
                        this.errorGeneral = error.response.data.error;
                    }
                })
                .finally(() => { this.formulario.processing = false });
        },
        // Carga los horarios disponibles para la fecha seleccionada
        cargarHorarios() {
            // Si no hay fecha seleccionada, no hacemos nada
            if (!this.formulario.fecha_seleccionada) return;

            this.cargandoHorarios = true;
            // Si no estamos evitando el reset, limpiamos la fecha_hora y el tipo
            if (!this.evitarResetHorario) {
                this.formulario.fecha_hora = '';
                this.formulario.tipo = 'normal';
            }

            // Si hay veterinario seleccionado, obtenemos los horarios
            if (this.formulario.veterinario_id) {
                // Obtenemos los horarios de la API
                axios.get('/api/citas/horarios-disponibles', {
                    params: {
                        fecha:           this.formulario.fecha_seleccionada,
                        veterinario_id:  this.formulario.veterinario_id,
                    }
                })
                // Si la petición es exitosa
                .then(r => {
                    // Obtenemos los horarios
                    this.horariosNormales  = r.data.normal;
                    this.horariosUrgencia  = r.data.urgencia;
                }).catch(error => {
                    // En caso de error, mostramos un mensaje de error
                    console.error('Error al cargar horarios:', error);
                }).finally(() => {
                    // Ocultamos el cargando
                    this.cargandoHorarios = false;
                });
            } else {
                // Si no hay veterinario seleccionado, mostramos los horarios de todos los veterinarios
                this.horariosPorVeterinario = {};
                
                // Mapeamos los veterinarios filtrados y obtenemos los horarios de cada uno
                const promesas = this.veterinariosFiltrados.map(vet => {
                    // Obtenemos los horarios de cada veterinario
                    return axios.get('/api/citas/horarios-disponibles', {
                        params: {
                            fecha:           this.formulario.fecha_seleccionada,
                            veterinario_id:  vet.id,
                        }
                    })
                    // Si la petición es exitosa
                    .then(r => {
                        this.horariosPorVeterinario[vet.id] = {
                            veterinario: vet,
                            normal: r.data.normal,
                            urgencia: r.data.urgencia
                        };
                    }).catch(error => {
                        console.error(`Error al cargar horarios para veterinario ${vet.id}:`, error);
                    });
                });

                Promise.all(promesas).finally(() => {
                    // Ocultamos el cargando
                    this.cargandoHorarios = false;
                    // Abrir automáticamente el primer elemento del acordeón si ninguno está abierto
                    if (this.veterinariosFiltrados.length > 0 && !this.vetAcordeonAbiertoId) {
                        this.vetAcordeonAbiertoId = this.veterinariosFiltrados[0].id;
                    }
                });
            }
        },
        // Alterna el acordeón del veterinario
        // Alterna el acordeón del veterinario
        toggleAcordeon(vetId) {
            this.vetAcordeonAbiertoId = this.vetAcordeonAbiertoId === vetId ? null : vetId;
        },
        // Obtiene la cantidad de slots disponibles para un veterinario
        obtenerCantSlotsDisponibles(vetId) {
            // Obtiene los horarios del veterinario
            const data = this.horariosPorVeterinario[vetId];
            if (!data) return 0;
            // Cuenta los horarios disponibles
            const normales = data.normal?.filter(s => s.disponible).length || 0;
            const urgencias = data.urgencia?.filter(s => s.disponible).length || 0;
            // Retorna la cantidad de horarios disponibles
            return normales + urgencias;
        },
        // Selecciona un horario del acordeón
        seleccionarHorarioAcordeon(slot, vetId) {   
            // Previene el reseteo del horario
            this.evitarResetHorario = true;
            // Asigna el horario
            this.formulario.veterinario_id = vetId;
            this.formulario.fecha_hora = slot.fecha_hora;
            this.formulario.tipo       = slot.tipo;
            // Resetea el flag después de un pequeño delay para evitar conflictos
            setTimeout(() => {
                this.evitarResetHorario = false;
            }, 100);
        },  
        // Selecciona un horario
        seleccionarHorario(slot) {
            this.formulario.fecha_hora = slot.fecha_hora;
            this.formulario.tipo       = slot.tipo;
        },
        // Confirma la cancelación de una cita
        confirmarCancelar(cita) {
            // Asigna la cita a eliminar
            this.citaAEliminar = cita;
            // Confirma la cancelación
            this.$confirmar(
                '¿Cancelar cita?',
                `Se cancelará la cita "${cita.titulo}" de ${cita.mascota?.nombre || 'la mascota'}. El registro se conservará en el historial con estado Cancelada.`
            ).then((resultado) => {
                // Si la cancelación es confirmada, cancela la cita
                if (resultado.isConfirmed) return this.cancelarCita();
            })
        },
        // Cancela una cita
        cancelarCita() {
            // Llama a la API para cancelar la cita
            axios.patch(`/api/citas/${this.citaAEliminar.id}/cancelar`)
            // Si la petición es exitosa
            .then(() => { this.obtenerCitas(); })
            // Si hay errores
            .catch((error) => { console.error('Error al cancelar la cita:', error); })
            // Finalmente, oculta el cargando
            .finally(() => { this.formulario.processing = false; });
        },
        // Muestra el comprobante
        verComprobante(transaccion, cita) {
            this.transaccionSeleccionada = transaccion;
            this.citaSeleccionadaParaComprobante = cita;
            this.mostrarModalComprobante = true;
        },
        // Imprime el comprobante
        imprimirComprobante() {
            if (this.transaccionSeleccionada) {
                const urlPdf = route('transacciones.comprobante', this.transaccionSeleccionada.id);
                window.open(urlPdf, '_blank');
            }
        },  
        // Formatea la fecha
        formatearFecha(fechaStr) {
            if (!fechaStr) return 'N/A';
            const f = new Date(fechaStr);
            return f.toLocaleDateString('es-CL', { day: '2-digit', month: 'long', year: 'numeric' });
        },
        // Formatea la hora
        formatearHora(fechaStr) {
            if (!fechaStr) return '';
            const f = new Date(fechaStr);
            return f.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' });
        },
        // Formatea el método de pago
        formatearMetodo(metodo) {
            if (!metodo) return 'No registrado';
            return metodo.charAt(0).toUpperCase() + metodo.slice(1);
        }
    },
    // MONTAJE: Se ejecuta al cargar el componente
    mounted() {
        // Obtenemos el estado del filtro de la URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('estado')) {
            this.filtroEstado = urlParams.get('estado');
        }
        // Obtenemos las citas
        this.obtenerCitas();
    },

}
</script>

<style scoped>
@media print {
    body * {
        visibility: hidden;
    }
    #comprobante-imprimir, #comprobante-imprimir * {
        visibility: visible;
    }
    #comprobante-imprimir {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        background-color: white;
    }
}
.row-hover:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.03) !important;
    transition: background-color 0.2s ease-in-out;
}
</style>
3