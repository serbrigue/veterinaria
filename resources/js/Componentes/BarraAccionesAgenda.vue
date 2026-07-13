<template>
    <div>
        <!-- Barra de Acciones -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4 d-flex flex-wrap gap-3 align-items-center justify-content-between">
                <div>
                    <h5 class="mb-1 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-gear-fill text-primary"></i>
                        Gestionar Agenda
                    </h5>
                    <p class="text-muted small mb-0">Configura horarios de atención y registra bloqueos o ausencias.</p>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <button @click="mostrarHorario = true" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm hover-lift transition-all">
                        <i class="bi bi-calendar2-week me-1"></i> Gestionar Horario
                    </button>
                    <button @click="mostrarBloqueo = true" class="btn btn-outline-danger rounded-pill px-4 fw-bold hover-shadow transition-all">
                        <i class="bi bi-shield-slash me-1"></i> Registrar Bloqueo
                    </button>
                </div>
            </div>
        </div>

        <!-- Modales -->
        <ModalGestionHorario
            :visible="mostrarHorario"
            :veterinario-id="veterinario.id"
            :planes-actuales="veterinario.horario || []"
            :especialidades="especialidades"
            :sucursales="sucursales"
            @cerrar="mostrarHorario = false"
            @guardado="recargarDatos(['veterinario'])"
        />

        <ModalBloqueoHorario
            :visible="mostrarBloqueo"
            :veterinario-id="veterinario.id"
            :especialidades="especialidades"
            :sucursales="sucursales"
            @cerrar="mostrarBloqueo = false"
            @guardado="recargarDatos(['bloqueos'])"
        />
    </div>
</template>

<script>
import ModalGestionHorario from '@/Componentes/ModalGestionHorario.vue';
import ModalBloqueoHorario from '@/Componentes/ModalBloqueoHorario.vue';

export default {
    name: 'BarraAccionesAgenda',
    components: {
        ModalGestionHorario,
        ModalBloqueoHorario,
    },
    props: {
        veterinario: {
            type: Object,
            required: true,
        },
        especialidades: {
            type: Array,
            default: () => [],
        },
        sucursales: {
            type: Array,
            default: () => [],
        },
    },

    data() {
        return {
            mostrarHorario: false,
            mostrarBloqueo: false,
        };
    },

    methods: {
        recargarDatos(propiedades) {
            this.$inertia.reload({ only: propiedades });
        },
    },
};
</script>
