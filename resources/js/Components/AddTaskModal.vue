<script setup>
import { ref, watch, computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Alert from '@/Components/Alert.vue';
import axios from 'axios';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    leads: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['close', 'task-added']);

// Form fields
const form = ref({
    title: '',
    description: '',
    type: 'other',
    priority: 'medium',
    due_date: '',
    lead_id: null,
});

// Internal leads list (used if not provided via props)
const internalLeads = ref([]);
const loadingLeads = ref(false);
const errors = ref({});
const saving = ref(false);
const notification = ref({ type: null, message: '' });

// Computed leads list: use props.leads if provided, otherwise use internal list
const availableLeads = computed(() => {
    return props.leads.length > 0 ? props.leads : internalLeads.value;
});

// Fetch leads if not provided via props
const fetchLeads = async () => {
    if (props.leads.length > 0) {
        return; // Don't fetch if leads are provided via props
    }

    loadingLeads.value = true;
    try {
        const response = await axios.get('/api/leads', {
            params: {
                per_page: 1000,
                include_clients: true // Include both leads and clients
            }
        });
        internalLeads.value = response.data.data || [];
    } catch (error) {
        console.error('Error fetching leads:', error);
        internalLeads.value = [];
    } finally {
        loadingLeads.value = false;
    }
};

// Get lead display name helper
const getLeadDisplayName = (lead) => {
    if (!lead) return '';
    const name = lead.name || '';
    const company = lead.company || '';
    if (name && company) {
        return `${name} - ${company}`;
    }
    return name || company || 'Unknown';
};

const resetForm = () => {
    form.value = {
        title: '',
        description: '',
        type: 'other',
        priority: 'medium',
        due_date: '',
        lead_id: null,
    };
    errors.value = {};
    notification.value = { type: null, message: '' };
};

const handleClose = () => {
    resetForm();
    emit('close');
};

const handleSubmit = async () => {
    errors.value = {};
    saving.value = true;
    notification.value = { type: null, message: '' };

    try {
        const response = await axios.post('/api/tasks', form.value);

        notification.value = {
            type: 'success',
            message: 'Task created successfully!',
        };

        emit('task-added', response.data);

        // Close modal after showing success
        setTimeout(() => {
            handleClose();
        }, 1500);
    } catch (error) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        }

        notification.value = {
            type: 'error',
            message: error.response?.data?.message || 'Failed to create task. Please try again.',
        };
        console.error('Error creating task:', error);
    } finally {
        saving.value = false;
    }
};

// Watch for modal open to fetch leads if needed
watch(() => props.show, (isShowing) => {
    if (isShowing) {
        resetForm();
        fetchLeads();
    }
});
</script>

<template>
    <Modal :show="show" max-width="lg" @close="handleClose">
        <div class="p-6">
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-6">
                <h3 class="font-heading text-2xl font-semibold text-light-black">Create New Task</h3>
                <button
                    type="button"
                    @click="handleClose"
                    class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Success/Error Alert -->
            <Alert v-if="notification.type === 'success'" type="success" :message="notification.message" class="mb-4" />
            <Alert v-if="notification.type === 'error'" type="error" :message="notification.message" class="mb-4" />

            <!-- Form -->
            <form @submit.prevent="handleSubmit" class="space-y-5">
                <!-- Task Title -->
                <div>
                    <label class="block font-body text-sm font-medium text-light-black mb-2">
                        Task Title <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.title"
                        type="text"
                        placeholder="What needs to be done?"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 font-body text-sm text-light-black placeholder-gray-400 focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple"
                        :class="{ 'border-red-500': errors.title }"
                    />
                    <p v-if="errors.title" class="mt-1 text-sm text-red-500">{{ errors.title[0] }}</p>
                </div>

                <!-- Description -->
                <div>
                    <label class="block font-body text-sm font-medium text-light-black mb-2">Description</label>
                    <textarea
                        v-model="form.description"
                        rows="4"
                        placeholder="Provide more details about this task..."
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 font-body text-sm text-light-black placeholder-gray-400 focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple resize-none"
                    ></textarea>
                </div>

                <!-- Lead/Client Selection -->
                <div>
                    <label class="block font-body text-sm font-medium text-light-black mb-2">
                        Lead/Client
                        <span class="text-xs text-zurit-gray font-normal ml-1">(Optional - leave empty for internal tasks)</span>
                    </label>
                    <select
                        v-model="form.lead_id"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 font-body text-sm text-light-black focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple"
                        :disabled="loadingLeads"
                    >
                        <option :value="null">Select a lead/client (or leave as internal task)</option>
                        <option
                            v-for="lead in availableLeads"
                            :key="lead.id"
                            :value="lead.id"
                        >
                            {{ getLeadDisplayName(lead) }}
                        </option>
                    </select>
                    <p v-if="errors.lead_id" class="mt-1 text-sm text-red-500">{{ errors.lead_id[0] }}</p>
                </div>

                <!-- Due Date -->
                <div>
                    <label class="block font-body text-sm font-medium text-light-black mb-2">
                        Due Date <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input
                            v-model="form.due_date"
                            type="date"
                            placeholder="dd/mm/yyyy"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 pr-10 font-body text-sm text-light-black placeholder-gray-400 focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple"
                            :class="{ 'border-red-500': errors.due_date }"
                        />
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                    <p v-if="errors.due_date" class="mt-1 text-sm text-red-500">{{ errors.due_date[0] }}</p>
                </div>

                <!-- Priority -->
                <div>
                    <label class="block font-body text-sm font-medium text-light-black mb-2">Priority</label>
                    <select
                        v-model="form.priority"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 font-body text-sm text-light-black focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple"
                    >
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                    <p v-if="errors.priority" class="mt-1 text-sm text-red-500">{{ errors.priority[0] }}</p>
                </div>

                <!-- Type -->
                <div>
                    <label class="block font-body text-sm font-medium text-light-black mb-2">Type</label>
                    <select
                        v-model="form.type"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 font-body text-sm text-light-black focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple"
                    >
                        <option value="call">Call</option>
                        <option value="email">Email</option>
                        <option value="meeting">Meeting</option>
                        <option value="follow_up">Follow Up</option>
                        <option value="other">Other</option>
                    </select>
                    <p v-if="errors.type" class="mt-1 text-sm text-red-500">{{ errors.type[0] }}</p>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-3 pt-4">
                    <button
                        type="submit"
                        :disabled="saving"
                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-zurit-purple px-4 py-3 font-body text-sm font-medium text-white hover:bg-zurit-purple/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg v-if="!saving" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span v-if="saving" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Creating...
                        </span>
                        <span v-else>Add Task</span>
                    </button>
                    <button
                        type="button"
                        @click="handleClose"
                        class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-3 font-body text-sm font-medium text-light-black hover:bg-gray-50 transition-colors"
                        :disabled="saving"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>
