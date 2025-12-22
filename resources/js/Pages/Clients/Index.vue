<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ClientsHeader from '@/Components/ClientsHeader.vue';
import ClientList from '@/Components/ClientList.vue';
import ClientViewModal from '@/Components/ClientViewModal.vue';
import CsvImportModal from '@/Components/CsvImportModal.vue';
import AddLeadModal from '@/Components/AddLeadModal.vue';
import LeadTypeSelector from '@/Components/LeadTypeSelector.vue';
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
const showAddLeadModal = ref(false);
const showTypeSelector = ref(false);
const selectedContactType = ref('company');
const pendingAction = ref(null); // 'add' or 'import'
const importing = ref(false);
const showSuccessAlert = ref(false);
const successMessage = ref('');
const importDuplicates = ref([]);
const selectedClient = ref(null);
const startInEditMode = ref(false);
let loadingTimeout = null;

// Stats for the header (specific to logged-in user)
const stats = ref({
    avgProgress: 0,
    activeClients: 0,
    totalLeads: 0,
    completed: 0,
});

const fetchStats = async () => {
    try {
        const response = await window.axios.get('/api/clients/stats');
        stats.value = response.data;
    } catch (error) {
        console.error('Error fetching stats:', error);
    }
};

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
    fetchStats();
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
    // Show type selector first
    pendingAction.value = 'add';
    showTypeSelector.value = true;
};

const handleImport = () => {
    // Show type selector first
    pendingAction.value = 'import';
    showTypeSelector.value = true;
};

const handleTypeSelected = (type) => {
    selectedContactType.value = type;
    showTypeSelector.value = false;

    // Proceed based on pending action
    if (pendingAction.value === 'add') {
        showAddLeadModal.value = true;
    } else if (pendingAction.value === 'import') {
        showImportModal.value = true;
    }
    pendingAction.value = null;
};

const handleTypeSelectorClose = () => {
    showTypeSelector.value = false;
    pendingAction.value = null;
};

const handleLeadAdded = () => {
    showAddLeadModal.value = false;
    fetchClients(searchTerm.value, 1);
    fetchStats();
};

const handleAddLeadClose = () => {
    showAddLeadModal.value = false;
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

            // Check required field based on contact type
            const contactType = importData.contactType || selectedContactType.value;
            const isPersonal = contactType === 'personal';
            const requiredField = isPersonal ? lead.name : lead.company;

            if (requiredField) {
                leads.push(lead);
            }
        }

        const contactType = importData.contactType || selectedContactType.value;
        const isPersonal = contactType === 'personal';
        const requiredFieldLabel = isPersonal ? 'name' : 'company name';

        if (leads.length === 0) {
            alert(`No valid leads found in the CSV file. Please ensure the file has data rows with at least a ${requiredFieldLabel}.`);
            importing.value = false;
            return;
        }

        // Send to backend with contact type
        const response = await window.axios.post('/api/leads/import', {
            leads: leads,
            contact_type: contactType,
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
        importDuplicates.value = response.data.duplicates || [];
        showSuccessAlert.value = true;

        // Auto-hide alert after 10 seconds (longer if there are duplicates to read)
        const hideDelay = importDuplicates.value.length > 0 ? 15000 : 5000;
        setTimeout(() => {
            showSuccessAlert.value = false;
            importDuplicates.value = [];
        }, hideDelay);
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
                    :avg-progress="stats.avgProgress" :active-clients="stats.activeClients" :total-leads="stats.totalLeads" :completed="stats.completed"
                    :company-sort-order="sortOrder" @export="handleExport" @add-lead="handleAddLead" @import="handleImport"
                    @search="handleSearch" @filter="handleFilter" @sort="handleSort" />

                <ClientList :clients="clients" :total="total" :current-page="currentPage" :last-page="lastPage"
                    :per-page="perPage" :loading="loading" :show-empty-state="showEmptyState" @view="handleView"
                    @edit="handleEdit" @page-change="handlePageChange" />

                <!-- Client View Modal -->
                <ClientViewModal :show="showModal" :client="selectedClient" :initial-edit-mode="startInEditMode"
                    @close="closeModal" @edit="handleEdit" @updated="handleClientUpdated" />

                <!-- Lead Type Selector Modal -->
                <LeadTypeSelector
                    :show="showTypeSelector"
                    :title="pendingAction === 'import' ? 'What type of leads are you importing?' : 'What type of lead would you like to add?'"
                    @close="handleTypeSelectorClose"
                    @select="handleTypeSelected"
                />

                <!-- Add Lead Modal -->
                <AddLeadModal
                    :show="showAddLeadModal"
                    :contact-type="selectedContactType"
                    @close="handleAddLeadClose"
                    @lead-added="handleLeadAdded"
                />

                <!-- CSV Import Modal -->
                <CsvImportModal
                    :show="showImportModal"
                    :importing="importing"
                    :contact-type="selectedContactType"
                    @close="handleImportClose"
                    @confirm="handleImportConfirm"
                />

                <!-- Import Results Alert -->
                <div v-if="showSuccessAlert" class="fixed top-4 right-4 z-50 max-w-lg">
                    <div class="rounded-lg border bg-white shadow-lg overflow-hidden">
                        <!-- Success Header -->
                        <div class="bg-green-50 border-b border-green-200 px-4 py-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="font-body text-sm font-medium text-green-800">{{ successMessage }}</p>
                                </div>
                                <button @click="showSuccessAlert = false; importDuplicates = [];" class="text-green-600 hover:text-green-800">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Duplicates List -->
                        <div v-if="importDuplicates.length > 0" class="max-h-64 overflow-y-auto">
                            <div class="px-4 py-2 bg-amber-50 border-b border-amber-200">
                                <p class="font-body text-xs font-medium text-amber-800">Skipped duplicates:</p>
                            </div>
                            <ul class="divide-y divide-gray-100">
                                <li v-for="(dup, index) in importDuplicates" :key="index" class="px-4 py-2 hover:bg-gray-50">
                                    <p class="font-body text-sm text-gray-900 font-medium">{{ dup.company }}</p>
                                    <p class="font-body text-xs text-gray-600">
                                        Row {{ dup.row }}: {{ dup.reason }}
                                    </p>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
