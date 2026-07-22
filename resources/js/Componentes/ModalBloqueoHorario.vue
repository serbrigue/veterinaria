<template>
    <div v-if="visible" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5); backdrop-filter: blur(4px); z-index: 1055;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-bottom-0 bg-danger text-white p-4">
                    <div>
                        <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                            <i class="bi bi-shield-slash"></i> Registrar Bloqueo de Horario
                        </h5>
                        <p class="mb-0 small opacity-75">Suspenda temporalmente la atención del veterinario.</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white" @click="cerrar"></button>
                </div>

                <div class="modal-body p-4">
                    <form @submit.prevent="guardar">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Fecha Inicio</label>
                                <input type="date" v-model="formulario.fecha_inicio" class="form-control rounded-pill border-light bg-light px-3" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Fecha Fin</label>
                                <input type="date" v-model="formulario.fecha_fin" class="form-control rounded-pill border-light bg-light px-3" required />
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase">Tipo de Bloqueo</label>
                                <select v-model="formulario.tipo_bloqueo" class="form-select rounded-pill border-light bg-light px-3">
                                    <option value="completo">Todo el día</option>
                                    <option value="horas">Rango de horas específico</option>
                                </select>
                            </div>

                            <div v-if="formulario.tipo_bloqueo === 'horas'" class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Hora Inicio</label>
                                <input type="time" v-model="formulario.hora_inicio" class="form-control rounded-pill border-light bg-light px-3" required />
                            </div>
                            <div v-if="formulario.tipo_bloqueo === 'horas'" class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Hora Fin</label>
                                <input type="time" v-model="formulario.hora_fin" class="form-control rounded-pill border-light bg-light px-3" required />
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Especialidad (Opcional)</label>
                                <select v-model="formulario.especialidad_id" class="form-select rounded-pill border-light bg-light px-3">
                                    <option value="">Todas las especialidades</option>
                                    <option v-for="especialidad in especialidades" :key="especialidad.id" :value="especialidad.id">
                                        {{ especialidad.nombre }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Sucursal (Opcional)</label>
                                <select v-model="formulario.sucursal_id" class="form-select rounded-pill border-light bg-light px-3">
                                    <option value="">Todas las sucursales</option>
                                    <option v-for="sucursal in sucursales" :key="sucursal.id" :value="sucursal.id">
                                        {{ sucursal.nombre }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase">Motivo del Bloqueo</label>
                                <textarea v-model="formulario.motivo" class="form-control rounded-4 border-light bg-light px-3 py-2" rows="3" placeholder="Ej. Retiro por urgencia familiar, Licencia médica, etc." required></textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn btn-light rounded-pill px-4 fw-semibold" @click="cerrar" :disabled="guardando">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-danger rounded-pill px-4 fw-semibold" :disabled="guardando">
                                <span v-if="guardando" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Registrar Bloqueo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { alertaExito, alertaError } from '@/alertas';

export default {
    name: 'ModalBloqueoHorario',
    props: {
        visible: {
            type: Boolean,
            default: false,
        },
        veterinarioId: {
            type: Number,
            required: true,
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
            guardando: false,
            formulario: this.crearFormularioVacio(),
        };
    },

    watch: {
        visible(nuevoValor) {
            if (nuevoValor) {
                this.formulario = this.crearFormularioVacio();
            }
        },
    },

    methods: {
        crearFormularioVacio() {
            const hoy = new Date().toISOString().split('T')[0];
            return {
                fecha_inicio: hoy,
                fecha_fin: hoy,
                tipo_bloqueo: 'completo',
                especialidad_id: '',
                sucursal_id: '',
                hora_inicio: '',
                hora_fin: '',
                motivo: '',
            };
        },

        cerrar() {
            if (!this.guardando) {
                this.$emit('cerrar');
            }
        },

        async guardar() {
            this.guardando = true;
            const payload = {
                fecha_inicio: this.formulario.fecha_inicio,
                fecha_fin: this.formulario.fecha_fin,
                hora_inicio: this.formulario.tipo_bloqueo === 'completo' ? null : this.formulario.hora_inicio,
                hora_fin: this.formulario.tipo_bloqueo === 'completo' ? null : this.formulario.hora_fin,
                especialidad_id: this.formulario.especialidad_id || null,
                sucursal_id: this.formulario.sucursal_id || null,
                motivo: this.formulario.motivo,
            };

            try {
                await axios.post(`/api/veterinarios/${this.veterinarioId}/bloqueos`, payload);
                alertaExito('Bloqueo registrado', 'El bloqueo de horario se registró correctamente.');
                this.$emit('guardado');
                this.$emit('cerrar');
            } catch (error) {
                alertaError('Error', error.response?.data?.message || 'No se pudo registrar el bloqueo.');
            } finally {
                this.guardando = false;
            }
        },
    },
};
</script>
