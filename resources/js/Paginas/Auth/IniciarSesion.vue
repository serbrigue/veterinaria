<template>
    <GuestLayout>
        <div class="card shadow-sm">
            <div class="card-header">Iniciar sesión</div>
            <div class="card-body">
                <div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input
                            id="email"
                            v-model="formulario.email"
                            type="email"
                            class="form-control"
                            :class="{ 'is-invalid': formulario.errors.email }"
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
                        />
                        <div v-if="formulario.errors.password" class="invalid-feedback">{{ formulario.errors.password }}</div>
                    </div>
                    <div class="mb-3 form-check">
                        <input id="remember" v-model="formulario.remember" type="checkbox" class="form-check-input" />
                        <label for="remember" class="form-check-label">Recordarme</label>
                    </div>
                    <button type="button" class="btn btn-primary w-100" :disabled="formulario.processing" @click="guardar">
                        <span v-if="formulario.processing" class="spinner-border spinner-border-sm me-2" />
                        Entrar
                    </button>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<script>
import GuestLayout from '@/Disenos/LayoutInvitado.vue'

export default {
    components: {
        GuestLayout,
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
