<template>
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

            <div v-if="mustVerifyEmail && usuario.email_verified_at === null" class="mt-3">
                <p class="small mb-2">
                    Tu dirección de correo electrónico no esta verificada.
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
                <button type="button" class="btn btn-primary" :disabled="formulario.processing" @click="guardar">
                    <span v-if="formulario.processing" class="spinner-border spinner-border-sm me-2" />
                    Guardar
                </button>
                <span v-if="guardado" class="small text-muted">Guardado.</span>
            </div>
        </div>
    </section>
</template>

<script>
export default {
    components: {},
    props: {
        mustVerifyEmail: {
            type: Boolean,
        },
        status: {
            type: String,
        },
    },
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
    created() {
        this.usuario = this.$page.props.auth.user
        this.formulario.name = this.usuario.name
        this.formulario.email = this.usuario.email
    },
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
