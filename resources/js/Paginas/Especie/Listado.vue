<template>
    <!--
    ============================================
    MÓDULO 2: Completa este CRUD usando
    Mascota/Listado.vue como referencia
    ============================================
    -->

    <!-- TODO: Agregar <Head title="..." /> -->
    <!-- TODO: Envolver en <AuthenticatedLayout> -->
    <!-- TODO: Card con header (título + botón "Nueva Especie") -->

    <!-- TODO: Estado de carga (v-if="cargando") -->

    <!-- TODO: Estado vacío (v-else-if="especies.length === 0") -->

    <!-- TODO: Tabla con columnas: Nombre, Descripción, Acciones -->

    <!-- TODO: Modal para crear/editar especie con formulario -->

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
            especieEditando: null,
            mostrarConfirmacion: false,
            especieAEliminar: null,
            eliminando: false,
            formulario: {
                nombre: '',
                descripcion: '',
                errors: {},
                processing: false,
            },
        }
    },
    methods: {
        abrirModalCrear() {
            // TODO: Resetear formulario y abrir modal en modo creación
        },
        abrirModalEditar(especie) {
            // TODO: Cargar datos de la especie en el formulario y abrir modal en modo edición
        },
        cerrarModal() {
            // TODO: Cerrar modal y limpiar estado
        },
        guardar() {
            // TODO: Hacer axios.post() o axios.put() según modoEdicion
            // this.formulario.processing = true
            // this.formulario.errors = {}
            // if (this.modoEdicion) {
            //     axios.put(`/api/especies/${this.especieEditando.id}`, { ... })
            // } else {
            //     axios.post('/api/especies', { ... })
            // }
            // .then(() => { this.cerrarModal(); window.location.reload() })
            // .catch((error) => { if (error.response?.status === 422) this.formulario.errors = error.response.data.errors })
            // .finally(() => { this.formulario.processing = false })
        },
        confirmarEliminar(especie) {
            // TODO: Mostrar confirmación de eliminación
        },
        eliminarEspecie() {
            // TODO: Hacer axios.delete() y recargar
        },
    },
}
</script>
