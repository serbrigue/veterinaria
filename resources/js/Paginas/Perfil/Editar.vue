<template>
    <Head title="Perfil" />

    <AuthenticatedLayout>
        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-6">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <ActualizarInformacion
                                :must-verify-email="mustVerifyEmail"
                                :status="status"
                            />
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <ActualizarContrasena />
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <EliminarUsuario />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from '@/Disenos/LayoutAutenticado.vue';
import EliminarUsuario from './Partials/EliminarUsuario.vue';
import ActualizarContrasena from './Partials/ActualizarContrasena.vue';
import ActualizarInformacion from './Partials/ActualizarInformacion.vue';
import { Head } from '@inertiajs/vue3';

export default {
    components: {
        AuthenticatedLayout,
        EliminarUsuario,
        ActualizarContrasena,
        ActualizarInformacion,
        Head,
    },
    props: {
        mustVerifyEmail: {
            type: Boolean,
        },
        status: {
            type: String,
        },
    },
}
</script>
