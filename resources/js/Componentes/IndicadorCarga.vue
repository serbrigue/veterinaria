<template>
    <!-- ================================================================================== -->
    <!-- COMPONENTE: IndicadorCarga -->
    <!-- ================================================================================== -->
    <!-- DIRECTIVA (v-if): Renderizado condicional basado en "cargando" -->
    <div v-if="cargando" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
        <p class="mt-3 text-muted fw-semibold">Cargando {{ mensaje }}...</p>
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
    name: 'IndicadorCarga',
    // PROPIEDADES (PROPS): Datos inyectados desde el componente padre o estado
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
