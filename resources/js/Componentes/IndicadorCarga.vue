<template>
    <div v-if="cargando" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
        <p class="mt-3 text-muted fw-semibold">Cargando {{ mensaje }}...</p>
    </div>
</template>

<script>
export default {
    name: 'IndicadorCarga',
    props: {
        cargando: {
            type: Boolean,
            default: false,
        },
        mensaje: {
            type: String,
            default: 'datos',
        },
    },
}
</script>
