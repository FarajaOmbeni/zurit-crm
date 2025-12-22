<script setup>
import { ref, watch, onMounted } from 'vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Alert from '@/Components/Alert.vue';
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

const emit = defineEmits(['close', 'reassigned']);

const selectedUserId = ref('');
const users = ref([]);
const loading = ref(false);
const saving = ref(false);
const error = ref('');
const success = ref('');

const fetchAssignableUsers = async () => {
    loading.value = true;
    error.value = '';
    try {
        const response = await axios.get('/api/users/assignable');
        users.value = response.data.users;
    } catch (err) {
        console.error('Error fetching assignable users:', err);
        error.value = 'Failed to load users. Please try again.';
    } finally {
        loading.value = false;
    }
};

const handleSubmit = async () => {
    if (!selectedUserId.value) {
        error.value = 'Please select a user to reassign to.';
        return;
    }

    if (selectedUserId.value == props.lead?.added_by) {
        error.value = 'Lead is already assigned to this user.';
        return;
    }

    saving.value = true;
    error.value = '';
    success.value = '';

    try {
        const response = await axios.patch(`/api/leads/${props.lead.id}/reassign`, {
            new_user_id: parseInt(selectedUserId.value),
        });

        success.value = 'Lead reassigned successfully!';
        emit('reassigned', response.data);

        // Close modal after showing success
        setTimeout(() => {
            handleClose();
        }, 1500);
    } catch (err) {
        console.error('Error reassigning lead:', err);
        if (err.response?.data?.errors?.new_user_id) {
            error.value = err.response.data.errors.new_user_id[0];
        } else {
            error.value = err.response?.data?.message || 'Failed to reassign lead. Please try again.';
        }
    } finally {
        saving.value = false;
    }
};

const handleClose = () => {
    selectedUserId.value = '';
    error.value = '';
    success.value = '';
    emit('close');
};

// Fetch users when modal opens
watch(() => props.show, (isShowing) => {
    if (isShowing) {
        fetchAssignableUsers();
        // Pre-select current assignee
        if (props.lead?.added_by) {
            selectedUserId.value = props.lead.added_by.toString();
        }
    } else {
        selectedUserId.value = '';
        error.value = '';
        success.value = '';
    }
});

const currentAssigneeName = () => {
    return props.lead?.added_by_name || props.lead?.addedBy?.name || props.lead?.added_by?.name || 'Unknown';
};
</script>

<template>
    <Modal :show="show" max-width="md" @close="handleClose">
        <div class="p-6">
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-gray-200 pb-4">
                <h2 class="font-heading text-xl font-bold text-light-black">Reassign Lead</h2>
                <button @click="handleClose"
                    class="rounded-lg p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="py-6">
                <!-- Lead info -->
                <div class="mb-4 rounded-lg bg-gray-50 p-4">
                    <p class="text-sm text-gray-600">Lead</p>
                    <p class="font-medium text-light-black">{{ lead?.company || 'Unknown Company' }}</p>
                    <p class="mt-1 text-sm text-gray-500">
                        Currently assigned to: <span class="font-medium">{{ currentAssigneeName() }}</span>
                    </p>
                </div>

                <!-- Error message -->
                <Alert v-if="error" type="error" :message="error" class="mb-4" />

                <!-- Success message -->
                <Alert v-if="success" type="success" :message="success" class="mb-4" />

                <!-- User selection -->
                <div>
                    <label for="new_user" class="mb-2 block font-body text-sm font-medium text-light-black">
                        Reassign to <span class="text-red-500">*</span>
                    </label>

                    <div v-if="loading" class="flex items-center gap-2 py-2 text-gray-500">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Loading users...
                    </div>

                    <div v-else-if="users.length === 0" class="py-2 text-gray-500">
                        No users available for reassignment.
                    </div>

                    <div v-else class="relative">
                        <select id="new_user" v-model="selectedUserId"
                            class="block w-full appearance-none rounded-lg border border-gray-300 bg-white px-3 py-2 pr-10 font-body text-sm text-light-black focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple"
                            :disabled="saving">
                            <option value="">Select a team member</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">
                                {{ user.name }} ({{ user.email }})
                            </option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-4">
                <button type="button" @click="handleClose"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 font-body text-sm font-medium text-light-black transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-zurit-purple focus:ring-offset-2"
                    :disabled="saving">
                    Cancel
                </button>
                <PrimaryButton @click="handleSubmit" :disabled="saving || loading || !selectedUserId"
                    class="inline-flex items-center gap-2">
                    <span v-if="saving" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Reassigning...
                    </span>
                    <span v-else>Reassign Lead</span>
                </PrimaryButton>
            </div>
        </div>
    </Modal>
</template>
