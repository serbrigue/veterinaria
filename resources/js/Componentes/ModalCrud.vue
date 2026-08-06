<template>
    <!-- ================================================================================== -->
    <!-- COMPONENTE: ModalCrud -->
    <!-- ================================================================================== -->


    <!-- Renderizado condicional basado en "visible" -->
    <template v-if="visible">
        <div class="modal fade show d-block" tabindex="-1" @click.self="cerrarSiPermitido">
            <div class="modal-dialog modal-dialog-centered" :class="claseTamanio">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    <!-- Header -->
                    <div class="modal-header bg-light border-bottom-0 py-3 px-4">
                        <h5 class="modal-title fw-bold text-primary">
                            <i :class="modoEdicion ? 'bi bi-pencil-square' : 'bi bi-plus-circle'" class="me-2"></i>
                            {{ titulo }}
                        </h5>
                        <!-- EVENTO QUE DISPARA LA ACCIÓN emit("cerrar") -->
                        <button type="button" class="btn-close shadow-none" @click="$emit('cerrar')"></button>
                    </div>

                    <!-- Body (contenido del formulario via slot) -->
                    <div class="modal-body p-4 bg-white">
                        <form @submit.prevent="$emit('guardar')">
                            <slot />
                        </form>
                    </div>

                    <!-- Footer -->
                    <div class="modal-footer bg-light border-top-0 py-3 px-4">
                        <!-- Dispara la acción emit("cerrar") -->
                        <button 
                            type="button" 
                            class="btn btn-light rounded-pill px-4 text-muted fw-medium" 
                            @click="$emit('cerrar')" 
                            :disabled="processing"
                        >
                            Cancelar
                        </button>
                        <!-- Dispara la acción emit("guardar") -->
                        <button 
                            type="button" 
                            class="btn btn-primary rounded-pill px-4 fw-medium shadow-sm" 
                            :disabled="processing" 
                            @click="$emit('guardar')"
                        >
                            <!-- Renderizado condicional basado en "processing" -->
                            <span v-if="processing" class="spinner-border spinner-border-sm me-2"></span>
                            {{ textoBoton }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    </template>
</template>

<script>
// ==================================================================================
// LÓGICA DEL COMPONENTE (VUE 3)
// ==================================================================================

// ------------------------------------------------------------------------------
// EXPORT DEFAULT: Definición principal del componente
// ------------------------------------------------------------------------------
export default {
    name: 'ModalCrud',
    // PROPIEDADES: Datos recibidos desde el componente padre o estado
    props: {
        visible: {
            type: Boolean,
            default: false,
        },
        titulo: {
            type: String,
            required: true,
        },
        modoEdicion: {
            type: Boolean,
            default: false,
        },
        processing: {
            type: Boolean,
            default: false,
        },
        textoGuardar: {
            type: String,
            default: 'Guardar cambios',
        },
        textoCrear: {
            type: String,
            default: 'Crear',
        },
        tamanio: {
            type: String,
            default: 'md',
            validator: (value) => ['sm', 'md', 'lg', 'xl'].includes(value),
        },
        cerrable: {
            type: Boolean,
            default: true,
        },
    },
    emits: ['cerrar', 'guardar'],
    // PROPIEDADES COMPUTADAS: Variables reactivas que dependen de otras
    computed: {
        // Determina el texto del boton de guardar segun el modo de edicion
        textoBoton() {
            return this.modoEdicion ? this.textoGuardar : this.textoCrear;
        },
        // Determina el tamaño del modal segun el tamaño recibido
        claseTamanio() {
            if (this.tamanio === 'md') return '';
            return `modal-${this.tamanio}`;
        },
    },
    // MÉTODOS: Bloque de funciones y eventos
    methods: {
        // Cierra el modal si es permitido
        cerrarSiPermitido() {
            if (this.cerrable) {
                this.$emit('cerrar');
            }
        },
    },
}
</script>
