<script setup>
defineProps({
    message: {
        type: String,
    },
});
</script>

<template>
    <div v-show="message">
        <p class="font-body text-sm text-prosper">
            {{ message }}
        </p>
    </div>
</template>
