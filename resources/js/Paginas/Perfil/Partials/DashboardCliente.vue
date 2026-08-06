<template>
    <!-- ================================================================================== -->
    <!-- COMPONENTE: DashboardCliente -->
    <!-- ================================================================================== -->
    <div class="row g-4 mb-4">
        <!-- Widget 1: Mascotas -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 rounded-4 border-top border-success border-4 hover-lift transition-all">
                <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                    <div class="mb-3">
                        <div class="d-inline-flex bg-success bg-opacity-10 rounded-circle p-2">
                            <img :src="mascota?.imagen_url || '/images/pets_illustration.png'" alt="Foto de la mascota" class="rounded-circle object-fit-cover border shadow-sm" style="width: 80px; height: 80px;">
                        </div>
                    </div>  
                    <h3 class="h5 fw-bold text-dark">Mis Mascotas</h3>
                    <p class="text-muted small mb-4">Revisa las fichas médicas y perfiles de tus compañeros de vida.</p>
                    <Link :href="route('mascotas.listado')" class="btn btn-outline-success btn-sm mt-auto rounded-pill px-4 fw-medium">Gestionar Mascotas</Link>
                </div>
            </div>
        </div>

        <!-- Widget 2: Citas -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 rounded-4 border-top border-primary border-4 hover-lift transition-all">
                <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                    <div class="mb-3">
                        <div class="d-inline-flex bg-primary bg-opacity-10 rounded-circle p-2">
                            <img :src="veterinario?.foto_perfil_url || '/images/calendar_illustration.png'" alt="Foto del veterinario" class="rounded-circle object-fit-cover border shadow-sm" style="width: 80px; height: 80px;">
                        </div>
                    </div>
                    <h3 class="h5 fw-bold text-dark">Mis Citas</h3>
                    <p class="text-muted small mb-4">Agenda nuevas horas o revisa tus próximas visitas a la clínica.</p>
                    <Link :href="route('citas.listado')" class="btn btn-outline-primary btn-sm mt-auto rounded-pill px-4 fw-medium">Ver Agenda</Link>
                </div>
            </div>
        </div>
        
        <!-- Widget 3: Finanzas e Historial -->
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 border-top border-warning border-4 hover-lift transition-all">
                <div class="card-body p-4 d-flex flex-column flex-sm-row align-items-center justify-content-between text-center text-sm-start gap-3">
                    <div class="d-flex flex-column flex-sm-row align-items-center gap-3">
                        <div class="bg-warning bg-opacity-10 rounded-circle p-2 flex-shrink-0">
                            <img src="/images/finance_illustration.png" alt="Finanzas" class="object-fit-contain" style="width: 50px; height: 50px;">
                        </div>
                        <div>
                            <h3 class="h5 fw-bold text-dark mb-1">Historial Clínico y Finanzas</h3>
                            <p class="text-muted small mb-0">Revisa recetas médicas, boletas emitidas y atenciones previas de tus mascotas.</p>
                        </div>
                    </div>
                    <Link :href="route('citas.listado') + '?estado=completada'" class="btn btn-warning text-dark btn-sm rounded-pill px-4 fw-bold flex-shrink-0">Revisar Historial</Link>
                </div>
            </div>
        </div>

        <!-- Widget 4: Próxima Cita (Destacado) -->
        <!-- DIRECTIVA (v-if): Renderizado condicional basado en "proximasCitas && proximasCitas.fecha_hora" -->
        <div class="col-md-6" v-if="proximasCitas && proximasCitas.fecha_hora">
            <div class="card border-0 shadow-sm h-100 rounded-4 border-start border-info border-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="h6 fw-bold text-info text-uppercase mb-0"><i class="bi bi-clock-history me-1"></i> Próxima Cita</h3>
                        <span class="badge bg-info text-dark rounded-pill">Agendada</span>
                    </div>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-light rounded p-3 text-center border shadow-sm">
                            <span class="d-block h4 fw-bold text-dark mb-0">{{ formatearDia(proximasCitas.fecha_hora) }}</span>
                            <span class="d-block small text-muted text-uppercase fw-bold">{{ formatearMes(proximasCitas.fecha_hora) }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <img :src="proximasCitas.mascota?.foto_url || `https://ui-avatars.com/api/?name=${proximasCitas.mascota?.nombre || 'Mascota'}&background=random&color=fff&rounded=true`" 
                                 alt="Foto mascota" class="rounded-circle shadow-sm" style="width: 50px; height: 50px; object-fit: cover;">
                            <div>
                                <h4 class="h6 fw-bold text-dark mb-1">{{ proximasCitas.mascota?.nombre || 'Tu mascota' }}</h4>
                                <p class="small text-muted mb-0"><i class="bi bi-clock me-1"></i> {{ formatearHora(proximasCitas.fecha_hora) }} hrs</p>
                            </div>
                        </div>
                    </div>
                    <Link :href="route('citas.detalle', proximasCitas.id)" class="btn btn-outline-info w-100 btn-sm rounded-pill fw-medium">Ver Detalles</Link>
                </div>
            </div>
        </div>
        <div class="col-md-6" v-else>
            <div class="card border-0 shadow-sm h-100 rounded-4 border-start border-secondary border-opacity-25 border-4 bg-light">
                <div class="card-body p-4 text-center d-flex flex-column justify-content-center align-items-center">
                    <i class="bi bi-calendar-x text-muted opacity-50 mb-2 fs-1"></i>
                    <h3 class="h6 fw-bold text-secondary mb-1">Sin Citas Próximas</h3>
                    <p class="small text-muted mb-0">No tienes agendada ninguna visita por ahora.</p>
                </div>
            </div>
        </div>

        <!-- Widget 5: Última Cita (Historial) -->
        <!-- DIRECTIVA (v-if): Renderizado condicional basado en "historialClinico && historialClinico.id" -->
        <div class="col-md-6" v-if="historialClinico && historialClinico.id">
            <div class="card border-0 shadow-sm h-100 rounded-4 border-start border-secondary border-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="h6 fw-bold text-secondary text-uppercase mb-0"><i class="bi bi-journal-check me-1"></i> Última Atención</h3>
                        <span class="badge bg-secondary rounded-pill">Completada</span>
                    </div>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-light rounded p-3 text-center border shadow-sm">
                            <span class="d-block h4 fw-bold text-dark mb-0">{{ formatearDia(historialClinico.fecha_hora) }}</span>
                            <span class="d-block small text-muted text-uppercase fw-bold">{{ formatearMes(historialClinico.fecha_hora) }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <img :src="historialClinico.mascota?.foto_url || `https://ui-avatars.com/api/?name=${historialClinico.mascota?.nombre || 'Mascota'}&background=random&color=fff&rounded=true`" 
                                 alt="Foto mascota" class="rounded-circle shadow-sm border" style="width: 50px; height: 50px; object-fit: cover;">
                            <div>
                                <h4 class="h6 fw-bold text-dark mb-1">{{ historialClinico.mascota?.nombre || 'Tu mascota' }}</h4>
                                <p class="small text-muted mb-0"><i class="bi bi-check2-circle text-success me-1"></i> Finalizada</p>
                            </div>
                        </div>
                    </div>
                    <Link :href="route('citas.detalle', historialClinico.id)" class="btn btn-outline-secondary w-100 btn-sm rounded-pill fw-medium">Ver Resumen</Link>
                </div>
            </div>
        </div>
        <div class="col-md-6" v-else>
            <div class="card border-0 shadow-sm h-100 rounded-4 border-start border-secondary border-opacity-25 border-4 bg-light">
                <div class="card-body p-4 text-center d-flex flex-column justify-content-center align-items-center">
                    <i class="bi bi-journal-x text-muted opacity-50 mb-2 fs-1"></i>
                    <h3 class="h6 fw-bold text-secondary mb-1">Sin Historial</h3>
                    <p class="small text-muted mb-0">Aún no tienes registros de atenciones previas.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
// ==================================================================================
// LÓGICA DEL COMPONENTE (VUE 3)
// ==================================================================================

import { Link } from '@inertiajs/vue3';

// ------------------------------------------------------------------------------
// EXPORT DEFAULT: Definición principal del componente
// ------------------------------------------------------------------------------
export default {
    name: 'DashboardCliente',
    // COMPONENTES (COMPONENTS): Registro de componentes importados
    components: {
        Link,
    },
    // PROPIEDADES (PROPS): Datos inyectados desde el componente padre o estado
    props: {
        proximasCitas: {
            type: Object,
        },
        historialClinico: {
            type: Object,
        },
        mascota: {
            type: Object,
        },
        veterinario: {
            type: Object,
        },
    },
    // MÉTODOS (METHODS): Bloque de funciones y eventos
    methods: {
        formatearDia(fechaIso) {
            if (!fechaIso) return '--';
            const fecha = new Date(fechaIso);
            return fecha.getDate().toString().padStart(2, '0');
        },
        formatearMes(fechaIso) {
            if (!fechaIso) return '---';
            const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            const fecha = new Date(fechaIso);
            return meses[fecha.getMonth()];
        },
        formatearHora(fechaIso) {
            if (!fechaIso) return '--:--';
            const fecha = new Date(fechaIso);
            return fecha.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' });
        },
    },
}
</script>
