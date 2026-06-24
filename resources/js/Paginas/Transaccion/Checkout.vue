<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    transaccion: Object,
});

const form = useForm({
    metodo_pago: 'tarjeta',
    numero_tarjeta: '',
    nombre_titular: '',
    vencimiento: '',
    cvv: ''
});

const isProcessing = ref(false);
const isSuccess = ref(false);

const submitPayment = () => {
    isProcessing.value = true;
    
    // Simulamos un retraso de pasarela bancaria
    setTimeout(() => {
        axios.post(route('transacciones.pagar', props.transaccion.id), {
            metodo_pago: form.metodo_pago
        })
        .then(response => {
            isProcessing.value = false;
            isSuccess.value = true;
            
            // Redirigir después de mostrar el éxito
            setTimeout(() => {
                window.location.href = route('citas.detalle', props.transaccion.cita_id);
            }, 2500);
        })
        .catch(error => {
            isProcessing.value = false;
            alert('Error al procesar el pago. Por favor, intente nuevamente.');
        });
    }, 1500);
};
</script>

<template>
    <Head title="Checkout Segura" />

    <div class="min-vh-100 bg-light py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-10 col-xl-8">
                    
                    <!-- Animación de Éxito -->
                    <div v-if="isSuccess" class="card border-0 shadow-lg rounded-4 overflow-hidden text-center p-5">
                        <div class="mb-4">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                        </div>
                        <h2 class="fw-bold text-dark mb-3">¡Pago Exitoso!</h2>
                        <p class="text-muted fs-5 mb-4">Tu transacción ha sido procesada correctamente. Generando tu comprobante...</p>
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>

                    <!-- Pasarela de Pago -->
                    <div v-else class="card border-0 shadow-lg rounded-4 overflow-hidden">
                        <div class="row g-0">
                            
                            <!-- Columna Resumen (Izquierda) -->
                            <div class="col-md-5 bg-dark text-white p-4 p-md-5 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="mb-5">
                                        <Link :href="route('citas.detalle', transaccion.cita_id)" class="text-white text-decoration-none opacity-75 hover-opacity-100 transition-all">
                                            <i class="bi bi-arrow-left me-2"></i> Volver a la cita
                                        </Link>
                                    </div>
                                    <h4 class="fw-bold mb-4 opacity-75">Resumen de Compra</h4>
                                    
                                    <div class="mb-4">
                                        <h5 class="fs-6 text-uppercase opacity-75 mb-1" style="letter-spacing: 1px;">Prestación Principal</h5>
                                        <p class="fs-5 fw-semibold mb-0">{{ transaccion.cita?.prestacion?.nombre || 'Atención Veterinaria' }}</p>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <h5 class="fs-6 text-uppercase opacity-75 mb-1" style="letter-spacing: 1px;">Paciente</h5>
                                        <p class="fs-5 fw-semibold mb-0">
                                            <i class="bi bi-suit-heart-fill text-danger me-2"></i> 
                                            {{ transaccion.cita?.mascota?.nombre || 'Mascota' }}
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="mt-auto pt-4 border-top border-secondary border-opacity-50">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fs-5 opacity-75">Total a pagar</span>
                                        <span class="fs-2 fw-bold">${{ Number(transaccion.monto_total).toLocaleString('es-CL') }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Columna Formulario (Derecha) -->
                            <div class="col-md-7 p-4 p-md-5 bg-white">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h3 class="fw-bold text-dark mb-0">Pago Seguro</h3>
                                    <div class="d-flex gap-2 fs-4 text-muted">
                                        <i class="bi bi-credit-card-fill text-primary"></i>
                                        <i class="bi bi-shield-lock-fill text-success"></i>
                                    </div>
                                </div>
                                
                                <form @submit.prevent="submitPayment">
                                    <div class="mb-4">
                                        <label class="form-label fw-semibold text-muted small text-uppercase" style="letter-spacing: 0.5px;">Método de Pago</label>
                                        <div class="d-flex gap-3">
                                            <div class="form-check form-check-inline border rounded p-3 m-0 flex-grow-1 position-relative" :class="{'border-primary bg-primary bg-opacity-10': form.metodo_pago === 'tarjeta'}">
                                                <input class="form-check-input ms-0 me-2" type="radio" name="metodo" id="tarjeta" value="tarjeta" v-model="form.metodo_pago">
                                                <label class="form-check-label stretched-link fw-semibold" for="tarjeta">Tarjeta</label>
                                            </div>
                                            <div class="form-check form-check-inline border rounded p-3 m-0 flex-grow-1 position-relative" :class="{'border-primary bg-primary bg-opacity-10': form.metodo_pago === 'efectivo'}">
                                                <input class="form-check-input ms-0 me-2" type="radio" name="metodo" id="efectivo" value="efectivo" v-model="form.metodo_pago">
                                                <label class="form-check-label stretched-link fw-semibold" for="efectivo">Efectivo / Caja</label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div v-if="form.metodo_pago === 'tarjeta'" class="tarjeta-form slide-down">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-muted small">Nombre en la tarjeta</label>
                                            <input type="text" class="form-control form-control-lg bg-light" v-model="form.nombre_titular" placeholder="Ej. Juan Pérez" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-muted small">Número de Tarjeta</label>
                                            <div class="input-group input-group-lg">
                                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-credit-card"></i></span>
                                                <input type="text" class="form-control bg-light border-start-0 ps-0" v-model="form.numero_tarjeta" placeholder="0000 0000 0000 0000" maxlength="19" required>
                                            </div>
                                        </div>
                                        
                                        <div class="row g-3 mb-4">
                                            <div class="col-6">
                                                <label class="form-label fw-semibold text-muted small">Vencimiento</label>
                                                <input type="text" class="form-control form-control-lg bg-light" v-model="form.vencimiento" placeholder="MM/AA" maxlength="5" required>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-semibold text-muted small">CVV</label>
                                                <div class="input-group input-group-lg">
                                                    <input type="text" class="form-control bg-light" v-model="form.cvv" placeholder="123" maxlength="4" required>
                                                    <span class="input-group-text bg-light" title="Código de 3 o 4 dígitos al reverso"><i class="bi bi-question-circle"></i></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div v-if="form.metodo_pago === 'efectivo'" class="mb-4 p-4 bg-warning bg-opacity-10 text-warning-emphasis rounded text-center">
                                        <i class="bi bi-info-circle-fill fs-3 mb-2 d-block"></i>
                                        <p class="mb-0 fw-semibold">Al seleccionar Efectivo, el pago debe ser registrado físicamente en caja por el personal de la clínica.</p>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold d-flex align-items-center justify-content-center gap-2" :disabled="isProcessing" style="height: 56px;">
                                        <span v-if="isProcessing" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                        <span v-else><i class="bi bi-lock-fill"></i> Pagar ${{ Number(transaccion.monto_total).toLocaleString('es-CL') }}</span>
                                    </button>
                                    
                                    <div class="text-center mt-4">
                                        <small class="text-muted"><i class="bi bi-shield-check text-success"></i> Pago encriptado de extremo a extremo.</small>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.hover-opacity-100:hover {
    opacity: 1 !important;
}
.transition-all {
    transition: all 0.3s ease;
}
.slide-down {
    animation: slideDown 0.3s ease-out;
}
@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
input.form-control:focus {
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
    border-color: #86b7fe;
}
</style>
