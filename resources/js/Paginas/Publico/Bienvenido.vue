<template>
    <Head title="Bienvenido — Cuidado Profesional para tu Mascota" />

    <div class="min-vh-100 d-flex flex-column bg-light-modern">
        <!-- Barra de navegación -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm border-bottom sticky-top py-3">
            <div class="container">
                <span class="navbar-brand d-flex align-items-center fw-bold text-primary fs-4">
                    <i class="bi bi-heart-pulse-fill me-2 text-danger animate-pulse"></i>
                    Veterinaria Aprendizaje
                </span>
                
                <div class="d-flex gap-2 ms-auto">
                    <template v-if="$page.props.auth.user">
                        <Link :href="route('panel')" class="btn btn-primary btn-sm px-4 rounded-pill shadow-sm">
                            <i class="bi bi-speedometer2 me-1"></i> Panel de Control
                        </Link>
                    </template>
                    <template v-else-if="puedeIniciarSesion">
                        <Link :href="route('iniciar-sesion')" class="btn btn-link text-secondary text-decoration-none fw-semibold">
                            Iniciar sesión
                        </Link>
                        <Link v-if="puedeRegistrarse" :href="route('registrarse')" class="btn btn-primary btn-sm px-4 rounded-pill shadow-sm hover-grow">
                            Registrarse
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Sección Hero -->
        <main class="flex-grow-1">
            <div class="container py-5">
                <div class="row align-items-center g-5 py-3">
                    <div class="col-lg-6 text-center text-lg-start">
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-semibold mb-3 border border-primary border-opacity-25 shadow-sm">
                            🐾 Tu clínica veterinaria digital
                        </span>
                        <h1 class="display-4 fw-black text-dark mb-4 lh-sm">
                            Cuidamos a quienes <br class="d-none d-lg-block">
                            <span class="text-gradient">más te importan</span>
                        </h1>
                        <p class="lead text-secondary mb-4 fs-5">
                            Reserva citas en línea, gestiona el historial médico de tus mascotas, y accede al mejor equipo de veterinarios desde nuestra plataforma segura.
                        </p>
                        
                        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start mb-5">
                            <template v-if="$page.props.auth.user">
                                <Link :href="route('panel')" class="btn btn-primary btn-lg px-5 py-3 rounded-pill shadow hover-grow">
                                    Ir al Panel de Control <i class="bi bi-arrow-right ms-2"></i>
                                </Link>
                            </template>
                            <template v-else>
                                <Link v-if="puedeRegistrarse" :href="route('registrarse')" class="btn btn-primary btn-lg px-5 py-3 rounded-pill shadow hover-grow">
                                    Comenzar ahora <i class="bi bi-arrow-right ms-2"></i>
                                </Link>
                                <Link :href="route('iniciar-sesion')" class="btn btn-outline-secondary btn-lg px-5 py-3 rounded-pill hover-grow">
                                    Conocer más
                                </Link>
                            </template>
                        </div>

                        <!-- Estadísticas breves -->
                        <div class="row g-4 text-center text-sm-start border-top pt-4">
                            <div class="col-4 col-md-4">
                                <h3 class="fw-bold text-dark mb-0 fs-2">98%</h3>
                                <p class="text-muted small mb-0">Clientes felices</p>
                            </div>
                            <div class="col-4 col-md-4">
                                <h3 class="fw-bold text-dark mb-0 fs-2">24/7</h3>
                                <p class="text-muted small mb-0">Soporte médico</p>
                            </div>
                            <div class="col-4 col-md-4">
                                <h3 class="fw-bold text-dark mb-0 fs-2">10+</h3>
                                <p class="text-muted small mb-0">Especialidades</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="position-relative">
                            <!-- Decoración de fondo de Hero -->
                            <div class="hero-bg-glow position-absolute top-50 start-50 translate-middle bg-primary bg-opacity-10 rounded-circle filter-blur"></div>
                            
                            <!-- Imagen Hero Principal -->
                            <img 
                                src="/images/veterinary_hero.png" 
                                alt="Veterinaria Aprendizaje" 
                                class="img-fluid rounded-4 shadow-lg border border-white border-5 position-relative z-1 hero-image animate-float"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección de características/servicios -->
            <div class="bg-white py-5 border-top border-bottom">
                <div class="container py-4">
                    <div class="text-center max-w-xl mx-auto mb-5">
                        <h2 class="fw-bold text-dark fs-2 mb-3">Nuestros Servicios Integrales</h2>
                        <p class="text-secondary">Diseñamos una plataforma pensada en la comodidad tuya y de tus mascotas.</p>
                    </div>

                    <div class="row g-4">
                        <!-- Tarjeta 1 -->
                        <div class="col-md-6 col-lg-3">
                            <div class="card h-100 border-0 shadow-sm p-4 text-center hover-card transition-all">
                                <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center">
                                    <i class="bi bi-calendar2-check fs-4"></i>
                                </div>
                                <h5 class="fw-bold mb-2">Citas Online</h5>
                                <p class="text-muted small mb-0">Agenda consultas médicas escogiendo el día, sucursal, box y profesional que prefieras.</p>
                            </div>
                        </div>

                        <!-- Tarjeta 2 -->
                        <div class="col-md-6 col-lg-3">
                            <div class="card h-100 border-0 shadow-sm p-4 text-center hover-card transition-all">
                                <div class="icon-box bg-success bg-opacity-10 text-success rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center">
                                    <i class="bi bi-file-earmark-medical fs-4"></i>
                                </div>
                                <h5 class="fw-bold mb-2">Fichas Clínicas</h5>
                                <p class="text-muted small mb-0">Historial médico completo de tus mascotas, incluyendo diagnósticos, recetas e insumos.</p>
                            </div>
                        </div>

                        <!-- Tarjeta 3 -->
                        <div class="col-md-6 col-lg-3">
                            <div class="card h-100 border-0 shadow-sm p-4 text-center hover-card transition-all">
                                <div class="icon-box bg-warning bg-opacity-10 text-warning rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center">
                                    <i class="bi bi-credit-card fs-4"></i>
                                </div>
                                <h5 class="fw-bold mb-2">Pago en Línea</h5>
                                <p class="text-muted small mb-0">Paga las consultas directamente en la plataforma de forma segura con tarjetas de crédito o débito.</p>
                            </div>
                        </div>

                        <!-- Tarjeta 4 -->
                        <div class="col-md-6 col-lg-3">
                            <div class="card h-100 border-0 shadow-sm p-4 text-center hover-card transition-all">
                                <div class="icon-box bg-info bg-opacity-10 text-info rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center">
                                    <i class="bi bi-shield-heart fs-4"></i>
                                </div>
                                <h5 class="fw-bold mb-2">Cuidado Integral</h5>
                                <p class="text-muted small mb-0">Médicos calificados por especialidad listos para dar el trato que tu compañero merece.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Pie de página -->
        <footer class="bg-dark text-light py-4 border-top border-secondary border-opacity-25 mt-auto">
            <div class="container text-center">
                <p class="mb-1 fw-semibold text-white-50">Veterinaria Aprendizaje &copy; {{ new Date().getFullYear() }}</p>
                <p class="small text-muted mb-0">
                    Desarrollado con Laravel v{{ laravelVersion }} &bull; PHP v{{ phpVersion }}
                </p>
            </div>
        </footer>
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
/* Colores de fondo modernos */
.bg-light-modern {
    background: linear-gradient(135deg, #f3f4f6 0%, #f9fafb 100%);
}

/* Efectos de texto gradiente */
.text-gradient {
    background: linear-gradient(45deg, #0d6efd, #0dcaf0);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.fw-black {
    font-weight: 800;
}

/* Animaciones */
.animate-pulse {
    animation: pulse 2s infinite;
}

.animate-float {
    animation: float 6s ease-in-out infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.15); }
    100% { transform: scale(1); }
}

@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-12px); }
    100% { transform: translateY(0px); }
}

/* Imagen Hero */
.hero-image {
    max-height: 480px;
    width: 100%;
    object-fit: cover;
}

.hero-bg-glow {
    width: 350px;
    height: 350px;
    filter: blur(80px);
    z-index: 0;
}

/* Cajas de iconos en las tarjetas */
.icon-box {
    width: 60px;
    height: 60px;
}

/* Efectos de hover interactivos */
.hover-grow {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}
.hover-grow:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(13, 110, 253, 0.15) !important;
}

.hover-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 1rem 3rem rgba(0,0,0,.08) !important;
}

.max-w-xl {
    max-width: 36rem;
}
.mx-auto {
    margin-left: auto;
    margin-right: auto;
}
</style>
