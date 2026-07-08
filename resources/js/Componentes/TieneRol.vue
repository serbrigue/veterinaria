<template>
    <slot v-if="permitido" />
    <slot v-else name="denegado">
        <div v-if="mostrarMensaje" class="alert alert-warning d-flex align-items-center gap-2 rounded-4 shadow-sm border-0 p-4" role="alert">
            <i class="bi bi-shield-lock-fill fs-3 text-warning"></i>
            <div>
                <h4 class="alert-heading h6 fw-bold mb-1">Acceso Restringido</h4>
                <p class="small mb-0 text-secondary">No cuentas con los privilegios necesarios para ver esta sección.</p>
            </div>
        </div>
    </slot>
</template>

<script>
export default {
    name: 'TieneRol',
    props: {
        rol: {
            type: [String, Array],
            required: true
        },
        mostrarMensaje: {
            type: Boolean,
            default: false
        }
    },
    computed: {
        permitido() {
            return this.$hasRole(this.rol);
        }
    }
}
</script>
