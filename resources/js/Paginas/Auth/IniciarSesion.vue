<template>
    <!-- ================================================================================== -->
    <!-- COMPONENTE: IniciarSesion -->
    <!-- ================================================================================== -->
    
    <GuestLayout>
        <Head title="Iniciar Sesión" />
        
        <div class="mb-5">
            <h3 class="fw-bold text-dark mb-1">¡Bienvenido de nuevo!</h3>
            <p class="text-secondary">Por favor, ingresa tus credenciales para acceder a tu cuenta.</p>
        </div>

        <form @submit.prevent="guardar">
            <div class="form-floating mb-3">
                <!-- Enlace de datos bidireccional con "formulario.email" -->
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
                <!-- Renderizado condicional basado en "formulario.errors.email" -->
                <div v-if="formulario.errors.email" class="invalid-feedback">{{ formulario.errors.email }}</div>
            </div>

            <div class="form-floating mb-4">
                <!-- Enlace de datos bidireccional con "formulario.password" -->
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
                <!-- Si existe un error, lo muestra -->
                <div v-if="formulario.errors.password" class="invalid-feedback">{{ formulario.errors.password }}</div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <!-- Enlace de datos bidireccional con "formulario.remember" -->
                    <input id="remember" v-model="formulario.remember" type="checkbox" class="form-check-input shadow-none" />
                    <label for="remember" class="form-check-label text-secondary" style="font-size: 0.9rem;">Recordarme</label>
                </div>
                <Link :href="route('contrasena.solicitar')" class="text-primary fw-semibold text-decoration-none" style="font-size: 0.9rem;">
                    ¿Olvidaste tu contraseña?
                </Link>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-3 shadow-sm btn-hover-effect" :disabled="formulario.processing">
                <!-- Si el formulario está procesando, muestra un spinner -->
                <span v-if="formulario.processing" class="spinner-border spinner-border-sm me-2" />
                Ingresar
            </button>
            
            <div class="text-center mt-4">
                <p class="text-secondary" style="font-size: 0.95rem;">
                    ¿No tienes una cuenta?
                    <!-- Enlace a la página de registro -->
                    <Link :href="route('registrarse')" class="text-primary fw-semibold text-decoration-none ms-1">Regístrate aquí</Link>
                </p>
            </div>
        </form>
    </GuestLayout>
</template>

<script>
// ==================================================================================
// LÓGICA DEL COMPONENTE (VUE 3)
// ==================================================================================

import GuestLayout from '@/Disenos/LayoutInvitado.vue'
import { Head, Link } from '@inertiajs/vue3'

// ------------------------------------------------------------------------------
// EXPORT DEFAULT: Definición principal del componente
// ------------------------------------------------------------------------------
export default {
    // COMPONENTES: Registro de componentes importados
    components: {
        GuestLayout,
        Head,
        Link,
    },
    // ESTADO REACTIVO: Variables locales del componente
    data() {
        return {
            // Formulario de inicio de sesión
            formulario: {
                email: '',
                password: '',
                remember: false,
                errors: {},
                processing: false,
            },
        }
    },
    // MÉTODOS: Bloque de funciones y eventos
    methods: {
        // Guarda el formulario
        guardar() {
            // Establece que el formulario está procesando
            this.formulario.processing = true
            // Limpia los errores
            this.formulario.errors = {}
            // Petición a la API para iniciar sesión
            axios.post('/api/iniciar-sesion', {
                email: this.formulario.email,
                password: this.formulario.password,
                remember: this.formulario.remember,
            })
            // Si la petición es exitosa
            .then((response) => {
                // Redirige a la página de panel
                window.location.href = response.data.redirect || route('panel')
            })
            // Si la petición falla
            .catch((error) => {
                // Si la petición falla, muestra los errores
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
