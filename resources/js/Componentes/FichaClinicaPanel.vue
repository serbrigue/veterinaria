<template>
    <!-- ================================================================================== -->
    <!-- COMPONENTE: FichaClinicaPanel -->
    <!-- ================================================================================== -->
    <div class="mb-4 p-4 rounded-4 shadow-sm bg-white border border-primary border-opacity-25">
        <div class="d-flex justify-content-between align-items-center mb-3" 
             :class="{'user-select-none': esSoloLectura}"
             :style="esSoloLectura ? 'cursor: pointer;' : ''" 
             @click="esSoloLectura ? mostrarContenidoFicha = !mostrarContenidoFicha : null"
             title="Clic para expandir/ocultar">
            <div class="d-flex align-items-center gap-2">
                <h3 class="h5 fw-bold text-primary mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-file-medical-fill"></i> Ficha Clínica
                </h3>
                <!-- Si es solo lectura, se muestra un indicador para desplegar o no los detalles -->
                <span v-if="esSoloLectura" class="badge bg-light text-primary border border-primary border-opacity-50 ms-2" style="font-size: 0.75rem;">
                    {{ mostrarContenidoFicha ? 'Ocultar detalles' : 'Ver detalles (Click)' }}
                    <i :class="mostrarContenidoFicha ? 'bi bi-caret-up-fill' : 'bi bi-caret-down-fill'" class="ms-1"></i>
                </span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <!-- Si es solo lectura y tiene ficha clinica, se muestra el boton de descarga -->
                <a v-if="esSoloLectura && cita.ficha_clinica" :href="`/api/citas/${cita.id}/ficha-clinica/pdf`" target="_blank" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1" title="Descargar PDF" @click.stop>
                    <i class="bi bi-file-earmark-pdf-fill"></i> PDF
                </a>
                <!-- Si es solo lectura, se muestra el badge de solo lectura -->
                <span v-if="esSoloLectura" class="badge bg-secondary">Solo Lectura</span>
                <span v-else class="badge bg-secondary">Modo Edición</span>
            </div>
        </div>

        <div v-show="!esSoloLectura || mostrarContenidoFicha">
            <!-- Si se está guardando, se muestra el spinner -->
            <div v-if="guardando" class="text-center py-4">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="text-muted mt-2 small">Guardando ficha clínica...</p>
        </div>

        <!-- Si no se está guardando, se muestra el formulario -->
        <div v-else>
            <!-- Formulario para guardar la ficha -->
            <form @submit.prevent="guardarFicha">
                <!-- Constantes Vitales -->
                <h4 class="h6 fw-semibold text-secondary mb-3 mt-4 border-bottom pb-2">Constantes Vitales</h4>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted">Peso Actual (kg)</label>
                        <input v-model.number="ficha.peso_actual" type="number" step="0.01" class="form-control form-control-sm" :disabled="esSoloLectura">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted">Frec. Cardíaca (lpm)</label>
                        <input v-model.number="ficha.frecuencia_cardiaca" type="number" class="form-control form-control-sm" :disabled="esSoloLectura">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted">Temperatura (°C)</label>
                        <input v-model.number="ficha.temperatura" type="number" step="0.1" class="form-control form-control-sm" :disabled="esSoloLectura">
                    </div>
                </div>

                <!-- Examen Clínico -->
                <h4 class="h6 fw-semibold text-secondary mb-3 border-bottom pb-2">Examen Clínico</h4>
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Anamnesis (Motivo de consulta y antecedentes)</label>
                    <!-- DIRECTIVA (v-model): Enlace de datos bidireccional con "ficha.anamnesis" -->
                    <textarea v-model="ficha.anamnesis" class="form-control form-control-sm" rows="3" :disabled="esSoloLectura"></textarea>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Síntomas</label>
                        <!-- DIRECTIVA (v-model): Enlace de datos bidireccional con "ficha.sintomas" -->
                        <textarea v-model="ficha.sintomas" class="form-control form-control-sm" rows="3" :disabled="esSoloLectura"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Diagnóstico</label>
                        <!-- DIRECTIVA (v-model): Enlace de datos bidireccional con "ficha.diagnostico" -->
                        <textarea v-model="ficha.diagnostico" class="form-control form-control-sm" rows="3" :disabled="esSoloLectura"></textarea>
                    </div>
                </div>

                <!-- Elementos Utilizados (Cargos) - Integrado a la Ficha Clinica -->
                <div class="d-flex justify-content-between align-items-center mb-3 mt-5 border-bottom pb-2">
                    <h4 class="h6 fw-semibold text-secondary mb-0">
                        <i class="bi bi-box-seam-fill me-1 text-warning"></i> Uso de Insumos / Mermas
                    </h4>
                    <!-- Si el estado de la cita no es "completada", se muestra el boton de agregar insumo -->
                    <button v-if="estadoCita != 'completada'" type="button" class="btn btn-sm btn-outline-primary" @click="mostrandoFormularioInsumo = !mostrandoFormularioInsumo">
                        <!-- Si se está mostrando el formulario, se muestra el boton de cancelar, si no, se muestra el boton de agregar -->
                        <i :class="mostrandoFormularioInsumo ? 'bi bi-dash-circle' : 'bi bi-plus-circle'"></i> {{ mostrandoFormularioInsumo ? 'Cancelar Insumo' : 'Agregar Insumo' }}

                    </button>
                </div>

                <!-- Si la lista de cargos existe y al menos uno es un insumo, se muestra el boton de eliminar -->
                <div v-if="cargosList && cargosList.some(c => c.insumo)" class="mb-3">
                    <!-- Si existe un cargo que sea un insumo, se muestra la lista de insumos -->
                    <!-- Se itera sobre la lista de cargos que son insumos y filtramos para mostrar solo los insumos -->
                    <div v-for="cargo in cargosList.filter(c => c.insumo)" :key="'insumo-' + cargo.id"
                         class="d-flex justify-content-between align-items-center p-2 rounded mb-1 bg-light border">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-capsule text-warning"></i>
                            <span class="small fw-semibold text-dark">{{ cargo.insumo.nombre }}</span>
                       cv       
                            <!-- Si el estado de la cita no es "completada", se muestra el boton de agregar insumo -->
                            <div v-if="estadoCita != 'completada'" class="input-group input-group-sm ms-2" style="width: 90px;">
                                <!-- Si el estado de la cita no es "completada", se muestra el boton de agregar insumo -->
                                 <!-- Dispara la acción $emit actualizar-cantidad para actualizar la cantidad del insumo-->
                                <button class="btn btn-outline-secondary px-2 fw-bold" type="button" @click="$emit('actualizar-cantidad', cargo, -1)" :disabled="procesandoCargo === cargo.id || cargo.cantidad <= 1">
                                    -
                                </button>
                                <input type="text" class="form-control text-center px-1 bg-white" :value="cargo.cantidad" readonly>
                                <!-- Dispara la acción $emit actualizar-cantidad para actualizar la cantidad del insumo-->
                                <button class="btn btn-outline-secondary px-2 fw-bold" type="button" @click="$emit('actualizar-cantidad', cargo, 1)" :disabled="procesandoCargo === cargo.id">
                                    +
                                </button>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span class="small text-muted fw-semibold">${{ Number(cargo.subtotal).toLocaleString('es-CL') }}</span>
                            <!-- Si el estado de la cita no es "completada", se muestra el boton de eliminar insumo-->
                            <!-- Dispara la acción $emit eliminar-cargo para eliminar el insumo-->
                            <button v-if="estadoCita != 'completada'" type="button" class="btn btn-sm btn-outline-danger p-1" @click="$emit('eliminar-cargo', cargo.id)" :disabled="procesandoCargo === cargo.id" title="Eliminar Insumo">
                                <!-- Renderizado condicional basado en "procesandoCargo === cargo.id" -->
                                <!-- Si se está procesando el cargo, se muestra el spinner de carga -->
                                <span v-if="procesandoCargo === cargo.id" class="spinner-border spinner-border-sm"></span>
                                <!-- Si no se está procesando el cargo, se muestra la X para eliminar -->
                                <span v-else class="fw-bold px-2">X</span>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Si el estado de la cita no es "completada" y se está mostrando el formulario de insumos -->

                <div v-if="estadoCita != 'completada' && mostrandoFormularioInsumo" class="border rounded-3 p-3 bg-white mb-4">
                    <!-- Mostramos el formulario de insumos -->
                    <div class="d-flex justify-content-between align-items-center mb-2"> 
                        <h4 class="h6 fw-semibold text-dark mb-0"><i class="bi bi-plus-circle me-1 text-success"></i> Agregar Insumo</h4>
                        <!-- Boton de cancelar formulario, oculta el formulario de insumos -->
                        <button type="button" class="btn btn-sm btn-outline-danger p-1" @click="mostrandoFormularioInsumo = false; nuevoCargo.insumoId = ''; nuevoCargo.cantidad = 1" title="Cancelar">
                            <span class="fw-bold px-2">X</span>
                        </button>
                    </div>
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-semibold text-secondary mb-1">Insumo (stock disponible)</label>
                            <!-- Buscador de insumos donde el padre envia una lista de opciones-->
                            <BuscadorSelect 
                                v-model="nuevoCargo.insumoId" 
                                :opciones="opcionesInsumos" 
                                placeholder="Buscar y seleccionar insumo..." 
                            />
                        </div>
                        <div class="col-6 col-sm-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">Cantidad</label>
                            <!-- Campo de cantidad con validacion de stock -->
                            <input v-model.number="nuevoCargo.cantidad" type="number" min="1" class="form-control form-control-sm" placeholder="1">
                        </div>
                        <div class="col-6 col-sm-3">
                            <!-- Boton de agregar insumo -->
                            <button type="button" @click="emitirAgregarCargo" class="btn btn-success btn-sm w-100" :disabled="!nuevoCargo.insumoId || nuevoCargo.cantidad < 1 || guardandoCargo">
                                <!-- Renderizado condicional basado en "guardandoCargo" -->
                                <span v-if="guardandoCargo" class="spinner-border spinner-border-sm me-1"></span>
                                <i v-else class="bi bi-plus-lg me-1"></i> Añadir  
                            </button>
                        </div>
                    </div>
                    <!-- Renderizado condicional basado en "errorCargo" -->
                    <div v-if="errorCargo" class="alert alert-danger alert-sm py-1 px-2 mt-2 small mb-0">{{ errorCargo }}</div>
                </div>

                <!-- Recetas -->
                <div class="d-flex justify-content-between align-items-center mb-2 mt-4 border-bottom pb-2">
                    <h4 class="h6 fw-semibold text-secondary mb-0">Recetas Médicas</h4>
                    <!-- Boton de agregar receta -->
                    <button v-if="!esSoloLectura" type="button" class="btn btn-sm btn-outline-primary" @click="agregarReceta">
                        <!-- Si está en solo lectura lo bloqueamos -->
                        <i class="bi bi-plus-circle"></i> Agregar Receta
                    </button>
                </div>
                <!-- Renderizado iterativo de lista -->
                <div v-for="(receta, index) in ficha.recetas" :key="'receta-'+index" class="card bg-light border-0 mb-3">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold small text-primary">Receta #{{ index + 1 }}</span>
                            <!-- Boton de eliminar receta -->
                            <button v-if="!esSoloLectura" type="button" class="btn btn-sm btn-outline-danger p-1" @click="eliminarReceta(index)" title="Eliminar Receta">
                                <!-- Si está en solo lectura lo bloqueamos -->
                                <span class="fw-bold px-2">X</span>
                            </button>
                        </div>
                        
                        <div class="mb-2">
                            <label class="form-label small fw-semibold text-muted">Medicamento</label>
                            <!-- Buscador de medicamentos con validacion de stock -->
                            <BuscadorSelect 
                                v-if="!esSoloLectura" 
                                v-model="receta.medicamentosTexto" 
                                :opciones="catalogoMedicamentos" 
                                trackBy="nombre"
                                placeholder="Buscar medicamento..." 
                                @change="!medicamentoEnStock(receta.medicamentosTexto) && (receta.comprado_en_clinica = false)"
                            />
                            <input v-else type="text" class="form-control form-control-sm" :value="receta.medicamentosTexto" disabled>
                        </div>
                        <!-- Renderizado condicional basado en "medicamentoEnStock(receta.medicamentosTexto)" -->
                        <div class="mb-2 form-check form-switch ps-5" v-if="medicamentoEnStock(receta.medicamentosTexto)">
                            <!-- Enlace de datos bidireccional con "receta.comprado_en_clinica" -->
                            <input v-model="receta.comprado_en_clinica" type="checkbox" class="form-check-input" :id="'chkComprar-'+index" :disabled="esSoloLectura">
                            <label class="form-check-label small fw-semibold text-primary" :for="'chkComprar-'+index">
                                Añadir costo al recibo de la cita (Se comprará aquí)
                            </label>
                        </div>
                        <div class="mb-0">
                            <label class="form-label small fw-semibold text-muted">Indicaciones Generales</label>
                            <!-- Enlace de datos bidireccional con "receta.indicaciones_generales" -->
                            <input v-model="receta.indicaciones_generales" type="text" class="form-control form-control-sm" :disabled="esSoloLectura">
                        </div>
                    </div>
                </div>

                <!-- Vacunas -->
                <div class="d-flex justify-content-between align-items-center mb-2 mt-4 border-bottom pb-2">
                    <h4 class="h6 fw-semibold text-secondary mb-0">Aplicación de Vacunas</h4>
                    <!-- Boton de registrar vacuna -->
                    <button v-if="!esSoloLectura" type="button" class="btn btn-sm btn-outline-success" @click="agregarVacuna">
                        <!-- Si está en solo lectura lo bloqueamos -->
                        <i class="bi bi-plus-circle"></i> Registrar Vacuna
                    </button>
                </div>
                <!-- Renderizado iterativo de lista -->
                <div v-for="(vacuna, vIndex) in ficha.vacunas" :key="'vacuna-'+vIndex" class="row g-2 mb-3 bg-success bg-opacity-10 p-2 rounded align-items-end">
                    <!-- Boton de eliminar vacuna -->
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted mb-0">Vacuna</label>
                        <!-- Buscador de vacunas con validacion de stock -->
                        <BuscadorSelect 
                            v-if="!esSoloLectura" 
                            v-model="vacuna.nombre_vacuna" 
                            :opciones="catalogoVacunas" 
                            trackBy="nombre"
                            placeholder="Buscar vacuna..." 
                        />
                        <input v-else type="text" class="form-control form-control-sm" :value="vacuna.nombre_vacuna" disabled>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted mb-0">Fecha Aplicación</label>
                        <!-- Enlace de datos bidireccional con "vacuna.fecha_aplicacion" -->
                        <input v-model="vacuna.fecha_aplicacion" type="date" class="form-control form-control-sm" required :disabled="esSoloLectura">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted mb-0">Próxima Dosis</label>
                        <!-- Enlace de datos bidireccional con "vacuna.fecha_proxima_dosis" -->
                        <input v-model="vacuna.fecha_proxima_dosis" type="date" class="form-control form-control-sm" :disabled="esSoloLectura">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-muted mb-0">Lote</label>
                        <!-- Enlace de datos bidireccional con "vacuna.numero_lote" -->
                        <input v-model="vacuna.numero_lote" type="text" class="form-control form-control-sm" :disabled="esSoloLectura">
                    </div>
                    <!-- Boton de eliminar vacuna -->
                    <div class="col-md-1 text-end" v-if="!esSoloLectura">
                        <!-- Si está en solo lectura lo bloqueamos -->
                        <button type="button" class="btn btn-sm btn-outline-danger p-1" style="height: 31px;" @click="eliminarVacuna(vIndex)" title="Eliminar Vacuna">
                            <span class="fw-bold px-2">X</span>
                        </button>
                    </div>
                </div>

                <!-- Boton de guardar ficha clinica SOLO SI NO ESTÁ EN SOLO LECTURA -->
                <div class="text-end mt-4" v-if="!esSoloLectura">
                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                        <i class="bi bi-save me-1"></i> Guardar Ficha Clínica
                    </button>
                </div>
            </form>
        </div>
        </div>
    </div>
