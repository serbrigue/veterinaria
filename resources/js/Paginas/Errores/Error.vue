<template>
    <Head :title="tituloPagina" />
    
    <!-- Si está autenticado, lo envolvemos en el layout para mantener la navegación -->
    <component :is="componenteLayout" :class="estaAutenticado ? '' : 'min-vh-100 d-flex align-items-center justify-content-center bg-light bg-opacity-50'">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 text-center">
                    <!-- Tarjeta de Error con premium aesthetics -->
                    <div class="card border-0 shadow-lg rounded-4 overflow-hidden p-5 bg-white">
                        <div class="mb-4">
                            <span class="display-1 fw-bold text-gradient-primary">{{ status }}</span>
                        </div>
                        <div class="mb-4">
                            <div class="error-icon-wrapper mx-auto mb-3" :class="status === 403 ? 'bg-danger bg-opacity-10 text-danger' : 'bg-primary bg-opacity-10 text-primary'">
                                <i :class="status === 403 ? 'bi bi-shield-lock-fill' : 'bi bi-exclamation-triangle-fill'" class="fs-1"></i>
                            </div>
                            <h2 class="h3 fw-bold text-dark mb-2">{{ tituloError }}</h2>
                            <p class="text-muted fs-5 mb-0 px-md-4">{{ mensaje }}</p>
                        </div>
                        <div class="d-flex justify-content-center gap-3">
                            <button @click="irAtras" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold">
                                <i class="bi bi-arrow-left me-2"></i>Volver Atrás
                            </button>
                            <Link :href="estaAutenticado ? (esAdmin ? '/panel' : '/perfil') : '/'" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm">
                                <i class="bi bi-house-door-fill me-2"></i>Ir al Inicio
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </component>
</template>

<script>
import AuthenticatedLayout from '@/Disenos/LayoutAutenticado.vue';
import { Head, Link } from '@inertiajs/vue3';

export default {
    name: 'Error',
    components: {
        AuthenticatedLayout,
        Head,
        Link,
    },
    props: {
        status: {
            type: Number,
            required: true,
        },
        mensaje: {
            type: String,
            required: true,
        },
    },
    computed: {
        estaAutenticado() {
            return !!this.$page.props.auth?.user;
        },
        esAdmin() {
            const user = this.$page.props.auth?.user;
            return !!(user && user.rol?.nombre_interno === 'admin');
        },
        componenteLayout() {
            return this.estaAutenticado ? AuthenticatedLayout : 'div';
        },
        tituloPagina() {
            return `Error ${this.status}`;
        },
        tituloError() {
            return this.status === 403 ? 'Acceso Denegado' : 'Página no encontrada';
        }
    },
    methods: {
        irAtras() {
            window.history.back();
        }
    }
}
</script>

<style scoped>
.text-gradient-primary {
    background: linear-gradient(135deg, var(--bs-primary) 0%, #6f42c1 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.error-icon-wrapper {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
