<template>
    <Head title="Panel de control" />
    <AuthenticatedLayout>
        <div class="container py-4">
            <!--
            ============================================
            MÓDULO 1: Panel de control
            ============================================
            Mejora este panel mostrando:
            - Tarjetas con estadísticas (total mascotas, especies, razas, clientes, citas)
            - Últimas mascotas registradas
            - Próximas citas
            - Enlaces rápidos a las secciones

            PanelController ya envía props: estadisticas, ultimasMascotas.
            Usa v-for, v-if, v-show, computed y $inertia.visit(route('...')).

            REFERENCIA: Mascota/Listado.vue (Inertia + axios).
            ============================================
            -->

            <div class="card shadow-sm">
                <div class="card-header">
                    <h1 class="h5 mb-0">Panel de control</h1>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Total de mascotas: <strong>{{ totalMascotas }}</strong></p>
                    <div v-show="sinUltimasMascotas" class="alert alert-secondary mb-0">
                        Aún no tienes mascotas registradas.
                    </div>
                    <ul v-show="!sinUltimasMascotas" class="list-group list-group-flush mb-3">
                        <li v-for="mascota in ultimasMascotas" :key="mascota.id" class="list-group-item px-0">
                            {{ mascota.nombre }}
                            <span v-show="mascota.sexo" class="text-muted"> · {{ mascota.sexo }}</span>
                            <span v-show="mascota.fecha_nacimiento_formato" class="text-muted d-block small">
                                {{ mascota.fecha_nacimiento_formato }}
                            </span>
                            <span v-show="mascota.creado_en" class="text-muted d-block small">{{ mascota.creado_en }}</span>
                        </li>
                    </ul>
                    <button type="button" class="btn btn-primary btn-sm" @click="$inertia.visit(route('mascotas.listado'))">
                        Ver mascotas
                    </button>
                </div>
            </div>

            <!-- TODO: Agregar sección de próximas citas -->
            <!-- TODO: Agregar sección de próximas citas -->
        </div>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from '@/Disenos/LayoutAutenticado.vue';
import { Head } from '@inertiajs/vue3';

export default {
    components: {
        AuthenticatedLayout,
        Head,
    },
    props: {
        estadisticas: {
            type: Object,
            default: () => ({}),
        },
        ultimasMascotas: {
            type: Array,
            default: () => [],
        },
    },
    computed: {
        totalMascotas() {
            return this.estadisticas.mascotas ?? 0
        },
        sinUltimasMascotas() {
            return this.ultimasMascotas.length === 0
        },
    },
}
</script>
