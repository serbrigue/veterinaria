<template>
    <div class="min-vh-100 d-flex flex-column">
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1">Veterinaria Aprendizaje</span>
                <div class="d-flex flex-wrap align-items-center gap-1 ms-auto mt-2 mt-lg-0">
                    <!-- Sección Principal -->
                    <div class="d-flex align-items-center bg-white bg-opacity-10 rounded-pill px-2 py-1 mb-2 mb-lg-0 me-0 me-lg-2">
                        <button type="button" class="btn btn-sm btn-link nav-link text-white px-3 transition-all hover-opacity" @click="$inertia.visit(route('mascotas.listado'))">Mascotas</button>
                        <button type="button" class="btn btn-sm btn-link nav-link text-white px-3 transition-all hover-opacity" @click="irSiExiste('citas.listado')">Citas</button>
                        <button type="button" class="btn btn-sm btn-link nav-link text-white px-3 transition-all hover-opacity" @click="irSiExiste('veterinarios.listado')">Veterinarios</button>
                    </div>

                    <!-- Sección Catálogos -->
                    <div class="d-flex align-items-center bg-white bg-opacity-10 rounded-pill px-2 py-1 mb-2 mb-lg-0 me-0 me-lg-2">
                        <button type="button" class="btn btn-sm btn-link nav-link text-white px-3 transition-all hover-opacity" @click="irSiExiste('especies.listado')">Especies</button>
                        <button type="button" class="btn btn-sm btn-link nav-link text-white px-3 transition-all hover-opacity" @click="irSiExiste('razas.listado')">Razas</button>
                        <button type="button" class="btn btn-sm btn-link nav-link text-white px-3 transition-all hover-opacity" @click="irSiExiste('prestaciones.listado')">Servicios</button>
                    </div>

                    <!-- Sección Administración (Solo Vets/Admin/Secretaria) -->
                    <TieneRol :rol="['veterinario','secretaria']">
                        <div class="d-flex align-items-center bg-warning bg-opacity-25 border border-warning border-opacity-50 rounded-pill px-2 py-1 mb-2 mb-lg-0 me-0 me-lg-3 shadow-sm">
                            <span class="text-warning small fw-bold ms-2 me-1 d-none d-md-inline"><i class="bi bi-shield-lock-fill"></i></span>
                            <button type="button" class="btn btn-sm btn-link nav-link text-white px-2 fw-medium transition-all hover-opacity" @click="irSiExiste('clientes.listado')">Clientes</button>
                            <TieneRol :rol="['secretaria']">
                                <button type="button" class="btn btn-sm btn-link nav-link text-white px-2 fw-medium transition-all hover-opacity" @click="irSiExiste('agenda.secretaria')">Agenda</button>
                            </TieneRol>
                        </div>
                    </TieneRol>
                    <TieneRol :rol="['admin']">
                        <div class="d-flex align-items-center bg-warning bg-opacity-25 border border-warning border-opacity-50 rounded-pill px-2 py-1 mb-2 mb-lg-0 me-0 me-lg-3 shadow-sm">
                            <span class="text-warning small fw-bold ms-2 me-1 d-none d-md-inline"><i class="bi bi-shield-lock-fill"></i></span>
                            <button type="button" class="btn btn-sm btn-link nav-link text-white px-2 fw-medium transition-all hover-opacity" @click="irSiExiste('clientes.listado')">Clientes</button>
                            <button type="button" class="btn btn-sm btn-link nav-link text-white px-2 fw-medium transition-all hover-or-opacity" @click="irSiExiste('boxes.listado')">Boxes</button>
                            <button type="button" class="btn btn-sm btn-link nav-link text-white px-2 fw-medium transition-all hover-opacity" @click="irSiExiste('insumos.listado')">Insumos</button>

                            <TieneRol rol="admin">
                                <button type="button" class="btn btn-sm btn-link nav-link text-white px-3 transition-all hover-opacity" @click="$inertia.visit(route('panel'))">Panel</button>
                            </TieneRol>
                            <TieneRol rol="admin">
                                <button type="button" class="btn btn-sm btn-link nav-link text-white px-3 transition-all hover-opacity" @click="$inertia.visit(route('sucursales.listado'))">Sucursales</button>
                            </TieneRol>
                        </div>
                    </TieneRol>

                    <div class="d-flex align-items-center border-start-lg border-white border-opacity-25 ps-0 ps-lg-3 py-1">
                        <button type="button" class="btn btn-sm btn-link nav-link text-white px-2 transition-all hover-opacity" @click="$inertia.visit(route('perfil.editar'))">
                            <i class="bi bi-person-circle me-1"></i>Perfil
                        </button>
                        <button type="button" class="btn btn-sm btn-link nav-link text-danger fw-bold px-2 transition-all hover-opacity" @click="cerrarSesion">
                            <i class="bi bi-box-arrow-right me-1"></i>Salir
                        </button>
                    </div>
                </div>
            </div>
        </nav>         
        <main class="flex-grow-1">
            <slot />
        </main>
        <!-- Botón del Chatbot de n8n -->
        <BotonChatbotn8n />
    </div>
</template>

<script>
import TieneRol from '@/Componentes/TieneRol.vue';
import BotonChatbotn8n from '@/Componentes/BotonChatbotn8n.vue';

export default {
    components: {
        TieneRol,
        BotonChatbotn8n
    },
    methods: {
        irSiExiste(nombreRuta) {
            if (typeof route === 'function' && route().has(nombreRuta)) {
                this.$inertia.visit(route(nombreRuta));
            }
        },
        cerrarSesion() {
            axios.post('/api/cerrar-sesion')
            .then((response) => {
                window.location.href = response.data.redirect || '/'
            })
        }
    },
}
</script>

<style scoped>
.transition-all {
    transition: all 0.3s ease;
}
.hover-opacity:hover {
    opacity: 0.8;
}
</style>
