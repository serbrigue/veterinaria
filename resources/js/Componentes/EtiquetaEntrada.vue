<template>
    <label class="form-label" :for="for">
        <span v-if="valor">{{ valor }}</span>
        <span v-else><slot /></span>
    </label>
</template>

<script>
export default {
    props: {
        valor: {
            type: String,
        },
        for: {
            type: String,
        },
    },
}
</script>
