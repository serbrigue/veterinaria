<template>
    <div class="min-vh-100 d-flex flex-column">
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1">Veterinaria Aprendizaje</span>
                <div class="d-flex flex-wrap gap-2 ms-auto">
                    <button type="button" class="btn btn-link nav-link text-white" @click="$inertia.visit(route('panel'))">Panel</button>
                    <button type="button" class="btn btn-link nav-link text-white" @click="$inertia.visit(route('mascotas.listado'))">Mascotas</button>
                    <button type="button" class="btn btn-link nav-link text-white" @click="irSiExiste('especies.listado')">Especies</button>
                    <button type="button" class="btn btn-link nav-link text-white" @click="irSiExiste('razas.listado')">Razas</button>
                    <button type="button" v-if="esVeterinarioOAdmin()" class="btn btn-link nav-link text-white" @click="irSiExiste('clientes.listado')">Clientes</button>
                    <button type="button" class="btn btn-link nav-link text-white" @click="irSiExiste('citas.listado')">Citas</button>
                    <button type="button" class="btn btn-link nav-link text-white" @click="$inertia.visit(route('perfil.editar'))">Perfil</button>
                    <button type="button" class="btn btn-link nav-link text-white" @click="cerrarSesion">Cerrar sesión</button>
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
        esVeterinarioOAdmin() 
        {
            return this.$page.props.auth.user.rol === 'veterinario' || this.$page.props.auth.user.rol === 'admin';
        }
    },
}
</script>
