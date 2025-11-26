<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'inline-flex items-center px-1 pt-1 border-b-2 border-prosper font-body text-sm font-medium leading-5 text-light-black focus:outline-none focus:border-prosper transition duration-150 ease-in-out'
        : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent font-body text-sm font-medium leading-5 text-zurit-gray hover:text-light-black hover:border-light-gray focus:outline-none focus:text-light-black focus:border-light-gray transition duration-150 ease-in-out',
);
</script>

<template>
    <Link :href="href" :class="classes">
    <slot />
    </Link>
</template>
