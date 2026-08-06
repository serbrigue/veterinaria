<template>
    <!-- ================================================================================== -->
    <!-- COMPONENTE: EstadoVacio -->
    <!-- ================================================================================== -->


    <!-- Si "visible" es true, se muestra el contenido -->
    <div v-if="visible" class="text-center py-5 bg-light rounded-4 border border-dashed">
        <!-- Muestra el icono proporcionado -->
        <i :class="icono" class="text-muted display-4 d-block mb-3 opacity-50"></i>

        <!-- Muestra el mensaje proporcionado -->
        <p class="text-muted mb-3 fw-medium">{{ mensaje }}</p>

        <!-- Muestra el boton si se proporciona un texto de boton -->
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
// ==================================================================================
// LÓGICA DEL COMPONENTE (VUE 3)
// ==================================================================================

// ------------------------------------------------------------------------------
// EXPORT DEFAULT: Definición principal del componente
// ------------------------------------------------------------------------------
export default {
    name: 'EstadoVacio',
    // PROPIEDADES: Datos inyectados desde el componente padre o estado
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
    // EVENTOS: Eventos que se pueden emitir desde el componente
    emits: ['accion'],
}
</script>
