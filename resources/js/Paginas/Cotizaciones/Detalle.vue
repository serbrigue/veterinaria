<script setup>
import { Head, Link } from '@inertiajs/vue3';

// Props básicos esperados (puedes ajustarlos luego)
const props = defineProps({
    cotizacion: {
        type: Object,
        default: () => ({})
    }
});
</script>

<template>
    <Head title="Detalle de Cotización" />

    <div class="container-fluid py-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h6 class="mb-0">Detalle de Cotización</h6>
            </div>
            <div class="card-body">
                <p class="text-muted text-sm">
                    Vista en construcción. Aquí irá el detalle de la cotización.
                </p>
                <Link href="/cotizaciones" class="btn btn-secondary btn-sm mt-3">
                    Volver
                </Link>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Estilos específicos del componente */
</style>
