<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ClientsHeader from '@/Components/ClientsHeader.vue';
import ClientList from '@/Components/ClientList.vue';
import ClientViewModal from '@/Components/ClientViewModal.vue';
import CsvImportModal from '@/Components/CsvImportModal.vue';
import Alert from '@/Components/Alert.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const clients = ref([]);
const total = ref(0);
const currentPage = ref(1);
const lastPage = ref(1);
const perPage = ref(15); // Default per page, will be updated from API response
const searchTerm = ref('');
const filterType = ref(null);
const sortField = ref(null);
const sortOrder = ref(null);
const loading = ref(false);
const showEmptyState = ref(false);
const showModal = ref(false);
const showImportModal = ref(false);
const importing = ref(false);
const showSuccessAlert = ref(false);
const successMessage = ref('');
const selectedClient = ref(null);
const startInEditMode = ref(false);
let loadingTimeout = null;

const fetchClients = async (search = '', page = 1) => {
    loading.value = true;
    showEmptyState.value = false;

    // Clear any existing timeout
    if (loadingTimeout) {
        clearTimeout(loadingTimeout);
    }

    // Set timeout to show empty state after 5 seconds
    loadingTimeout = setTimeout(() => {
        if (clients.value.length === 0) {
            showEmptyState.value = true;
        }
    }, 5000);

    try {
        const params = {
            per_page: perPage.value,
            page: page,
        };

        // Only add search parameter if it's not empty
        if (search && search.trim()) {
            params.search = search.trim();
        }

        // Add sort parameters if sorting is active
        if (sortField.value && sortOrder.value) {
            params.sort_by = sortField.value;
            params.sort_order = sortOrder.value;
        }

        const response = await window.axios.get('/api/clients', { params });
        clients.value = response.data.data || [];
        total.value = response.data.total || 0;
        currentPage.value = response.data.current_page || 1;
        lastPage.value = response.data.last_page || 1;
        perPage.value = response.data.per_page || 15;

        // If we got data, clear the timeout and hide empty state
        if (loadingTimeout) {
            clearTimeout(loadingTimeout);
        }
        showEmptyState.value = false;
    } catch (error) {
        console.error('Error fetching clients:', error);
        clients.value = [];
        total.value = 0;
        currentPage.value = 1;
        lastPage.value = 1;

        // On error, show empty state immediately
        if (loadingTimeout) {
            clearTimeout(loadingTimeout);
        }
        showEmptyState.value = true;
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchClients();
});

const handleExport = async () => {
    try {
        // Call the export endpoint
        const response = await window.axios.get('/api/leads/export', {
            responseType: 'blob', // Important: handle binary data
        });

        // Create a blob from the response
        const blob = new Blob([response.data], { type: 'text/csv;charset=utf-8;' });
        
        // Create a download link
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        
        // Generate filename with timestamp (matching backend format: Y-m-d_His)
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const filename = `leads_export_${year}-${month}-${day}_${hours}${minutes}${seconds}.csv`;
        
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        
        // Trigger download
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Clean up the URL object
        URL.revokeObjectURL(url);
    } catch (error) {
        console.error('Error exporting leads:', error);
        alert('Failed to export leads. Please try again.');
    }
};

const handleAddLead = () => {
    // Show import modal
    showImportModal.value = true;
};

const handleImportConfirm = async (importData) => {
    importing.value = true;
    showSuccessAlert.value = false;

    try {
        // Parse CSV content
        const lines = importData.content.split('\n').filter(line => line.trim() !== '');
        if (lines.length < 2) {
            alert('CSV file must have at least a header row and one data row.');
            importing.value = false;
            return;
        }

        // Parse CSV lines
        const parseCSVLine = (line) => {
            const result = [];
            let current = '';
            let inQuotes = false;

            for (let i = 0; i < line.length; i++) {
                const char = line[i];
                const nextChar = line[i + 1];

                if (char === '"') {
                    if (inQuotes && nextChar === '"') {
                        current += '"';
                        i++;
                    } else {
                        inQuotes = !inQuotes;
                    }
                } else if (char === ',' && !inQuotes) {
                    result.push(current.trim());
                    current = '';
                } else {
                    current += char;
                }
            }
            result.push(current.trim());
            return result;
        };

        // Get headers
        const headers = parseCSVLine(lines[0]).map(h => h.replace(/^"|"$/g, '').trim());
        
        // Map headers to expected field names (case-insensitive)
        const fieldMapping = {
            'name': 'name',
            'position': 'position',
            'company': 'company',
            'email': 'email',
            'phone': 'phone',
            'city': 'city',
            'country': 'country',
            'source': 'source',
            'sector': 'sector',
        };

        // Normalize header names
        const normalizedHeaders = headers.map(h => {
            const lower = h.toLowerCase().trim();
            return fieldMapping[lower] || h;
        });

        // Parse data rows
        const leads = [];
        for (let i = 1; i < lines.length; i++) {
            const values = parseCSVLine(lines[i]);
            const lead = {};
            
            normalizedHeaders.forEach((field, index) => {
                if (fieldMapping[field]) {
                    lead[field] = values[index] ? values[index].replace(/^"|"$/g, '').trim() : '';
                }
            });

            // Only add if company is present (required field)
            if (lead.company) {
                leads.push(lead);
            }
        }

        if (leads.length === 0) {
            alert('No valid leads found in the CSV file. Please ensure the file has data rows with at least a company name.');
            importing.value = false;
            return;
        }

        // Send to backend
        const response = await window.axios.post('/api/leads/import', {
            leads: leads,
        });

        // Close modal and refresh client list
        showImportModal.value = false;
        importing.value = false;
        fetchClients(searchTerm.value, 1);
        
        // Show success alert with import details
        let message = `Successfully imported ${response.data.imported} lead(s).`;
        if (response.data.skipped > 0) {
            message += ` ${response.data.skipped} duplicate(s) skipped.`;
        }
        if (response.data.errors && response.data.errors.length > 0) {
            message += ` ${response.data.errors.length} error(s) occurred.`;
        }
        successMessage.value = message;
        showSuccessAlert.value = true;

        // Auto-hide alert after 5 seconds
        setTimeout(() => {
            showSuccessAlert.value = false;
        }, 5000);
    } catch (error) {
        console.error('Error importing leads:', error);
        importing.value = false;
        const errorMessage = error.response?.data?.message || 'Failed to import leads. Please try again.';
        alert(errorMessage);
    }
};

