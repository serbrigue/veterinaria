<template>
    <template v-if="visible">
        <div class="modal fade show d-block" tabindex="-1" @click.self="manejarCierre">
            <div class="modal-dialog">
                <slot />
            </div>
        </div>
        <div class="modal-backdrop fade show" />
    </template>
</template>

<script>
export default {
    props: {
        visible: {
            type: Boolean,
            default: false,
        },
        cerrable: {
            type: Boolean,
            default: true,
        },
    },
    emits: ['cerrar'],
    methods: {
        manejarCierre() {
            if (this.cerrable) {
                this.$emit('cerrar');
            }
        },
    },
}
</script>
