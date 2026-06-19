<template>
    <GuestLayout>
        <Head title="Confirmar contraseña" />

        <div class="card shadow-sm">
            <div class="card-header">Confirmar contraseña</div>
            <div class="card-body">
                <p class="text-muted mb-4">
                    Esta es una área segura de la aplicación. Por favor, confirma tu contraseña antes de continuar.
                </p>

                <div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input
                            id="password"
                            v-model="formulario.password"
                            type="password"
                            class="form-control"
                            :class="{ 'is-invalid': formulario.errors.password }"
                            required
                            autocomplete="current-password"
                            autofocus
                        />
                        <div v-if="formulario.errors.password" class="invalid-feedback">{{ formulario.errors.password }}</div>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-primary" :disabled="formulario.processing" @click="guardar">
                            <span v-if="formulario.processing" class="spinner-border spinner-border-sm me-2" />
                            Confirmar
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
    data() {
        return {
            formulario: {
                password: '',
                errors: {},
                processing: false,
            },
        }
    },
    methods: {
        guardar() {
            this.formulario.processing = true
            this.formulario.errors = {}
            axios.post('/api/confirmar-contrasena', {
                password: this.formulario.password,
            })
            .then((response) => {
                window.location.href = response.data.redirect || route('panel')
            })
            .catch((error) => {
                if (error.response?.status === 422) {
                    this.formulario.errors = error.response.data.errors
                }
            })
            .finally(() => {
                this.formulario.processing = false
                this.formulario.password = ''
            })
        },
    },
}
</script>
