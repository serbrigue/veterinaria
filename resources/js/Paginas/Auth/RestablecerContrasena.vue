<template>
    <!-- ================================================================================== -->
    <!-- COMPONENTE: RestablecerContrasena -->
    <!-- ================================================================================== -->
    <GuestLayout>
        <Head title="Restablecer contraseña" />

        <div class="card shadow-sm">
            <div class="card-header">Restablecer contraseña</div>
            <div class="card-body">
                <!-- Formulario -->
                <div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <!-- Enlace de datos bidireccional con "formulario.email" -->
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
                        <!-- Si existe un error, lo muestra -->
                        <div v-if="formulario.errors.email" class="invalid-feedback">{{ formulario.errors.email }}</div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <!-- Enlace de datos bidireccional con "formulario.password" -->
                        <input
                            id="password"
                            v-model="formulario.password"
                            type="password"
                            class="form-control"
                            :class="{ 'is-invalid': formulario.errors.password }"
                            required
                            autocomplete="new-password"
                        />
                        <!-- Si existe un error, lo muestra -->
                        <div v-if="formulario.errors.password" class="invalid-feedback">{{ formulario.errors.password }}</div>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
                        <!-- Enlace de datos bidireccional con "formulario.password_confirmation" -->
                        <input
                            id="password_confirmation"
                            v-model="formulario.password_confirmation"
                            type="password"
                            class="form-control"
                            :class="{ 'is-invalid': formulario.errors.password_confirmation }"
                            required
                            autocomplete="new-password"
                        />
                        <!-- Si existe un error, lo muestra -->
                        <div v-if="formulario.errors.password_confirmation" class="invalid-feedback">{{ formulario.errors.password_confirmation }}</div>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <!-- Si el formulario está procesando, muestra un spinner -->
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
        email: {
            type: String,
            required: true,
        },
        token: {
            type: String,
            required: true,
        },
    },
    // ESTADO REACTIVO: Variables locales del componente
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
    // CICLO DE VIDA: Se ejecuta al crear la instancia del componente
    created() {
        // Tokern y email por defecto son los que vienen desde el backend
        this.formulario.token = this.token
        this.formulario.email = this.email
    },
    // MÉTODOS: Bloque de funciones y eventos
    methods: {
        // Guarda el formulario
        guardar() {
            this.formulario.processing = true
            this.formulario.errors = {}
            // Petición a la API para restablecer la contraseña
            axios.post('/api/restablecer-contrasena', {
                token: this.formulario.token,
                email: this.formulario.email,
                password: this.formulario.password,
                password_confirmation: this.formulario.password_confirmation,
            })
            // Si la petición es exitosa
            .then((response) => {
                // Redirige al usuario a la ruta de inicio de sesión
                window.location.href = response.data.redirect || route('iniciar-sesion')
            })
            // Si la petición falla
            .catch((error) => {
                // Si el error es 422, muestra los errores
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
