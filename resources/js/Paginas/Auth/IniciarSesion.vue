<template>
    <GuestLayout>
        <Head title="Iniciar Sesión" />
        
        <div class="mb-5">
            <h3 class="fw-bold text-dark mb-1">¡Bienvenido de nuevo!</h3>
            <p class="text-secondary">Por favor, ingresa tus credenciales para acceder a tu cuenta.</p>
        </div>

        <form @submit.prevent="guardar">
            <div class="form-floating mb-3">
                <input
                    id="email"
                    v-model="formulario.email"
                    type="email"
                    class="form-control"
                    :class="{ 'is-invalid': formulario.errors.email }"
                    placeholder="name@example.com"
                    required
                    autofocus
                />
                <label for="email" class="text-muted">Correo electrónico</label>
                <div v-if="formulario.errors.email" class="invalid-feedback">{{ formulario.errors.email }}</div>
            </div>

            <div class="form-floating mb-4">
                <input
                    id="password"
                    v-model="formulario.password"
                    type="password"
                    class="form-control"
                    :class="{ 'is-invalid': formulario.errors.password }"
                    placeholder="Contraseña"
                    required
                />
                <label for="password" class="text-muted">Contraseña</label>
                <div v-if="formulario.errors.password" class="invalid-feedback">{{ formulario.errors.password }}</div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input id="remember" v-model="formulario.remember" type="checkbox" class="form-check-input shadow-none" />
                    <label for="remember" class="form-check-label text-secondary" style="font-size: 0.9rem;">Recordarme</label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-3 shadow-sm btn-hover-effect" :disabled="formulario.processing">
                <span v-if="formulario.processing" class="spinner-border spinner-border-sm me-2" />
                Ingresar
            </button>
            
            <div class="text-center mt-4">
                <p class="text-secondary" style="font-size: 0.95rem;">
                    ¿No tienes una cuenta?
                    <Link :href="route('registrarse')" class="text-primary fw-semibold text-decoration-none ms-1">Regístrate aquí</Link>
                </p>
            </div>
        </form>
    </GuestLayout>
</template>

<script>
import GuestLayout from '@/Disenos/LayoutInvitado.vue'
import { Head, Link } from '@inertiajs/vue3'

export default {
    components: {
        GuestLayout,
        Head,
        Link,
    },
    data() {
        return {
            formulario: {
                email: '',
                password: '',
                remember: false,
                errors: {},
                processing: false,
            },
        }
    },
    methods: {
        guardar() {
            this.formulario.processing = true
            this.formulario.errors = {}
            axios.post('/api/iniciar-sesion', {
                email: this.formulario.email,
                password: this.formulario.password,
                remember: this.formulario.remember,
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
            })
        },
    },
}
</script>

<style scoped>
.form-control {
    border-radius: 0.5rem;
}
.form-control:focus {
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
    border-color: #86b7fe;
}
.btn-hover-effect {
    transition: all 0.3s ease;
}
.btn-hover-effect:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px -10px rgba(13, 110, 253, 0.5) !important;
}
</style>
