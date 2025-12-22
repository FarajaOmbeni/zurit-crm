<script setup>
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import LeadHeader from '@/Components/LeadHeader.vue';
import KanbanBoard from '@/Components/KanbanBoard.vue';
import ClientViewModal from '@/Components/ClientViewModal.vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    selectedProductId: {
        type: Number,
        default: null,
    },
    selectedProductName: {
        type: String,
        default: null,
    },
});

const searchQuery = ref('');
const showLeadModal = ref(false);
const selectedLead = ref(null);
const startInEditMode = ref(false);
const kanbanKey = ref(0); // Used to force refresh kanban board

// Check if product is selected, redirect if missing
onMounted(() => {
    if (!props.selectedProductId || !props.selectedProductName) {
        router.visit(route('pipeline.select-product'), {
            preserveState: false,
            preserveScroll: false,
        });
    }
});

const handleSearch = (query) => {
    searchQuery.value = query;
};

const handleExport = async () => {
    try {
        // Call the export endpoint
        const response = await axios.get('/api/leads/export', {
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

const handleLeadAdded = () => {
    // Force refresh the kanban board when a lead is added
    kanbanKey.value++;
};

const handleFilter = (filterType) => {
    // TODO: Implement filter functionality
    console.log('Filter by:', filterType);
};

const handleViewLead = async (lead) => {
    // Fetch full lead details
    try {
        const response = await axios.get(`/api/leads/${lead.id}`);
        selectedLead.value = response.data;
        startInEditMode.value = false;
        showLeadModal.value = true;
    } catch (error) {
        console.error('Error fetching lead details:', error);
        // Fallback to basic lead data
        selectedLead.value = lead;
        startInEditMode.value = false;
        showLeadModal.value = true;
    }
};

const handleEditLead = async (lead) => {
    // Fetch full lead details and open in edit mode
    try {
        const response = await axios.get(`/api/leads/${lead.id}`);
        selectedLead.value = response.data;
        startInEditMode.value = true;
        showLeadModal.value = true;
    } catch (error) {
        console.error('Error fetching lead details:', error);
        // Fallback to basic lead data
        selectedLead.value = lead;
        startInEditMode.value = true;
        showLeadModal.value = true;
    }
};

const handleLeadUpdated = (updatedLead) => {
    // Emit event to refresh kanban board if needed
    // The kanban board will refresh on next fetch
    console.log('Lead updated:', updatedLead);
};

const closeLeadModal = () => {
    showLeadModal.value = false;
    selectedLead.value = null;
    startInEditMode.value = false;
};
</script>

<template>

    <Head title="Pipeline" />

    <AuthenticatedLayout>
        <div v-if="selectedProductId && selectedProductName" class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <LeadHeader :product-id="selectedProductId" :product-name="selectedProductName" @search="handleSearch"
                    @export="handleExport" @add-lead="handleLeadAdded" @filter="handleFilter" />

                <KanbanBoard :key="kanbanKey" :search-query="searchQuery" :product-id="selectedProductId" @add-lead="handleLeadAdded"
                    @view-lead="handleViewLead" />

                <!-- Lead View Modal (using ClientViewModal component) -->
                <ClientViewModal :show="showLeadModal" :client="selectedLead" :initial-edit-mode="startInEditMode"
                    :is-lead="true" @close="closeLeadModal" @edit="handleEditLead" @updated="handleLeadUpdated" />
            </div>
        </div>
        <div v-else class="flex items-center justify-center py-12">
            <p class="text-gray-500">Redirecting to product selection...</p>
        </div>
    </AuthenticatedLayout>
</template>