</template>

<script>
// ==================================================================================
// LÓGICA DEL COMPONENTE (VUE 3)
// ==================================================================================

import axios from 'axios';
import Swal from 'sweetalert2';
import BuscadorSelect from '@/Componentes/BuscadorSelect.vue';

// ------------------------------------------------------------------------------
// EXPORT DEFAULT: Definición principal del componente
// ------------------------------------------------------------------------------
export default {
    name: 'FichaClinicaPanel',
    // COMPONENTES (COMPONENTS): Registro de componentes importados
    components: {
        BuscadorSelect
    },
    // PROPIEDADES (PROPS): Datos inyectados desde el componente padre o estado
    props: {
        cita: {
            type: Object,
            required: true
        },
        insumosSucursal: {
            type: Array,
            default: () => []
        },
        catalogoMedicamentos: {
            type: Array,
            default: () => []
        },
        catalogoVacunas: {
            type: Array,
            default: () => []
        },
        cargosList: {
            type: Array,
            default: () => []
        },
        errorCargo: {
            type: String,
            default: null
        },
        procesandoCargo: {
            type: Number,
            default: null
        },
        guardandoCargo: {
            type: Boolean,
            default: false
        },
        estadoCita: {
            type: String,
            default: 'pendiente'
        },
        forzarLectura: {
            type: Boolean,
            default: false
        }
    },
    // ESTADO REACTIVO (DATA): Variables locales del componente
    data() {
        return {
            guardando: false,
            mostrarContenidoFicha: false,
            mostrandoFormularioInsumo: false,
            nuevoCargo: {
                insumoId: '',
                cantidad: 1
            },
            ficha: {
                peso_actual: null,
                frecuencia_cardiaca: null,
                temperatura: null,
                anamnesis: '',
                sintomas: '',
                diagnostico: '',
                recetas: [],
                vacunas: []
            }
        }
    },
    // PROPIEDADES COMPUTADAS (COMPUTED): Variables reactivas que dependen de otras
    computed: {
        esSoloLectura() {
            if (this.forzarLectura) return true;
            return this.cita.estado === 'completada' && this.$page.props.auth.user.rol?.nombre_interno !== 'admin';
        },
        insumosMedicamentos() {
            // CategoriaInsumo 1 = Medicamento
            return this.insumosSucursal.filter(ins => ins.categoria_insumo_id === 1);
        },
        insumosVacunas() {
            // CategoriaInsumo 3 = Vacuna
            return this.insumosSucursal.filter(ins => ins.categoria_insumo_id === 3);
        },
        opcionesInsumos() {
            return this.insumosSucursal.map(ins => ({
                id: ins.id,
                nombre: ins.nombre,
                subtexto: `Stock: ${ins.stock_actual} — $${Number(ins.precio_venta).toLocaleString('es-CL')}`
            }));
        }
    },
    // CICLO DE VIDA: Se ejecuta al cargar el componente en el DOM
    mounted() {
        this.cargarDatosFicha();
    },
    // MÉTODOS: Bloque de funciones y eventos
    methods: {
        // Función auxiliar para verificar si el medicamento seleccionado tiene stock
        medicamentoEnStock(nombre) {
            if (!nombre) return false;
            return this.insumosSucursal.some(ins => ins.nombre === nombre && ins.categoria_insumo_id === 1);
        },

        // Emite un evento al componente padre "AgregarCargo"
        emitirAgregarCargo() {
            this.$emit('agregar-cargo', { insumoId: this.nuevoCargo.insumoId, cantidad: this.nuevoCargo.cantidad });
            this.nuevoCargo.insumoId = '';
            this.nuevoCargo.cantidad = 1;
            this.mostrandoFormularioInsumo = false;
        },
        
        // Carga los datos de la ficha clínica
        cargarDatosFicha() {
            // Si la cita tiene ficha clínica, la carga en el estado del componente
            if (this.cita.ficha_clinica) {
                const fc = this.cita.ficha_clinica;
                this.ficha.peso_actual = fc.peso_actual;
                this.ficha.frecuencia_cardiaca = fc.frecuencia_cardiaca;
                this.ficha.temperatura = fc.temperatura;
                this.ficha.anamnesis = fc.anamnesis;
                this.ficha.sintomas = fc.sintomas;
                this.ficha.diagnostico = fc.diagnostico;
                
                // Carga las recetas si existen
                if (fc.recetas) {
                    // Itera sobre cada receta y la formatea
                    this.ficha.recetas = fc.recetas.map(r => ({
                        medicamentosTexto: Array.isArray(r.medicamentos) 
                            ? r.medicamentos.map(m => (typeof m === 'object' && m !== null) ? `${m.nombre || 'Medicamento'}${m.dosis ? ' - ' + m.dosis : ''}` : m).join('\n') 
                            : r.medicamentos,
                        indicaciones_generales: r.indicaciones_generales,
                        comprado_en_clinica: r.comprado_en_clinica !== undefined ? Boolean(r.comprado_en_clinica) : true
                    }));
                }
                
                // Carga las vacunas si existen
                if (fc.vacunas) {
                    // Itera sobre cada vacuna y la formatea
                    this.ficha.vacunas = fc.vacunas.map(v => ({
                        nombre_vacuna: v.nombre_vacuna,
                        fecha_aplicacion: v.fecha_aplicacion,
                        fecha_proxima_dosis: v.fecha_proxima_dosis,
                        numero_lote: v.numero_lote,
                        notas: v.notas
                    }));
                }
            }
        },
        // Agregar una nueva receta
        agregarReceta() {
            this.ficha.recetas.push({ medicamentosTexto: '', indicaciones_generales: '', comprado_en_clinica: true });
        },
        // Eliminar una receta
        eliminarReceta(index) {
            this.ficha.recetas.splice(index, 1);
        },
        // Agregar una nueva vacuna
        agregarVacuna() {
            const hoy = new Date().toISOString().split('T')[0];
            this.ficha.vacunas.push({
                nombre_vacuna: '',
                fecha_aplicacion: hoy,
                fecha_proxima_dosis: null,
                numero_lote: '',
                notas: ''
            });
        },
        // Eliminar una vacuna
        eliminarVacuna(index) {
            this.ficha.vacunas.splice(index, 1);
        },
        // Verifica si la ficha clínica está vacía
        esFichaVacia() {
            return !(
                this.ficha.peso_actual ||
                this.ficha.frecuencia_cardiaca ||
                this.ficha.temperatura ||
                (this.ficha.anamnesis && this.ficha.anamnesis.trim() !== '') ||
                (this.ficha.sintomas && this.ficha.sintomas.trim() !== '') ||
                (this.ficha.diagnostico && this.ficha.diagnostico.trim() !== '')
            );
        },
        // Guarda la ficha clínica en la base de datos
        guardarFicha() {
            if (this.esFichaVacia()) {
                Swal.fire({
                    title: 'Ficha Vacía',
                    text: 'Debe ingresar al menos un dato médico (peso, anamnesis, diagnóstico, etc.) antes de guardar.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            this.guardando = true;
            
            // Prepara el payload con la información de la ficha clínica
            const payload = {
                peso_actual: this.ficha.peso_actual,
                frecuencia_cardiaca: this.ficha.frecuencia_cardiaca,
                temperatura: this.ficha.temperatura,
                anamnesis: this.ficha.anamnesis,
                sintomas: this.ficha.sintomas,
                diagnostico: this.ficha.diagnostico,
                recetas: this.ficha.recetas.map(r => ({
                    medicamentos: r.medicamentosTexto ? r.medicamentosTexto.split('\n').filter(l => l.trim() !== '') : [],
                    indicaciones_generales: r.indicaciones_generales,
                    comprado_en_clinica: r.comprado_en_clinica

                    //Filtrar las recetas que tengan al menos un medicamento
                })).filter(r => r.medicamentos.length > 0),

                //Filtrar las vacunas que tengan al menos un nombre y fecha
                vacunas: this.ficha.vacunas.filter(v => v.nombre_vacuna && v.fecha_aplicacion)
            };

            //Guarda la ficha clínica en la base de datos
            axios.post(`/api/citas/${this.cita.id}/ficha-clinica`, payload)
                .then(response => {
                    // Muestra un mensaje de éxito
                    Swal.fire({
                        title: 'Éxito',
                        text: 'Ficha Clínica guardada correctamente.',
                        icon: 'success',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    // Emite el evento actualizado
                    this.$emit('actualizado');
                })
                // Maneja los errores de la petición
                .catch(error => {
                    // Muestra un mensaje de error
                    console.error('Error al guardar ficha clínica:', error);
                    const mensaje = error.response?.data?.message || error.response?.data?.error || 'Ocurrió un error al guardar.';
                    Swal.fire('Error', mensaje, 'error');
                })
                // Finaliza el guardado
                .finally(() => {
                    this.guardando = false;
                });
        }
    }
}
</script>
