<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import LeadsPipelineHeader from '@/Components/LeadsPipelineHeader.vue';
import ClientList from '@/Components/ClientList.vue';
import ClientViewModal from '@/Components/ClientViewModal.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const clients = ref([]);
const total = ref(0);
const searchTerm = ref('');
const filterType = ref(null);
const loading = ref(false);
const showModal = ref(false);
const selectedClient = ref(null);

const fetchClients = async (search = '') => {
    loading.value = true;
    try {
        const params = {
            per_page: 50,
        };

        // Only add search parameter if it's not empty
        if (search && search.trim()) {
            params.search = search.trim();
        }

        const response = await window.axios.get('/api/clients', { params });
        clients.value = response.data.data || [];
        total.value = response.data.total || 0;
    } catch (error) {
        console.error('Error fetching clients:', error);
        clients.value = [];
        total.value = 0;
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
    // Fetch clients with search term (empty string will fetch all)
    fetchClients(term);
};

const handleFilter = (type) => {
    filterType.value = type;
    // Implement filter logic if needed
    fetchClients(searchTerm.value);
};

const handleView = async (client) => {
    // Fetch full client details
    try {
        const response = await window.axios.get(`/api/clients/${client.id}`);
        selectedClient.value = response.data;
        showModal.value = true;
    } catch (error) {
        console.error('Error fetching client details:', error);
        // Fallback to basic client data
        selectedClient.value = client;
        showModal.value = true;
    }
};

const handleEdit = (client) => {
    // Handle edit action
    console.log('Edit client:', client);
    showModal.value = false;
    // You can navigate to edit page or open edit modal here
};

const closeModal = () => {
    showModal.value = false;
    selectedClient.value = null;
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

                <ClientList :clients="clients" :total="total" @view="handleView" @edit="handleEdit" />

                <!-- Client View Modal -->
                <ClientViewModal :show="showModal" :client="selectedClient" @close="closeModal" @edit="handleEdit" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
