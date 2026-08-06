<template>
    <!-- ================================================================================== -->
    <!-- COMPONENTE: ModalImportarSimple -->
    <!-- ================================================================================== -->
    <!-- COMPONENTE: ModalImportarSimple -->
    <!-- ================================================================================== -->

    <!-- Renderizado condicional basado en "visible" -->
    <div v-if="visible" class="modal show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary bg-opacity-10 border-0">
                    <h5 class="modal-title text-primary fw-bold">
                        <i class="bi bi-upload me-2"></i>Importar {{ etiqueta }}
                    </h5>
                    <!-- Dispara la acción "cerrar" -->
                    <button type="button" class="btn-close" @click="cerrar" :disabled="cargando"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Renderizado condicional basado en "!cargando" -->
                    <div v-if="!cargando">
                        <div
                            class="border border-2 border-dashed rounded-3 p-4 text-center"
                            :class="arrastrando ? 'border-primary bg-primary bg-opacity-10' : 'border-secondary'"
                            @dragover.prevent="arrastrando = true"
                            @dragleave.prevent="arrastrando = false"
                            @drop.prevent="manejarSoltar"
                            @click="$refs.inputArchivo.click()"
                            style="cursor: pointer;"
                        >
                            <i class="bi bi-cloud-arrow-up display-5 text-primary"></i>
                            <p class="mt-2 mb-1 fw-semibold">Arrastra tu archivo Excel aquí</p>
                            <p class="text-muted small mb-0">o haz clic para seleccionar (.xlsx, .xls, .csv)</p>
                            <input
                                ref="inputArchivo"
                                type="file"
                                class="d-none"
                                accept=".xlsx,.xls,.csv"
                                @change="seleccionarArchivo"
                            />
                        </div>
                        <!-- Renderizado condicional basado en "archivo" -->
                        <div v-if="archivo" class="alert alert-info mt-3 d-flex align-items-center gap-2 mb-0">
                            <i class="bi bi-file-earmark-spreadsheet fs-5"></i>
                            <span class="small">{{ archivo.name }} <span class="text-muted">({{ tamanoArchivo }})</span></span>
                            <!-- Dispara la acción "limpiarArchivo" -->
                            <button type="button" class="btn-close btn-close-sm ms-auto" @click="limpiarArchivo"></button>
                        </div>
                    </div>
                    <div v-else class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Importando...</span>
                        </div>
                        <p class="mt-2 text-muted small">Importando datos, por favor espera...</p>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <!-- Dispara la acción "cerrar" -->
                    <button type="button" class="btn btn-outline-secondary btn-sm" @click="cerrar" :disabled="cargando">Cancelar</button>
                    <!-- Dispara la acción "importar" -->
                    <button type="button" class="btn btn-primary btn-sm" @click="importar" :disabled="!archivo || cargando">
                        <i class="bi bi-upload me-1"></i>Importar
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
        entidad: { type: String, required: true },
        etiqueta: { type: String, required: true },
    },
    emits: ['cerrar', 'importado'],
    //Variables locales del componente
    data() {
        return {
            archivo: null,
            cargando: false,
            arrastrando: false,
        };
    },
    //Variables reactivas que dependen de otras
    computed: {
        
        tamanoArchivo() {
            if (!this.archivo) return '';
            const kb = (this.archivo.size / 1024).toFixed(1);
            return kb > 1024 ? (kb / 1024).toFixed(1) + ' MB' : kb + ' KB';
        },
    },
    // MÉTODOS Bloque de funciones y eventos
    methods: {
        //Selecciona el archivo que se va a importar
        seleccionarArchivo(evento) {
            // Captura el archivo seleccionado
            const archivos = evento.target.files;
            // Si se selecciona un archivo, se asigna a la variable local "archivo"
            if (archivos && archivos.length > 0) {
                this.archivo = archivos[0];
            }
        },

        //Maneja la soltar del archivo
        manejarSoltar(evento) {
            // Establece la variable "arrastrando" a false
            this.arrastrando = false;
            // Captura el archivo seleccionado
            const archivos = evento.dataTransfer.files;
            // Si se selecciona un archivo, se asigna a la variable local "archivo"
            if (archivos && archivos.length > 0) {
                this.archivo = archivos[0];
            }
        },
        //Limpia el archivo seleccionado
        limpiarArchivo() {
            this.archivo = null;
            if (this.$refs.inputArchivo) {
                this.$refs.inputArchivo.value = '';
            }
        },
        //Cierra el modal
        cerrar() {
            this.limpiarArchivo();
            this.$emit('cerrar');
        },
        //Importa el archivo
        importar() {
            // Si no hay archivo, no se importa
            if (!this.archivo) return;

            // Establece la variable "cargando" a true
            this.cargando = true;
            // Crea un objeto FormData para enviar el archivo
            const formData = new FormData();
            // Agrega el archivo al FormData
            formData.append('file', this.archivo);

            // Envía el archivo al backend
            axios.post(`/api/import/simple/${this.entidad}`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            })
            // Si la importación es exitosa
            .then((response) => {
                Swal.fire({
                    icon: 'success',
                    title: '¡Importación Exitosa!',
                    text: response.data.message,
                });
                this.$emit('importado');
                this.cerrar();
            })
            // Si la importación falla
            .catch((error) => {
                // Muestra un mensaje de error
                const mensaje = error.response?.data?.message || 'Ocurrió un error durante la importación.';
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Importación',
                    text: mensaje,
                });
            })
            // Finalmente
            .finally(() => {
                // Establece la variable "cargando" a false
                this.cargando = false;
            });
        },
    },
};
</script>
