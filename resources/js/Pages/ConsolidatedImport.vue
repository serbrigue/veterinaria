<template>
  <div class="container py-4">
    <h2 class="mb-4">Importador Consolidado de Datos</h2>

    <!-- Step 1: Upload (Drag & Drop) -->
    <div v-if="step === 1" class="card shadow-sm">
      <div class="card-body text-center p-5">
        <div
          class="dropzone border-primary border-dashed rounded p-5 bg-light"
          @dragover.prevent="dragover = true"
          @dragleave.prevent="dragover = false"
          @drop.prevent="handleDrop"
          :class="{ 'bg-primary text-white': dragover }"
          style="border: 2px dashed #0d6efd; cursor: pointer;"
          @click="triggerFileInput"
        >
          <i class="bi bi-cloud-arrow-up display-4"></i>
          <h4 class="mt-3">Arrastra tu archivo Excel aquí</h4>
          <p class="text-muted" :class="{ 'text-white': dragover }">o haz clic para seleccionar (Máx. 10MB)</p>
          <input
            type="file"
            ref="fileInput"
            class="d-none"
            accept=".xlsx, .xls, .csv"
            @change="handleFileSelect"
          />
        </div>
      </div>
    </div>

    <!-- Loading Overlay -->
    <div v-if="loading" class="text-center my-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Cargando...</span>
      </div>
      <p class="mt-2">Procesando archivo...</p>
    </div>

    <!-- Step 2: Mapping -->
    <div v-if="step === 2 && !loading" class="card shadow-sm">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Mapeo de Datos</h5>
        <button class="btn btn-outline-secondary btn-sm" @click="reset">Volver</button>
      </div>
      <div class="card-body">
        <form @submit.prevent="submitImport">
          
          <!-- Módulo: Clientes -->
          <div class="mb-4 p-3 border rounded">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="text-primary mb-0"><i class="bi bi-person"></i> Clientes</h5>
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="switchClientes" v-model="modules.clientes">
                <label class="form-check-label" for="switchClientes">Importar Clientes</label>
              </div>
            </div>
            
            <div v-if="modules.clientes" class="row g-3">
              <div class="col-md-6" v-for="field in fields.clientes" :key="field.id">
                <label class="form-label">{{ field.label }}</label>
                <select class="form-select" v-model="mapping[field.id]">
                  <option :value="null">-- Ignorar columna --</option>
                  <option v-for="(header, index) in headers" :key="index" :value="header">
                    {{ header }} <span class="text-muted" v-if="sampleData[index]">(Ej: {{ sampleData[index] }})</span>
                  </option>
                </select>
              </div>
            </div>
          </div>

          <!-- Módulo: Mascotas -->
          <div class="mb-4 p-3 border rounded">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="text-primary mb-0"><i class="bi bi-heart"></i> Mascotas</h5>
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="switchMascotas" v-model="modules.mascotas" :disabled="!modules.clientes">
                <label class="form-check-label" for="switchMascotas">Importar Mascotas</label>
              </div>
            </div>
            
            <div v-if="modules.mascotas" class="row g-3">
              <div class="col-md-6" v-for="field in fields.mascotas" :key="field.id">
                <label class="form-label">{{ field.label }}</label>
                <select class="form-select" v-model="mapping[field.id]">
                  <option :value="null">-- Ignorar columna --</option>
                  <option v-for="(header, index) in headers" :key="index" :value="header">
                    {{ header }}
                  </option>
                </select>
              </div>
            </div>
          </div>

          <!-- Módulo: Citas -->
          <div class="mb-4 p-3 border rounded">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="text-primary mb-0"><i class="bi bi-calendar"></i> Citas</h5>
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="switchCitas" v-model="modules.citas" :disabled="!modules.mascotas">
                <label class="form-check-label" for="switchCitas">Importar Citas</label>
              </div>
            </div>
            
            <div v-if="modules.citas" class="row g-3">
              <div class="col-md-6" v-for="field in fields.citas" :key="field.id">
                <label class="form-label">{{ field.label }}</label>
                <select class="form-select" v-model="mapping[field.id]">
                  <option :value="null">-- Ignorar columna --</option>
                  <option v-for="(header, index) in headers" :key="index" :value="header">
                    {{ header }}
                  </option>
                </select>
              </div>
            </div>
          </div>

          <div class="text-end mt-4">
            <button type="submit" class="btn btn-success" :disabled="loading">
              <i class="bi bi-play-circle"></i> Ejecutar Importación Transaccional
            </button>
          </div>

        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

