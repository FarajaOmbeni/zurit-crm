<script setup>
import { ref, watch, computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
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
    productId: {
        type: Number,
        required: true,
    },
});

const emit = defineEmits(['close', 'note-added']);

const notes = ref('');
const newNote = ref('');
const loading = ref(false);
const saving = ref(false);
const error = ref(null);

// Parse notes string into array of note objects
const parsedNotes = computed(() => {
    if (!notes.value || !notes.value.trim()) {
        return [];
    }

    // Split by timestamp separator pattern: "--- YYYY-MM-DD HH:mm:ss ---"
    const parts = notes.value.split(/\n\n--- \d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} ---\n/);

    // If no timestamps found, return single note
    if (parts.length === 1) {
        return [{
            text: parts[0].trim(),
            timestamp: null,
        }];
    }

    // Extract timestamps and match with notes
    const timestampPattern = /--- (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}) ---/g;
    const timestamps = [];
    let match;
    while ((match = timestampPattern.exec(notes.value)) !== null) {
        timestamps.push(match[1]);
    }

    // Combine parts with timestamps
    const result = [];
    for (let i = 0; i < parts.length; i++) {
        if (parts[i].trim()) {
            result.push({
                text: parts[i].trim(),
                timestamp: i > 0 ? timestamps[i - 1] : null,
            });
        }
    }

    // Reverse to show newest first
    return result.reverse();
});

const formatTimestamp = (timestamp) => {
    if (!timestamp) return '';
    try {
        const date = new Date(timestamp.replace(' ', 'T'));
        return date.toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    } catch (e) {
        return timestamp;
    }
};

const fetchNotes = async () => {
    if (!props.lead || !props.productId) {
        return;
    }

    loading.value = true;
    error.value = null;
    try {
        const response = await axios.get(
            `/api/leads/${props.lead.id}/products/${props.productId}/notes`
        );
        notes.value = response.data.notes || '';
    } catch (err) {
        console.error('Error fetching notes:', err);
        error.value = err.response?.data?.message || 'Failed to load notes';
        notes.value = '';
    } finally {
        loading.value = false;
    }
};

const addNote = async () => {
    if (!newNote.value.trim() || !props.lead || !props.productId) {
        return;
    }

    saving.value = true;
    error.value = null;
    try {
        const response = await axios.post(
            `/api/leads/${props.lead.id}/products/${props.productId}/notes`,
            {
                note: newNote.value.trim(),
            }
        );

        // Update notes with the response
        notes.value = response.data.notes || '';
        newNote.value = '';

        // Emit event to parent
        emit('note-added', {
            leadId: props.lead.id,
            productId: props.productId,
            notes: notes.value,
        });
    } catch (err) {
        console.error('Error adding note:', err);
        error.value = err.response?.data?.message || 'Failed to add note';
    } finally {
        saving.value = false;
    }
};

// Watch for modal show/hide and lead changes
watch(() => props.show, (isShowing) => {
    if (isShowing && props.lead && props.productId) {
        fetchNotes();
    } else {
        // Reset when modal closes
        notes.value = '';
        newNote.value = '';
        error.value = null;
    }
});

watch(() => [props.lead, props.productId], () => {
    if (props.show && props.lead && props.productId) {
        fetchNotes();
    }
});
</script>

<template>
    <Modal :show="show" max-width="2xl" @close="emit('close')">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between border-b border-gray-200 pb-4">
                <div>
                    <h2 class="font-heading text-2xl font-bold text-light-black">Notes</h2>
                    <p v-if="lead" class="mt-1 font-body text-sm text-zurit-gray">
                        {{ lead.name }} - {{ lead.company }}
                    </p>
                </div>
                <button @click="emit('close')"
                    class="rounded-lg p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Error Message -->
            <div v-if="error" class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="font-body text-sm text-red-800">{{ error }}</p>
                </div>
            </div>

            <!-- Notes List -->
            <div class="mb-6">
                <div v-if="loading" class="flex items-center justify-center py-12">
                    <div class="flex items-center gap-2 text-gray-500">
                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span class="font-body text-sm">Loading notes...</span>
                    </div>
                </div>

                <div v-else-if="parsedNotes.length === 0" class="flex items-center justify-center py-12">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="mt-4 font-body text-sm text-gray-500">No notes yet</p>
                        <p class="mt-1 font-body text-xs text-gray-400">Add your first note below</p>
                    </div>
                </div>

                <div v-else class="space-y-4 max-h-96 overflow-y-auto">
                    <div v-for="(note, index) in parsedNotes" :key="index"
                        class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <div v-if="note.timestamp" class="mb-2 flex items-center gap-2">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="font-body text-xs text-gray-500">
                                {{ formatTimestamp(note.timestamp) }}
                            </span>
                        </div>
                        <p class="font-body text-sm text-light-black whitespace-pre-wrap">{{ note.text }}</p>
                    </div>
                </div>
            </div>

            <!-- Add Note Form -->
            <div class="border-t border-gray-200 pt-6">
                <label for="new-note" class="mb-2 block font-body text-sm font-medium text-light-black">
                    Add Note
                </label>
                <textarea id="new-note" v-model="newNote" rows="4" placeholder="Write your note here..."
                    class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 font-body text-sm text-light-black placeholder-zurit-gray focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple"
                    :disabled="saving"></textarea>
                <div class="mt-4 flex items-center justify-end gap-3">
                    <button @click="emit('close')"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 font-body text-sm font-medium text-light-black transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-zurit-purple focus:ring-offset-2"
                        :disabled="saving">
                        Cancel
                    </button>
                    <PrimaryButton @click="addNote" :disabled="!newNote.trim() || saving" class="min-w-[100px]">
                        <span v-if="saving" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Saving...
                        </span>
                        <span v-else>Add Note</span>
                    </PrimaryButton>
                </div>
            </div>
        </div>
    </Modal>
</template>
