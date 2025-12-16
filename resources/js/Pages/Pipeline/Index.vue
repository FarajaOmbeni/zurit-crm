<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import LeadHeader from '@/Components/LeadHeader.vue';
import KanbanBoard from '@/Components/KanbanBoard.vue';
import { Head } from '@inertiajs/vue3';

const searchQuery = ref('');

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
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <LeadHeader @search="handleSearch" @export="handleExport" @add-lead="handleAddLead"
                    @filter="handleFilter" />

                <KanbanBoard :search-query="searchQuery" @add-lead="handleAddLead" @view-lead="handleViewLead" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