// Estados
const step = ref(1);
const loading = ref(false);
const dragover = ref(false);
const fileInput = ref(null);
const file = ref(null);

// Datos recibidos del backend
const headers = ref([]);
const sampleData = ref([]);

// Configuración de Mapeo y Módulos
const mapping = reactive({});
const modules = reactive({
  clientes: true,
  mascotas: true,
  citas: true
});

// Campos esperados en el sistema
const fields = {
  clientes: [
    { id: 'cliente_email', label: 'Email del Cliente (Llave)' },
    { id: 'cliente_nombre', label: 'Nombre del Cliente' },
    { id: 'cliente_telefono', label: 'Teléfono' },
    { id: 'cliente_direccion', label: 'Dirección' }
  ],
  mascotas: [
    { id: 'mascota_nombre', label: 'Nombre de la Mascota' },
    { id: 'mascota_raza', label: 'Raza (Texto)' }
  ],
  citas: [
    { id: 'cita_titulo', label: 'Motivo / Título de la Cita' },
    { id: 'cita_fecha_hora', label: 'Fecha y Hora (YYYY-MM-DD HH:MM:SS)' },
    { id: 'cita_veterinario', label: 'Veterinario Asignado' }
  ]
};

// Acciones Drag & Drop
const triggerFileInput = () => {
  fileInput.value.click();
};

const handleFileSelect = (event) => {
  const selectedFile = event.target.files[0];
  if (selectedFile) {
    processFile(selectedFile);
  }
};

const handleDrop = (event) => {
  dragover.value = false;
  const droppedFile = event.dataTransfer.files[0];
  if (droppedFile) {
    processFile(droppedFile);
  }
};

// RF-02: Pre-lectura Estructural
const processFile = async (selectedFile) => {
  if (selectedFile.size > 10 * 1024 * 1024) {
    Swal.fire('Error', 'El archivo no debe superar los 10MB', 'error');
    return;
  }

  file.value = selectedFile;
  loading.value = true;

  const formData = new FormData();
  formData.append('file', file.value);

  try {
    const response = await axios.post('/api/import/analyze', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    if (response.data.success) {
      headers.value = response.data.headers;
      sampleData.value = response.data.sample;
      autoMapHeaders();
      step.value = 2; // Avanzar al paso de mapeo
    }
  } catch (error) {
    Swal.fire('Error', error.response?.data?.message || 'Error al analizar el archivo', 'error');
    reset();
  } finally {
    loading.value = false;
  }
};

// Auto-mapeo simple basado en nombres
const autoMapHeaders = () => {
  const lowerHeaders = headers.value.map(h => h ? h.toString().toLowerCase() : '');
  
  // Buscar coincidencia simple
  const findMatch = (keywords) => {
    const idx = lowerHeaders.findIndex(h => keywords.some(k => h.includes(k)));
    return idx !== -1 ? headers.value[idx] : null;
  };

  mapping['cliente_email'] = findMatch(['email', 'correo']);
  mapping['cliente_nombre'] = findMatch(['cliente', 'dueño', 'nombre cliente']);
  mapping['mascota_nombre'] = findMatch(['mascota', 'paciente', 'nombre mascota']);
  mapping['mascota_raza'] = findMatch(['raza']);
  mapping['cita_fecha_hora'] = findMatch(['fecha', 'hora', 'cuando']);
};

// RF-01, RNF-01: Ejecutar la importación real transaccional
const submitImport = async () => {
  loading.value = true;

  const formData = new FormData();
  formData.append('file', file.value);
  formData.append('mapping', JSON.stringify(mapping));
  formData.append('modules', JSON.stringify(modules));

  try {
    const response = await axios.post('/api/import/process', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    if (response.data.success) {
      Swal.fire({
        icon: 'success',
        title: '¡Importación Exitosa!',
        text: response.data.message
      });
      reset();
    }
  } catch (error) {
    // RNF-03: Feedback UX de Errores con la fila exacta y motivo
    const errorMsg = error.response?.data?.message || 'Hubo un error en la importación.';
    Swal.fire({
      icon: 'error',
      title: 'Importación Fallida',
      text: errorMsg,
      footer: 'Se ha realizado un Rollback completo. No se guardaron datos en la base de datos.'
    });
  } finally {
    loading.value = false;
  }
};

const reset = () => {
  step.value = 1;
  file.value = null;
  headers.value = [];
  sampleData.value = [];
  if (fileInput.value) {
    fileInput.value.value = '';
  }
};
</script>
