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

                    <!-- Sección Administración (Solo Vets/Admin) -->
                    <div v-if="esVeterinarioOAdmin()" class="d-flex align-items-center bg-warning bg-opacity-25 border border-warning border-opacity-50 rounded-pill px-2 py-1 mb-2 mb-lg-0 me-0 me-lg-3 shadow-sm">
                        <span class="text-warning small fw-bold ms-2 me-1 d-none d-md-inline"><i class="bi bi-shield-lock-fill"></i></span>
                        <button type="button" class="btn btn-sm btn-link nav-link text-white px-2 fw-medium transition-all hover-opacity" @click="irSiExiste('clientes.listado')">Clientes</button>
                        <button type="button" class="btn btn-sm btn-link nav-link text-white px-2 fw-medium transition-all hover-opacity" @click="irSiExiste('sucursales.listado')">Sucursales</button>
                        <button type="button" class="btn btn-sm btn-link nav-link text-white px-2 fw-medium transition-all hover-opacity" @click="irSiExiste('boxes.listado')">Boxes</button>
                        <button type="button" class="btn btn-sm btn-link nav-link text-white px-2 fw-medium transition-all hover-opacity" @click="irSiExiste('insumos.listado')">Insumos</button>
                        <button type="button" class="btn btn-sm btn-link nav-link text-white px-3 transition-all hover-opacity" @click="$inertia.visit(route('panel'))">Panel</button>
                    </div>

                    <!-- Sección Usuario -->
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
    </div>
</template>

<script>
export default {
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
        },
        esVeterinarioOAdmin() {
            const user = this.$page.props.auth.user;
            return user && user.rol && (user.rol.nombre_interno === 'veterinario' || user.rol.nombre_interno === 'admin');
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
