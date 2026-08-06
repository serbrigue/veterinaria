<template>
    <!-- ================================================================================== -->
    <!-- COMPONENTE: ActualizarInformacion -->
    <!-- ================================================================================== -->
    <section>
        <header class="mb-4">
            <h2 class="h5 mb-2">Información de perfil</h2>
            <p class="text-muted small mb-0">
                Actualiza la información de tu perfil y la dirección de correo electrónico.
            </p>
        </header>

        <div>
            <div class="mb-3">
                <label for="name" class="form-label">Nombre</label>
                <!-- DIRECTIVA (v-model): Enlace de datos bidireccional con "formulario.name" -->
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
                <!-- DIRECTIVA (v-if): Renderizado condicional basado en "formulario.errors.name" -->
                <div v-if="formulario.errors.name" class="invalid-feedback">{{ formulario.errors.name }}</div>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Correo electrónico</label>
                <!-- DIRECTIVA (v-model): Enlace de datos bidireccional con "formulario.email" -->
                <input
                    id="email"
                    v-model="formulario.email"
                    type="email"
                    class="form-control"
                    :class="{ 'is-invalid': formulario.errors.email }"
                    required
                    autocomplete="username"
                />
                <!-- DIRECTIVA (v-if): Renderizado condicional basado en "formulario.errors.email" -->
                <div v-if="formulario.errors.email" class="invalid-feedback">{{ formulario.errors.email }}</div>
            </div>

            <!-- DIRECTIVA (v-if): Renderizado condicional basado en "mustVerifyEmail && usuario.email_verified_at === null" -->

            <div v-if="mustVerifyEmail && usuario.email_verified_at === null" class="mt-3">
                <p class="small mb-2">
                    Tu dirección de correo electrónico no esta verificada.
                    <!-- EVENTO (@click): Dispara la acción "reenviarVerificacion" -->
                    <button
                        type="button"
                        class="btn btn-link p-0 align-baseline"
                        @click="reenviarVerificacion"
                    >
                        Haz clic aquí para reenviar el correo de verificación.
                    </button>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="alert alert-success mt-2"
                >
                    Un nuevo enlace de verificación ha sido enviado a tu dirección de correo electrónico.
                </div>
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
    // COMPONENTES (COMPONENTS): Registro de componentes importados
    components: {},
    // PROPIEDADES (PROPS): Datos inyectados desde el componente padre o estado
    props: {
        mustVerifyEmail: {
            type: Boolean,
        },
        status: {
            type: String,
        },
    },
    // ESTADO REACTIVO (DATA): Variables locales del componente
    data() {
        return {
            usuario: null,
            guardado: false,
            formulario: {
                name: '',
                email: '',
                errors: {},
                processing: false,
            },
        }
    },
    // CICLO DE VIDA (CREATED): Se ejecuta al crear la instancia del componente
    created() {
        this.usuario = this.$page.props.auth.user
        this.formulario.name = this.usuario.name
        this.formulario.email = this.usuario.email
    },
    // MÉTODOS (METHODS): Bloque de funciones y eventos
    methods: {
        reenviarVerificacion() {
            axios.post('/api/verificacion/enviar')
            .then(() => {
                window.location.reload()
            })
        },
        guardar() {
            this.formulario.processing = true
            this.formulario.errors = {}
            this.guardado = false
            axios.patch('/api/perfil', {
                name: this.formulario.name,
                email: this.formulario.email,
            })
            .then(() => {
                this.guardado = true
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
