<script setup>
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref, watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    client: {
        type: Object,
        default: null,
    },
    initialEditMode: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'edit', 'updated']);

const isEditMode = ref(false);
const form = ref({
    name: '',
    position: '',
    company: '',
    email: '',
    phone: '',
    city: '',
    country: '',
    source: '',
    sector: '',
});
const errors = ref({});
const processing = ref(false);
const notification = ref({
    type: null, // 'loading', 'success', 'error'
    message: '',
});

// Watch for client changes and populate form
watch(() => props.client, (newClient) => {
    if (newClient) {
        form.value = {
            name: newClient.name || '',
            position: newClient.position || '',
            company: newClient.company || '',
            email: newClient.email || '',
            phone: newClient.phone || newClient.mobile || '',
            city: newClient.city || '',
            country: newClient.country || '',
            source: newClient.source || '',
            sector: newClient.sector || '',
        };
        // Set edit mode based on initialEditMode prop
        isEditMode.value = props.initialEditMode;
        errors.value = {};
    }
}, { immediate: true });

// Watch for initialEditMode prop changes
watch(() => props.initialEditMode, (newValue) => {
    if (props.show && props.client) {
        isEditMode.value = newValue;
    }
});

// Reset edit mode when modal closes
watch(() => props.show, (isShowing) => {
    if (!isShowing) {
        isEditMode.value = false;
        errors.value = {};
        notification.value = { type: null, message: '' };
    }
});

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
    isEditMode.value = true;
};

const cancelEdit = () => {
    isEditMode.value = false;
    errors.value = {};
    notification.value = { type: null, message: '' };
    // Reset form to original client data
    if (props.client) {
        form.value = {
            name: props.client.name || '',
            position: props.client.position || '',
            company: props.client.company || '',
            email: props.client.email || '',
            phone: props.client.phone || props.client.mobile || '',
            city: props.client.city || '',
            country: props.client.country || '',
            source: props.client.source || '',
            sector: props.client.sector || '',
        };
    }
};

const submit = async () => {
    if (!props.client) return;

    processing.value = true;
    errors.value = {};
    notification.value = {
        type: 'loading',
        message: '',
    };

    try {
        const response = await window.axios.put(`/api/clients/${props.client.id}`, form.value);

        // Show success message
        notification.value = {
            type: 'success',
            message: 'Success',
        };

        // Emit updated event with the updated client data
        emit('updated', response.data);

        // Auto-hide success message and switch to view mode after 2 seconds
        setTimeout(() => {
            notification.value = { type: null, message: '' };
            isEditMode.value = false;
        }, 2000);
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
            // Show error notification
            const errorMessages = error.response.data.errors || {};
            const firstError = Object.values(errorMessages)[0];
            notification.value = {
                type: 'error',
                message: Array.isArray(firstError) ? firstError[0] : 'Validation error occurred',
            };
        } else {
            console.error('Error updating client:', error);
            errors.value = { general: 'Failed to update client. Please try again.' };
            notification.value = {
                type: 'error',
                message: 'Failed to update client. Please try again.',
            };
        }
    } finally {
        processing.value = false;
    }
};
</script>

