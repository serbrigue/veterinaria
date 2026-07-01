<template>
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
                        <button 
                            type="button" 
                            class="btn btn-light rounded-pill px-4 text-muted fw-medium" 
                            @click="$emit('cerrar')" 
                            :disabled="processing"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="button" 
                            class="btn btn-primary rounded-pill px-4 fw-medium shadow-sm" 
                            :disabled="processing" 
                            @click="$emit('guardar')"
                        >
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
export default {
    name: 'ModalCrud',
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
    computed: {
        textoBoton() {
            return this.modoEdicion ? this.textoGuardar : this.textoCrear;
        },
        claseTamanio() {
            if (this.tamanio === 'md') return '';
            return `modal-${this.tamanio}`;
        },
    },
    methods: {
        cerrarSiPermitido() {
            if (this.cerrable) {
                this.$emit('cerrar');
            }
        },
    },
}
</script>
