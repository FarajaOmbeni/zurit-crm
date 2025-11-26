<template>
    <button
        :class="[
            'inline-flex items-center justify-center rounded-lg border border-transparent px-6 py-3 text-sm font-semibold text-white transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-25',
            baseClasses
        ]"
        :disabled="disabled"
    >
        <slot />
    </button>
</template>

<script setup>
defineProps({
    disabled: {
        type: Boolean,
        default: false,
    },
    baseClasses: {
        type: String,
        default: 'bg-zurit-purple hover:bg-zurit-purple/90 focus:ring-zurit-purple',
    },
});
</script>
