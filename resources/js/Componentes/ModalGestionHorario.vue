<template>
    <div v-if="visible" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5); backdrop-filter: blur(4px); z-index: 1060; overflow-y: auto;">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="max-height: 90vh;">

                <!-- Header -->
                <div class="modal-header border-bottom-0 p-4 pb-3 bg-white">
                    <div>
                        <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-calendar2-week-fill text-primary"></i>
                            Planes de Horario Semanal
                        </h5>
                        <p class="text-muted small mb-0">
                            Crea planes de atención segmentados por fecha, especialidad y sucursal.
                        </p>
                    </div>
                    <button type="button" class="btn-close" @click="cerrar" aria-label="Cerrar"></button>
                </div>

                <!-- Body -->
                <div class="modal-body px-4 py-3" style="overflow-y: auto;">

                    <!-- Botón Nuevo Plan -->
                    <div class="d-flex justify-content-end mb-3">
                        <button v-if="!editandoPlan" @click="crearNuevoPlan" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
                            <i class="bi bi-plus-circle me-1"></i> Nuevo Plan
                        </button>
                    </div>

                    <!-- Formulario de Plan (crear/editar) -->
                    <div v-if="editandoPlan" class="card border-0 shadow-sm rounded-4 mb-4 border-start border-4 border-primary">
                        <div class="card-body p-4">
                            <h6 class="fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                                <i :class="planEditado.esNuevo ? 'bi bi-plus-circle' : 'bi bi-pencil-square'"></i>
                                {{ planEditado.esNuevo ? 'Crear Nuevo Plan' : 'Editar Plan' }}
                            </h6>

                            <div class="row g-3 mb-4">
                                <!-- Nombre del Plan -->
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Nombre del Plan</label>
                                    <input type="text" v-model="planEditado.nombre" class="form-control rounded-pill border-light bg-light px-3" placeholder="Ej: Enero - Consulta General Centro" required />
                                </div>

                                <!-- Rango de Fechas -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Fecha Inicio</label>
                                    <input type="date" v-model="planEditado.fecha_inicio" class="form-control rounded-pill border-light bg-light px-3" required />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Fecha Fin</label>
                                    <input type="date" v-model="planEditado.fecha_fin" class="form-control rounded-pill border-light bg-light px-3" required />
                                </div>

                                <!-- Especialidad y Sucursal -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Especialidad (Opcional)</label>
                                    <select v-model="planEditado.especialidad_id" class="form-select rounded-pill border-light bg-light px-3">
                                        <option :value="null">Todas las especialidades</option>
                                        <option v-for="especialidad in especialidades" :key="especialidad.id" :value="especialidad.id">
                                            {{ especialidad.nombre }}
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Sucursal (Opcional)</label>
                                    <select v-model="planEditado.sucursal_id" class="form-select rounded-pill border-light bg-light px-3">
                                        <option :value="null">Todas las sucursales</option>
                                        <option v-for="sucursal in sucursales" :key="sucursal.id" :value="sucursal.id">
                                            {{ sucursal.nombre }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Grilla Semanal -->
                            <h6 class="fw-bold text-dark mb-3">
                                <i class="bi bi-clock me-1 text-secondary"></i> Configuración Semanal
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-0 rounded-3 overflow-hidden">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="px-3 py-2 fw-bold text-dark" style="width: 140px;">Día</th>
                                            <th class="px-3 py-2 text-center" style="width: 50px;">
                                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2">Normal</span>
                                            </th>
                                            <th class="px-3 py-2 text-center">Horario Normal</th>
                                            <th class="px-3 py-2 text-center" style="width: 50px;">
                                                <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-2">Urgencia</span>
                                            </th>
                                            <th class="px-3 py-2 text-center">Horario Urgencias</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="dia in planEditado.dias" :key="dia.dia" :class="{ 'table-light opacity-50': !dia.normal.activo && !dia.urgencia.activo }">
                                            <td class="px-3 py-2 fw-semibold text-dark">
                                                <i class="bi bi-calendar3 me-1 text-muted"></i>
                                                {{ nombreDia(dia.dia) }}
                                            </td>

                                            <!-- Normal: checkbox -->
                                            <td class="px-3 py-2 text-center">
                                                <div class="form-check d-flex justify-content-center mb-0">
                                                    <input type="checkbox" v-model="dia.normal.activo" class="form-check-input" :id="'plan-normal-' + dia.dia" />
                                                </div>
                                            </td>
                                            <!-- Normal: horas -->
                                            <td class="px-3 py-2">
                                                <div v-if="dia.normal.activo" class="d-flex gap-2 align-items-center justify-content-center">
                                                    <input type="time" v-model="dia.normal.inicio" class="form-control form-control-sm border-light" style="max-width: 120px;" />
                                                    <span class="text-muted small fw-bold">a</span>
                                                    <input type="time" v-model="dia.normal.fin" class="form-control form-control-sm border-light" style="max-width: 120px;" />
                                                </div>
                                                <span v-else class="text-muted small fst-italic">— No atiende —</span>
                                            </td>

                                            <!-- Urgencia: checkbox -->
                                            <td class="px-3 py-2 text-center">
                                                <div class="form-check d-flex justify-content-center mb-0">
                                                    <input type="checkbox" v-model="dia.urgencia.activo" class="form-check-input" :id="'plan-urgencia-' + dia.dia" />
                                                </div>
                                            </td>
                                            <!-- Urgencia: horas -->
                                            <td class="px-3 py-2">
                                                <div v-if="dia.urgencia.activo" class="d-flex gap-2 align-items-center justify-content-center">
                                                    <input type="time" v-model="dia.urgencia.inicio" class="form-control form-control-sm border-light" style="max-width: 120px;" />
                                                    <span class="text-muted small fw-bold">a</span>
                                                    <input type="time" v-model="dia.urgencia.fin" class="form-control form-control-sm border-light" style="max-width: 120px;" />
                                                </div>
                                                <span v-else class="text-muted small fst-italic">— No atiende —</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Acciones del formulario -->
                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <button type="button" class="btn btn-light rounded-pill px-4 fw-medium" @click="cancelarEdicion">
                                    Cancelar
                                </button>
                                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" @click="confirmarPlan">
                                    <i class="bi bi-check-lg me-1"></i>
                                    {{ planEditado.esNuevo ? 'Agregar Plan' : 'Actualizar Plan' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de Planes Existentes -->
                    <div v-if="planesLocales.length === 0 && !editandoPlan" class="text-center py-5 bg-light rounded-4">
                        <i class="bi bi-calendar-plus text-muted display-4 d-block mb-3 opacity-50"></i>
                        <p class="text-muted mb-2 fw-medium">No hay planes de horario configurados.</p>
                        <p class="text-muted small">Crea un plan para definir los días y horas de atención del veterinario.</p>
                    </div>

                    <div v-else-if="!editandoPlan" class="d-flex flex-column gap-3">
                        <div v-for="(plan, indice) in planesLocales" :key="plan.id" class="card border-0 shadow-sm rounded-4 overflow-hidden plan-card">
                            <!-- Cabecera del plan -->
                            <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center cursor-pointer" @click="alternarPlan(plan.id)">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="bi bi-calendar-week"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark">{{ plan.nombre || `Plan ${indice + 1}` }}</h6>
                                        <div class="d-flex gap-2 align-items-center mt-1 flex-wrap">
                                            <span class="badge bg-light text-dark border rounded-pill px-2 py-1 small">
                                                <i class="bi bi-calendar3 me-1"></i>
                                                {{ formatearFecha(plan.fecha_inicio) }} → {{ formatearFecha(plan.fecha_fin) }}
                                            </span>
                                            <span v-if="nombreEspecialidad(plan.especialidad_id)" class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-1 small">
                                                {{ nombreEspecialidad(plan.especialidad_id) }}
                                            </span>
                                            <span v-if="nombreSucursal(plan.sucursal_id)" class="badge bg-info bg-opacity-10 text-info rounded-pill px-2 py-1 small">
                                                {{ nombreSucursal(plan.sucursal_id) }}
                                            </span>
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1 small">
                                                {{ contarDiasActivos(plan.dias) }} días activos
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <button @click.stop="editarPlan(indice)" class="btn btn-outline-primary btn-sm rounded-pill px-3 d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-pencil-square"></i>
                                        <span>Editar Plan</span>
                                    </button>
                                    <button @click.stop="duplicarPlan(indice)" class="btn btn-outline-secondary btn-sm rounded-pill px-3 d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-copy"></i>
                                        <span>Duplicar</span>
                                    </button>
                                    <button @click.stop="eliminarPlan(indice)" class="btn btn-outline-danger btn-sm rounded-pill px-3 d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-trash3"></i>
                                        <span>Eliminar</span>
                                    </button>
                                    <button @click.stop="alternarPlan(plan.id)" class="btn btn-light btn-sm rounded-pill px-3 d-inline-flex align-items-center gap-1">
                                        <i :class="planesExpandidos[plan.id] ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                                        <span>{{ planesExpandidos[plan.id] ? 'Ocultar Detalle' : 'Ver Detalle' }}</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Detalle colapsable -->
                            <div v-if="planesExpandidos[plan.id]" class="card-body p-0">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="px-3 py-2 small text-muted fw-bold">Día</th>
                                            <th class="px-3 py-2 small text-muted fw-bold text-center">Normal</th>
                                            <th class="px-3 py-2 small text-muted fw-bold text-center">Urgencias</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="dia in plan.dias" :key="dia.dia" :class="{ 'opacity-50': !dia.normal.activo && !dia.urgencia.activo }">
                                            <td class="px-3 py-2 fw-medium">{{ nombreDia(dia.dia) }}</td>
                                            <td class="px-3 py-2 text-center">
                                                <span v-if="dia.normal.activo" class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1">
                                                    {{ dia.normal.inicio }} - {{ dia.normal.fin }}
                                                </span>
                                                <span v-else class="text-muted small">—</span>
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                <span v-if="dia.urgencia.activo" class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-2 py-1">
                                                    {{ dia.urgencia.inicio }} - {{ dia.urgencia.fin }}
                                                </span>
                                                <span v-else class="text-muted small">—</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer border-top bg-light py-3 px-4 justify-content-end gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-medium" @click="cerrar" :disabled="guardando">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" @click="guardarTodo" :disabled="guardando || editandoPlan">
                        <span v-if="guardando" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                        <i v-else class="bi bi-cloud-upload me-1"></i>
                        Guardar Todos los Planes
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { alertaExito, alertaError, confirmar } from '@/alertas';

const NOMBRES_DIAS = {
    1: 'Lunes',
    2: 'Martes',
    3: 'Miércoles',
    4: 'Jueves',
    5: 'Viernes',
    6: 'Sábado',
    7: 'Domingo',
};

function crearDiasPorDefecto() {
    return [1, 2, 3, 4, 5, 6, 7].map(numeroDia => {
        const esFinSemana = numeroDia === 6 || numeroDia === 7;
        return {
            dia: numeroDia,
            normal: { activo: !esFinSemana, inicio: '09:00', fin: '18:00' },
            urgencia: { activo: !esFinSemana, inicio: '18:00', fin: '21:30' },
        };
    });
}

function generarIdPlan() {
    return `plan_${Date.now()}_${Math.random().toString(36).slice(2, 8)}`;
}

function convertirHorarioLegacy(horarioViejo) {
    if (!Array.isArray(horarioViejo) || horarioViejo.length === 0) {
        return [];
    }

    const primerElemento = horarioViejo[0];
    if (primerElemento.dias) {
        return horarioViejo;
    }

    return [{
        id: generarIdPlan(),
        nombre: 'Horario General',
        fecha_inicio: '',
        fecha_fin: '',
        especialidad_id: null,
        sucursal_id: null,
        dias: JSON.parse(JSON.stringify(horarioViejo)),
    }];
}

export default {
    name: 'ModalGestionHorario',
    props: {
        visible: {
            type: Boolean,
            default: false,
        },
        veterinarioId: {
            type: Number,
            required: true,
        },
        planesActuales: {
            type: Array,
            default: () => [],
        },
        especialidades: {
            type: Array,
            default: () => [],
        },
        sucursales: {
            type: Array,
            default: () => [],
        },
    },
    emits: ['cerrar', 'guardado'],

    data() {
        return {
            planesLocales: [],
            planesExpandidos: {},
            editandoPlan: false,
            indiceEditado: null,
            planEditado: null,
            guardando: false,
        };
    },

    watch: {
        visible(nuevoValor) {
            if (nuevoValor) {
                this.inicializarPlanes();
            }
        },
    },

    methods: {
        inicializarPlanes() {
            const planesConvertidos = convertirHorarioLegacy(this.planesActuales);
            this.planesLocales = JSON.parse(JSON.stringify(planesConvertidos));
            this.planesExpandidos = {};
            this.editandoPlan = false;
            this.planEditado = null;
            this.indiceEditado = null;
        },

        crearNuevoPlan() {
            const hoy = new Date();
            const primerDia = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
            const ultimoDia = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);

            this.planEditado = {
                id: generarIdPlan(),
                nombre: '',
                fecha_inicio: primerDia.toISOString().split('T')[0],
                fecha_fin: ultimoDia.toISOString().split('T')[0],
                especialidad_id: null,
                sucursal_id: null,
                dias: crearDiasPorDefecto(),
                esNuevo: true,
            };
            this.indiceEditado = null;
            this.editandoPlan = true;
        },

        editarPlan(indice) {
            const planOriginal = this.planesLocales[indice];
            this.planEditado = JSON.parse(JSON.stringify(planOriginal));
            this.planEditado.esNuevo = false;
            this.indiceEditado = indice;
            this.editandoPlan = true;
        },

        async duplicarPlan(indice) {
            const planOriginal = this.planesLocales[indice];
            const copia = JSON.parse(JSON.stringify(planOriginal));
            copia.id = generarIdPlan();
            copia.nombre = `${copia.nombre} (Copia)`;
            this.planesLocales.push(copia);
        },

        async eliminarPlan(indice) {
            const resultado = await confirmar(
                '¿Eliminar este plan?',
                `Se eliminará "${this.planesLocales[indice].nombre || `Plan ${indice + 1}`}".`
            );
            if (resultado.isConfirmed) {
                this.planesLocales.splice(indice, 1);
            }
        },

        confirmarPlan() {
            if (!this.planEditado.nombre?.trim()) {
                alertaError('Campo requerido', 'El nombre del plan es obligatorio.');
                return;
            }
            if (!this.planEditado.fecha_inicio || !this.planEditado.fecha_fin) {
                alertaError('Campo requerido', 'Las fechas de inicio y fin son obligatorias.');
                return;
            }
            if (this.planEditado.fecha_fin < this.planEditado.fecha_inicio) {
                alertaError('Fechas inválidas', 'La fecha de fin debe ser igual o posterior a la de inicio.');
                return;
            }

            const planLimpio = { ...this.planEditado };
            delete planLimpio.esNuevo;

            if (this.indiceEditado !== null) {
                this.planesLocales.splice(this.indiceEditado, 1, planLimpio);
            } else {
                this.planesLocales.push(planLimpio);
            }

            this.cancelarEdicion();
        },

        cancelarEdicion() {
            this.editandoPlan = false;
            this.planEditado = null;
            this.indiceEditado = null;
        },

        alternarPlan(planId) {
            this.planesExpandidos = {
                ...this.planesExpandidos,
                [planId]: !this.planesExpandidos[planId],
            };
        },

        async guardarTodo() {
            this.guardando = true;
            try {
                await axios.patch(`/api/veterinarios/${this.veterinarioId}/horario`, {
                    horario: this.planesLocales,
                });
                alertaExito('Horario guardado', 'Los planes de horario se actualizaron correctamente.');
                this.$emit('guardado');
                this.$emit('cerrar');
            } catch (error) {
                alertaError('Error', error.response?.data?.message || 'Error al guardar el horario.');
            } finally {
                this.guardando = false;
            }
        },

        cerrar() {
            if (!this.guardando) {
                this.$emit('cerrar');
            }
        },

        nombreDia(numeroDia) {
            return NOMBRES_DIAS[numeroDia] || '';
        },

        formatearFecha(fechaStr) {
            if (!fechaStr) return 'Sin definir';
            const partes = fechaStr.split('-');
            if (partes.length !== 3) return fechaStr;
            return `${partes[2]}/${partes[1]}/${partes[0]}`;
        },

        nombreEspecialidad(especialidadId) {
            if (!especialidadId) return null;
            const encontrada = this.especialidades.find(e => e.id === especialidadId);
            return encontrada?.nombre || null;
        },

        nombreSucursal(sucursalId) {
            if (!sucursalId) return null;
            const encontrada = this.sucursales.find(s => s.id === sucursalId);
            return encontrada?.nombre || null;
        },

        contarDiasActivos(dias) {
            if (!Array.isArray(dias)) return 0;
            return dias.filter(d => d.normal.activo || d.urgencia.activo).length;
        },
    },
};
</script>

<style scoped>
.plan-card {
    transition: box-shadow 0.2s ease, transform 0.15s ease;
}
.plan-card:hover {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08) !important;
}
.cursor-pointer {
    cursor: pointer;
}
</style>
