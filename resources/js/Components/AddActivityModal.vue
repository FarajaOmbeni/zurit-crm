<script setup>
import { ref, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Alert from '@/Components/Alert.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import axios from 'axios';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    lead: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close', 'activity-added']);

// Form fields
const form = ref({
    type: 'call',
    activity_date: new Date().toISOString().split('T')[0], // Today's date
    description: '',
    outcome: '',
});

const errors = ref({});
const saving = ref(false);
const notification = ref({ type: null, message: '' });

// Activity type options
const activityTypes = [
    { value: 'call', label: 'Call', icon: 'phone' },
    { value: 'email', label: 'Email', icon: 'email' },
    { value: 'meeting', label: 'Meeting', icon: 'meeting' },
    { value: 'note', label: 'Note', icon: 'note' },
];

const resetForm = () => {
    form.value = {
        type: 'call',
        activity_date: new Date().toISOString().split('T')[0],
        description: '',
        outcome: '',
    };
    errors.value = {};
    notification.value = { type: null, message: '' };
};

const handleClose = () => {
    resetForm();
    emit('close');
};

const handleSubmit = async () => {
    if (!props.lead) return;

    saving.value = true;
    errors.value = {};
    notification.value = { type: null, message: '' };

    try {
        const payload = {
            lead_id: props.lead.id,
            type: form.value.type,
            activity_date: form.value.activity_date,
            description: form.value.description,
            outcome: form.value.outcome,
        };

        const response = await axios.post('/api/activities', payload);

        notification.value = {
            type: 'success',
            message: 'Activity added successfully!',
        };

        emit('activity-added', response.data);

        // Close modal after showing success
        setTimeout(() => {
            handleClose();
        }, 1500);
    } catch (err) {
        console.error('Error adding activity:', err);
        if (err.response?.status === 422) {
            errors.value = err.response.data.errors || {};
            const firstError = Object.values(err.response.data.errors)[0];
            notification.value = {
                type: 'error',
                message: Array.isArray(firstError) ? firstError[0] : 'Validation error occurred',
            };
        } else {
            notification.value = {
                type: 'error',
                message: err.response?.data?.message || 'Failed to add activity. Please try again.',
            };
        }
    } finally {
        saving.value = false;
    }
};

// Reset form when modal opens
watch(() => props.show, (isShowing) => {
    if (isShowing) {
        resetForm();
    }
});

// Get icon SVG path based on activity type
const getActivityIcon = (iconType) => {
    const icons = {
        phone: 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z',
        email: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        meeting: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
        note: 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
    };
    return icons[iconType] || icons.note;
};
</script>

<template>
    <Modal :show="show" max-width="lg" @close="handleClose">
        <div class="p-6">
            <!-- Header -->
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h2 class="font-heading text-2xl font-bold text-light-black">Add Activity</h2>
                    <p class="font-body text-sm text-zurit-gray mt-1">
                        Log an activity for {{ lead?.name || lead?.company || 'this lead' }}
                    </p>
                </div>
                <button @click="handleClose" class="text-zurit-gray hover:text-light-black transition-colors">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Success/Error Alert -->
            <Alert v-if="notification.type === 'success'" type="success" :message="notification.message"
                class="mb-4" />
            <Alert v-if="notification.type === 'error'" type="error" :message="notification.message" class="mb-4" />

            <!-- Form -->
            <form @submit.prevent="handleSubmit" class="space-y-5">
                <!-- Activity Type Selection -->
                <div>
                    <InputLabel value="Activity Type" class="mb-3" />
                    <div class="grid grid-cols-2 gap-3">
                        <button v-for="activityType in activityTypes" :key="activityType.value" type="button"
                            @click="form.type = activityType.value" :class="[
                                'flex items-center space-x-3 rounded-lg border-2 p-4 transition-all',
                                form.type === activityType.value
                                    ? 'border-zurit-purple bg-zurit-purple/5'
                                    : 'border-gray-200 hover:border-gray-300 bg-white'
                            ]">
                            <div :class="[
                                'flex-shrink-0',
                                form.type === activityType.value ? 'text-zurit-purple' : 'text-zurit-gray'
                            ]">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        :d="getActivityIcon(activityType.icon)" />
                                </svg>
                            </div>
                            <span :class="[
                                'font-body text-sm font-medium',
                                form.type === activityType.value ? 'text-light-black' : 'text-zurit-gray'
                            ]">
                                {{ activityType.label }}
                            </span>
                        </button>
                    </div>
                    <InputError v-if="errors.type" :message="errors.type[0]" class="mt-1" />
                </div>

                <!-- Activity Date -->
                <div>
                    <InputLabel for="activity_date" value="Activity Date" />
                    <TextInput id="activity_date" v-model="form.activity_date" type="date"
                        class="mt-1 block w-full" required />
                    <InputError v-if="errors.activity_date" :message="errors.activity_date[0]" class="mt-1" />
                </div>

                <!-- Description -->
                <div>
                    <InputLabel for="description" value="Description" />
                    <textarea id="description" v-model="form.description" rows="4"
                        class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 font-body text-sm text-light-black placeholder-zurit-gray shadow-sm transition-colors focus:border-zurit-purple focus:outline-none focus:ring-2 focus:ring-zurit-purple focus:ring-offset-0"
                        placeholder="What happened during this activity?"></textarea>
                    <InputError v-if="errors.description" :message="errors.description[0]" class="mt-1" />
                </div>

                <!-- Outcome -->
                <div>
                    <InputLabel for="outcome" value="Outcome (Optional)" />
                    <textarea id="outcome" v-model="form.outcome" rows="3"
                        class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 font-body text-sm text-light-black placeholder-zurit-gray shadow-sm transition-colors focus:border-zurit-purple focus:outline-none focus:ring-2 focus:ring-zurit-purple focus:ring-offset-0"
                        placeholder="What was the result or next steps?"></textarea>
                    <InputError v-if="errors.outcome" :message="errors.outcome[0]" class="mt-1" />
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" @click="handleClose"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 font-body text-sm font-medium text-light-black shadow-sm hover:bg-light-gray focus:outline-none focus:ring-2 focus:ring-zurit-purple focus:ring-offset-2"
                        :disabled="saving">
                        Cancel
                    </button>
                    <PrimaryButton :disabled="saving">
                        {{ saving ? 'Saving...' : 'Add Activity' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
