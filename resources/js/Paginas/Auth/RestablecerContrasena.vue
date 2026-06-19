<template>
    <GuestLayout>
        <Head title="Restablecer contraseña" />

        <div class="card shadow-sm">
            <div class="card-header">Restablecer contraseña</div>
            <div class="card-body">
                <div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electrónico</label>
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
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input
                            id="password"
                            v-model="formulario.password"
                            type="password"
                            class="form-control"
                            :class="{ 'is-invalid': formulario.errors.password }"
                            required
                            autocomplete="new-password"
                        />
                        <div v-if="formulario.errors.password" class="invalid-feedback">{{ formulario.errors.password }}</div>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
                        <input
                            id="password_confirmation"
                            v-model="formulario.password_confirmation"
                            type="password"
                            class="form-control"
                            :class="{ 'is-invalid': formulario.errors.password_confirmation }"
                            required
                            autocomplete="new-password"
                        />
                        <div v-if="formulario.errors.password_confirmation" class="invalid-feedback">{{ formulario.errors.password_confirmation }}</div>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-primary" :disabled="formulario.processing" @click="guardar">
                            <span v-if="formulario.processing" class="spinner-border spinner-border-sm me-2" />
                            Restablecer contraseña
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
        email: {
            type: String,
            required: true,
        },
        token: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            formulario: {
                token: '',
                email: '',
                password: '',
                password_confirmation: '',
                errors: {},
                processing: false,
            },
        }
    },
    created() {
        this.formulario.token = this.token
        this.formulario.email = this.email
    },
    methods: {
        guardar() {
            this.formulario.processing = true
            this.formulario.errors = {}
            axios.post('/api/restablecer-contrasena', {
                token: this.formulario.token,
                email: this.formulario.email,
                password: this.formulario.password,
                password_confirmation: this.formulario.password_confirmation,
            })
            .then((response) => {
                window.location.href = response.data.redirect || route('iniciar-sesion')
            })
            .catch((error) => {
                if (error.response?.status === 422) {
                    this.formulario.errors = error.response.data.errors
                }
            })
            .finally(() => {
                this.formulario.processing = false
                this.formulario.password = ''
                this.formulario.password_confirmation = ''
            })
        },
    },
}
</script>
