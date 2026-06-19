<template>
    <Head title="Bienvenido" />

    <div class="no-scroll min-vh-100 d-flex flex-column">
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1">Veterinaria Aprendizaje</span>
                <div class="d-flex gap-2 ms-auto">
                    <template v-if="$page.props.auth.user">
                        <Link :href="route('panel')" class="btn btn-link text-white text-decoration-none">Dashboard</Link>
                    </template>
                    <template v-else-if="puedeIniciarSesion">
                        <Link :href="route('iniciar-sesion')" class="btn btn-link text-white text-decoration-none">Iniciar sesión</Link>
                        <Link v-if="puedeRegistrarse" :href="route('registrarse')" class="btn btn-outline-light">Registrarse</Link>
                    </template>
                </div>
            </div>
        </nav>

        <main class="no-scroll flex-grow-1">
            <div class="bg-dots-darker d-flex align-items-center h-100">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-12 col-md-10 col-lg-8">
                            <div class="text-center">
                                <div class="laravel-logo mb-4 text-primary display-1 fw-bold">V</div>
                                <h1 class="display-4 fw-bold mb-4">Veterinaria Aprendizaje</h1>
                                <p class="h5 text-muted mb-3">Proyecto de prueba con Laravel y Vue para una clínica veterinaria simple.</p>
                                <p class="text-muted">Regístrate, prueba el CRUD de Mascotas y consulta <code>docs/MANUAL-PRUEBA.md</code>.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3';

export default {
    components: {
        Head,
        Link,
    },
    props: {
        puedeIniciarSesion: {
            type: Boolean,
        },
        puedeRegistrarse: {
            type: Boolean,
        },
        laravelVersion: {
            type: String,
            required: true,
        },
        phpVersion: {
            type: String,
            required: true,
        },
    },
}
</script>

<style scoped>
.bg-dots-darker {
    background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(0,0,0,0.07)'/%3E%3C/svg%3E");
    min-height: 100%;
}
.laravel-logo {
    height: 80px;
    width: auto;
    display: block;
    margin: 0 auto;
}
</style>

<style>
html, body, .no-scroll {
    overflow: hidden !important;
    height: 100vh !important;
}
</style>
