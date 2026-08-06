<template>
    <!-- ================================================================================== -->
    <!-- COMPONENTE: Editar -->
    <!-- ================================================================================== -->
    <Head title="Panel de Usuario" />
    <AuthenticatedLayout>
        <div class="container py-4 py-lg-5">
            <!-- Cabecera del Panel -->
            <div class="row mb-4 align-items-center">
                <div class="col-md-8">
                    <h1 class="h2 fw-bold text-dark mb-1">Mi Panel de Usuario</h1>
                    <p class="text-muted mb-0">Bienvenido/a, <strong>{{ usuario.name }}</strong>. Aquí puedes gestionar tu cuenta, mascotas y servicios.</p>
                </div>
            </div>

            <div class="row g-4">
                <!-- Columna Izquierda: Widgets del Dashboard por Rol -->
                <div class="col-lg-7 col-xl-8">
                    <!--Si es cliente se muestra el dashboard del cliente -->
                    <DashboardCliente 
                        v-if="$isCliente()" 
                        :proximas-citas="proximasCitas" 
                        :historial-clinico="historialClinico" 
                        :mascota="mascota" 
                        :veterinario="veterinario" 
                    />
                    <!--Si es veterinario se muestra el dashboard del veterinario -->
                    <DashboardVeterinario 
                        v-else-if="$isVeterinario()" 
                        :proxima-cita-vet="proximaCitaVet" 
                        :veterinario="veterinario" 
                    />
                    <!--Si es admin se muestra el dashboard del admin -->
                    <DashboardAdmin 
                        v-else-if="$isAdmin()" 
                    />
                    <!--Si es secretaria se muestra el dashboard del secretaria -->
                    <DashboardSecretaria 
                        v-else-if="$isSecretaria()" 
                    />
                </div>

                <!-- Columna Derecha: Configuración de la Cuenta -->
                <div class="col-lg-5 col-xl-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-light bg-opacity-50 border-bottom border-light pt-4 px-4 pb-3 rounded-top-4">
                            <h3 class="h5 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                <i class="bi bi-person-gear text-secondary"></i> Ajustes de Cuenta
                            </h3>
                            <p class="small text-muted mb-0 mt-1">Configura tu perfil y seguridad.</p>
                        </div>
                        <div class="card-body p-4">
                            <div class="accordion custom-accordion" id="accordionPerfil">
                                <!-- Info Personal -->
                                <div class="accordion-item bg-transparent">
                                    <h2 class="accordion-header">
                                        <!-- Si se hace clic en el botón, se cambia el estado de la pestaña -->
                                        <button class="accordion-button bg-transparent fw-semibold text-dark px-1 py-3" :class="{'collapsed': tabActiva !== 'info'}" type="button" @click="tabActiva = tabActiva === 'info' ? '' : 'info'">
                                            <i class="bi bi-person-lines-fill me-2 text-primary"></i> Información Personal
                                        </button>
                                    </h2>
                                    <!-- Se muestra la información personal si la pestaña está activa -->
                                    <div class="accordion-collapse collapse" :class="{'show': tabActiva === 'info'}">
                                        <div class="accordion-body px-1 pt-2 pb-4">
                                            <ActualizarInformacion :must-verify-email="mustVerifyEmail" :status="status" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Seguridad -->
                                <div class="accordion-item bg-transparent">
                                    <h2 class="accordion-header">
                                        <!-- Si se hace clic en el botón, se cambia el estado de la pestaña -->
                                        <button class="accordion-button bg-transparent fw-semibold text-dark px-1 py-3" :class="{'collapsed': tabActiva !== 'seguridad'}" type="button" @click="tabActiva = tabActiva === 'seguridad' ? '' : 'seguridad'">
                                            <i class="bi bi-shield-lock-fill me-2 text-success"></i> Seguridad y Contraseña
                                        </button>
                                    </h2>
                                    <!-- Se muestra la seguridad si la pestaña está activa -->
                                    <div class="accordion-collapse collapse" :class="{'show': tabActiva === 'seguridad'}">
                                        <div class="accordion-body px-1 pt-2 pb-4">
                                            <ActualizarContrasena />
                                        </div>
                                    </div>
                                </div>

                                <!-- Eliminar Cuenta -->
                                <div class="accordion-item bg-transparent border-0">
                                    <h2 class="accordion-header">
                                        <!-- Si se hace clic en el botón, se cambia el estado de la pestaña -->
                                        <button class="accordion-button bg-transparent fw-semibold text-danger px-1 py-3" :class="{'collapsed': tabActiva !== 'eliminar'}" type="button" @click="tabActiva = tabActiva === 'eliminar' ? '' : 'eliminar'">
                                            <i class="bi bi-exclamation-triangle-fill me-2"></i> Zona de Peligro
                                        </button>
                                    </h2>
                                    <!-- Se muestra la eliminación de cuenta si la pestaña está activa -->
                                    <div class="accordion-collapse collapse" :class="{'show': tabActiva === 'eliminar'}">
                                        <div class="accordion-body px-1 pt-2 pb-0">
                                            <EliminarUsuario />
                                        </div>
                                    </div>
                                </div>


                                
                                <!-- Finanzas del Veterinario -->
                                <!-- Si es veterinario y tiene cotizaciones se muestra el dashboard de finanzas -->
                                <div class="accordion-item bg-transparent border-0" v-if="$isVeterinario() && cotizaciones">
                                    <h2 class="accordion-header">
                                        <!-- Si se hace clic en el botón, se cambia el estado de la pestaña -->
                                        <button class="accordion-button bg-transparent fw-semibold text-success px-1 py-3" :class="{'collapsed': tabActiva !== 'finanzas'}" type="button" @click="tabActiva = tabActiva === 'finanzas' ? '' : 'finanzas'">
                                            <i class="bi bi-cash-stack me-2"></i> Mis Finanzas (Cotizaciones)
                                        </button>
                                    </h2>
                                    <div class="accordion-collapse collapse" :class="{'show': tabActiva === 'finanzas'}">
                                        <div class="accordion-body px-1 pt-2 pb-0">
                                            <!-- Si no tiene cotizaciones, se muestra un mensaje -->
                                            <div v-if="cotizaciones.length === 0" class="text-center py-5 bg-light rounded-4">
                                                <i class="bi bi-wallet2 text-muted display-4 d-block mb-3 opacity-50"></i>
                                                <p class="text-muted mb-0">No hay ganancias registradas todavía.</p>
                                            </div>
                                            <!-- Si tiene cotizaciones, se muestra la tabla -->
                                            <div v-else class="table-responsive">
                                                <table class="table table-hover align-middle mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th class="px-2 py-3 small">Mes</th>
                                                            <th class="px-2 py-3 small text-end">Comisión</th>
                                                            <th class="px-2 py-3 small">Estado</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <!-- Iteramos sobre las cotizaciones para mostrarlas en la tabla -->
                                                        <tr v-for="(cot, index) in cotizaciones" :key="index" class="border-bottom border-light">
                                                            <td class="px-2 py-3 fw-bold text-dark text-capitalize small">
                                                                {{ cot.mes_nombre }}<br>
                                                                <span class="text-muted fw-normal">{{ cot.citas_count }} citas</span>
                                                            </td>
                                                            <td class="px-2 py-3 fw-bold text-success text-end small">
                                                                ${{ Number(cot.comision_calculada).toLocaleString('es-CL') }}
                                                            </td>
                                                            <td class="px-2 py-3 small">
                                                                <!-- Si la cotización está pagada, se muestra un badge verde -->
                                                                <span v-if="cot.estado === 'pagado'" class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1 fw-medium">
                                                                    <i class="bi bi-check-circle-fill"></i> Pagado
                                                                </span>
                                                                <!-- Si la cotización está pendiente, se muestra un badge amarillo -->
                                                                <span v-else class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-2 py-1 fw-medium">
                                                                    <i class="bi bi-clock-fill"></i> Pendiente
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        

    </AuthenticatedLayout>
