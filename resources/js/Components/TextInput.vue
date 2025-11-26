<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="block w-full rounded-lg border-light-gray bg-light-gray px-4 py-3 font-body text-sm text-light-black placeholder-zurit-gray focus:border-zurit-purple focus:ring-1 focus:ring-zurit-purple focus:outline-none transition-colors"
        v-model="model"
        ref="input"
    />
</template>
