<template>
    <div v-if="visible" class="text-center py-5 bg-light rounded-4 border border-dashed">
        <i class="bi bi-search text-muted display-4 d-block mb-3 opacity-50"></i>
        <p class="text-muted mb-3 fw-medium">{{ mensaje }}</p>
        <button 
            type="button" 
            class="btn btn-outline-secondary rounded-pill px-4" 
            @click="$emit('limpiar')"
        >
            Quitar filtro
        </button>
    </div>
</template>

<script>
export default {
    name: 'SinResultados',
    props: {
        visible: {
            type: Boolean,
            default: false,
        },
        mensaje: {
            type: String,
            default: 'Ningún registro coincide con la búsqueda.',
        },
    },
    emits: ['limpiar'],
}
</script>
