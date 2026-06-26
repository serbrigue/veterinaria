<template>
    <Head :title="'Cliente - ' + (cliente.usuario?.name || 'Detalle')" />

    <AuthenticatedLayout>
        <div class="container py-4">
            <!-- Encabezado con botón de volver -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center gap-3">
                    <Link :href="route('clientes.listado')" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                        <i class="bi bi-arrow-left"></i> Volver a Clientes
                    </Link>
                    <h1 class="h3 mb-0 text-dark fw-bold">Expediente del Cliente</h1>
                </div>
            </div>

            <div class="row g-4">
                <!-- COLUMNA IZQUIERDA: PERFIL DEL CLIENTE -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 h-100">
                        <div class="card-header bg-primary bg-opacity-10 border-0 pt-4 pb-0 text-center">
                            <div class="position-relative d-inline-block mb-3">
                                <img v-if="cliente.foto_perfil_url" :src="cliente.foto_perfil_url" :alt="cliente.usuario?.name" class="rounded-circle object-fit-cover shadow border border-3 border-white" style="width: 120px; height: 120px;">
                                <div v-else class="bg-primary text-white rounded-circle shadow d-flex align-items-center justify-content-center border border-3 border-white" style="width: 120px; height: 120px;">
                                    <i class="bi bi-person-fill" style="font-size: 4rem;"></i>
                                </div>
                            </div>
                            <h2 class="h5 fw-bold text-dark mb-1">{{ cliente.usuario?.name || 'Cliente sin nombre' }}</h2>
                            <p class="text-muted small mb-3">
                                <i class="bi bi-person-badge me-1"></i> ID: #{{ cliente.id.toString().padStart(4, '0') }}
                            </p>
                            
                            <!-- Badge de Deuda Global -->
                            <div class="mb-4">
                                <div v-if="transaccionesPendientesCount > 0" class="badge bg-danger rounded-pill px-3 py-2 fs-6 shadow-sm">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Deuda Activa: {{ formatearDinero(deudaTotal) }}
                                </div>
                                <div v-else class="badge bg-success rounded-pill px-3 py-2 fs-6 shadow-sm">
                                    <i class="bi bi-check-circle-fill me-1"></i> Cuenta al día
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4 pt-2">
                            <h3 class="h6 text-uppercase fw-bold text-muted mb-3" style="letter-spacing: 0.5px;">Información de Contacto</h3>
                            
                            <ul class="list-unstyled mb-0">
                                <li class="mb-3 d-flex align-items-center gap-3">
                                    <div class="bg-light rounded p-2 text-secondary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="bi bi-envelope-fill fs-5"></i>
                                    </div>
                                    <div>
                                        <span class="d-block small text-muted">Correo Electrónico</span>
                                        <span class="fw-medium text-dark">{{ cliente.usuario?.email || 'No registrado' }}</span>
                                    </div>
                                </li>
                                <li class="mb-3 d-flex align-items-center gap-3">
                                    <div class="bg-light rounded p-2 text-secondary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="bi bi-telephone-fill fs-5"></i>
                                    </div>
                                    <div>
                                        <span class="d-block small text-muted">Teléfono</span>
                                        <span class="fw-medium text-dark">{{ cliente.telefono || 'No registrado' }}</span>
                                    </div>
                                </li>
                                <li class="d-flex align-items-center gap-3">
                                    <div class="bg-light rounded p-2 text-secondary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="bi bi-geo-alt-fill fs-5"></i>
                                    </div>
                                    <div>
                                        <span class="d-block small text-muted">Dirección</span>
                                        <span class="fw-medium text-dark">{{ cliente.direccion || 'No registrada' }}</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- COLUMNA DERECHA: MASCOTAS Y TRANSACCIONES -->
                <div class="col-lg-8">
                    
                    <!-- SECCIÓN: MASCOTAS DEL CLIENTE -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                            <h3 class="h5 mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                                <i class="bi bi-hearts text-danger"></i> Pacientes (Mascotas)
                            </h3>
                            <span class="badge bg-secondary rounded-pill">{{ cliente.mascotas?.length || 0 }} Registros</span>
                        </div>
                        <div class="card-body p-4">
                            <div v-if="!cliente.mascotas || cliente.mascotas.length === 0" class="text-center py-4 bg-light rounded-3">
                                <i class="bi bi-info-circle text-muted fs-2 d-block mb-2"></i>
                                <span class="text-muted small">Este cliente aún no tiene mascotas registradas.</span>
                            </div>
                            <div v-else class="row g-3">
                                <div v-for="mascota in cliente.mascotas" :key="mascota.id" class="col-md-6">
                                    <Link :href="route('mascotas.detalle', mascota.id)" class="text-decoration-none">
                                        <div class="d-flex align-items-center gap-3 p-3 border rounded-3 hover-shadow transition-all bg-white h-100">
                                            <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 50px; height: 50px;">
                                                <img v-if="mascota.imagen_url" :src="mascota.imagen_url" class="rounded-circle object-fit-cover w-100 h-100">
                                                <i v-else class="bi bi-heart-fill fs-5"></i>
                                            </div>
                                            <div class="overflow-hidden">
                                                <h4 class="h6 mb-1 fw-bold text-dark text-truncate">{{ mascota.nombre }}</h4>
                                                <p class="text-muted small mb-0 text-truncate">
                                                    {{ mascota.raza?.especie?.nombre || 'Especie N/A' }} - {{ mascota.raza?.nombre || 'Raza N/A' }}
                                                </p>
                                            </div>
                                            <div class="ms-auto text-muted">
                                                <i class="bi bi-chevron-right"></i>
                                            </div>
                                        </div>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN: HISTORIAL DE PAGOS / TRANSACCIONES -->
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                            <h3 class="h5 mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                                <i class="bi bi-receipt-cutoff text-success"></i> Historial de Transacciones
                            </h3>
                        </div>
                        <div class="card-body p-4">
                            <div v-if="!transacciones.data || transacciones.data.length === 0" class="text-center py-4 bg-light rounded-3">
                                <i class="bi bi-wallet2 text-muted fs-2 d-block mb-2"></i>
                                <span class="text-muted small">No hay registro de transacciones para este cliente.</span>
                            </div>
                            <div v-else class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-secondary small fw-bold text-uppercase border-0 rounded-start">Fecha</th>
                                            <th class="text-secondary small fw-bold text-uppercase border-0">Concepto</th>
                                            <th class="text-secondary small fw-bold text-uppercase border-0 text-end">Monto</th>
                                            <th class="text-secondary small fw-bold text-uppercase border-0 text-center rounded-end">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="tx in transacciones.data" :key="tx.id">
                                            <td>
                                                <span class="d-block fw-medium text-dark small">{{ formatearFechaCorta(tx.created_at) }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-bold text-dark small">Atención Médica</span>
                                                    <Link v-if="tx.cita" :href="route('citas.detalle', tx.cita.id)" class="text-muted small text-decoration-none hover-primary text-truncate d-inline-block" style="max-width: 200px;">
                                                        <i class="bi bi-link-45deg"></i> {{ tx.cita.titulo || 'Cita #' + tx.cita.id }}
                                                    </Link>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <span class="fw-bold text-dark">{{ formatearDinero(tx.monto_total) }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge rounded-pill px-3 py-1" :class="{
                                                    'bg-success': tx.estado === 'pagado',
                                                    'bg-danger': tx.estado === 'pendiente',
                                                    'bg-warning text-dark': tx.estado === 'abonado',
                                                    'bg-secondary': tx.estado === 'anulado',
                                                }">
                                                    {{ tx.estado.charAt(0).toUpperCase() + tx.estado.slice(1) }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Controles de Paginación -->
                            <div v-if="transacciones.last_page > 1" class="d-flex justify-content-between align-items-center mt-4">
                                <div class="text-muted small">
                                    Mostrando {{ transacciones.from }} a {{ transacciones.to }} de {{ transacciones.total }}
                                </div>
                                <nav aria-label="Navegación de páginas">
                                    <ul class="pagination pagination-sm mb-0">
                                        <li class="page-item" :class="{ disabled: !transacciones.prev_page_url }">
                                            <Link class="page-link" :href="transacciones.prev_page_url || '#'" preserve-scroll>Anterior</Link>
                                        </li>
                                        <li 
                                            v-for="link in transacciones.links.slice(1, -1)" 
                                            :key="link.label" 
                                            class="page-item" 
                                            :class="{ active: link.active }"
                                        >
                                            <Link class="page-link" :href="link.url || '#'" v-html="link.label" preserve-scroll></Link>
                                        </li>
                                        <li class="page-item" :class="{ disabled: !transacciones.next_page_url }">
                                            <Link class="page-link" :href="transacciones.next_page_url || '#'" preserve-scroll>Siguiente</Link>
                                        </li>
                                    </ul>
                                </nav>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from '@/Disenos/LayoutAutenticado.vue';
import { Head, Link } from '@inertiajs/vue3';

export default {
    components: {
        AuthenticatedLayout,
        Head,
        Link
    },
    props: {
        cliente: {
            type: Object,
            required: true
        },
        transacciones: {
            type: Object,
            required: true
        },
        deudaTotal: {
            type: Number,
            default: 0
        },
        transaccionesPendientesCount: {
            type: Number,
            default: 0
        }
    },
    methods: {
        formatearDinero(monto) {
            return '$' + Math.round(monto).toLocaleString('es-CL');
        },
        formatearFechaCorta(fechaStr) {
            if (!fechaStr) return 'N/A';
            const f = new Date(fechaStr);
            return f.toLocaleDateString('es-CL', { day: '2-digit', month: 'short', year: 'numeric' });
        }
    }
}
</script>

<style scoped>
.hover-shadow:hover {
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    transform: translateY(-2px);
}
.hover-primary:hover {
    color: var(--bs-primary) !important;
}
.transition-all {
    transition: all 0.3s ease;
}
</style>
