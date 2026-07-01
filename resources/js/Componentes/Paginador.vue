<template>
    <div v-if="data && data.last_page > 1" class="d-flex justify-content-between align-items-center mt-4">
        <div class="text-muted small">
            Mostrando {{ data.from }} a {{ data.to }} de {{ data.total }} {{ entidad }}
        </div>
        <nav aria-label="Navegación de páginas">
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item" :class="{ disabled: !data.prev_page_url }">
                    <button class="page-link" @click.prevent="$emit('cambiar-pagina', data.prev_page_url)">Anterior</button>
                </li>
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
export default {
    name: 'Paginador',
    props: {
        data: {
            type: Object,
            default: null,
        },
        entidad: {
            type: String,
            default: 'registros',
        },
    },
    emits: ['cambiar-pagina'],
}
</script>
