<template>
    <input
        ref="entrada"
        :value="valorModelo"
        class="form-control"
        :class="{ 'is-invalid': error }"
        @input="$emit('update:valorModelo', $event.target.value)"
    />
    <div v-if="error" class="invalid-feedback">{{ error }}</div>
</template>

<script>
export default {
    props: {
        valorModelo: {
            type: String,
            required: true,
        },
        error: {
            type: String,
            default: '',
        },
    },
    emits: ['update:valorModelo'],
    mounted() {
        if (this.$refs.entrada?.hasAttribute('autofocus')) {
            this.$refs.entrada.focus();
        }
    },
    methods: {
        enfocar() {
            this.$refs.entrada?.focus();
        },
    },
}
</script>
