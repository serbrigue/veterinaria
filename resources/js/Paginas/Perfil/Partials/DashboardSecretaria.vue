<template>
    <div class="row g-4 mb-4">
        <!-- Widget 1: Agenda de Citas -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 rounded-4 border-top border-primary border-4 hover-lift transition-all">
                <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                    <div class="mb-3">
                        <div class="d-inline-flex bg-primary bg-opacity-10 rounded-circle p-2">
                            <img src="/images/calendar_illustration.png" alt="Agenda" class="object-fit-contain" style="width: 80px; height: 80px;">
                        </div>
                    </div>
                    <h3 class="h5 fw-bold text-dark">Agenda de Citas</h3>
                    <p class="text-muted small mb-4">Revisa, agenda y gestiona las citas médicas de la clínica.</p>
                    <Link :href="route('citas.listado')" class="btn btn-outline-primary btn-sm mt-auto rounded-pill px-4 fw-medium">Ver Agenda Global</Link>
                </div>
            </div>
        </div>

        <!-- Widget 2: Directorio de Clientes -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 rounded-4 border-top border-success border-4 hover-lift transition-all">
                <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                    <div class="mb-3">
                        <div class="d-inline-flex bg-success bg-opacity-10 rounded-circle p-2">
                            <img src="/images/medical_records_illustration.png" alt="Clientes" class="object-fit-contain" style="width: 80px; height: 80px;">
                        </div>
                    </div>
                    <h3 class="h5 fw-bold text-dark">Registro de Clientes</h3>
                    <p class="text-muted small mb-4">Administra las cuentas de clientes y sus datos de contacto.</p>
                    <Link :href="route('clientes.listado')" class="btn btn-outline-success btn-sm mt-auto rounded-pill px-4 fw-medium">Buscar Cliente</Link>
                </div>
            </div>
        </div>

        <!-- Widget 3: Fichas de Mascotas -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 rounded-4 border-top border-warning border-4 hover-lift transition-all">
                <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                    <div class="mb-3">
                        <div class="d-inline-flex bg-warning bg-opacity-10 rounded-circle p-2">
                            <img src="/images/pets_illustration.png" alt="Mascotas" class="object-fit-contain" style="width: 80px; height: 80px;">
                        </div>
                    </div>
                    <h3 class="h5 fw-bold text-dark">Control de Mascotas</h3>
                    <p class="text-muted small mb-4">Busca historiales clínicos y asocia nuevas mascotas a sus dueños.</p>
                    <Link :href="route('mascotas.listado')" class="btn btn-outline-warning text-dark btn-sm mt-auto rounded-pill px-4 fw-medium">Ver Fichas</Link>
                </div>
            </div>
        </div>

        <!-- Widget 4: Caja y Transacciones -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 rounded-4 border-top border-info border-4 hover-lift transition-all">
                <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                    <div class="mb-3">
                        <div class="d-inline-flex bg-info bg-opacity-10 rounded-circle p-2">
                            <img src="/images/finance_illustration.png" alt="Caja" class="object-fit-contain" style="width: 80px; height: 80px;">
                        </div>
                    </div>
                    <h3 class="h5 fw-bold text-dark">Caja y Transacciones</h3>
                    <p class="text-muted small mb-4">Revisa boletas emitidas, pagos de citas y transacciones del día.</p>
                    <Link :href="route('ingresos.listado')" class="btn btn-outline-info btn-sm mt-auto rounded-pill px-4 fw-medium">Ver Caja</Link>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { Link } from '@inertiajs/vue3';

export default {
    name: 'DashboardSecretaria',
    components: {
        Link,
    },
}
</script>
