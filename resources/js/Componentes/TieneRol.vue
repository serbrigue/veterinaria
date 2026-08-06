<template>
    <!-- ================================================================================== -->
    <!-- COMPONENTE: TieneRol -->
    <!-- ================================================================================== -->
    
    <!-- El componente "TieneRol" permite validar si un usuario tiene un rol determinado -->
    <!-- Si el usuario tiene el rol, se renderiza el contenido del slot -->
    <!-- Si el usuario no tiene el rol, se renderiza el contenido del slot "denegado" -->

    <slot v-if="permitido" />
    <slot v-else name="denegado">
        <!-- Renderizado condicional basado en "mostrarMensaje" -->
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
// ==================================================================================
// LÓGICA DEL COMPONENTE (VUE 3)
// ==================================================================================

// Definición principal del componente
export default {
    name: 'TieneRol',
    // PROPIEDADES: Datos inyectados desde el componente padre o estado
    props: {
        //Rol que se debe tener para acceder al contenido
        rol: {
            type: [String, Array],
            required: true
        },
        //Mostrar mensaje de acceso denegado
        mostrarMensaje: {
            type: Boolean,
            default: false
        }
    },
    // PROPIEDADES COMPUTADAS: Variables reactivas que dependen de otras
    computed: { 
        //Método que permite validar si el usuario tiene el rol
        permitido() {
            return this.$hasRole(this.rol);
        }
    }
}
</script>
