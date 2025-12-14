<script setup>
import Modal from '@/Components/Modal.vue';
import { computed } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    client: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close', 'edit']);

const formatDate = (date) => {
    if (!date) return '-';
    const d = new Date(date);
    const months = ['January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'];
    return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
};

const getStatusBadgeClass = (status) => {
    switch (status?.toLowerCase()) {
        case 'active':
        case 'won':
            return 'bg-green-100 text-green-800';
        case 'completed':
            return 'bg-blue-100 text-blue-800';
        case 'paused':
        case 'lost':
            return 'bg-yellow-100 text-yellow-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const formatStatus = (status) => {
    if (!status) return 'Unknown';
    const statusMap = {
        'won': 'Active',
        'lost': 'Paused',
        'new_lead': 'New',
        'initial_outreach': 'Active',
        'follow_ups': 'Active',
        'negotiations': 'Active',
    };
    return statusMap[status.toLowerCase()] || status;
};

const getServices = () => {
    if (!props.client?.product) return [];
    // Split product by comma and create service objects
    const services = props.client.product.split(',').map(s => s.trim()).filter(s => s);
    return services.map((service, index) => {
        const serviceConfigs = [
            { name: 'Prosperity Talks', icon: 'microphone', color: 'bg-blue-50 border-blue-200', iconColor: 'text-blue-600', description: 'Expert speaker series on wealth building' },
            { name: 'Governance & Oversight', icon: 'gear', color: 'bg-orange-50 border-orange-200', iconColor: 'text-orange-600', description: 'Corporate governance and compliance' },
            { name: 'Prosperity Circles', icon: 'circles', color: 'bg-red-50 border-red-200', iconColor: 'text-red-600', description: 'Peer learning & networking groups' },
            { name: 'Fundamentals of Investments', icon: 'handshake', color: 'bg-purple-50 border-purple-200', iconColor: 'text-purple-600', description: 'Investment fundamentals and strategies' },
        ];
        const config = serviceConfigs[index % serviceConfigs.length];
        return {
            name: service,
            ...config,
        };
    });
};

const handleEdit = () => {
    emit('edit', props.client);
    emit('close');
};
</script>

<template>
    <Modal :show="show" max-width="2xl" @close="emit('close')">
        <div v-if="client" class="p-6">
            <!-- Header -->
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h2 class="font-heading text-2xl font-bold text-light-black">
                        {{ client.company || client.name }}
                    </h2>
                    <p class="font-body text-sm text-zurit-gray mt-1">
                        {{ client.company ? client.name : (client.position || 'Contact') }}
                    </p>
                </div>
                <button @click="emit('close')" class="text-zurit-gray hover:text-light-black transition-colors">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Contact Information -->
            <div class="mb-6">
                <h3 class="font-heading text-sm font-semibold text-light-black mb-3">Contact Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Email -->
                    <div class="flex items-center space-x-3 rounded-lg border border-gray-200 bg-white p-4">
                        <svg class="h-5 w-5 text-zurit-gray" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span class="font-body text-sm text-light-black">{{ client.email || '-' }}</span>
                    </div>
                    <!-- Phone -->
                    <div class="flex items-center space-x-3 rounded-lg border border-gray-200 bg-white p-4">
                        <svg class="h-5 w-5 text-zurit-gray" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <span class="font-body text-sm text-light-black">{{ client.phone || client.mobile || '-'
                            }}</span>
                    </div>
                </div>
            </div>

            <!-- Enrolled Services -->
            <div class="mb-6">
                <h3 class="font-heading text-sm font-semibold text-light-black mb-3">Enrolled Services</h3>
                <div class="space-y-3">
                    <div v-for="(service, index) in getServices()" :key="index"
                        :class="['flex items-start space-x-4 rounded-lg border p-4', service.color]">
                        <!-- Service Icon -->
                        <div :class="['flex-shrink-0', service.iconColor]">
                            <!-- Microphone Icon -->
                            <svg v-if="service.icon === 'microphone'" class="h-6 w-6" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                            </svg>
                            <!-- Gear Icon -->
                            <svg v-else-if="service.icon === 'gear'" class="h-6 w-6" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <!-- Circles Icon -->
                            <svg v-else-if="service.icon === 'circles'" class="h-6 w-6" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            <!-- Handshake Icon -->
                            <svg v-else class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                            </svg>
                        </div>
                        <!-- Service Info -->
                        <div class="flex-1">
                            <h4 class="font-body text-sm font-semibold text-light-black">{{ service.name }}</h4>
                            <p class="font-body text-xs text-zurit-gray mt-1">{{ service.description }}</p>
                        </div>
                    </div>
                    <div v-if="getServices().length === 0" class="text-center py-4">
                        <p class="font-body text-sm text-zurit-gray">No services enrolled</p>
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div class="mb-6">
                <div class="inline-flex flex-col rounded-lg bg-green-100 px-4 py-3">
                    <span class="font-body text-xs text-green-700 mb-1">Status</span>
                    <span :class="['font-body text-sm font-semibold', getStatusBadgeClass(client.status)]">
                        {{ formatStatus(client.status) }}
                    </span>
                </div>
            </div>

            <!-- Client Details Grid -->
            <div class="mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div class="space-y-4">
                        <div>
                            <p class="font-body text-xs text-zurit-gray mb-1">Join Date</p>
                            <p class="font-body text-sm font-medium text-light-black">{{ formatDate(client.won_at ||
                                client.created_at) }}</p>
                        </div>
                        <div>
                            <p class="font-body text-xs text-zurit-gray mb-1">Last Contact</p>
                            <p class="font-body text-sm font-medium text-light-black">{{ formatDate(client.updated_at)
                                }}</p>
                        </div>
                    </div>
                    <!-- Right Column -->
                    <div class="space-y-4">
                        <div>
                            <p class="font-body text-xs text-zurit-gray mb-1">Location</p>
                            <p class="font-body text-sm font-medium text-light-black">
                                {{ client.city && client.country ? `${client.city}, ${client.country}` : (client.city ||
                                client.country || '-') }}
                            </p>
                        </div>
                        <div>
                            <p class="font-body text-xs text-zurit-gray mb-1">Next Session</p>
                            <p class="font-body text-sm font-medium text-light-black">{{
                                formatDate(client.expected_close_date) }}</p>
                        </div>
                        <div>
                            <p class="font-body text-xs text-zurit-gray mb-1">Salesperson</p>
                            <p class="font-body text-sm font-medium text-light-black">
                                {{ client.added_by?.name || client.addedBy?.name || '-' }}
                            </p>
                        </div>
                        <div>
                            <p class="font-body text-xs text-zurit-gray mb-1">Email</p>
                            <p class="font-body text-sm font-medium text-light-black">
                                {{ client.added_by?.email || client.addedBy?.email || '-' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Profile Button -->
            <div class="flex justify-end">
                <button @click="handleEdit"
                    class="inline-flex items-center space-x-2 rounded-lg bg-zurit-purple px-6 py-3 font-body text-sm font-medium text-white transition-colors hover:bg-zurit-purple/90 focus:outline-none focus:ring-2 focus:ring-zurit-purple focus:ring-offset-2">
                    <span>Edit profile</span>
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
    </Modal>
</template>
