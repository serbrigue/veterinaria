<template>
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
                    <DashboardCliente 
                        v-if="$isCliente()" 
                        :proximas-citas="proximasCitas" 
                        :historial-clinico="historialClinico" 
                        :mascota="mascota" 
                        :veterinario="veterinario" 
                    />
                    <DashboardVeterinario 
                        v-else-if="$isVeterinario()" 
                        :proxima-cita-vet="proximaCitaVet" 
                        :veterinario="veterinario" 
                    />
                    <DashboardAdmin 
                        v-else-if="$isAdmin()" 
                    />
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
                                        <button class="accordion-button bg-transparent fw-semibold text-dark px-1 py-3" :class="{'collapsed': tabActiva !== 'info'}" type="button" @click="tabActiva = tabActiva === 'info' ? '' : 'info'">
                                            <i class="bi bi-person-lines-fill me-2 text-primary"></i> Información Personal
                                        </button>
                                    </h2>
                                    <div class="accordion-collapse collapse" :class="{'show': tabActiva === 'info'}">
                                        <div class="accordion-body px-1 pt-2 pb-4">
                                            <ActualizarInformacion :must-verify-email="mustVerifyEmail" :status="status" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Seguridad -->
                                <div class="accordion-item bg-transparent">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button bg-transparent fw-semibold text-dark px-1 py-3" :class="{'collapsed': tabActiva !== 'seguridad'}" type="button" @click="tabActiva = tabActiva === 'seguridad' ? '' : 'seguridad'">
                                            <i class="bi bi-shield-lock-fill me-2 text-success"></i> Seguridad y Contraseña
                                        </button>
                                    </h2>
                                    <div class="accordion-collapse collapse" :class="{'show': tabActiva === 'seguridad'}">
                                        <div class="accordion-body px-1 pt-2 pb-4">
                                            <ActualizarContrasena />
                                        </div>
                                    </div>
                                </div>

                                <!-- Eliminar Cuenta -->
                                <div class="accordion-item bg-transparent border-0">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button bg-transparent fw-semibold text-danger px-1 py-3" :class="{'collapsed': tabActiva !== 'eliminar'}" type="button" @click="tabActiva = tabActiva === 'eliminar' ? '' : 'eliminar'">
                                            <i class="bi bi-exclamation-triangle-fill me-2"></i> Zona de Peligro
                                        </button>
                                    </h2>
                                    <div class="accordion-collapse collapse" :class="{'show': tabActiva === 'eliminar'}">
                                        <div class="accordion-body px-1 pt-2 pb-0">
                                            <EliminarUsuario />
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
import AuthenticatedLayout from '@/Disenos/LayoutAutenticado.vue';
import EliminarUsuario from './Partials/EliminarUsuario.vue';
import ActualizarContrasena from './Partials/ActualizarContrasena.vue';
import ActualizarInformacion from './Partials/ActualizarInformacion.vue';
import DashboardCliente from './Partials/DashboardCliente.vue';
import DashboardVeterinario from './Partials/DashboardVeterinario.vue';
import DashboardAdmin from './Partials/DashboardAdmin.vue';
import DashboardSecretaria from './Partials/DashboardSecretaria.vue';
import { Head } from '@inertiajs/vue3';

export default {
    name: 'Editar',
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
    },
    data() {
        return {
            tabActiva: 'info',
        }
    },
    computed: {
        usuario() {
            return this.$page.props.auth.user;
        }
    }
}
</script>
