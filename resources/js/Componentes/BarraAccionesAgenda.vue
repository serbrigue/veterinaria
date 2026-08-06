<template>
    <!-- ================================================================================== -->
    <!-- COMPONENTE: BarraAccionesAgenda                                                    -->
    <!-- ================================================================================== -->
    <div>
        <!-- ------------------------------------------------------------------------------ -->
        <!-- SECCIÓN: CABECERA Y BOTONES DE ACCIÓN                                          -->
        <!-- ------------------------------------------------------------------------------ -->

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
                    <!-- Si se clickea el boton, se muestra el modal de Gestion de Horario -->
                    <button @click="mostrarHorario = true" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm hover-lift transition-all">
                        <i class="bi bi-calendar2-week me-1"></i> Gestionar Horario
                    </button>
                    <!-- Si se clickea el boton, se muestra el modal de Registro de Bloqueo -->
                    <button @click="mostrarBloqueo = true" class="btn btn-outline-danger rounded-pill px-4 fw-bold hover-shadow transition-all">
                        <i class="bi bi-shield-slash me-1"></i> Registrar Bloqueo
                    </button>
                </div>
            </div>
        </div>

        <!-- ------------------------------------------------------------------------------ -->
        <!-- SECCIÓN: COMPONENTES HIJOS (Modales dinámicos)                                 -->
        <!-- ------------------------------------------------------------------------------ -->
        
        <!-- COMPONENTE: ModalGestionHorario -->
        <!-- Su visibilidad depende del estado de 'mostrarHorario' -->
        <ModalGestionHorario
            :visible="mostrarHorario"
            :veterinario-id="veterinario.id"
            :planes-actuales="veterinario.horario || []"
            :especialidades="especialidades"
            :sucursales="sucursales"
            @cerrar="mostrarHorario = false"
            @guardado="recargarDatos(['veterinario'])"
        />

        <!-- COMPONENTE: ModalBloqueoHorario -->
        <!-- Su visibilidad depende del estado de 'mostrarBloqueo' -->
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
// ==================================================================================
// LÓGICA DEL COMPONENTE (VUE 3 - OPTIONS API)
// ==================================================================================
import ModalGestionHorario from '@/Componentes/ModalGestionHorario.vue';
import ModalBloqueoHorario from '@/Componentes/ModalBloqueoHorario.vue';

// ------------------------------------------------------------------------------
// EXPORT DEFAULT: Definición principal del componente
// ------------------------------------------------------------------------------
export default {
    name: 'BarraAccionesAgenda',
    
    // COMPONENTES: Registro de los modales importados
    components: {
        ModalGestionHorario,
        ModalBloqueoHorario,
    },
    
    // PROPIEDADES: Datos inyectados desde el componente padre
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

    // ESTADO REACTIVO: Variables locales que disparan renderizados al cambiar
    data() {
        return {
            mostrarHorario: false, // Activa/desactiva la vista del modal de horario
            mostrarBloqueo: false, // Activa/desactiva la vista del modal de bloqueo
        };
    },

    // MÉTODOS: Bloque de funciones y disparadores
    methods: {
        // Método para recargar datos desde Inertia sin refrescar la página.
        recargarDatos(propiedades) {
            this.$inertia.reload({ only: propiedades });
        },
    },
};
</script>
