<template>
    <div v-show="mensaje">
        <p class="text-caption text-error">
            {{ mensaje }}
        </p>
    </div>
</template>

<script>
export default {
    props: {
        mensaje: {
            type: String,
        },
    },
}
</script>
