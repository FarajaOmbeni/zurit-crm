<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import LeadsPipelineHeader from '@/Components/LeadsPipelineHeader.vue';
import ClientList from '@/Components/ClientList.vue';
import ClientViewModal from '@/Components/ClientViewModal.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const clients = ref([]);
const total = ref(0);
const currentPage = ref(1);
const lastPage = ref(1);
const perPage = ref(15); // Default per page, will be updated from API response
const searchTerm = ref('');
const filterType = ref(null);
const loading = ref(false);
const showEmptyState = ref(false);
const showModal = ref(false);
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

const handleExport = () => {
    // Handle CSV export
    console.log('Export CSV clicked');
};

const handleAddLead = () => {
    // Handle add lead action
    console.log('Add Lead clicked');
};

const handleSearch = (term) => {
    searchTerm.value = term;
    // Reset to first page when searching
    currentPage.value = 1;
    fetchClients(term, 1);
};

const handleFilter = (type) => {
    filterType.value = type;
    // Reset to first page when filtering
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
                <LeadsPipelineHeader title="Client Database"
                    subtitle="Help clients build sustainable wealth through strategic financial guidance"
                    :avg-progress="75" :active-clients="8" :paused-clients="3" :completed="5" @export="handleExport"
                    @add-lead="handleAddLead" @search="handleSearch" @filter="handleFilter" />

                <ClientList :clients="clients" :total="total" :current-page="currentPage" :last-page="lastPage"
                    :per-page="perPage" :loading="loading" :show-empty-state="showEmptyState" @view="handleView"
                    @edit="handleEdit" @page-change="handlePageChange" />

                <!-- Client View Modal -->
                <ClientViewModal :show="showModal" :client="selectedClient" :initial-edit-mode="startInEditMode"
                    @close="closeModal" @edit="handleEdit" @updated="handleClientUpdated" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
