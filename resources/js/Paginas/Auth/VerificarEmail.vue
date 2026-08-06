<template>
    <!-- ================================================================================== -->
    <!-- COMPONENTE: VerificarEmail -->
    <!-- ================================================================================== -->
    <GuestLayout>
        <Head title="Verificación de email" />

        <div class="card shadow-sm">
            <div class="card-header">Verificación de correo electrónico</div>
            <div class="card-body">
                <p class="text-muted mb-4">
                    Gracias por registrarte! Antes de empezar, podrías verificar tu dirección de correo electrónico haciendo clic en el enlace
                    que te acabamos de enviar? Si no recibiste el correo, te enviaremos otro.
                </p>

                <!-- Si se solicita reenviar el correo de verificación -->
                <div v-if="enlaceVerificacionEnviado" class="alert alert-success mb-4">
                    Un nuevo enlace de verificación ha sido enviado a la dirección de correo electrónico que proporcionaste durante el registro.
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <button type="button" class="btn btn-primary" :disabled="formulario.processing" @click="guardar">
                        <!-- Si el formulario está procesando, muestra un spinner -->
                        <span v-if="formulario.processing" class="spinner-border spinner-border-sm me-2" />
                        Reenviar correo de verificación
                    </button>
                    <button type="button" class="btn btn-link" :disabled="formulario.processing" @click="cerrarSesion">Cerrar sesión</button>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<script>
// ==================================================================================
// LÓGICA DEL COMPONENTE (VUE 3)
// ==================================================================================

import GuestLayout from '@/Disenos/LayoutInvitado.vue';
import { Head } from '@inertiajs/vue3';

// ------------------------------------------------------------------------------
// EXPORT DEFAULT: Definición principal del componente
// ------------------------------------------------------------------------------
export default {
    // COMPONENTES: Registro de componentes importados
    components: {
        GuestLayout,
        Head,
    },
    // PROPIEDADES: Datos inyectados desde el componente padre o estado
    props: {
        status: {
            type: String,
        },
    },
    // ESTADO REACTIVO: Variables locales del componente
    data() {
        return {
            formulario: {
                processing: false,
            },
        }
    },
    // PROPIEDADES COMPUTADAS: Variables reactivas que dependen de otras
    computed: {
        // Verifica si se solicitó reenviar el correo de verificación
        enlaceVerificacionEnviado() {
            return this.status === 'verification-link-sent';
        },
    },
    // MÉTODOS: Bloque de funciones y eventos
    methods: {
        //Maneja el envio de correo de verificación
        guardar() {
            // Establece la variable "processing" a true
            this.formulario.processing = true
            // Envía una solicitud POST a la API para reenviar el correo de verificación
            axios.post('/api/verificacion/enviar')
            .then(() => {
                // Si la solicitud es exitosa, recarga la página
                window.location.reload()
            })
            .finally(() => {
                // Si la solicitud falla, recarga la página
                this.formulario.processing = false
            })
        },
        //Maneja el cierre de sesión
        cerrarSesion() {
            // Envía una solicitud POST a la API para cerrar la sesión
            axios.post('/api/cerrar-sesion')
            .then((response) => {
                window.location.href = response.data.redirect || '/'
            })
        },
    },
}
</script>
