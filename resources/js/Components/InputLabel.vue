<script setup>
defineProps({
    value: {
        type: String,
    },
    for: {
        type: String,
    },
});
</script>

<template>
    <label :for="for" class="block font-body text-sm font-medium text-light-black">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
