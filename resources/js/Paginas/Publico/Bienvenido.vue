<template>
    <Head title="Bienvenido — Cuidado Profesional para tu Mascota" />

    <div class="min-vh-100 d-flex flex-column" style="background-color: #f8fafc;">
        <!-- Navbar Glassmorphism -->
        <nav class="navbar navbar-expand-lg navbar-light fixed-top shadow-sm" style="backdrop-filter: blur(12px); background-color: rgba(255, 255, 255, 0.85); border-bottom: 1px solid rgba(226, 232, 240, 0.8);">
            <div class="container py-1">
                <span class="navbar-brand d-flex align-items-center fw-bold text-dark fs-4">
                    <div class="bg-primary bg-gradient text-white rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm animate-pulse-slow" style="width: 40px; height: 40px;">
                        <i class="bi bi-heart-pulse-fill fs-5"></i>
                    </div>
                    Vet<span class="text-primary">Clinic</span>
                </span>
                
                <div class="d-flex gap-3 ms-auto align-items-center">
                    <template v-if="$page.props.auth.user">
                        <Link v-if="esAdmin" :href="route('panel')" class="btn btn-primary fw-semibold rounded-pill px-4 shadow-sm btn-hover-elevate">
                            <i class="bi bi-speedometer2 me-1"></i> Panel
                        </Link>
                        <Link v-else :href="route('perfil.editar')" class="btn btn-primary fw-semibold rounded-pill px-4 shadow-sm btn-hover-elevate">
                            <i class="bi bi-person-circle me-1"></i> Mi Perfil
                        </Link>
                    </template>
                    <template v-else-if="puedeIniciarSesion">
                        <Link :href="route('iniciar-sesion')" class="text-dark fw-semibold text-decoration-none nav-link-custom d-none d-sm-block">
                            Ingresar
                        </Link>
                        <Link v-if="puedeRegistrarse" :href="route('registrarse')" class="btn btn-primary fw-bold rounded-pill px-4 shadow-sm btn-hover-elevate">
                            Regístrate
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <main class="flex-grow-1" style="padding-top: 80px;">
            <div class="hero-wrapper position-relative overflow-hidden">
                <!-- Gradientes decorativos -->
                <div class="position-absolute top-0 start-0 w-100 h-100" style="background: radial-gradient(circle at 10% 20%, rgba(219, 234, 254, 0.6) 0%, transparent 40%), radial-gradient(circle at 90% 80%, rgba(224, 231, 255, 0.6) 0%, transparent 40%); z-index: 0;"></div>
                
                <div class="container py-5 py-lg-6 position-relative z-1">
                    <div class="row align-items-center g-5 min-vh-75">
                        <div class="col-lg-6 text-center text-lg-start">
                            <div class="d-inline-flex align-items-center bg-white border rounded-pill px-3 py-2 mb-4 shadow-sm animate-fade-in-up">
                                <span class="badge bg-primary rounded-pill me-2">Nuevo</span>
                                <span class="text-muted small fw-medium">Atención 24/7 disponible ahora</span>
                            </div>
                            
                            <h1 class="display-3 fw-bold text-dark mb-4 lh-sm animate-fade-in-up" style="animation-delay: 0.1s; letter-spacing: -1px;">
                                El cuidado que <br class="d-none d-lg-block">
                                <span class="text-transparent bg-clip-text bg-gradient-primary">merece tu familia</span>
                            </h1>
                            
                            <p class="lead text-secondary mb-5 fs-5 pe-lg-5 animate-fade-in-up" style="animation-delay: 0.2s;">
                                Plataforma integral para agendar consultas, revisar historiales médicos y asegurar la mejor calidad de vida para tus mascotas.
                            </p>
                            
                            <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start animate-fade-in-up" style="animation-delay: 0.3s;">
                                <template v-if="$page.props.auth.user">
                                    <Link :href="esAdmin ? route('panel') : route('perfil.editar')" class="btn btn-primary btn-lg rounded-pill px-5 shadow-lg btn-hover-elevate fw-semibold">
                                        Ir al Panel <i class="bi bi-arrow-right ms-2"></i>
                                    </Link>
                                </template>
                                <template v-else>
                                    <Link v-if="puedeRegistrarse" :href="route('registrarse')" class="btn btn-primary btn-lg rounded-pill px-5 shadow-lg btn-hover-elevate fw-semibold">
                                        Comenzar ahora
                                    </Link>
                                    <Link :href="route('iniciar-sesion')" class="btn btn-outline-dark bg-white btn-lg rounded-pill px-5 shadow-sm btn-hover-elevate fw-semibold">
                                        Saber más
                                    </Link>
                                </template>
                            </div>
                        </div>

                        <div class="col-lg-6 animate-fade-in" style="animation-delay: 0.4s;">
                            <div class="position-relative hero-image-container">
                                <!-- Elementos flotantes 3D o imágenes superpuestas -->
                                <div class="position-absolute bg-white p-3 rounded-4 shadow-lg floating-card" style="top: 10%; left: -5%; z-index: 2;">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-success bg-opacity-10 text-success rounded-circle p-2">
                                            <i class="bi bi-check-circle-fill fs-5"></i>
                                        </div>
                                        <div>
                                            <p class="mb-0 fw-bold text-dark lh-1">Cita Confirmada</p>
                                            <small class="text-muted">Hoy, 14:30 hrs</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <img 
                                    src="/images/veterinary_hero.png" 
                                    alt="Cuidado veterinario" 
                                    class="img-fluid rounded-5 shadow-xl main-hero-img"
                                    style="border: 8px solid white;"
                                />

                                <div class="position-absolute bg-white p-3 rounded-4 shadow-lg floating-card-reverse" style="bottom: 10%; right: -5%; z-index: 2;">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2">
                                            <i class="bi bi-star-fill fs-5"></i>
                                        </div>
                                        <div>
                                            <h4 class="mb-0 fw-bold text-dark lh-1">4.9/5</h4>
                                            <small class="text-muted">1,200+ Reseñas</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Servicios Premium -->
            <div class="py-6 bg-white position-relative z-1" style="margin-top: -20px; border-top-left-radius: 40px; border-top-right-radius: 40px; box-shadow: 0 -10px 40px rgba(0,0,0,0.03);">
                <div class="container pt-5 pb-6">
                    <div class="text-center mx-auto mb-5" style="max-width: 600px;">
                        <h2 class="display-6 fw-bold text-dark mb-3">Servicios de primer nivel</h2>
                        <p class="text-secondary fs-5">Todo lo que necesitas para la salud y felicidad de tus mascotas en una sola plataforma intuitiva.</p>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6 col-xl-3">
                            <div class="service-card h-100 p-4 rounded-4 bg-light border-0">
                                <div class="icon-wrapper bg-white shadow-sm mb-4 rounded-circle d-flex align-items-center justify-content-center overflow-hidden" style="width: 72px; height: 72px;">
                                    <img src="/images/icon_appointment.png" alt="Agendamiento" class="img-fluid w-100 h-100 object-fit-cover" />
                                </div>
                                <h4 class="fw-bold mb-2">Agendamiento Web</h4>
                                <p class="text-muted mb-0">Reserva horas con especialistas 24/7 sin llamadas ni esperas, totalmente en línea.</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <div class="service-card h-100 p-4 rounded-4 bg-light border-0">
                                <div class="icon-wrapper bg-white shadow-sm mb-4 rounded-circle d-flex align-items-center justify-content-center overflow-hidden" style="width: 72px; height: 72px;">
                                    <img src="/images/icon_history.png" alt="Historial" class="img-fluid w-100 h-100 object-fit-cover" />
                                </div>
                                <h4 class="fw-bold mb-2">Historial Clínico</h4>
                                <p class="text-muted mb-0">Accede a vacunas, diagnósticos y tratamientos pasados desde cualquier lugar.</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <div class="service-card h-100 p-4 rounded-4 bg-light border-0">
                                <div class="icon-wrapper bg-white shadow-sm mb-4 rounded-circle d-flex align-items-center justify-content-center overflow-hidden" style="width: 72px; height: 72px;">
                                    <img src="/images/icon_payment.png" alt="Pagos" class="img-fluid w-100 h-100 object-fit-cover" />
                                </div>
                                <h4 class="fw-bold mb-2">Pagos Seguros</h4>
                                <p class="text-muted mb-0">Paga las consultas e insumos con tarjetas de crédito de forma rápida y encriptada.</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <div class="service-card h-100 p-4 rounded-4 bg-light border-0">
                                <div class="icon-wrapper bg-white shadow-sm mb-4 rounded-circle d-flex align-items-center justify-content-center overflow-hidden" style="width: 72px; height: 72px;">
                                    <img src="/images/icon_communication.png" alt="Comunicación" class="img-fluid w-100 h-100 object-fit-cover" />
                                </div>
                                <h4 class="fw-bold mb-2">Comunicación</h4>
                                <p class="text-muted mb-0">Recibe recordatorios, resultados y recetas médicas directo en tu correo.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CTA Final -->
            <div class="bg-primary bg-gradient text-white py-5">
                <div class="container text-center py-5">
                    <h2 class="display-5 fw-bold mb-4">¿Listo para unirte a la familia?</h2>
                    <p class="lead mb-5 opacity-75 mx-auto" style="max-width: 600px;">Crea tu cuenta gratuita hoy mismo y experimenta la tranquilidad de tener el control total sobre la salud de tus mascotas.</p>
                    <Link v-if="puedeRegistrarse" :href="route('registrarse')" class="btn btn-light btn-lg rounded-pill px-5 py-3 shadow fw-bold text-primary btn-hover-elevate">
                        Crear mi cuenta gratis
                    </Link>
                </div>
            </div>
        </main>

        <!-- Footer moderno -->
        <footer class="bg-primary text-white py-5">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                        <div class="d-flex align-items-center justify-content-center justify-content-md-start mb-2">
                            <i class="bi bi-heart-pulse-fill text-primary me-2"></i>
                            <span class="fs-5 fw-bold">Veterinaria</span>
                        </div>
                        <p class="text-muted small mb-0">&copy; {{ new Date().getFullYear() }} Todos los derechos reservados.</p>
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        <div class="text-muted small">
                            Plataforma desarrollada con <i class="bi bi-heart-fill text-danger mx-1"></i> usando<br>
                            <span class="text-light">Laravel v{{ laravelVersion }}</span> &bull; <span class="text-light">PHP v{{ phpVersion }}</span>
                        </div>
                    </div>
                </div>
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
    computed: {
        esAdmin() {
            const user = this.$page.props.auth.user;
            return user && user.rol && user.rol.nombre_interno === 'admin';
        }
    }
}
</script>

