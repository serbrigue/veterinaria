<template>
    <button :type="type" class="btn btn-primary" :disabled="deshabilitado || cargando">
        <span v-if="cargando" class="spinner-border spinner-border-sm me-2" />
        <slot />
    </button>
</template>

<script>
export default {
    props: {
        type: {
            type: String,
            default: 'button',
        },
        deshabilitado: {
            type: Boolean,
            default: false,
        },
        cargando: {
            type: Boolean,
            default: false,
        },
    },
}
</script>
