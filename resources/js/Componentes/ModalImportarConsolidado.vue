<template>
    <!-- ================================================================================== -->
    <!-- COMPONENTE: ModalImportarConsolidado -->
    <!-- ================================================================================== -->


    <!--Renderizado condicional basado en "visible" -->
    <div v-if="visible" class="modal show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); z-index: 1055;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary bg-opacity-10 border-0">
                    <h5 class="modal-title text-primary fw-bold">
                        <i class="bi bi-layer-forward me-2"></i>Importación Consolidada
                    </h5>
                    <!-- Dispara la acción "cerrar" -->
                    <button type="button" class="btn-close" @click="cerrar" :disabled="cargando"></button>
                </div>
                
                <div class="modal-body p-4">
                    <!-- Paso 1: Subir Archivo -->
                    <!--Renderizado condicional basado en "step === 1" -->
                    <div v-if="step === 1">
                        <div class="alert alert-info small mb-3">
                            <i class="bi bi-info-circle me-1"></i> 
                            Sube un archivo Excel para realizar una importación avanzada. Podrás relacionar clientes, mascotas y citas de forma automática.
                        </div>
                        
                        <!--Modal de subida de archivo excel -->
                        <div
                            class="border border-2 border-dashed rounded-3 p-5 text-center"
                            :class="arrastrando ? 'border-primary bg-primary bg-opacity-10' : 'border-secondary'"
                            @dragover.prevent="arrastrando = true"
                            @dragleave.prevent="arrastrando = false"
                            @drop.prevent="manejarSoltar"
                            @click="$refs.inputArchivo.click()"
                            style="cursor: pointer;"
                        >
                            <i class="bi bi-cloud-arrow-up display-4 text-primary mb-3"></i>
                            <h5 class="fw-bold">Arrastra tu archivo Excel aquí</h5>
                            <p class="text-muted small mb-0">o haz clic para seleccionar (.xlsx, .xls, .csv)</p>
                            <input
                                ref="inputArchivo"
                                type="file"
                                class="d-none"
                                accept=".xlsx,.xls,.csv"
                                @change="seleccionarArchivo"
                            />
                        </div>

                        <!--Renderizado condicional basado en "cargando" -->

                        <div v-if="cargando" class="text-center mt-4">
                            <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                            <span class="text-muted small">Analizando archivo...</span>
                        </div>
                    </div>

                    <!-- Paso 2: Mapeo -->
                    <!--Renderizado condicional basado en "step === 2" -->
                    <div v-if="step === 2">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <div>
                                <h6 class="fw-bold mb-0">Configuración de Importación</h6>
                                <p class="text-muted small mb-0">Archivo: {{ archivo?.name }}</p>
                            </div>
                            <!-- Dispara la acción "reiniciar" -->
                            <button class="btn btn-sm btn-outline-secondary" @click="reiniciar" :disabled="cargando">
                                <i class="bi bi-arrow-left me-1"></i>Cambiar Archivo
                            </button>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="bg-light p-3 rounded-3 h-100">
                                    <!-- Secciones de los modales -->
                                    <h6 class="fw-bold small text-uppercase mb-3">1. Seleccionar Módulos</h6>
                                    <div class="form-check form-switch mb-2">
                                        <!-- Enlace de datos bidireccional con "modules.clientes" -->
                                        <input class="form-check-input" type="checkbox" id="modClientes" v-model="modules.clientes">
                                        <label class="form-check-label small fw-semibold" for="modClientes">Clientes</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <!-- Enlace de datos bidireccional con "modules.mascotas" -->
                                        <input class="form-check-input" type="checkbox" id="modMascotas" v-model="modules.mascotas">
                                        <label class="form-check-label small fw-semibold" for="modMascotas">Mascotas</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <!-- Enlace de datos bidireccional con "modules.citas" -->
                                        <input class="form-check-input" type="checkbox" id="modCitas" v-model="modules.citas">
                                        <label class="form-check-label small fw-semibold" for="modCitas">Citas</label>
                                    </div>
                                    <!-- Renderizado condicional basado en "modules.citas && !modules.mascotas" -->
                                    <div class="alert alert-warning small mt-3 p-2 border-0 shadow-sm" v-if="modules.citas && !modules.mascotas">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Para importar citas, es recomendable importar mascotas para relacionarlas.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-8">
                                <div class="bg-light p-3 rounded-3 h-100" style="max-height: 50vh; overflow-y: auto;">
                                    <h6 class="fw-bold small text-uppercase mb-3">2. Mapear Columnas</h6>
                                    <p class="small text-muted mb-3">Asocia las columnas de tu Excel con los campos del sistema.</p>

                                    <!-- Clientes -->
                                    <!--Si se activa el checkbox de clientes -->
                                    <div v-if="modules.clientes" class="mb-4">
                                        <h6 class="fw-bold text-primary small border-bottom pb-1 mb-2">Datos del Cliente</h6>
                                        <!-- Recorremos el array de campos de los clientes -->
                                        <div class="row g-2 align-items-center mb-2" v-for="field in camposClientes" :key="field.key">
                                            <div class="col-5">
                                                <label class="small text-secondary">{{ field.label }}</label>
                                            </div>
                                            <div class="col-7">
                                                <!--Enlace de datos bidireccional con el valor de mapping[field.key] -->
                                                <select class="form-select form-select-sm" v-model="mapping[field.key]">
                                                    <option :value="null">-- Ignorar / No disponible --</option>
                                                    <!-- Recorremos los encabezados del Excel -->
                                                    <option v-for="header in headers" :key="header" :value="header">{{ header }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Mascotas -->
                                    <!--Renderizado condicional basado en "modules.mascotas" -->
                                    <div v-if="modules.mascotas" class="mb-4">
                                        <h6 class="fw-bold text-primary small border-bottom pb-1 mb-2">Datos de la Mascota</h6>
                                        <!-- Iteramos los campos de las mascotas -->
                                        <div class="row g-2 align-items-center mb-2" v-for="field in camposMascotas" :key="field.key">
                                            <div class="col-5">
                                                <label class="small text-secondary">{{ field.label }}</label>
                                            </div>
                                            <div class="col-7">
                                                <!-- Enlace de datos bidireccional con el valor de mapping[field.key] -->
                                                <select class="form-select form-select-sm" v-model="mapping[field.key]">
                                                    <option :value="null">-- Ignorar / No disponible --</option>
                                                    <!-- Iteramos los encabezados del Excel -->
                                                    <option v-for="header in headers" :key="header" :value="header">{{ header }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Citas -->
                                    <!-- Renderizado condicional basado en "modules.citas" -->
                                    <div v-if="modules.citas" class="mb-4">
                                        <h6 class="fw-bold text-primary small border-bottom pb-1 mb-2">Datos de la Cita</h6>
                                        <!-- Iteramos los campos de las citas -->
                                        <div class="row g-2 align-items-center mb-2" v-for="field in camposCitas" :key="field.key">
                                            <div class="col-5">
                                                <label class="small text-secondary">{{ field.label }}</label>
                                            </div>
                                            <div class="col-7">
                                                <!-- Enlace de datos bidireccional con el valor de mapping[field.key] -->
                                                <select class="form-select form-select-sm" v-model="mapping[field.key]">
                                                    <option :value="null">-- Ignorar / No disponible --</option>
                                                    <!-- Iteramos los encabezados del Excel -->
                                                    <option v-for="header in headers" :key="header" :value="header">{{ header }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                    
                    <!-- Modal de resultado manejado por SWAL, pero mostramos la carga aquí -->
                    <!-- Renderizado condicional basado en "cargandoImportacion" -->
                    <div v-if="cargandoImportacion" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-3 fw-bold text-secondary">Procesando datos importados...</p>
                        <p class="small text-muted">Por favor, no cierres esta ventana. Este proceso puede tardar unos minutos.</p>
                    </div>

                </div>
                
                <!-- Renderizado condicional basado en "step === 2 && !cargandoImportacion" -->
                
                <div class="modal-footer border-0 bg-light" v-if="step === 2 && !cargandoImportacion">
                    <!-- Dispara la acción "cerrar" -->
                    <button type="button" class="btn btn-outline-secondary" @click="cerrar">Cancelar</button>
                    <!-- Dispara la acción "procesar" -->
                    <button type="button" class="btn btn-primary px-4" @click="procesar" :disabled="!isMappingValid">
                        <i class="bi bi-check2-circle me-1"></i> Iniciar Importación
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
// ==================================================================================
// LÓGICA DEL COMPONENTE (VUE 3)
// ==================================================================================

import axios from 'axios';
import Swal from 'sweetalert2';

// ------------------------------------------------------------------------------
// EXPORT DEFAULT: Definición principal del componente
// ------------------------------------------------------------------------------
export default {
    // PROPIEDADES: Datos inyectados desde el componente padre o estado
    props: {
        visible: { type: Boolean, default: false },
    },
    emits: ['cerrar', 'importado'],
    // ESTADO REACTIVO: Variables locales del componente
    data() {
        return {
            // Estado del Modal
            step: 1,
            // Datos del Archivo
            archivo: null,
            arrastrando: false,
            cargando: false,
            // Carga de Importación
            cargandoImportacion: false,
            headers: [],
            sample: [],
            modules: {
                clientes: true,
                mascotas: true,
                citas: false
            },
            // Configuración de Mapeo del archivo Excel
            mapping: {
                cliente_email: null,
                cliente_nombre: null,
                cliente_telefono: null,
                cliente_direccion: null,
                mascota_nombre: null,
                mascota_raza: null,
                cita_fecha_hora: null,
                cita_veterinario: null,
                cita_titulo: null,
                cita_valor: null,
                cita_estado_transaccion: null,
                cita_cargo: null,
            },
            // Campos de Clientes, Mascotas y Citas para generar el select de mapeo
            camposClientes: [
                { key: 'cliente_email', label: 'Correo Electrónico (Requerido)' },
                { key: 'cliente_nombre', label: 'Nombre' },
                { key: 'cliente_telefono', label: 'Teléfono' },
                { key: 'cliente_direccion', label: 'Dirección' },
            ],
            camposMascotas: [
                { key: 'mascota_nombre', label: 'Nombre Mascota' },
                { key: 'mascota_raza', label: 'Raza' },
            ],
            camposCitas: [
                { key: 'cita_fecha_hora', label: 'Fecha y Hora (Requerido)' },
                { key: 'cita_veterinario', label: 'Veterinario' },
                { key: 'cita_titulo', label: 'Título/Motivo' },
                { key: 'cita_valor', label: 'Valor/Costo' },
                { key: 'cita_estado_transaccion', label: 'Estado de Transacción' },
                { key: 'cita_cargo', label: 'Cargo / Prestación' },
            ]
        };
    },
    // PROPIEDADES COMPUTADAS: Variables reactivas que dependen de otras
    computed: {
        // Valida si el mapeo es válido para continuar con la importación
        isMappingValid() {
            // Si no hay ningún módulo activo, retornar false
            if (!this.modules.clientes && !this.modules.mascotas && !this.modules.citas) return false;
            
            // Requerir campos específicos si el módulo está activo
            if (this.modules.clientes && !this.mapping.cliente_email) return false;
            if (this.modules.citas && !this.mapping.cita_fecha_hora) return false;
            
            // Debe tener al menos un mapeo
            return Object.values(this.mapping).some(val => val !== null);
        }
    },
    // MÉTODOS: Bloque de funciones y eventos
    methods: {
        // Método para seleccionar el archivo Excel y analizarlo
        seleccionarArchivo(evento) {
            const archivos = evento.target.files;
            if (archivos && archivos.length > 0) {
                this.archivo = archivos[0];
                this.analizarArchivo();
            }
        },
        // Método para manejar el drag and drop
        manejarSoltar(evento) {
            this.arrastrando = false;
            const archivos = evento.dataTransfer.files;
            if (archivos && archivos.length > 0) {
                this.archivo = archivos[0];
                this.analizarArchivo();
            }
        },
        // Método para analizar el archivo Excel
        analizarArchivo() {
            // Si no hay archivo, retornar  
            if (!this.archivo) return;
            
            // Mostrar indicador de carga
            this.cargando = true;
            // Crear FormData para enviar el archivo
            const formData = new FormData();
            formData.append('file', this.archivo);

            // Llamada a la API para analizar el archivo
            axios.post('/api/import/analyze', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            })
            // Si la respuesta es exitosa
            .then(response => {
                // Guardar los encabezados y la muestra
                this.headers = response.data.headers;
                this.sample = response.data.sample;
                // Intentar el mapeo automático
                this.autoMapHeaders();
                // Cambiar al paso 2
                this.step = 2;
            })
            // Si hay un error
            .catch(error => {
                // Mostrar mensaje de error
                const msj = error.response?.data?.message || 'Error al leer el archivo.';
                Swal.fire('Error', msj, 'error');
                // Reiniciar el modal
                this.reiniciar();
            })
            // Al finalizar
            .finally(() => {
                this.cargando = false;
            });
        },
        // Método para auto mapear los encabezados
        autoMapHeaders() {
            // Auto mapeo simple basado en palabras clave comunes
            const encabezadosMinuscula = this.headers.map(h => h ? h.toLowerCase() : '');
            // Función para buscar encabezados por palabras clave
            const buscarEncabezado = (keywords) => {
                // Buscar cada palabra clave en los encabezados
                for (let word of keywords) {
                    const idx = encabezadosMinuscula.findIndex(h => h.includes(word));
                    // Si se encuentra el encabezado, retornarlo
                    if (idx !== -1) return this.headers[idx];
                }
                return null;
            };
            
            // Buscar encabezados para cada campo
            this.mapping.cliente_email = buscarEncabezado(['email', 'correo']);
            this.mapping.cliente_nombre = buscarEncabezado(['nombre cliente', 'cliente', 'propietario', 'dueño']);
            this.mapping.cliente_telefono = buscarEncabezado(['telefono', 'celular', 'tel']);
            this.mapping.cliente_direccion = buscarEncabezado(['direccion']);
            
            this.mapping.mascota_nombre = buscarEncabezado(['mascota', 'paciente', 'nombre mascota']);
            this.mapping.mascota_raza = buscarEncabezado(['raza', 'especie']);

            this.mapping.cita_fecha_hora = buscarEncabezado(['fecha', 'hora', 'fecha_hora']);
            this.mapping.cita_veterinario = buscarEncabezado(['veterinario', 'medico', 'doctor']);
            this.mapping.cita_titulo = buscarEncabezado(['titulo', 'motivo', 'detalle']);
            this.mapping.cita_valor = buscarEncabezado(['valor', 'precio', 'costo', 'total']);
            this.mapping.cita_estado_transaccion = buscarEncabezado(['estado transaccion', 'estado de la transaccion', 'estado pago']);
            this.mapping.cita_cargo = buscarEncabezado(['cargo', 'cargos', 'prestacion', 'servicio']);
        },  
        // Método para procesar el archivo
        procesar() {
            // Mostrar indicador de carga
            this.cargandoImportacion = true;
            // Crear FormData para enviar el archivo
            const formData = new FormData();    
            formData.append('file', this.archivo);
            formData.append('mapping', JSON.stringify(this.mapping));
            formData.append('modules', JSON.stringify(this.modules));

            // Llamada a la API para procesar el archivo
            axios.post('/api/import/process', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            })
            // Si la respuesta es exitosa
            .then(response => {
                // Obtener el conteo de filas descartadas
                const descartados = response.data.descartados_count || 0;
                
                // Si hay filas descartadas y una URL de descarga
                if (descartados > 0 && response.data.download_url) {
                    // Mostrar alerta de SweetAlert2 con opción de descargar reporte
                    Swal.fire({
                        icon: 'warning',
                        title: 'Importación Parcial',
                        html: `Se procesaron los datos, pero <b>${descartados} filas</b> fueron descartadas por errores de validación o conflictos.<br><br>¿Deseas descargar el reporte de filas descartadas?`,
                        showCancelButton: true,
                        confirmButtonText: 'Descargar Reporte',
                        cancelButtonText: 'Cerrar',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = response.data.download_url;
                        }
                    });
                } else {
                    // Mostrar alerta de éxito si no hay filas descartadas
                    Swal.fire({
                        icon: 'success',
                        title: '¡Importación Exitosa!',
                        text: response.data.message || 'Todos los datos se han importado correctamente.',
                    });
                }
                
                // Emitir evento de importación exitosa
                this.$emit('importado');
                // Cerrar el modal
                this.cerrar();
            })
            // Si hay un error
            .catch(error => {
                // Mostrar mensaje de error
                Swal.fire('Error', error.response?.data?.message || 'Ocurrió un error grave durante la importación.', 'error');
            })
            // Al finalizar
            .finally(() => {
                this.cargandoImportacion = false;
            });
        },
        // Método para reiniciar el modal
        reiniciar() { 
            // Resetear el paso actual
            this.step = 1;
            // Limpiar el archivo
            this.archivo = null;
            // Limpiar el input de archivo
            if (this.$refs.inputArchivo) this.$refs.inputArchivo.value = '';
            // Reiniciar mapeo
            Object.keys(this.mapping).forEach(k => this.mapping[k] = null);
        },
        // Método para cerrar el modal
        cerrar() {
            // Reiniciar el modal
            this.reiniciar();
            // Emitir evento de cierre
            this.$emit('cerrar');
        }
    }
};
</script>
