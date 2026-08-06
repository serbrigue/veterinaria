<template>
    <!-- ================================================================================== -->
    <!-- COMPONENTE: OlvideContrasena -->
    <!-- ================================================================================== -->
    <GuestLayout>
        <Head title="Olvidé mi contraseña" />

        <div class="mb-4">
            <h3 class="fw-bold text-dark mb-2">Recuperar contraseña</h3>
            <p class="text-secondary" style="font-size: 0.95rem;">
                ¿Olvidaste tu contraseña? No hay problema. 
                Simplemente indícanos tu dirección de correo electrónico y te enviaremos un enlace que te permitirá elegir una nueva.
            </p>
        </div>

        <!--Renderizado condicional basado en "status" -->
        <div v-if="status" class="alert alert-success mb-4 rounded-3 border-0 bg-success bg-opacity-10 text-success fw-medium">
            {{ status }}
        </div>

        <form @submit.prevent="guardar">
            <div class="form-floating mb-4">
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
                    autocomplete="username"
                />
                <label for="email" class="text-muted">Correo electrónico</label>
                <!-- Si existe un error, lo muestra -->
                <div v-if="formulario.errors.email" class="invalid-feedback">{{ formulario.errors.email }}</div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-3 shadow-sm btn-hover-effect" :disabled="formulario.processing">
                <!-- Si el formulario está procesando, muestra un spinner -->
                <span v-if="formulario.processing" class="spinner-border spinner-border-sm me-2" />
                Enviar enlace de recuperación
            </button>

            <div class="text-center mt-4">
                <Link :href="route('iniciar-sesion')" class="text-secondary text-decoration-none fw-medium" style="font-size: 0.95rem;">
                    <i class="bi bi-arrow-left me-1"></i> Volver a iniciar sesión
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>

<script>
// ==================================================================================
// LÓGICA DEL COMPONENTE (VUE 3)
// ==================================================================================

import GuestLayout from '@/Disenos/LayoutInvitado.vue';
import { Head, Link } from '@inertiajs/vue3';

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
                email: '',
                errors: {},
                processing: false,
            },
        }
    },
    // MÉTODO: Bloque de funciones y eventos
    methods: {
        guardar() {
            // Iniciando el estado de procesamiento
            this.formulario.processing = true
            // Limpiando errores previos
            this.formulario.errors = {}
            // Enviando solicitud al servidor
            axios.post('/api/recuperar-contrasena', {
                email: this.formulario.email,
            })
            // Si la solicitud es exitosa
            .then(() => {
                window.location.reload()
            })
            // Si ocurre un error
            .catch((error) => {
                if (error.response?.status === 422) {
                    this.formulario.errors = error.response.data.errors
                }
            })
            // Finaliza el estado de procesamiento
            .finally(() => {
                this.formulario.processing = false
            })
        },
    },
}
</script>