const handleImportClose = () => {
    showImportModal.value = false;
};

const handleSearch = (term) => {
    searchTerm.value = term;
    // Reset to first page when searching
    currentPage.value = 1;
    // Reset sort when searching
    sortField.value = null;
    sortOrder.value = null;
    fetchClients(term, 1);
};

const handleFilter = (type) => {
    filterType.value = type;
    // Reset to first page when filtering
    currentPage.value = 1;
    fetchClients(searchTerm.value, 1);
};

const handleSort = ({ field, order }) => {
    if (order === null) {
        // Reset to default (no sort)
        sortField.value = null;
        sortOrder.value = null;
    } else {
        sortField.value = field;
        sortOrder.value = order;
    }
    // Reset to first page when sorting
    currentPage.value = 1;
    fetchClients(searchTerm.value, 1);
};

const handlePageChange = (page) => {
    currentPage.value = page;
    fetchClients(searchTerm.value, page);
};

const handleView = async (client) => {
    // Fetch full client details
    try {
        const response = await window.axios.get(`/api/clients/${client.id}`);
        selectedClient.value = response.data;
        startInEditMode.value = false;
        showModal.value = true;
    } catch (error) {
        console.error('Error fetching client details:', error);
        // Fallback to basic client data
        selectedClient.value = client;
        startInEditMode.value = false;
        showModal.value = true;
    }
};

const handleEdit = async (client) => {
    // Fetch full client details and open in edit mode
    try {
        const response = await window.axios.get(`/api/clients/${client.id}`);
        selectedClient.value = response.data;
        startInEditMode.value = true;
        showModal.value = true;
    } catch (error) {
        console.error('Error fetching client details:', error);
        // Fallback to basic client data
        selectedClient.value = client;
        startInEditMode.value = true;
        showModal.value = true;
    }
};

const handleClientUpdated = (updatedClient) => {
    // Update the client in the list
    const index = clients.value.findIndex(c => c.id === updatedClient.id);
    if (index !== -1) {
        // Merge updated data with existing client data
        clients.value[index] = { ...clients.value[index], ...updatedClient };
    }
    // Update selected client to reflect changes
    selectedClient.value = { ...selectedClient.value, ...updatedClient };
};

const closeModal = () => {
    showModal.value = false;
    selectedClient.value = null;
    startInEditMode.value = false;
};
</script>

<template>

    <Head title="Client Database" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <ClientsHeader title="Client Database"
                    subtitle="Help clients build sustainable wealth through strategic financial guidance"
                    :avg-progress="75" :active-clients="8" :paused-clients="3" :completed="5"
                    :company-sort-order="sortOrder" @export="handleExport" @add-lead="handleAddLead"
                    @search="handleSearch" @filter="handleFilter" @sort="handleSort" />

                <ClientList :clients="clients" :total="total" :current-page="currentPage" :last-page="lastPage"
                    :per-page="perPage" :loading="loading" :show-empty-state="showEmptyState" @view="handleView"
                    @edit="handleEdit" @page-change="handlePageChange" />

                <!-- Client View Modal -->
                <ClientViewModal :show="showModal" :client="selectedClient" :initial-edit-mode="startInEditMode"
                    @close="closeModal" @edit="handleEdit" @updated="handleClientUpdated" />

                <!-- CSV Import Modal -->
                <CsvImportModal :show="showImportModal" :importing="importing" @close="handleImportClose" @confirm="handleImportConfirm" />

                <!-- Success Alert -->
                <div class="fixed top-4 right-4 z-50 max-w-md">
                    <Alert v-if="showSuccessAlert" type="success" :message="successMessage" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
