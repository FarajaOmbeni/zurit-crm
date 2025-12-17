<script setup>
import { ref, computed } from 'vue';
import Modal from './Modal.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    importing: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'confirm']);

const fileInput = ref(null);
const selectedFile = ref(null);
const previewData = ref([]);
const headers = ref([]);
const error = ref(null);

const hasPreview = computed(() => previewData.value.length > 0);

const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    // Validate file type
    if (!file.name.endsWith('.csv')) {
        error.value = 'Please select a CSV file.';
        return;
    }

    error.value = null;
    selectedFile.value = file;
    previewData.value = [];
    headers.value = [];

    // Read and parse CSV
    const reader = new FileReader();
    reader.onload = (e) => {
        try {
            const text = e.target.result;
            const lines = text.split('\n').filter(line => line.trim() !== '');
            
            if (lines.length === 0) {
                error.value = 'The CSV file is empty.';
                return;
            }

            // Parse CSV (handling quoted fields)
            const parseCSVLine = (line) => {
                const result = [];
                let current = '';
                let inQuotes = false;

                for (let i = 0; i < line.length; i++) {
                    const char = line[i];
                    const nextChar = line[i + 1];

                    if (char === '"') {
                        if (inQuotes && nextChar === '"') {
                            // Escaped quote
                            current += '"';
                            i++; // Skip next quote
                        } else {
                            // Toggle quote state
                            inQuotes = !inQuotes;
                        }
                    } else if (char === ',' && !inQuotes) {
                        // End of field
                        result.push(current.trim());
                        current = '';
                    } else {
                        current += char;
                    }
                }
                result.push(current.trim()); // Add last field
                return result;
            };

            // Get headers from first line
            headers.value = parseCSVLine(lines[0]).map(h => h.replace(/^"|"$/g, ''));

            // Parse data rows (show first 5 rows)
            const dataRows = [];
            for (let i = 1; i < Math.min(lines.length, 6); i++) {
                const values = parseCSVLine(lines[i]);
                const row = {};
                headers.value.forEach((header, index) => {
                    row[header] = values[index] ? values[index].replace(/^"|"$/g, '') : '';
                });
                dataRows.push(row);
            }

            previewData.value = dataRows;
        } catch (err) {
            error.value = 'Error parsing CSV file: ' + err.message;
            console.error('CSV parsing error:', err);
        }
    };

    reader.onerror = () => {
        error.value = 'Error reading file.';
    };

    reader.readAsText(file);
};

const handleConfirm = () => {
    if (!selectedFile.value || previewData.value.length === 0) {
        error.value = 'Please select a valid CSV file.';
        return;
    }

    // Read full file content
    const reader = new FileReader();
    reader.onload = (e) => {
        emit('confirm', {
            file: selectedFile.value,
            content: e.target.result,
            headers: headers.value,
        });
    };
    reader.readAsText(selectedFile.value);
};

const handleClose = () => {
    // Reset state
    selectedFile.value = null;
    previewData.value = [];
    headers.value = [];
    error.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
    emit('close');
};

const triggerFileInput = () => {
    fileInput.value?.click();
};
</script>

<template>
    <Modal :show="show" max-width="2xl" @close="handleClose">
        <div class="p-6">
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-6">
                <h2 class="font-heading text-2xl font-bold text-light-black">Import Leads from CSV</h2>
                <button @click="handleClose"
                    class="rounded-lg p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Error Message -->
            <div v-if="error" class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4">
                <p class="font-body text-sm text-red-800">{{ error }}</p>
            </div>

            <!-- File Upload Section -->
            <div class="mb-6">
                <label class="block font-body text-sm font-medium text-light-black mb-2">
                    Select CSV File
                </label>
                <input
                    ref="fileInput"
                    type="file"
                    accept=".csv"
                    @change="handleFileSelect"
                    class="hidden"
                />
                <button
                    @click="triggerFileInput"
                    type="button"
                    class="w-full rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 px-6 py-8 text-center transition-colors hover:border-zurit-purple hover:bg-gray-100"
                >
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <p class="font-body text-sm text-gray-600">
                        <span class="font-medium text-zurit-purple">Click to upload</span> or drag and drop
                    </p>
                    <p class="font-body text-xs text-gray-500 mt-1">CSV file only</p>
                    <p v-if="selectedFile" class="font-body text-sm text-gray-700 mt-2 font-medium">
                        Selected: {{ selectedFile.name }}
                    </p>
                </button>
            </div>

            <!-- Preview Section -->
            <div v-if="hasPreview" class="mb-6">
                <h3 class="font-body text-sm font-medium text-light-black mb-3">
                    Preview (First 5 rows)
                </h3>
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    v-for="header in headers"
                                    :key="header"
                                    class="px-4 py-3 text-left font-body text-xs font-medium uppercase tracking-wider text-gray-700"
                                >
                                    {{ header }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(row, index) in previewData" :key="index">
                                <td
                                    v-for="header in headers"
                                    :key="header"
                                    class="px-4 py-3 font-body text-sm text-gray-900 whitespace-nowrap"
                                >
                                    {{ row[header] || '-' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                <button
                    @click="handleClose"
                    type="button"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 font-body text-sm font-medium text-light-black transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-zurit-purple focus:ring-offset-2"
                >
                    Cancel
                </button>
                <button
                    @click="handleConfirm"
                    :disabled="!hasPreview || importing"
                    type="button"
                    :class="[
                        'inline-flex items-center justify-center rounded-lg px-4 py-2 font-body text-sm font-medium text-white transition-colors focus:outline-none focus:ring-2 focus:ring-zurit-purple focus:ring-offset-2',
                        hasPreview && !importing
                            ? 'bg-zurit-purple hover:bg-zurit-purple/90'
                            : 'bg-gray-400 cursor-not-allowed'
                    ]"
                >
                    <svg v-if="importing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>{{ importing ? 'Importing...' : 'Confirm Import' }}</span>
                </button>
            </div>
        </div>
    </Modal>
</template>
