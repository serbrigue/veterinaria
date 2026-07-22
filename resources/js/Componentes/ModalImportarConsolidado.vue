<template>
    <div v-if="visible" class="modal show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); z-index: 1055;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary bg-opacity-10 border-0">
                    <h5 class="modal-title text-primary fw-bold">
                        <i class="bi bi-layer-forward me-2"></i>Importación Consolidada
                    </h5>
                    <button type="button" class="btn-close" @click="cerrar" :disabled="cargando"></button>
                </div>
                
                <div class="modal-body p-4">
                    <!-- Step 1: Upload File -->
                    <div v-if="step === 1">
                        <div class="alert alert-info small mb-3">
                            <i class="bi bi-info-circle me-1"></i> 
                            Sube un archivo Excel para realizar una importación avanzada. Podrás relacionar clientes, mascotas y citas de forma automática.
                        </div>

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

                        <div v-if="cargando" class="text-center mt-4">
                            <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                            <span class="text-muted small">Analizando archivo...</span>
                        </div>
                    </div>

                    <!-- Step 2: Mapping -->
                    <div v-if="step === 2">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <div>
                                <h6 class="fw-bold mb-0">Configuración de Importación</h6>
                                <p class="text-muted small mb-0">Archivo: {{ archivo?.name }}</p>
                            </div>
                            <button class="btn btn-sm btn-outline-secondary" @click="reiniciar" :disabled="cargando">
                                <i class="bi bi-arrow-left me-1"></i>Cambiar Archivo
                            </button>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="bg-light p-3 rounded-3 h-100">
                                    <h6 class="fw-bold small text-uppercase mb-3">1. Seleccionar Módulos</h6>
                                    
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="modClientes" v-model="modules.clientes">
                                        <label class="form-check-label small fw-semibold" for="modClientes">Clientes</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="modMascotas" v-model="modules.mascotas">
                                        <label class="form-check-label small fw-semibold" for="modMascotas">Mascotas</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="modCitas" v-model="modules.citas">
                                        <label class="form-check-label small fw-semibold" for="modCitas">Citas</label>
                                    </div>

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
                                    <div v-if="modules.clientes" class="mb-4">
                                        <h6 class="fw-bold text-primary small border-bottom pb-1 mb-2">Datos del Cliente</h6>
                                        <div class="row g-2 align-items-center mb-2" v-for="field in camposClientes" :key="field.key">
                                            <div class="col-5">
                                                <label class="small text-secondary">{{ field.label }}</label>
                                            </div>
                                            <div class="col-7">
                                                <select class="form-select form-select-sm" v-model="mapping[field.key]">
                                                    <option :value="null">-- Ignorar / No disponible --</option>
                                                    <option v-for="header in headers" :key="header" :value="header">{{ header }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Mascotas -->
                                    <div v-if="modules.mascotas" class="mb-4">
                                        <h6 class="fw-bold text-primary small border-bottom pb-1 mb-2">Datos de la Mascota</h6>
                                        <div class="row g-2 align-items-center mb-2" v-for="field in camposMascotas" :key="field.key">
                                            <div class="col-5">
                                                <label class="small text-secondary">{{ field.label }}</label>
                                            </div>
                                            <div class="col-7">
                                                <select class="form-select form-select-sm" v-model="mapping[field.key]">
                                                    <option :value="null">-- Ignorar / No disponible --</option>
                                                    <option v-for="header in headers" :key="header" :value="header">{{ header }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Citas -->
                                    <div v-if="modules.citas" class="mb-4">
                                        <h6 class="fw-bold text-primary small border-bottom pb-1 mb-2">Datos de la Cita</h6>
                                        <div class="row g-2 align-items-center mb-2" v-for="field in camposCitas" :key="field.key">
                                            <div class="col-5">
                                                <label class="small text-secondary">{{ field.label }}</label>
                                            </div>
                                            <div class="col-7">
                                                <select class="form-select form-select-sm" v-model="mapping[field.key]">
                                                    <option :value="null">-- Ignorar / No disponible --</option>
                                                    <option v-for="header in headers" :key="header" :value="header">{{ header }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                    
                    <!-- Result Modal handled by SWAL, but we show loading here -->
                    <div v-if="cargandoImportacion" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-3 fw-bold text-secondary">Procesando datos importados...</p>
                        <p class="small text-muted">Por favor, no cierres esta ventana. Este proceso puede tardar unos minutos.</p>
                    </div>

                </div>
                
                <div class="modal-footer border-0 bg-light" v-if="step === 2 && !cargandoImportacion">
                    <button type="button" class="btn btn-outline-secondary" @click="cerrar">Cancelar</button>
                    <button type="button" class="btn btn-primary px-4" @click="procesar" :disabled="!isMappingValid">
                        <i class="bi bi-check2-circle me-1"></i> Iniciar Importación
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
    props: {
        visible: { type: Boolean, default: false },
    },
    emits: ['cerrar', 'importado'],
    data() {
        return {
            step: 1,
            archivo: null,
            arrastrando: false,
            cargando: false,
            cargandoImportacion: false,
            headers: [],
            sample: [],
            modules: {
                clientes: true,
                mascotas: true,
                citas: false
            },
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
    computed: {
        isMappingValid() {
            if (!this.modules.clientes && !this.modules.mascotas && !this.modules.citas) return false;
            
            // Require specific fields if module is active
            if (this.modules.clientes && !this.mapping.cliente_email) return false;
            if (this.modules.citas && !this.mapping.cita_fecha_hora) return false;
            
            // Must have at least one mapping
            return Object.values(this.mapping).some(val => val !== null);
        }
    },
    methods: {
        seleccionarArchivo(evento) {
            const archivos = evento.target.files;
            if (archivos && archivos.length > 0) {
                this.archivo = archivos[0];
                this.analizarArchivo();
            }
        },
        manejarSoltar(evento) {
            this.arrastrando = false;
            const archivos = evento.dataTransfer.files;
            if (archivos && archivos.length > 0) {
                this.archivo = archivos[0];
                this.analizarArchivo();
            }
        },
        analizarArchivo() {
            if (!this.archivo) return;
            
            this.cargando = true;
            const formData = new FormData();
            formData.append('file', this.archivo);

            axios.post('/api/import/analyze', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            })
            .then(response => {
                this.headers = response.data.headers;
                this.sample = response.data.sample;
                this.autoMapHeaders();
                this.step = 2;
            })
            .catch(error => {
                const msj = error.response?.data?.message || 'Error al leer el archivo.';
                Swal.fire('Error', msj, 'error');
                this.reiniciar();
            })
            .finally(() => {
                this.cargando = false;
            });
        },
        autoMapHeaders() {
            // Auto mapping simple based on common keywords
            const lowerHeaders = this.headers.map(h => h ? h.toLowerCase() : '');
            
            const findHeader = (keywords) => {
                for (let word of keywords) {
                    const idx = lowerHeaders.findIndex(h => h.includes(word));
                    if (idx !== -1) return this.headers[idx];
                }
                return null;
            };

            this.mapping.cliente_email = findHeader(['email', 'correo']);
            this.mapping.cliente_nombre = findHeader(['nombre cliente', 'cliente', 'propietario', 'dueño']);
            this.mapping.cliente_telefono = findHeader(['telefono', 'celular', 'tel']);
            this.mapping.cliente_direccion = findHeader(['direccion']);
            
            this.mapping.mascota_nombre = findHeader(['mascota', 'paciente', 'nombre mascota']);
            this.mapping.mascota_raza = findHeader(['raza', 'especie']);

            this.mapping.cita_fecha_hora = findHeader(['fecha', 'hora', 'fecha_hora']);
            this.mapping.cita_veterinario = findHeader(['veterinario', 'medico', 'doctor']);
            this.mapping.cita_titulo = findHeader(['titulo', 'motivo', 'detalle']);
            this.mapping.cita_valor = findHeader(['valor', 'precio', 'costo', 'total']);
            this.mapping.cita_estado_transaccion = findHeader(['estado transaccion', 'estado de la transaccion', 'estado pago']);
            this.mapping.cita_cargo = findHeader(['cargo', 'cargos', 'prestacion', 'servicio']);
        },
        procesar() {
            this.cargandoImportacion = true;
            
            const formData = new FormData();
            formData.append('file', this.archivo);
            formData.append('mapping', JSON.stringify(this.mapping));
            formData.append('modules', JSON.stringify(this.modules));

            axios.post('/api/import/process', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            })
            .then(response => {
                const descartados = response.data.descartados_count || 0;
                
                if (descartados > 0 && response.data.download_url) {
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
                    Swal.fire({
                        icon: 'success',
                        title: '¡Importación Exitosa!',
                        text: response.data.message || 'Todos los datos se han importado correctamente.',
                    });
                }
                
                this.$emit('importado');
                this.cerrar();
            })
            .catch(error => {
                Swal.fire('Error', error.response?.data?.message || 'Ocurrió un error grave durante la importación.', 'error');
            })
            .finally(() => {
                this.cargandoImportacion = false;
            });
        },
        reiniciar() {
            this.step = 1;
            this.archivo = null;
            if (this.$refs.inputArchivo) this.$refs.inputArchivo.value = '';
            // Reset mapping
            Object.keys(this.mapping).forEach(k => this.mapping[k] = null);
        },
        cerrar() {
            this.reiniciar();
            this.$emit('cerrar');
        }
    }
};
</script>
