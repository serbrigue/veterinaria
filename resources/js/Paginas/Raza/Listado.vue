<template>
    <!--
    ============================================
    MÓDULO 3: Completa este CRUD usando
    Mascota/Listado.vue como referencia
    ============================================
    -->

    <!-- TODO: Agregar <Head title="Razas" /> -->
    <!-- TODO: Envolver en <AuthenticatedLayout> -->
    <!-- TODO: Card con header (título + botón "Nueva Raza") -->

    <!-- TODO: Estado de carga (v-if="cargando") -->

    <!-- TODO: Estado vacío (v-else-if="razas.length === 0") -->

    <!-- TODO: Tabla con columnas: Nombre, Descripción, Especie, Acciones -->

    <!-- TODO: Modal para crear/editar raza con formulario (select de especie con v-for) -->

    <!-- TODO: Modal para confirmar eliminación -->
</template>

<script>
import AuthenticatedLayout from '@/Disenos/LayoutAutenticado.vue';
import { Head } from '@inertiajs/vue3';

export default {
    components: {
        AuthenticatedLayout,
        Head,
    },
    props: {
        razas: {
            type: Array,
            default: () => [],
        },
        especies: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            cargando: false,
            mostrarModal: false,
            modoEdicion: false,
            razaEditando: null,
            mostrarConfirmacion: false,
            razaAEliminar: null,
            eliminando: false,
            formulario: {
                nombre: '',
                descripcion: '',
                especie_id: '',
                errors: {},
                processing: false,
            },
        }
    },
    methods: {
        abrirModalCrear() {
            // TODO: Resetear formulario y abrir modal en modo creación
        },
        abrirModalEditar(raza) {
            // TODO: Cargar datos de la raza en el formulario y abrir modal en modo edición
        },
        cerrarModal() {
            // TODO: Cerrar modal y limpiar estado
        },
        guardar() {
            // TODO: Hacer axios.post() o axios.put() según modoEdicion
            // this.formulario.processing = true
            // this.formulario.errors = {}
            // if (this.modoEdicion) {
            //     axios.put(`/api/razas/${this.razaEditando.id}`, { ... })
            // } else {
            //     axios.post('/api/razas', { ... })
            // }
            // .then(() => { this.cerrarModal(); window.location.reload() })
            // .catch((error) => { if (error.response?.status === 422) this.formulario.errors = error.response.data.errors })
            // .finally(() => { this.formulario.processing = false })
        },
        confirmarEliminar(raza) {
            // TODO: Mostrar confirmación de eliminación
        },
        eliminarRaza() {
            // TODO: Hacer axios.delete() y recargar
        },
    },
}
</script>
