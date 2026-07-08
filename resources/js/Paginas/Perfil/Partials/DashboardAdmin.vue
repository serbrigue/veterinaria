<template>
    <div class="row g-4 mb-4">
        <!-- Widget 1: Resumen General -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 rounded-4 border-top border-danger border-4 hover-lift transition-all">
                <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                    <div class="mb-3">
                        <div class="d-inline-flex bg-danger bg-opacity-10 rounded-circle p-2">
                            <img src="/images/medical_records_illustration.png" alt="Métricas" class="object-fit-contain" style="width: 80px; height: 80px;">
                        </div>
                    </div>
                    <h3 class="h5 fw-bold text-dark">Métricas del Sistema</h3>
                    <p class="text-muted small mb-4">Supervisa citas, ingresos financieros y nuevas mascotas registradas.</p>
                    <Link :href="route('panel')" class="btn btn-outline-danger btn-sm mt-auto rounded-pill px-4 fw-medium">Ver Reportes</Link>
                </div>
            </div>
        </div>

        <!-- Widget 2: Control de Usuarios -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 rounded-4 border-top border-primary border-4 hover-lift transition-all">
                <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                    <div class="mb-3">
                        <div class="d-inline-flex bg-primary bg-opacity-10 rounded-circle p-2">
                            <img src="/images/pets_illustration.png" alt="Usuarios" class="object-fit-contain" style="width: 80px; height: 80px;">
                        </div>
                    </div>
                    <h3 class="h5 fw-bold text-dark">Gestión de Usuarios</h3>
                    <p class="text-muted small mb-4">Administra cuentas de clientes, veterinarios y asigna permisos.</p>
                    <Link :href="route('clientes.listado')" class="btn btn-outline-primary btn-sm mt-auto rounded-pill px-4 fw-medium">Directorio de Cuentas</Link>
                </div>
            </div>
        </div>
        
        <!-- Widget 3: Infraestructura -->
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 border-top border-warning border-4 hover-lift transition-all">
                <div class="card-body p-4 d-flex flex-column flex-sm-row align-items-center justify-content-between text-center text-sm-start gap-3">
                    <div class="d-flex flex-column flex-sm-row align-items-center gap-3">
                        <div class="bg-warning bg-opacity-10 rounded-circle p-2 flex-shrink-0">
                            <img src="/images/calendar_illustration.png" alt="Sedes" class="object-fit-contain" style="width: 50px; height: 50px;">
                        </div>
                        <div>
                            <h3 class="h5 fw-bold text-dark mb-1">Sucursales y Boxes</h3>
                            <p class="text-muted small mb-0">Control total sobre la red de clínicas y organización de boxes físicos.</p>
                        </div>
                    </div>
                    <Link :href="route('sucursales.listado')" class="btn btn-warning text-dark btn-sm rounded-pill px-4 fw-bold flex-shrink-0">Gestionar Sedes</Link>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { Link } from '@inertiajs/vue3';

export default {
    name: 'DashboardAdmin',
    components: {
        Link,
    },
}
</script>
