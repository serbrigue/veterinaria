<template>
    <div class="d-flex min-vh-100 bg-light">
        <!-- Panel Izquierdo: Branding y Mensaje (oculto en pantallas pequeñas) -->
        <div class="col-lg-6 d-none d-lg-flex flex-column align-items-center justify-content-center text-white position-relative overflow-hidden" 
             style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
            
            <!-- Decoraciones de fondo (círculos) -->
            <div class="position-absolute rounded-circle" style="width: 600px; height: 600px; background: rgba(255,255,255,0.03); top: -100px; right: -100px;"></div>
            <div class="position-absolute rounded-circle" style="width: 400px; height: 400px; background: rgba(255,255,255,0.02); bottom: -50px; left: -50px;"></div>

            <div class="z-1 text-center px-5" style="max-width: 500px;">
                <div class="mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="72" height="72" fill="currentColor" class="bi bi-heart-pulse-fill text-primary" viewBox="0 0 16 16" style="filter: drop-shadow(0 0 10px rgba(13, 110, 253, 0.5));">
                        <path d="M1.475 9C2.702 10.84 4.779 12.871 8 15c3.221-2.129 5.298-4.16 6.525-6H12a.5.5 0 0 1-.464-.314l-1.457-3.642-1.598 5.593a.5.5 0 0 1-.945.049L5.889 6.568l-1.473 2.21A.5.5 0 0 1 4 9H1.475Z"/>
                        <path d="M.88 8C-2.427 1.68 4.41-2 7.823 1.143c.06.055.119.112.176.171a3.12 3.12 0 0 1 .176-.17C11.59-2 18.426 1.68 15.12 8h-2.783l-1.874-4.686a.5.5 0 0 0-.945.049L7.921 8.956 6.464 5.314a.5.5 0 0 0-.88-.091L3.732 8H.88Z"/>
                    </svg>
                </div>
                <h1 class="display-5 fw-bold mb-3">Veterinaria</h1>
                <p class="lead text-light opacity-75">Sistema integral de gestión veterinaria. Cuida de los que no tienen voz con la mejor tecnología.</p>
            </div>
        </div>

        <!-- Panel Derecho: Formulario -->
        <div class="col-12 col-lg-6 d-flex flex-column align-items-center justify-content-center bg-white shadow-lg position-relative">
            <!-- Botón Volver al inicio -->
            <Link href="/" class="btn btn-outline-secondary position-absolute top-0 end-0 m-4 rounded-pill px-3 shadow-sm border-0 bg-light" style="font-size: 0.85rem; transition: all 0.3s;">
                <i class="bi bi-arrow-left me-1"></i> Volver
            </Link>

            <div class="w-100 p-4 p-sm-5 mt-4 mt-lg-0" style="max-width: 480px;">
                <!-- Logo para móviles -->
                <div class="d-lg-none text-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-heart-pulse-fill text-primary" viewBox="0 0 16 16">
                        <path d="M1.475 9C2.702 10.84 4.779 12.871 8 15c3.221-2.129 5.298-4.16 6.525-6H12a.5.5 0 0 1-.464-.314l-1.457-3.642-1.598 5.593a.5.5 0 0 1-.945.049L5.889 6.568l-1.473 2.21A.5.5 0 0 1 4 9H1.475Z"/>
                        <path d="M.88 8C-2.427 1.68 4.41-2 7.823 1.143c.06.055.119.112.176.171a3.12 3.12 0 0 1 .176-.17C11.59-2 18.426 1.68 15.12 8h-2.783l-1.874-4.686a.5.5 0 0 0-.945.049L7.921 8.956 6.464 5.314a.5.5 0 0 0-.88-.091L3.732 8H.88Z"/>
                    </svg>
                    <h2 class="mt-2 fw-bold text-dark mb-0">Veterinaria</h2>
                </div>
                
                <slot />
            </div>
        </div>
    </div>
</template>

<script>
import { Link } from '@inertiajs/vue3'

export default {
    name: 'LayoutInvitado',
    components: {
        Link
    }
}
</script>
