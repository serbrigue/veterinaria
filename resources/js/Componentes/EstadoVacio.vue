<template>
    <div v-if="visible" class="text-center py-5 bg-light rounded-4 border border-dashed">
        <i :class="icono" class="text-muted display-4 d-block mb-3 opacity-50"></i>
        <p class="text-muted mb-3 fw-medium">{{ mensaje }}</p>
        <button 
            v-if="textoBoton" 
            type="button" 
            class="btn btn-primary rounded-pill px-4" 
            @click="$emit('accion')"
        >
            {{ textoBoton }}
        </button>
    </div>
</template>

<script>
export default {
    name: 'EstadoVacio',
    props: {
        visible: {
            type: Boolean,
            default: false,
        },
        mensaje: {
            type: String,
            default: 'No hay registros aún.',
        },
        textoBoton: {
            type: String,
            default: '',
        },
        icono: {
            type: String,
            default: 'bi bi-inbox',
        },
    },
    emits: ['accion'],
}
</script>
