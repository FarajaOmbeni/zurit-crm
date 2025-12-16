<script setup>
import { computed } from 'vue';

const props = defineProps({
    lead: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['drag-start', 'drag-end', 'view']);

const formatTimeAgo = (date) => {
    if (!date) return 'N/A';

    const now = new Date();
    const past = new Date(date);
    const diffInMs = now - past;
    const diffInHours = Math.floor(diffInMs / (1000 * 60 * 60));
    const diffInDays = Math.floor(diffInHours / 24);

    if (diffInHours < 1) return 'Just now';
    if (diffInHours === 1) return '1 hour ago';
    if (diffInHours < 24) return `${diffInHours} hours ago`;
    if (diffInDays === 1) return '1 day ago';
    if (diffInDays < 7) return `${diffInDays} days ago`;

    return new Date(date).toLocaleDateString();
};

const lastContacted = computed(() => {
    // Use updated_at as last contacted time, or you can use activities if available
    return formatTimeAgo(props.lead.updated_at);
});

const phoneNumber = computed(() => {
    return props.lead.mobile || props.lead.phone || 'N/A';
});

const serviceType = computed(() => {
    return props.lead.product || 'N/A';
});

const handleDragStart = (event) => {
    emit('drag-start', event, props.lead);
};

const handleDragEnd = (event) => {
    emit('drag-end', event);
};

const handleView = () => {
    emit('view', props.lead);
};
</script>

<template>
    <div draggable="true" @dragstart="handleDragStart" @dragend="handleDragEnd"
        class="cursor-move rounded-lg bg-white p-4 shadow-sm transition-shadow hover:shadow-md">
        <!-- Lead Title -->
        <h4 class="font-heading text-base font-semibold text-light-black mb-3">
            {{ lead.product || 'Untitled Lead' }}
        </h4>

        <!-- Company -->
        <div class="flex items-center gap-2 mb-2">
            <svg class="h-4 w-4 text-zurit-gray" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <span class="font-body text-sm text-zurit-gray">{{ lead.company || 'N/A' }}</span>
        </div>

        <!-- Contact Person -->
        <div class="flex items-center gap-2 mb-2">
            <svg class="h-4 w-4 text-zurit-gray" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span class="font-body text-sm text-zurit-gray">{{ lead.name || 'N/A' }}</span>
        </div>

        <!-- Phone Number -->
        <div class="flex items-center gap-2 mb-2">
            <svg class="h-4 w-4 text-zurit-gray" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
            </svg>
            <span class="font-body text-sm text-zurit-gray">{{ phoneNumber }}</span>
        </div>

        <!-- Last Contacted -->
        <div class="mb-2">
            <span class="font-body text-xs text-zurit-gray">Last contacted: {{ lastContacted }}</span>
        </div>

        <!-- Service Type -->
        <div class="mb-3">
            <span class="font-body text-xs text-zurit-gray">Service type: {{ serviceType }}</span>
        </div>

        <!-- View Full Profile Link -->
        <button @click="handleView"
            class="flex items-center gap-1 font-body text-sm text-zurit-purple hover:text-zurit-purple/80 transition-colors">
            <span>View full profile</span>
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>
</template>
