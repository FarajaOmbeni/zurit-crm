<script setup>
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import LeadHeader from '@/Components/LeadHeader.vue';
import KanbanBoard from '@/Components/KanbanBoard.vue';
import { Head, router } from '@inertiajs/vue3';

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

const handleExport = () => {
    // TODO: Implement export functionality
    console.log('Export CSV');
};

const handleAddLead = (stageSlug) => {
    // TODO: Implement add lead functionality
    console.log('Add lead to stage:', stageSlug);
};

const handleFilter = (filterType) => {
    // TODO: Implement filter functionality
    console.log('Filter by:', filterType);
};

const handleViewLead = (lead) => {
    // TODO: Navigate to lead detail page
    console.log('View lead:', lead);
};
</script>

<template>

    <Head title="Pipeline" />

    <AuthenticatedLayout>
        <div v-if="selectedProductId && selectedProductName" class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <LeadHeader :product-id="selectedProductId" :product-name="selectedProductName" @search="handleSearch"
                    @export="handleExport" @add-lead="handleAddLead" @filter="handleFilter" />

                <KanbanBoard :search-query="searchQuery" :product-id="selectedProductId" @add-lead="handleAddLead"
                    @view-lead="handleViewLead" />
            </div>
        </div>
        <div v-else class="flex items-center justify-center py-12">
            <p class="text-gray-500">Redirecting to product selection...</p>
        </div>
    </AuthenticatedLayout>
</template>