</template>

<style scoped>
.custom-accordion .accordion-button:not(.collapsed) {
    background-color: transparent;
    color: var(--bs-primary);
    box-shadow: none;
}
.custom-accordion .accordion-button:focus {
    box-shadow: none;
}
</style>

<script>
// ==================================================================================
// LÓGICA DEL COMPONENTE (VUE 3)
// ==================================================================================

import AuthenticatedLayout from '@/Disenos/LayoutAutenticado.vue';
import EliminarUsuario from './Partials/EliminarUsuario.vue';
import ActualizarContrasena from './Partials/ActualizarContrasena.vue';
import ActualizarInformacion from './Partials/ActualizarInformacion.vue';
import DashboardCliente from './Partials/DashboardCliente.vue';
import DashboardVeterinario from './Partials/DashboardVeterinario.vue';
import DashboardAdmin from './Partials/DashboardAdmin.vue';
import DashboardSecretaria from './Partials/DashboardSecretaria.vue';
import { Head } from '@inertiajs/vue3';

// ------------------------------------------------------------------------------
// EXPORT DEFAULT: Definición principal del componente
// ------------------------------------------------------------------------------
export default {
    name: 'Editar',
    // COMPONENTES:Registro de componentes importados
    components: {
        AuthenticatedLayout,
        EliminarUsuario,
        ActualizarContrasena,
        ActualizarInformacion,
        DashboardCliente,
        DashboardVeterinario,
        DashboardAdmin,
        DashboardSecretaria,
        Head,
    },
    // PROPIEDADES (PROPS): Datos inyectados desde el componente padre o estado
    props: {
        proximasCitas: {
            type: Object,
        },
        proximaCitaVet: {
            type: Object,
        },
        historialClinico: {
            type: Object,
        },
        mascota: {
            type: Object,
        },  
        veterinario: {
            type: Object,
        },
        mustVerifyEmail: {
            type: Boolean,
        },
        status: {
            type: String,
        },
        cotizaciones: {
            type: Array,
        }
    },
    // ESTADO REACTIVO (DATA): Variables locales del componente
    data() {
        return {
            tabActiva: 'info',
        }
    },
    // PROPIEDADES COMPUTA_AS (COMPUTED): Variables reactivas que dependen de otras
    computed: {
        usuario() {
            return this.$page.props.auth.user;
        }
    }
}
</script>
