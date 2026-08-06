<template>
    <!-- ================================================================================== -->
    <!-- COMPONENTE: Paginador -->
    <!-- ================================================================================== -->

    <!-- Renderizado condicional basado en Si existe data y es mayor a 1 -->
    <div v-if="data && data.last_page > 1" class="d-flex justify-content-between align-items-center mt-4">
        <div class="text-muted small">
            <!-- Muestra la información de la paginación -->
            Mostrando {{ data.from }} a {{ data.to }} de {{ data.total }} {{ entidad }}
        </div>
        <nav aria-label="Navegación de páginas">
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item" :class="{ disabled: !data.prev_page_url }">
                    <button class="page-link" @click.prevent="$emit('cambiar-pagina', data.prev_page_url)">Anterior</button>
                </li>
                <!-- Renderizado iterativo de lista para mostrar los números de página -->
                <li 
                    v-for="link in data.links.slice(1, -1)" 
                    :key="link.label" 
                    class="page-item" 
                    :class="{ active: link.active }"
                >
                    <button class="page-link" @click.prevent="$emit('cambiar-pagina', link.url)" v-html="link.label"></button>
                </li>
                <li class="page-item" :class="{ disabled: !data.next_page_url }">
                    <button class="page-link" @click.prevent="$emit('cambiar-pagina', data.next_page_url)">Siguiente</button>
                </li>
            </ul>
        </nav>
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
    name: 'Paginador',
    // PROPIEDADES: Datos inyectados desde el componente padre o estado
    props: {
        // Propiedad que recibe los datos de la paginación
        data: {
            type: Object,
            default: null,
        },
        // Propiedad que recibe el nombre de la entidad
        entidad: {
            type: String,
            default: 'registros',
        },
    },
    // EVENTOS: Eventos personalizados que emite el componente
    emits: ['cambiar-pagina'],
}
</script>
