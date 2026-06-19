<template>
    <GuestLayout>
        <Head title="Registrarse" />

        <div class="card shadow-sm">
            <div class="card-header">Registrar</div>
            <div class="card-body">
                <div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre</label>
                        <input
                            id="name"
                            v-model="formulario.name"
                            type="text"
                            class="form-control"
                            :class="{ 'is-invalid': formulario.errors.name }"
                            required
                            autofocus
                            autocomplete="name"
                        />
                        <div v-if="formulario.errors.name" class="invalid-feedback">{{ formulario.errors.name }}</div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <input
                            id="email"
                            v-model="formulario.email"
                            type="email"
                            class="form-control"
                            :class="{ 'is-invalid': formulario.errors.email }"
                            required
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
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <Link :href="route('iniciar-sesion')" class="text-decoration-none">Ya tienes una cuenta?</Link>
                        <button type="button" class="btn btn-primary" :disabled="formulario.processing" @click="guardar">
                            <span v-if="formulario.processing" class="spinner-border spinner-border-sm me-2" />
                            Registrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<script>
import GuestLayout from '@/Disenos/LayoutInvitado.vue';
import { Head, Link } from '@inertiajs/vue3';

export default {
    components: {
        GuestLayout,
        Head,
        Link,
    },
    data() {
        return {
            formulario: {
                name: '',
                email: '',
                password: '',
                password_confirmation: '',
                errors: {},
                processing: false,
            },
        }
    },
    methods: {
        guardar() {
            this.formulario.processing = true
            this.formulario.errors = {}
            axios.post('/api/registrarse', {
                name: this.formulario.name,
                email: this.formulario.email,
                password: this.formulario.password,
                password_confirmation: this.formulario.password_confirmation,
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
                this.formulario.password_confirmation = ''
            })
        },
    },
}
</script>
