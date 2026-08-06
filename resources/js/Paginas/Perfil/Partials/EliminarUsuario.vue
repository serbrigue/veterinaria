<template>
    <!-- ================================================================================== -->
    <!-- COMPONENTE: EliminarUsuario -->
    <!-- ================================================================================== -->
    <section>
        <header class="mb-4">
            <h2 class="h5 mb-2">Eliminar cuenta</h2>
            <p class="text-muted small mb-0">
                Una vez que se elimine tu cuenta, todos sus recursos y datos se eliminarán permanentemente. Antes de eliminar
                tu cuenta, por favor descarga cualquier dato o información que desees conservar.
            </p>
        </header>

        <!-- EVENTO (@click): Dispara la acción "confirmarEliminacion" -->

        <button type="button" class="btn btn-danger" @click="confirmarEliminacion">Eliminar cuenta</button>

        <!-- DIRECTIVA (v-if): Renderizado condicional basado en "confirmandoEliminacion" -->

        <template v-if="confirmandoEliminacion">
            <div class="modal fade show d-block" tabindex="-1" @click.self="cerrarModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">¿Estás seguro de querer eliminar tu cuenta?</h5>
                            <!-- EVENTO (@click): Dispara la acción "cerrarModal" -->
                            <button type="button" class="btn-close" @click="cerrarModal" />
                        </div>
                        <div class="modal-body">
                            <p class="text-muted small mb-3">
                                Una vez que se elimine tu cuenta, todos sus recursos y datos se eliminarán permanentemente. Por favor
                                ingresa tu contraseña para confirmar que deseas eliminar tu cuenta permanentemente.
                            </p>
                            <div class="mb-3">
                                <label for="delete_password" class="form-label">Contraseña</label>
                                <!-- DIRECTIVA (v-model): Enlace de datos bidireccional con "formulario.password" -->
                                <input
                                    id="delete_password"
                                    ref="entradaContrasena"
                                    v-model="formulario.password"
                                    type="password"
                                    class="form-control"
                                    :class="{ 'is-invalid': formulario.errors.password }"
                                    placeholder="Contraseña"
                                    @keyup.enter="eliminarUsuario"
                                />
                                <!-- DIRECTIVA (v-if): Renderizado condicional basado en "formulario.errors.password" -->
                                <div v-if="formulario.errors.password" class="invalid-feedback">{{ formulario.errors.password }}</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <!-- EVENTO (@click): Dispara la acción "cerrarModal" -->
                            <button type="button" class="btn btn-secondary" @click="cerrarModal">Cancelar</button>
                            <!-- EVENTO (@click): Dispara la acción "eliminarUsuario" -->
                            <button type="button" class="btn btn-danger" :disabled="formulario.processing" @click="eliminarUsuario">
                                <!-- DIRECTIVA (v-if): Renderizado condicional basado en "formulario.processing" -->
                                <span v-if="formulario.processing" class="spinner-border spinner-border-sm me-2" />
                                Eliminar cuenta
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-backdrop fade show" />
        </template>
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
            confirmandoEliminacion: false,
            formulario: {
                password: '',
                errors: {},
                processing: false,
            },
        }
    },
    // MÉTODOS (METHODS): Bloque de funciones y eventos
    methods: {
        confirmarEliminacion() {
            this.confirmandoEliminacion = true;
            this.$nextTick(() => this.$refs.entradaContrasena.focus());
        },
        eliminarUsuario() {
            this.formulario.processing = true
            this.formulario.errors = {}
            axios.delete('/api/perfil', {
                data: { password: this.formulario.password },
            })
            .then((response) => {
                window.location.href = response.data.redirect || '/'
            })
            .catch((error) => {
                if (error.response?.status === 422) {
                    this.formulario.errors = error.response.data.errors
                    this.$refs.entradaContrasena.focus()
                }
            })
            .finally(() => {
                this.formulario.processing = false
            })
        },
        cerrarModal() {
            this.confirmandoEliminacion = false;
            this.formulario.password = '';
            this.formulario.errors = {};
        },
    },
}
</script>
