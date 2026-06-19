<template>
    <GuestLayout>
        <Head title="Verificación de email" />

        <div class="card shadow-sm">
            <div class="card-header">Verificación de correo electrónico</div>
            <div class="card-body">
                <p class="text-muted mb-4">
                    Gracias por registrarte! Antes de empezar, podrías verificar tu dirección de correo electrónico haciendo clic en el enlace
                    que te acabamos de enviar? Si no recibiste el correo, te enviaremos otro.
                </p>

                <div v-if="enlaceVerificacionEnviado" class="alert alert-success mb-4">
                    Un nuevo enlace de verificación ha sido enviado a la dirección de correo electrónico que proporcionaste durante el registro.
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <button type="button" class="btn btn-primary" :disabled="formulario.processing" @click="guardar">
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
import GuestLayout from '@/Disenos/LayoutInvitado.vue';
import { Head } from '@inertiajs/vue3';

export default {
    components: {
        GuestLayout,
        Head,
    },
    props: {
        status: {
            type: String,
        },
    },
    data() {
        return {
            formulario: {
                processing: false,
            },
        }
    },
    computed: {
        enlaceVerificacionEnviado() {
            return this.status === 'verification-link-sent';
        },
    },
    methods: {
        guardar() {
            this.formulario.processing = true
            axios.post('/api/verificacion/enviar')
            .then(() => {
                window.location.reload()
            })
            .finally(() => {
                this.formulario.processing = false
            })
        },
        cerrarSesion() {
            axios.post('/api/cerrar-sesion')
            .then((response) => {
                window.location.href = response.data.redirect || '/'
            })
        },
    },
}
</script>
