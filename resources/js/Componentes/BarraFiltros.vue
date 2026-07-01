<template>
    <div class="bg-light p-3 rounded-4 border border-light mb-4 shadow-sm">
        <div class="row g-3 align-items-end">
            <!-- Espacio para los filtros inyectados -->
            <slot />
            
            <!-- Botón de Limpiar Filtros -->
            <div v-if="mostrarLimpiar" :class="claseBotonContenedor">
                <button 
                    type="button" 
                    class="btn btn-outline-secondary btn-sm w-100 rounded-pill px-3 py-2 fw-medium shadow-none transition-all" 
                    :disabled="deshabilitarLimpiar" 
                    @click="$emit('limpiar')"
                >
                    <slot name="texto-limpiar">
                        <i class="bi bi-eraser-fill me-1"></i>Limpiar Filtros
                    </slot>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'BarraFiltros',
    props: {
        mostrarLimpiar: {
            type: Boolean,
            default: true,
        },
        deshabilitarLimpiar: {
            type: Boolean,
            default: false,
        },
        claseBotonContenedor: {
            type: String,
            default: 'col-12 col-lg-2 ms-auto d-flex justify-content-lg-end',
        },
    },
    emits: ['limpiar'],
}
</script>

<style scoped>
.transition-all {
    transition: all 0.2s ease-in-out;
}
.btn-outline-secondary:hover:not(:disabled) {
    background-color: #6c757d;
    color: #fff;
}
</style>
