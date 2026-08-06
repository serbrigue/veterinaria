<template>
    <!-- ================================================================================== -->
    <!-- COMPONENTE: BuscadorSelect -->
    <!-- ================================================================================== -->
    <div class="position-relative">
        <div class="input-group input-group-sm">
            <span class="input-group-text bg-white border-end-0 text-muted">
                <i class="bi bi-search"></i>
            </span>
            <!-- Captura de datos bidireccional con "busqueda" -->
            <input 
                type="text" 
                class="form-control border-start-0" 
                :placeholder="placeholder"
                v-model="busqueda"
                @focus="abrir"
                @blur="cerrar"
                :disabled="disabled"
            >
            <!-- Renderizado condicional basado en "modelValue" Si existe un valor, se muestra el botón de limpiar -->
            <button v-if="modelValue" class="btn btn-light border text-danger px-3" type="button" @click="limpiar" :disabled="disabled" title="Borrar selección">
                &#10006;
            </button>
            <!-- Si no existe un valor, se muestra el botón de abrir -->
            <button v-else class="btn btn-light border text-secondary px-3" type="button" @click="abrir" :disabled="disabled">
                &#9660;
            </button>
        </div>
        
        <!-- Renderizado condicional basado en "abierto" Si la lista está abierta, se muestra -->
        <div v-if="abierto" class="position-absolute w-100 bg-white border rounded mt-1 shadow-lg" style="z-index: 1050; max-height: 250px; overflow-y: auto;">
            <!-- Renderizado iterativo de lista con "opcionesFiltradas" -->
            <div 
                v-for="opcion in opcionesFiltradas" 
                :key="opcion[trackBy]" 
                class="px-3 py-2 border-bottom"
                style="cursor: pointer; transition: background-color 0.1s;"
                @mousedown.prevent="seleccionar(opcion)"
                @mouseover="$event.currentTarget.classList.add('bg-light')"
                @mouseleave="$event.currentTarget.classList.remove('bg-light')"
            >
                <!-- Renderiza el nombre de la opción -->
                <div class="fw-medium text-dark">{{ opcion[label] }}</div>
                <!-- Renderizado condicional basado en "opcion.subtexto" Si existe un subtexto, se muestra -->
                <div v-if="opcion.subtexto" class="small text-muted">{{ opcion.subtexto }}</div>
            </div>
            <!-- Renderizado condicional basado en "opcionesFiltradas.length === 0" Si no hay resultados, se muestra un mensaje -->
            <div v-if="opcionesFiltradas.length === 0" class="px-3 py-3 text-center text-muted small">
                No se encontraron resultados para "{{ busqueda }}"
            </div>
        </div>
    </div>
</template>

<script>
// ==================================================================================
// LÓGICA DEL COMPONENTE (VUE 3)
// ==================================================================================

// ------------------------------------------------------------------------------
// EXPORT DEFAULT: Definición principal del componente
// ------------------------------------------------------------------------------
export default {
    name: 'BuscadorSelect',
    // PROPIEDADES: Datos inyectados desde el componente padre o estado
    props: {
        // Valor actual del select
        modelValue: { required: true },
        // Lista de opciones
        opciones: { type: Array, required: true },
        // Clave que se muestra como label
        label: { type: String, default: 'nombre' },
        // Clave que se usa como identificador
        trackBy: { type: String, default: 'id' },
        placeholder: { type: String, default: 'Buscar...' },
        disabled: { type: Boolean, default: false }
    },
    // EMIT: Eventos que el componente puede emitir al componente padre
    emits: ['update:modelValue', 'change'],
    // ESTADO REACTIVO: Variables locales del componente
    data() {
        // Inicializamos las variables locales del componente
        return {
            busqueda: '',
            abierto: false
        }
    },
    // PROPIEDADES COMPUTADAS: Variables reactivas que dependen de otras
    computed: {
        // Filtramos las opciones basado en la búsqueda
        opcionesFiltradas() {
            // Si no hay busqueda o la busqueda es igual al nombre seleccionado, devolvemos todas las opciones
            if (!this.busqueda || this.busqueda === this.nombreSeleccionado) return this.opciones;
            // Convertimos la busqueda a minusculas
            const termino = this.busqueda.toLowerCase();
            // Filtramos las opciones
            return this.opciones.filter(op => 
                String(op[this.label]).toLowerCase().includes(termino) || 
                (op.subtexto && String(op.subtexto).toLowerCase().includes(termino))
            );
        },
        // Obtenemos el nombre seleccionado
        nombreSeleccionado() {
            const seleccion = this.opciones.find(op => op[this.trackBy] === this.modelValue);
            return seleccion ? seleccion[this.label] : '';
        }
    },
    // OBSERVADORES: Reaccionan a cambios en propiedades o variables
    watch: {
        // Observa cambios en modelValue para sincronizar la busqueda
        modelValue: {
            // Ejecuta la funcion inmediatamente
            immediate: true,
            // Maneja el cambio
            handler() {
                // Sincroniza la busqueda
                this.sincronizarBusqueda();
            }
        },
        // Observa cambios en opciones
        opciones: {
            // Ejecuta la funcion inmediatamente
            deep: true,
            // Maneja el cambio
            handler() {
                // Sincroniza la busqueda
                this.sincronizarBusqueda();
            }
        }
    },
    // MÉTODOS: Bloque de funciones y eventos
    methods: {
        // Abre el select y limpia la busqueda
        abrir() {
            // Si el select esta deshabilitado, no se puede abrir
            if (this.disabled) return;
            // Abre el select
            this.abierto = true;
            // Limpia la busqueda
            this.busqueda = ''; 
        },
        // Selecciona una opcion
        seleccionar(opcion) {
            // Emite el evento update:modelValue con la opcion seleccionada para que el componente padre pueda reaccionar a los cambios del v-model
            this.$emit('update:modelValue', opcion[this.trackBy]);
            // Emite el evento change con la opcion seleccionada para que el componente padre pueda reaccionar a cambios
            this.$emit('change', opcion);
            // Cierra el select
            this.abierto = false;
        },
        // Cierra el select
        cerrar() {
            // Cierra el select
            this.abierto = false;
            // Sincroniza la busqueda
            this.sincronizarBusqueda();
        },
        // Limpia la busqueda y el valor seleccionado
        limpiar() {
            // Emite el evento update:modelValue con un valor vacio para que el componente padre pueda limpiar el input
            this.$emit('update:modelValue', '');
            // Emite el evento change con un valor null para que el componente padre pueda limpiar el input
            this.$emit('change', null);
            // Limpia la busqueda
            this.busqueda = '';
        },
        // Sincroniza la busqueda con el valor seleccionado
        sincronizarBusqueda() {
            this.busqueda = this.nombreSeleccionado;
        }
    }
}
</script>
