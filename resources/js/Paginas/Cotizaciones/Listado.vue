<script setup>
import { Head, Link } from '@inertiajs/vue3';

// Props básicos esperados
const props = defineProps({
    cotizaciones: {
        type: Array,
        default: () => []
    }
});
</script>

<template>
    <Head title="Listado de Cotizaciones" />

    <div class="container-fluid py-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Listado de Cotizaciones</h6>
            </div>
            <div class="card-body">
                <p class="text-muted text-sm">
                    Vista en construcción. Aquí se listarán las cotizaciones.
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Estilos específicos del componente */
</style>