<template>
    <Modal :show="show" max-width="2xl" @close="emit('close')">
        <div v-if="client" class="p-6 relative">
            <!-- Notification Area (Bottom Middle) -->
            <div class="fixed bottom-4 left-1/2 transform -translate-x-1/2 z-50 pointer-events-none">
                <!-- Loading Spinner -->
                <div v-if="notification.type === 'loading'"
                    class="bg-white rounded-lg shadow-lg px-6 py-4 flex items-center space-x-3 border border-gray-200">
                    <svg class="animate-spin h-5 w-5 text-zurit-purple" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span class="font-body text-sm text-light-black">Saving changes...</span>
                </div>

                <!-- Success Message -->
                <div v-if="notification.type === 'success'"
                    class="bg-green-50 border border-green-200 rounded-lg shadow-lg px-6 py-4 flex items-center space-x-3">
                    <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="font-body text-sm font-medium text-green-800">{{ notification.message }}</span>
                </div>

                <!-- Error Message -->
                <div v-if="notification.type === 'error'"
                    class="bg-red-50 border border-red-200 rounded-lg shadow-lg px-6 py-4 flex items-center space-x-3">
                    <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span class="font-body text-sm font-medium text-red-800">{{ notification.message }}</span>
                </div>
            </div>

            <!-- Header -->
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h2 class="font-heading text-2xl font-bold text-light-black">
                        {{ isEditMode ? (form.company || form.name) : (client.company || client.name) }}
                    </h2>
                    <p class="font-body text-sm text-zurit-gray mt-1">
                        {{ isEditMode
                            ? (form.company ? form.name : (form.position || 'Contact'))
                            : (client.company ? client.name : (client.position || 'Contact'))
                        }}
                    </p>
                </div>
                <button @click="emit('close')" class="text-zurit-gray hover:text-light-black transition-colors">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- View Mode Content -->
            <template v-if="!isEditMode">
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
                                <svg v-if="service.icon === 'microphone'" class="h-6 w-6" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                                </svg>
                                <!-- Gear Icon -->
                                <svg v-else-if="service.icon === 'gear'" class="h-6 w-6" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
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
                                <p class="font-body text-xs text-zurit-gray mb-1">Name</p>
                                <p class="font-body text-sm font-medium text-light-black">{{ client.name || '-' }}</p>
                            </div>
                            <div>
                                <p class="font-body text-xs text-zurit-gray mb-1">Position</p>
                                <p class="font-body text-sm font-medium text-light-black">{{ client.position || '-' }}
                                </p>
                            </div>
                            <div>
                                <p class="font-body text-xs text-zurit-gray mb-1">Company</p>
                                <p class="font-body text-sm font-medium text-light-black">{{ client.company || '-' }}
                                </p>
                            </div>
                            <div>
                                <p class="font-body text-xs text-zurit-gray mb-1">Email</p>
                                <p class="font-body text-sm font-medium text-light-black">{{ client.email || '-' }}</p>
                            </div>
                            <div>
                                <p class="font-body text-xs text-zurit-gray mb-1">Phone</p>
                                <p class="font-body text-sm font-medium text-light-black">{{ client.phone ||
                                    client.mobile || '-' }}</p>
                            </div>
                            <div>
                                <p class="font-body text-xs text-zurit-gray mb-1">City</p>
                                <p class="font-body text-sm font-medium text-light-black">{{ client.city || '-' }}</p>
                            </div>
                        </div>
                        <!-- Right Column -->
                        <div class="space-y-4">
                            <div>
                                <p class="font-body text-xs text-zurit-gray mb-1">Country</p>
                                <p class="font-body text-sm font-medium text-light-black">{{ client.country || '-' }}
                                </p>
                            </div>
                            <div>
                                <p class="font-body text-xs text-zurit-gray mb-1">Source</p>
                                <p class="font-body text-sm font-medium text-light-black">{{ client.source || '-' }}</p>
                            </div>
                            <div>
                                <p class="font-body text-xs text-zurit-gray mb-1">Sector</p>
                                <p class="font-body text-sm font-medium text-light-black">{{ client.sector || '-' }}</p>
                            </div>
                            <div>
                                <p class="font-body text-xs text-zurit-gray mb-1">Join Date</p>
                                <p class="font-body text-sm font-medium text-light-black">{{ formatDate(client.won_at ||
                                    client.created_at) }}</p>
                            </div>
                            <div>
                                <p class="font-body text-xs text-zurit-gray mb-1">Last Contact</p>
                                <p class="font-body text-sm font-medium text-light-black">{{
                                    formatDate(client.updated_at)
                                    }}</p>
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
                        </div>
                    </div>
                </div>
            </template>

            <!-- Edit Form -->
            <div v-if="isEditMode" class="mt-6">
                <h3 class="font-heading text-lg font-semibold text-light-black mb-4">Edit Client Information</h3>

                <form @submit.prevent="submit" class="space-y-4">
                    <!-- General Error -->
                    <div v-if="errors.general" class="rounded-lg bg-red-50 border border-red-200 p-4">
                        <p class="font-body text-sm text-red-800">{{ errors.general }}</p>
                    </div>

                    <!-- Name -->
                    <div>
                        <InputLabel for="name" value="Name" />
                        <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required />
                        <InputError v-if="errors.name" :message="errors.name[0]" />
                    </div>

                    <!-- Position -->
                    <div>
                        <InputLabel for="position" value="Position" />
                        <TextInput id="position" v-model="form.position" type="text" class="mt-1 block w-full" />
                        <InputError v-if="errors.position" :message="errors.position[0]" />
                    </div>

                    <!-- Company -->
                    <div>
                        <InputLabel for="company" value="Company" />
                        <TextInput id="company" v-model="form.company" type="text" class="mt-1 block w-full" required />
                        <InputError v-if="errors.company" :message="errors.company[0]" />
                    </div>

                    <!-- Email -->
                    <div>
                        <InputLabel for="email" value="Email" />
                        <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" />
                        <InputError v-if="errors.email" :message="errors.email[0]" />
                    </div>

                    <!-- Phone -->
                    <div>
                        <InputLabel for="phone" value="Phone" />
                        <TextInput id="phone" v-model="form.phone" type="text" class="mt-1 block w-full" />
                        <InputError v-if="errors.phone" :message="errors.phone[0]" />
                    </div>

                    <!-- City -->
                    <div>
                        <InputLabel for="city" value="City" />
                        <TextInput id="city" v-model="form.city" type="text" class="mt-1 block w-full" />
                        <InputError v-if="errors.city" :message="errors.city[0]" />
                    </div>

                    <!-- Country -->
                    <div>
                        <InputLabel for="country" value="Country" />
                        <TextInput id="country" v-model="form.country" type="text" class="mt-1 block w-full" />
                        <InputError v-if="errors.country" :message="errors.country[0]" />
                    </div>

                    <!-- Source -->
                    <div>
                        <InputLabel for="source" value="Source" />
                        <TextInput id="source" v-model="form.source" type="text" class="mt-1 block w-full" />
                        <InputError v-if="errors.source" :message="errors.source[0]" />
                    </div>

                    <!-- Sector -->
                    <div>
                        <InputLabel for="sector" value="Sector" />
                        <TextInput id="sector" v-model="form.sector" type="text" class="mt-1 block w-full" />
                        <InputError v-if="errors.sector" :message="errors.sector[0]" />
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" @click="cancelEdit"
                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 font-body text-sm font-medium text-light-black shadow-sm hover:bg-light-gray focus:outline-none focus:ring-2 focus:ring-zurit-purple focus:ring-offset-2">
                            Cancel
                        </button>
                        <PrimaryButton :disabled="processing">
                            {{ processing ? 'Saving...' : 'Save Changes' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>

            <!-- Edit Profile Button (only show in view mode) -->
            <div v-else class="flex justify-end">
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
