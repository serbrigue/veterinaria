<template>
    <GuestLayout>
        <Head title="Olvidé mi contraseña" />

        <div class="card shadow-sm">
            <div class="card-header">Recuperar contraseña</div>
            <div class="card-body">
                <p class="text-muted mb-4">
                    Olvidaste tu contraseña? No hay problema. Simplemente dínos tu dirección de correo electrónico y te enviaremos un enlace de restablecimiento de contraseña que te permitirá elegir una nueva.
                </p>

                <div v-if="status" class="alert alert-success mb-4">{{ status }}</div>

                <div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input
                            id="email"
                            v-model="formulario.email"
                            type="email"
                            class="form-control"
                            :class="{ 'is-invalid': formulario.errors.email }"
                            required
                            autofocus
                            autocomplete="username"
                        />
                        <div v-if="formulario.errors.email" class="invalid-feedback">{{ formulario.errors.email }}</div>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-primary" :disabled="formulario.processing" @click="guardar">
                            <span v-if="formulario.processing" class="spinner-border spinner-border-sm me-2" />
                            Enviar enlace de restablecimiento de contraseña
                        </button>
                    </div>
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
                email: '',
                errors: {},
                processing: false,
            },
        }
    },
    methods: {
        guardar() {
            this.formulario.processing = true
            this.formulario.errors = {}
            axios.post('/api/recuperar-contrasena', {
                email: this.formulario.email,
            })
            .then(() => {
                window.location.reload()
            })
            .catch((error) => {
                if (error.response?.status === 422) {
                    this.formulario.errors = error.response.data.errors
                }
            })
            .finally(() => {
                this.formulario.processing = false
            })
        },
    },
}
</script>
