<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import LeadsPipelineHeader from '@/Components/LeadsPipelineHeader.vue';
import ClientList from '@/Components/ClientList.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const clients = ref([]);
const total = ref(0);
const searchTerm = ref('');
const filterType = ref(null);
const loading = ref(false);

const fetchClients = async () => {
    loading.value = true;
    try {
        const params = {
            per_page: 50,
        };

        if (searchTerm.value) {
            params.search = searchTerm.value;
        }

        const response = await window.axios.get('/api/clients', { params });
        clients.value = response.data.data || [];
        total.value = response.data.total || 0;
    } catch (error) {
        console.error('Error fetching clients:', error);
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
    fetchClients();
};

const handleFilter = (type) => {
    filterType.value = type;
    // Implement filter logic
    fetchClients();
};

const handleView = (client) => {
    // Handle view action
    console.log('View client:', client);
    router.visit(`/api/clients/${client.id}`);
};

const handleEdit = (client) => {
    // Handle edit action
    console.log('Edit client:', client);
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
            </div>
        </div>
    </AuthenticatedLayout>
</template>
