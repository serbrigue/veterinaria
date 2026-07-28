<template>
    <GuestLayout>
        <Head title="Registrarse" />
        
        <div class="mb-4">
            <h3 class="fw-bold text-dark mb-1">Crear una cuenta</h3>
            <p class="text-secondary">Únete a nosotros para gestionar tu clínica veterinaria.</p>
        </div>

        <form @submit.prevent="guardar">
            <div class="form-floating mb-3">
                <input
                    id="name"
                    v-model="formulario.name"
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': formulario.errors.name }"
                    placeholder="Juan Pérez"
                    required
                    autofocus
                />
                <label for="name" class="text-muted">Nombre completo</label>
                <div v-if="formulario.errors.name" class="invalid-feedback">{{ formulario.errors.name }}</div>
            </div>

            <div class="form-floating mb-3">
                <input
                    id="email"
                    v-model="formulario.email"
                    type="email"
                    class="form-control"
                    :class="{ 'is-invalid': formulario.errors.email }"
                    placeholder="name@example.com"
                    required
                />
                <label for="email" class="text-muted">Correo electrónico</label>
                <div v-if="formulario.errors.email" class="invalid-feedback">{{ formulario.errors.email }}</div>
            </div>

            <div class="form-floating mb-3">
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

            <div class="form-floating mb-4">
                <input
                    id="password_confirmation"
                    v-model="formulario.password_confirmation"
                    type="password"
                    class="form-control"
                    :class="{ 'is-invalid': formulario.errors.password_confirmation }"
                    placeholder="Confirmar contraseña"
                    required
                />
                <label for="password_confirmation" class="text-muted">Confirmar contraseña</label>
                <div v-if="formulario.errors.password_confirmation" class="invalid-feedback">{{ formulario.errors.password_confirmation }}</div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-3 shadow-sm btn-hover-effect" :disabled="formulario.processing">
                <span v-if="formulario.processing" class="spinner-border spinner-border-sm me-2" />
                Registrarse
            </button>
            
            <div class="text-center mt-4">
                <p class="text-secondary" style="font-size: 0.95rem;">
                    ¿Ya tienes una cuenta?
                    <Link :href="route('iniciar-sesion')" class="text-primary fw-semibold text-decoration-none ms-1">Inicia sesión aquí</Link>
                </p>
            </div>
        </form>
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
