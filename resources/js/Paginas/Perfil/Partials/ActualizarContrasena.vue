<template>
    <!-- ================================================================================== -->
    <!-- COMPONENTE: ActualizarContrasena -->
    <!-- ================================================================================== -->
    <section>
        <header class="mb-4">
            <h2 class="h5 mb-2">Actualizar contraseña</h2>
            <p class="text-muted small mb-0">
                Asegura que tu cuenta esté usando una contraseña larga y aleatoria para mantenerse segura.
            </p>
        </header>

         <div>
            <div class="mb-3">
                <label for="current_password" class="form-label">Contraseña actual</label>
                <!-- DIRECTIVA (v-model): Enlace de datos bidireccional con "formulario.current_password" -->
                <input
                    id="current_password"
                    ref="entradaContrasenaActual"
                    v-model="formulario.current_password"
                    type="password"
                    class="form-control"
                    :class="{ 'is-invalid': formulario.errors.current_password }"
                    autocomplete="current-password"
                />
                <!-- DIRECTIVA (v-if): Renderizado condicional basado en "formulario.errors.current_password" -->
                <div v-if="formulario.errors.current_password" class="invalid-feedback">{{ formulario.errors.current_password }}</div>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Nueva contraseña</label>
                <!-- DIRECTIVA (v-model): Enlace de datos bidireccional con "formulario.password" -->
                <input
                    id="password"
                    ref="entradaContrasena"
                    v-model="formulario.password"
                    type="password"
                    class="form-control"
                    :class="{ 'is-invalid': formulario.errors.password }"
                    autocomplete="new-password"
                />
                <!-- DIRECTIVA (v-if): Renderizado condicional basado en "formulario.errors.password" -->
                <div v-if="formulario.errors.password" class="invalid-feedback">{{ formulario.errors.password }}</div>
            </div>
            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
                <!-- DIRECTIVA (v-model): Enlace de datos bidireccional con "formulario.password_confirmation" -->
                <input
                    id="password_confirmation"
                    v-model="formulario.password_confirmation"
                    type="password"
                    class="form-control"
                    :class="{ 'is-invalid': formulario.errors.password_confirmation }"
                    autocomplete="new-password"
                />
                <!-- DIRECTIVA (v-if): Renderizado condicional basado en "formulario.errors.password_confirmation" -->
                <div v-if="formulario.errors.password_confirmation" class="invalid-feedback">{{ formulario.errors.password_confirmation }}</div>
            </div>

            <div class="d-flex align-items-center gap-3 mt-4">
                <!-- EVENTO (@click): Dispara la acción "guardar" -->
                <button type="button" class="btn btn-primary" :disabled="formulario.processing" @click="guardar">
                    <!-- DIRECTIVA (v-if): Renderizado condicional basado en "formulario.processing" -->
                    <span v-if="formulario.processing" class="spinner-border spinner-border-sm me-2" />
                    Guardar
                </button>
                <!-- DIRECTIVA (v-if): Renderizado condicional basado en "guardado" -->
                <span v-if="guardado" class="small text-muted">Guardado.</span>
            </div>
        </div>
    </section>
</template>

<script>
// ==================================================================================
// LÓGICA DEL COMPONENTE (VUE 3)
// ==================================================================================

// ------------------------------------------------------------------------------
// EXPORT DEFAULT: Definición principal del componente
// ------------------------------------------------------------------------------
export default {
    // ESTADO REACTIVO (DATA): Variables locales del componente
    data() {
        return {
            guardado: false,
            formulario: {
                current_password: '',
                password: '',
                password_confirmation: '',
                errors: {},
                processing: false,
            },
        }
    },
    // MÉTODOS (METHODS): Bloque de funciones y eventos
    methods: {
        guardar() {
            this.formulario.processing = true
            this.formulario.errors = {}
            this.guardado = false
            axios.put('/api/perfil/contrasena', {
                current_password: this.formulario.current_password,
                password: this.formulario.password,
                password_confirmation: this.formulario.password_confirmation,
            })
            .then(() => {
                this.guardado = true
                this.formulario.current_password = ''
                this.formulario.password = ''
                this.formulario.password_confirmation = ''
            })
            .catch((error) => {
                if (error.response?.status === 422) {
                    const errors = error.response.data.errors
                    this.formulario.errors = errors
                    if (errors.password) {
                        this.formulario.password = ''
                        this.formulario.password_confirmation = ''
                        this.$refs.entradaContrasena.focus()
                    }
                    if (errors.current_password) {
                        this.formulario.current_password = ''
                        this.$refs.entradaContrasenaActual.focus()
                    }
                }
            })
            .finally(() => {
                this.formulario.processing = false
            })
        },
    },
}
</script>
