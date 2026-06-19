<template>
    <input
        type="checkbox"
        :value="valor"
        v-model="proxySeleccionado"
        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
    />
</template>

<script>
export default {
    props: {
        seleccionado: {
            type: [Array, Boolean],
            required: true,
        },
        valor: {
            default: null,
        },
    },
    emits: ['update:seleccionado'],
    computed: {
        proxySeleccionado: {
            get() {
                return this.seleccionado;
            },
            set(val) {
                this.$emit('update:seleccionado', val);
            },
        },
    },
}
</script>
