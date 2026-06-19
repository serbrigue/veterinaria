<template>
    <div class="relative">
        <div @click="abierto = !abierto">
            <slot name="trigger" />
        </div>

        <!-- Full Screen Dropdown Overlay -->
        <div v-show="abierto" class="fixed inset-0 z-40" @click="abierto = false"></div>

        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-show="abierto"
                class="absolute z-50 mt-2 rounded-md shadow-lg"
                :class="[claseAncho, clasesAlineacion]"
                style="display: none"
                @click="abierto = false"
            >
                <div class="rounded-md ring-1 ring-black ring-opacity-5" :class="clasesContenido">
                    <slot name="content" />
                </div>
            </div>
        </Transition>
    </div>
</template>

<script>
export default {
    props: {
        alineacion: {
            type: String,
            default: 'right',
        },
        width: {
            type: String,
            default: '48',
        },
        clasesContenido: {
            type: String,
            default: 'py-1 bg-white',
        },
    },
    data() {
        return {
            abierto: false,
        }
    },
    computed: {
        claseAncho() {
            return {
                48: 'w-48',
            }[this.width.toString()];
        },
        clasesAlineacion() {
            if (this.alineacion === 'left') {
                return 'ltr:origin-top-left rtl:origin-top-right start-0';
            } else if (this.alineacion === 'right') {
                return 'ltr:origin-top-right rtl:origin-top-left end-0';
            } else {
                return 'origin-top';
            }
        },
    },
    mounted() {
        document.addEventListener('keydown', this.cerrarConEscape);
    },
    beforeUnmount() {
        document.removeEventListener('keydown', this.cerrarConEscape);
    },
    methods: {
        cerrarConEscape(e) {
            if (this.abierto && e.key === 'Escape') {
                this.abierto = false;
            }
        },
    },
}
</script>
