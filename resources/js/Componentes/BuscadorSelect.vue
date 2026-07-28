<template>
    <div class="position-relative">
        <div class="input-group input-group-sm">
            <span class="input-group-text bg-white border-end-0 text-muted">
                <i class="bi bi-search"></i>
            </span>
            <input 
                type="text" 
                class="form-control border-start-0" 
                :placeholder="placeholder"
                v-model="busqueda"
                @focus="abrir"
                @blur="cerrar"
                :disabled="disabled"
            >
            <button v-if="modelValue" class="btn btn-light border text-danger px-3" type="button" @click="limpiar" :disabled="disabled" title="Borrar selección">
                &#10006;
            </button>
            <button v-else class="btn btn-light border text-secondary px-3" type="button" @click="abrir" :disabled="disabled">
                &#9660;
            </button>
        </div>
        
        <div v-if="abierto" class="position-absolute w-100 bg-white border rounded mt-1 shadow-lg" style="z-index: 1050; max-height: 250px; overflow-y: auto;">
            <div 
                v-for="opcion in opcionesFiltradas" 
                :key="opcion[trackBy]" 
                class="px-3 py-2 border-bottom"
                style="cursor: pointer; transition: background-color 0.1s;"
                @mousedown.prevent="seleccionar(opcion)"
                @mouseover="$event.currentTarget.classList.add('bg-light')"
                @mouseleave="$event.currentTarget.classList.remove('bg-light')"
            >
                <!-- Renderiza el nombre y si existe un subtexto -->
                <div class="fw-medium text-dark">{{ opcion[label] }}</div>
                <div v-if="opcion.subtexto" class="small text-muted">{{ opcion.subtexto }}</div>
            </div>
            <div v-if="opcionesFiltradas.length === 0" class="px-3 py-3 text-center text-muted small">
                No se encontraron resultados para "{{ busqueda }}"
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'BuscadorSelect',
    props: {
        modelValue: { required: true },
        opciones: { type: Array, required: true },
        label: { type: String, default: 'nombre' },
        trackBy: { type: String, default: 'id' },
        placeholder: { type: String, default: 'Buscar...' },
        disabled: { type: Boolean, default: false }
    },
    emits: ['update:modelValue', 'change'],
    data() {
        return {
            busqueda: '',
            abierto: false
        }
    },
    computed: {
        opcionesFiltradas() {
            if (!this.busqueda || this.busqueda === this.nombreSeleccionado) return this.opciones;
            const termino = this.busqueda.toLowerCase();
            return this.opciones.filter(op => 
                String(op[this.label]).toLowerCase().includes(termino) || 
                (op.subtexto && String(op.subtexto).toLowerCase().includes(termino))
            );
        },
        nombreSeleccionado() {
            const seleccion = this.opciones.find(op => op[this.trackBy] === this.modelValue);
            return seleccion ? seleccion[this.label] : '';
        }
    },
    watch: {
        modelValue: {
            immediate: true,
            handler() {
                this.sincronizarBusqueda();
            }
        },
        opciones: {
            deep: true,
            handler() {
                this.sincronizarBusqueda();
            }
        }
    },
    methods: {
        abrir() {
            if (this.disabled) return;
            this.abierto = true;
            this.busqueda = ''; // Limpiar busqueda para mostrar todo
        },
        seleccionar(opcion) {
            this.$emit('update:modelValue', opcion[this.trackBy]);
            this.$emit('change', opcion);
            this.abierto = false;
        },
        cerrar() {
            this.abierto = false;
            this.sincronizarBusqueda();
        },
        limpiar() {
            this.$emit('update:modelValue', '');
            this.$emit('change', null);
            this.busqueda = '';
        },
        sincronizarBusqueda() {
            this.busqueda = this.nombreSeleccionado;
        }
    }
}
</script>
