   <template>
    <div class="min-vh-100 d-flex flex-column bg-light">
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
            <div class="container-fluid px-4">

                <!--Logo y redireccionamiento a home-->
                <Link href="/" class="navbar-brand d-flex align-items-center gap-2 fw-bold fs-4 text-white">
                    <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center p-2 shadow-sm" style="width: 38px; height: 38px;">
                        <i class="bi bi-heart-pulse-fill"></i>
                    </div>
                    <span class="d-none d-sm-inline">Veterinaria</span>
                </Link>

                <!--Boton para desplegar el menu en dispositivos moviles-->
                <button 
                    class="navbar-toggler border-0 shadow-none" 
                    type="button" 
                    @click="menuAbierto = !menuAbierto"
                >
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!--Menu principal-->
                <div class="collapse navbar-collapse" :class="{ 'show': menuAbierto }">
                    <div class="navbar-nav ms-auto mb-2 mb-lg-0 py-3 py-lg-0 gap-3 gap-lg-2 align-items-lg-center">
                        
                        <!-- Sección Principal -->
                        <div class="nav-section d-flex flex-column flex-lg-row align-items-lg-center bg-white bg-opacity-10 rounded-4 px-3 px-lg-2 py-2 py-lg-1 gap-2 gap-lg-0">
                            <span class="d-lg-none text-white-50 small fw-bold text-uppercase tracking-wide mb-1">Clínica</span>
                            <button type="button" class="btn btn-link nav-link text-white text-start px-lg-3 transition-all rounded hover-bg" @click="irSiExiste('mascotas.listado')">Mascotas</button>
                            <button type="button" class="btn btn-link nav-link text-white text-start px-lg-3 transition-all rounded hover-bg" @click="irSiExiste('citas.listado')">Citas</button>
                            <button type="button" class="btn btn-link nav-link text-white text-start px-lg-3 transition-all rounded hover-bg" @click="irSiExiste('veterinarios.listado')">Veterinarios</button>
                        </div>
                        
                        <!-- Sección Catálogos -->
                        <div class="nav-section d-flex flex-column flex-lg-row align-items-lg-center bg-white bg-opacity-10 rounded-4 px-3 px-lg-2 py-2 py-lg-1 gap-2 gap-lg-0">
                            <span class="d-lg-none text-white-50 small fw-bold text-uppercase tracking-wide mb-1">Catálogos</span>
                            <button type="button" class="btn btn-link nav-link text-white text-start px-lg-3 transition-all rounded hover-bg" @click="irSiExiste('especies.listado')">Especies</button>
                            <button type="button" class="btn btn-link nav-link text-white text-start px-lg-3 transition-all rounded hover-bg" @click="irSiExiste('razas.listado')">Razas</button>
                            <button type="button" class="btn btn-link nav-link text-white text-start px-lg-3 transition-all rounded hover-bg" @click="irSiExiste('prestaciones.listado')">Servicios</button>
                        </div>
                        
                        <!-- Sección Administración (Secretaria / Vet) -->
                        <!-- Verificación de roles para secretaria y veterinario-->
                        <TieneRol :rol="['veterinario','secretaria']">
                            <div class="nav-section d-flex flex-column flex-lg-row align-items-lg-center bg-warning bg-opacity-25 border border-warning border-opacity-50 rounded-4 px-3 px-lg-2 py-2 py-lg-1 gap-2 gap-lg-0 shadow-sm">
                                <div class="d-flex align-items-center gap-2 mb-1 mb-lg-0 pe-lg-2 border-end-lg border-warning border-opacity-50">
                                    <span class="text-warning small fw-bold"><i class="bi bi-shield-lock-fill"></i></span>
                                    <span class="d-lg-none text-warning small fw-bold text-uppercase tracking-wide">Gestión</span>
                                </div>
                                <button type="button" class="btn btn-link nav-link text-white text-start px-lg-3 transition-all fw-medium rounded hover-bg" @click="irSiExiste('clientes.listado')">Clientes</button>
                                <TieneRol :rol="['secretaria']">
                                    <button type="button" class="btn btn-link nav-link text-white text-start px-lg-3 transition-all fw-medium rounded hover-bg" @click="irSiExiste('agenda.secretaria')">Agenda</button>
                                </TieneRol>
                            </div>
                        </TieneRol>
                        
                        <!-- Sección Administración (Admin) -->
                        <!-- Verificación de roles para admin-->
                        <TieneRol :rol="['admin']">
                            <div class="nav-section d-flex flex-column flex-lg-row align-items-lg-center bg-warning bg-opacity-25 border border-warning border-opacity-50 rounded-4 px-3 px-lg-2 py-2 py-lg-1 gap-2 gap-lg-0 shadow-sm">
                                <div class="d-flex align-items-center gap-2 mb-1 mb-lg-0 pe-lg-2 border-end-lg border-warning border-opacity-50">
                                    <span class="text-warning small fw-bold"><i class="bi bi-shield-lock-fill"></i></span>
                                    <span class="d-lg-none text-warning small fw-bold text-uppercase tracking-wide">Administración</span>
                                </div>
                                <button type="button" class="btn btn-link nav-link text-white text-start px-lg-3 transition-all fw-medium rounded hover-bg" @click="irSiExiste('clientes.listado')">Clientes</button>
                                <button type="button" class="btn btn-link nav-link text-white text-start px-lg-3 transition-all fw-medium rounded hover-bg" @click="irSiExiste('boxes.listado')">Boxes</button>
                                <button type="button" class="btn btn-link nav-link text-white text-start px-lg-3 transition-all fw-medium rounded hover-bg" @click="irSiExiste('insumos.listado')">Insumos</button>
                                <button type="button" class="btn btn-link nav-link text-white text-start px-lg-3 transition-all fw-medium rounded hover-bg" @click="$inertia.visit(route('panel'))">Panel</button>
                                <button type="button" class="btn btn-link nav-link text-white text-start px-lg-3 transition-all fw-medium rounded hover-bg" @click="$inertia.visit(route('sucursales.listado'))">Sucursales</button>
                            </div>
                        </TieneRol>

                        <!-- Usuario -->
                        <!-- Dropdown del usuario-->
                        <div class="nav-section dropdown position-relative d-flex flex-column align-items-lg-center border-top border-lg-0 border-start-lg border-white border-opacity-25 pt-3 pt-lg-0 ps-lg-3 mt-2 mt-lg-0">
                            <button 
                                class="btn btn-link nav-link text-white text-start px-lg-2 py-lg-1 transition-all fw-medium rounded hover-bg d-flex align-items-center gap-2 w-100" 
                                type="button" 
                                @click="dropdownUsuario = !dropdownUsuario"
                            >
                                <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent($page.props.auth.user.name)}&background=ffffff&color=0d6efd&bold=true`" alt="Avatar" class="rounded-circle shadow-sm border border-2 border-white" style="width: 34px; height: 34px; object-fit: cover;">
                                <span class="text-truncate" style="max-width: 120px;">{{ $page.props.auth.user.name }}</span>
                                <i class="bi bi-caret-down-fill ms-1 opacity-75 small d-none d-lg-inline"></i>
                            </button>

                            <!-- Backdrop para cerrar al hacer click fuera -->
                            <div 
                                v-if="dropdownUsuario" 
                                class="position-fixed top-0 start-0 w-100 h-100" 
                                style="z-index: 1040; background: transparent;" 
                                @click.stop="dropdownUsuario = false"
                            ></div>

                            <ul 
                                class="dropdown-menu dropdown-menu-end shadow-sm border-0 show mt-2" 
                                v-if="dropdownUsuario" 
                                :class="{'position-absolute': true}"
                                style="z-index: 1050; min-width: 200px; right: 0; top: 100%;"
                            >
                                <li>
                                    <button class="dropdown-item py-2 d-flex align-items-center gap-2 transition-all" @click="$inertia.visit(route('perfil.editar'))">
                                        <i class="bi bi-person-circle text-primary fs-5"></i> <span class="fw-medium text-dark">Mi Perfil</span>
                                    </button>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li>
                                    <button class="dropdown-item py-2 text-danger d-flex align-items-center gap-2 transition-all" @click="cerrarSesion">
                                        <i class="bi bi-box-arrow-right fs-5"></i> <span class="fw-bold">Cerrar Sesión</span>
                                    </button>
                                </li>
                            </ul>
                        </div>

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
import { Link } from '@inertiajs/vue3';

// Exportamos el componente
export default {
    // Componentes que se utilizan en el layout
    components: {
        TieneRol,
        BotonChatbotn8n,
        Link
    },  
    // Datos del componente
    data() {
        return {    
            menuAbierto: false,
            dropdownUsuario: false
        }
    },
    // Métodos del componente
    methods: {
        irSiExiste(nombreRuta) {
            this.menuAbierto = false;
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
    // Observadores para el manejo del menú y dropdown
    watch: {
        // Al cambiar la ruta, cerramos el menú y el dropdown
        '$page.url'() {
            this.menuAbierto = false;
            this.dropdownUsuario = false;
        }
    }
}
</script>

<style scoped>
.transition-all {
    transition: all 0.2s ease-in-out;
}
.hover-bg:hover {
    background-color: rgba(255, 255, 255, 0.15);
}
.tracking-wide {
    letter-spacing: 0.05em;
}
@media (min-width: 992px) {
    .border-start-lg {
        border-left: 1px solid rgba(255, 255, 255, 0.25) !important;
    }
    .border-end-lg {
        border-right: 1px solid rgba(255, 255, 255, 0.25) !important;
    }
}
.nav-link {
    text-decoration: none !important;
}
</style>
