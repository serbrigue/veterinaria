<template>
    <div class="card h-100 border-0 shadow-sm hover-elevate transition-all overflow-hidden group-card cursor-pointer rounded-4">
        <!-- Cabecera con Link si se define urlDetalle -->
        <Link 
            v-if="urlDetalle" 
            :href="urlDetalle" 
            class="position-relative bg-light bg-opacity-10 d-block text-decoration-none header-area" 
        >
            <div class="w-100 h-100 d-flex align-items-center justify-content-center text-primary bg-primary bg-opacity-10 hover-zoom">
                <img v-if="imagenUrl" :src="imagenUrl" class="w-100 h-100 object-fit-cover transition-all" alt="Imagen de entidad" />
                <i v-else-if="icono" :class="`bi ${icono} fs-1`"></i>
            </div>
            
            <div class="position-absolute bottom-0 start-0 w-100 h-50 bg-gradient-dark pointer-events-none"></div>
            <div class="position-absolute bottom-0 start-0 w-100 p-3 pb-2 text-white pointer-events-none d-flex flex-column justify-content-end h-100">
                <h3 class="h5 mb-0 fw-bold text-shadow text-truncate">{{ titulo }}</h3>
                <slot name="header-badge" />
            </div>
        </Link>

        <!-- Cabecera sin Link -->
        <div 
            v-else 
            class="position-relative bg-light bg-opacity-10 header-area" 
        >
            <div class="w-100 h-100 d-flex align-items-center justify-content-center text-primary bg-primary bg-opacity-10 hover-zoom">
                <img v-if="imagenUrl" :src="imagenUrl" class="w-100 h-100 object-fit-cover transition-all" alt="Imagen de entidad" />
                <i v-else-if="icono" :class="`bi ${icono} fs-1`"></i>
            </div>
            
            <div class="position-absolute bottom-0 start-0 w-100 h-50 bg-gradient-dark pointer-events-none"></div>
            <div class="position-absolute bottom-0 start-0 w-100 p-3 pb-2 text-white pointer-events-none d-flex flex-column justify-content-end h-100">
                <h3 class="h5 mb-0 fw-bold text-shadow text-truncate">{{ titulo }}</h3>
                <slot name="header-badge" />
            </div>
        </div>
        
        <!-- Cuerpo de la Tarjeta -->
        <div class="card-body p-4 d-flex flex-column bg-white">
            <div class="flex-grow-1">
                <slot name="body" />
            </div>
            
            <!-- Acciones de Edición/Eliminación -->
            <div v-if="mostrarAcciones" class="d-flex gap-2 pt-3 border-top mt-auto justify-content-between">
                <slot name="acciones">
                    <button 
                        type="button" 
                        class="btn btn-sm btn-light text-primary border border-primary-subtle flex-grow-1 btn-hover-primary transition-all rounded-pill py-2 px-3 fw-medium" 
                        @click.stop.prevent="$emit('editar')"
                    >
                        <i class="bi bi-pencil me-1"></i> Editar
                    </button>
                    <button 
                        type="button" 
                        class="btn btn-sm btn-light text-danger border border-danger-subtle flex-grow-1 btn-hover-danger transition-all rounded-pill py-2 px-3 fw-medium" 
                        @click.stop.prevent="$emit('eliminar')"
                    >
                        <i class="bi bi-trash me-1"></i> Eliminar
                    </button>
                </slot>
            </div>
        </div>
    </div>
</template>

<script>
import { Link } from '@inertiajs/vue3';

export default {
    name: 'TarjetaEntidad',
    components: {
        Link,
    },
    props: {
        titulo: {
            type: String,
            required: true,
        },
        icono: {
            type: String,
            default: '',
        },
        imagenUrl: {
            type: String,
            default: '',
        },
        urlDetalle: {
            type: String,
            default: '',
        },
        mostrarAcciones: {
            type: Boolean,
            default: false,
        },
    },
    emits: ['editar', 'eliminar'],
}
</script>

<style scoped>
.header-area {
    height: 140px;
    overflow: hidden;
}
.hover-elevate {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.hover-elevate:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
}
.transition-all {
    transition: all 0.3s ease;
}
.hover-zoom {
    transition: transform 0.5s ease;
}
.group-card:hover .hover-zoom {
    transform: scale(1.08);
}
.bg-gradient-dark {
    background: linear-gradient(to top, rgba(0,0,0,0.75) 0%, rgba(0,0,0,0) 100%);
}
.text-shadow {
    text-shadow: 0 2px 4px rgba(0,0,0,0.5);
}
.btn-hover-primary {
    transition: background-color 0.2s, color 0.2s;
}
.btn-hover-primary:hover {
    background-color: var(--bs-primary) !important;
    color: white !important;
}
.btn-hover-danger {
    transition: background-color 0.2s, color 0.2s;
}
.btn-hover-danger:hover {
    background-color: var(--bs-danger) !important;
    color: white !important;
}
.pointer-events-none {
    pointer-events: none;
}
.object-fit-cover {
    object-fit: cover;
}
</style>