<style scoped>
/* Tipografía y Textos */
.bg-clip-text {
    -webkit-background-clip: text;
    background-clip: text;
}
.text-transparent {
    color: transparent;
}
.bg-gradient-primary {
    background-image: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
}

/* Espaciados Personalizados */
.py-6 { padding-top: 5rem; padding-bottom: 5rem; }
.min-vh-75 { min-height: 75vh; }

/* Animaciones */
.animate-pulse-slow {
    animation: pulse 3s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.4); }
    50% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(13, 110, 253, 0); }
    100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(13, 110, 253, 0); }
}

.animate-fade-in-up {
    animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
}

.animate-fade-in {
    animation: fadeIn 1s ease-out both;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Elementos Flotantes (Hero) */
.floating-card {
    animation: float 6s ease-in-out infinite;
}
.floating-card-reverse {
    animation: float-reverse 7s ease-in-out infinite;
}

@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-15px); }
    100% { transform: translateY(0px); }
}
@keyframes float-reverse {
    0% { transform: translateY(0px); }
    50% { transform: translateY(15px); }
    100% { transform: translateY(0px); }
}

/* Interacciones Hover */
.btn-hover-elevate {
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}
.btn-hover-elevate:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
}

.nav-link-custom {
    transition: color 0.2s ease;
}
.nav-link-custom:hover {
    color: #0d6efd !important;
}

/* Tarjetas de Servicio */
.service-card {
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    border: 1px solid rgba(0,0,0,0.02) !important;
}
.service-card:hover {
    transform: translateY(-10px);
    background-color: #fff !important;
    box-shadow: 0 1rem 3rem rgba(0,0,0,0.08) !important;
}
.service-card .icon-wrapper {
    transition: transform 0.3s ease;
}
.service-card:hover .icon-wrapper {
    transform: scale(1.1) rotate(5deg);
}

.main-hero-img {
    object-position: center;
}
</style>
