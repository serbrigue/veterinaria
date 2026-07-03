<template>
    <Head :title="`Veterinario - ${veterinario?.usuario?.name || 'Detalle'}`" />
    <AuthenticatedLayout>
        <div class="container py-4">
            
            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb bg-light p-3 rounded-pill shadow-sm border border-light">
                    <li class="breadcrumb-item">
                        <Link :href="route('perfil.editar')" class="text-decoration-none text-muted hover-primary transition-all">
                            <i class="bi bi-house-door"></i> Inicio
                        </Link>
                    </li>
                    <li class="breadcrumb-item">
                        <Link :href="route('veterinarios.listado')" class="text-decoration-none text-muted hover-primary transition-all">
                            Veterinarios
                        </Link>
                    </li>
                    <li class="breadcrumb-item active fw-bold text-primary" aria-current="page">
                        {{ veterinario?.usuario?.name || 'Detalles' }}
                    </li>
                </ol>
            </nav>

            <div v-if="veterinario" class="row g-4">
                <!-- Tarjeta Principal de Información -->
                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                        <div class="position-relative bg-light text-center py-5" style="min-height: 200px;">
                            <div class="position-absolute bottom-0 start-0 w-100 h-50 bg-gradient-dark pointer-events-none"></div>
                            <div class="d-flex align-items-center justify-content-center flex-column position-relative z-index-1 mt-3">
                                <img v-if="veterinario.foto_perfil_url" :src="veterinario.foto_perfil_url" class="rounded-circle shadow-lg mb-3" style="width: 140px; height: 140px; object-fit: cover; border: 4px solid white;" alt="Foto de perfil">
                                <div v-else class="rounded-circle shadow-lg mb-3 bg-primary text-white d-flex align-items-center justify-content-center" style="width: 140px; height: 140px; border: 4px solid white;">
                                    <i class="bi bi-person-fill display-1"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-body p-4 pt-2 text-center bg-white">
                            <h2 class="h4 mb-1 fw-bold text-dark">{{ veterinario.usuario?.name }}</h2>
                            <span class="badge bg-primary bg-opacity-10 text-primary mt-1 mb-4 rounded-pill px-3 py-2 fs-6">{{ veterinario.especialidad?.nombre || 'Sin Especialidad' }}</span>
                            
                            <hr class="text-muted opacity-25 mx-4">
                            
                            <div class="d-flex justify-content-center gap-2 mt-4">
                                <Link :href="route('veterinarios.listado')" class="btn btn-outline-secondary rounded-pill px-4">
                                    <i class="bi bi-arrow-left me-1"></i> Volver al Listado
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detalles de Contacto y Trabajo -->
                <div class="col-12 col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white border-bottom-0 p-4 pb-0">
                            <h3 class="h5 mb-0 text-primary fw-bold">Información de Contacto</h3>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <!-- Datos de Contacto -->
                                <div class="col-12 col-md-6">
                                    <div class="d-flex align-items-start mb-4">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                            <i class="bi bi-envelope fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-muted small text-uppercase mb-1 fw-bold">Correo Electrónico</h6>
                                            <p class="mb-0 fw-medium text-dark">{{ veterinario.usuario?.email || 'No registrado' }}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex align-items-start mb-4">
                                        <div class="bg-success bg-opacity-10 text-success rounded-circle p-3 me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                            <i class="bi bi-telephone fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-muted small text-uppercase mb-1 fw-bold">Teléfono</h6>
                                            <p class="mb-0 fw-medium text-dark">{{ veterinario.telefono || 'No registrado' }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-12 col-md-6">
                                    <div class="d-flex align-items-start mb-4">
                                        <div class="bg-info bg-opacity-10 text-info rounded-circle p-3 me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                            <i class="bi bi-geo-alt fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-muted small text-uppercase mb-1 fw-bold">Dirección</h6>
                                            <p class="mb-0 fw-medium text-dark">{{ veterinario.direccion || 'No registrada' }}</p>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-start mb-4">
                                        <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-3 me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                            <i class="bi bi-building fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-muted small text-uppercase mb-1 fw-bold">Sucursal Principal</h6>
                                            <p class="mb-0 fw-medium text-dark">
                                                <span v-if="veterinario.sucursal" class="badge bg-light text-dark border">{{ veterinario.sucursal.nombre }}</span>
                                                <span v-else class="text-muted fst-italic">No asignada</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4 text-muted opacity-25">

                            <!-- Actividad o Info Adicional (Placeholder) -->
                            
                            <div v-if="esVeterinarioOAdmin"class="row g-3">
                                <h3 class="h6 mb-3 text-secondary fw-bold text-uppercase">Estadísticas</h3>
                                <div class="col-12 col-md-4">
                                    <div class="p-3 bg-light rounded-4 border border-light text-center">
                                        <i class="bi bi-calendar-check text-primary fs-3 mb-2"></i>
                                        <h4 class="mb-0 fw-bold">{{ veterinario.citas_count || '0' }}</h4>
                                        <span class="text-muted small">Citas Atendidas</span>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="p-3 bg-light rounded-4 border border-light text-center">
                                        <i class="bi bi-clock-history text-primary fs-3 mb-2"></i>
                                        <h4 class="mb-0 fw-bold">{{ veterinario.citas_pendientes_count || '0' }}</h4>
                                        <span class="text-muted small">Citas Pendientes</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bloqueos de Emergencia (Solo Admin) -->
            <div v-if="veterinario && isAdmin" class="card border-0 shadow-sm rounded-4 mt-4">
                <div class="card-header bg-white border-bottom-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="h5 mb-1 text-danger fw-bold">Bloqueos de Emergencia / Ausencias</h3>
                        <p class="text-muted small mb-0">Registre suspensiones temporales de horarios para este veterinario.</p>
                    </div>
                    <button @click="abrirModalBloqueo" class="btn btn-danger rounded-pill px-4 fw-medium shadow-sm">
                        <i class="bi bi-shield-slash me-1"></i> Registrar Bloqueo
                    </button>
                </div>
                <div class="card-body p-4">
                    <div v-if="bloqueos.length === 0" class="text-center py-5 bg-light rounded-4">
                        <i class="bi bi-calendar-x text-muted display-4 d-block mb-3 opacity-50"></i>
                        <p class="text-muted mb-0">No hay bloqueos de horario registrados para este veterinario.</p>
                    </div>
                    <div v-else class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4 py-3">Fecha Inicio</th>
                                    <th class="px-4 py-3">Fecha Fin</th>
                                    <th class="px-4 py-3">Horario</th>
                                    <th class="px-4 py-3">Motivo</th>
                                    <th class="px-4 py-3 text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="bloqueo in bloqueos" :key="bloqueo.id" class="border-bottom border-light">
                                    <td class="px-4 py-3 fw-semibold text-dark">{{ formatearFechaString(bloqueo.fecha_inicio) }}</td>
                                    <td class="px-4 py-3 fw-semibold text-dark">{{ formatearFechaString(bloqueo.fecha_fin) }}</td>
                                    <td class="px-4 py-3">
                                        <span v-if="!bloqueo.hora_inicio && !bloqueo.hora_fin" class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-1.5 fw-medium">
                                            Todo el día
                                        </span>
                                        <span v-else class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3 py-1.5 fw-medium">
                                            {{ bloqueo.hora_inicio.slice(0, 5) }} - {{ bloqueo.hora_fin.slice(0, 5) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-muted">{{ bloqueo.motivo }}</td>
                                    <td class="px-4 py-3 text-end">
                                        <button @click="confirmarEliminarBloqueo(bloqueo.id)" class="btn btn-outline-danger btn-sm rounded-circle p-1 d-inline-flex align-items-center justify-content-center hover-scale transition-all" style="width: 32px; height: 32px;" title="Eliminar Bloqueo">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal para Registrar Bloqueo de Horario -->
            <div v-if="mostrarModalBloqueo" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5); backdrop-filter: blur(4px); z-index: 1055;">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                        <div class="modal-header border-bottom-0 bg-danger text-white p-4">
                            <h5 class="modal-title fw-bold">Registrar Bloqueo de Horario</h5>
                            <button type="button" class="btn-close btn-close-white" @click="mostrarModalBloqueo = false"></button>
                        </div>
                        <div class="modal-body p-4">
                            <form @submit.prevent="guardarBloqueo">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Fecha Inicio</label>
                                        <input type="date" v-model="bloqueoForm.fecha_inicio" class="form-control rounded-pill border-light bg-light px-3" required />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Fecha Fin</label>
                                        <input type="date" v-model="bloqueoForm.fecha_fin" class="form-control rounded-pill border-light bg-light px-3" required />
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Tipo de Bloqueo</label>
                                        <select v-model="bloqueoForm.tipo_bloqueo" class="form-select rounded-pill border-light bg-light px-3">
                                            <option value="completo">Todo el día</option>
                                            <option value="horas">Rango de horas específico</option>
                                        </select>
                                    </div>

                                    <div v-if="bloqueoForm.tipo_bloqueo === 'horas'" class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Hora Inicio</label>
                                        <input type="time" v-model="bloqueoForm.hora_inicio" class="form-control rounded-pill border-light bg-light px-3" required />
                                    </div>
                                    <div v-if="bloqueoForm.tipo_bloqueo === 'horas'" class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Hora Fin</label>
                                        <input type="time" v-model="bloqueoForm.hora_fin" class="form-control rounded-pill border-light bg-light px-3" required />
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Motivo del Bloqueo</label>
                                        <textarea v-model="bloqueoForm.motivo" class="form-control rounded-4 border-light bg-light px-3 py-2" rows="3" placeholder="Ej. Retiro por urgencia familiar, Licencia médica, etc." required></textarea>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <button type="button" class="btn btn-light rounded-pill px-4 fw-semibold" @click="mostrarModalBloqueo = false" :disabled="cargandoBloqueo">Cancelar</button>
                                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-semibold" :disabled="cargandoBloqueo">
                                        <span v-if="cargandoBloqueo" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Registrar Bloqueo
                                    </button>
                                </div>
                            </form>
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
        veterinario: {
            type: Object,
            default: null,
        },
        bloqueos: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            mostrarModalBloqueo: false,
            cargandoBloqueo: false,
            bloqueoForm: {
                fecha_inicio: '',
                fecha_fin: '',
                tipo_bloqueo: 'completo',
                hora_inicio: '',
                hora_fin: '',
                motivo: ''
            }
        };
    },
    computed: {
        esVeterinarioOAdmin() {
            const user = this.$page.props.auth.user;
            return user && user.rol && (user.rol.nombre_interno === 'veterinario' || user.rol.nombre_interno === 'admin');
        },
        isAdmin() {
            const user = this.$page.props.auth.user;
            return user && user.rol && user.rol.nombre_interno === 'admin';
        }
    },
    methods: {
        abrirModalBloqueo() {
            this.bloqueoForm = {
                fecha_inicio: new Date().toISOString().split('T')[0],
                fecha_fin: new Date().toISOString().split('T')[0],
                tipo_bloqueo: 'completo',
                hora_inicio: '',
                hora_fin: '',
                motivo: ''
            };
            this.mostrarModalBloqueo = true;
        },
        formatearFechaString(fechaStr) {
            if (!fechaStr) return '';
            const parts = fechaStr.split('-');
            if (parts.length !== 3) return fechaStr;
            return `${parts[2]}/${parts[1]}/${parts[0]}`;
        },
        guardarBloqueo() {
            this.cargandoBloqueo = true;
            const payload = {
                fecha_inicio: this.bloqueoForm.fecha_inicio,
                fecha_fin: this.bloqueoForm.fecha_fin,
                hora_inicio: this.bloqueoForm.tipo_bloqueo === 'completo' ? null : this.bloqueoForm.hora_inicio,
                hora_fin: this.bloqueoForm.tipo_bloqueo === 'completo' ? null : this.bloqueoForm.hora_fin,
                motivo: this.bloqueoForm.motivo
            };

            axios.post(`/api/veterinarios/${this.veterinario.id}/bloqueos`, payload)
                .then(() => {
                    this.$inertia.reload({
                        only: ['bloqueos'],
                        onSuccess: () => {
                            this.mostrarModalBloqueo = false;
                        }
                    });
                })
                .catch(err => {
                    console.error(err);
                    alert(err.response?.data?.message || 'Error al registrar el bloqueo.');
                })
                .finally(() => {
                    this.cargandoBloqueo = false;
                });
        },
        confirmarEliminarBloqueo(bloqueoId) {
            if (confirm('¿Está seguro de que desea eliminar este bloqueo de horario?')) {
                axios.delete(`/api/bloqueos/${bloqueoId}`)
                    .then(() => {
                        this.$inertia.reload({
                            only: ['bloqueos']
                        });
                    })
                    .catch(err => {
                        console.error(err);
                        alert(err.response?.data?.message || 'Error al eliminar el bloqueo.');
                    });
            }
        }
    }
}
</script>

<style scoped>
.hover-primary {
    transition: color 0.3s ease;
}
.hover-primary:hover {
    color: var(--bs-primary) !important;
}
.bg-gradient-dark {
    background: linear-gradient(to top, rgba(0,0,0,0.4) 0%, rgba(0,0,0,0) 100%);
}
</style>
