<template>
    <button :type="type" class="btn btn-outline-secondary" :disabled="deshabilitado">
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
    },
}
</script>
