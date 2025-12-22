<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    title: {
        type: String,
        default: 'Leads Pipeline',
    },
    subtitle: {
        type: String,
        default: 'Help clients build sustainable wealth through strategic financial guidance',
    },
    avgProgress: {
        type: Number,
        default: 75,
    },
    activeClients: {
        type: Number,
        default: 8,
    },
    totalLeads: {
        type: Number,
        default: 0,
    },
    completed: {
        type: Number,
        default: 5,
    },
    companySortOrder: {
        type: String,
        default: null,
        validator: (value) => value === null || value === 'asc' || value === 'desc',
    },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

const emit = defineEmits(['export', 'addLead', 'import', 'search', 'filter', 'sort']);

const handleExport = () => {
    emit('export');
};

const handleAddLead = () => {
    emit('addLead');
};

const handleImport = () => {
    emit('import');
};

const handleSearch = (event) => {
    emit('search', event.target.value);
};

const handleFilter = (filterType) => {
    emit('filter', filterType);
};

const handleCompanySort = () => {
    // Cycle through: null -> 'asc' -> 'desc' -> null
    let nextOrder = null;
    if (props.companySortOrder === null) {
        nextOrder = 'asc';
    } else if (props.companySortOrder === 'asc') {
        nextOrder = 'desc';
    } else {
        nextOrder = null;
    }
    emit('sort', { field: 'company', order: nextOrder });
};
</script>

<template>
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="flex items-start justify-between">
            <div>
                <h1 class="font-heading text-3xl font-bold text-light-black">{{ title }}</h1>
                <p class="mt-2 font-body text-sm text-zurit-gray">{{ subtitle }}</p>
            </div>

            <!-- Action Buttons and User Avatar -->
            <div class="flex items-center space-x-8">
                <!-- Export CSV Button -->
                <button @click="handleExport"
                    class="inline-flex items-center space-x-2 rounded-lg border border-zurit-purple bg-white px-4 py-2 font-body text-sm font-medium text-zurit-purple transition-colors hover:bg-zurit-purple/5 focus:outline-none focus:ring-2 focus:ring-zurit-purple focus:ring-offset-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Export CSV</span>
                </button>

                <!-- Add Lead Button -->
                <button @click="handleAddLead"
                    class="inline-flex items-center space-x-2 rounded-lg border border-zurit-purple bg-white px-4 py-2 font-body text-sm font-medium text-zurit-purple transition-colors hover:bg-zurit-purple/5 focus:outline-none focus:ring-2 focus:ring-zurit-purple focus:ring-offset-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Add Lead</span>
                </button>

                <!-- Import Button -->
                <button @click="handleImport"
                    class="inline-flex items-center space-x-2 rounded-lg bg-zurit-purple px-4 py-2 font-body text-sm font-medium text-white transition-colors hover:bg-zurit-purple/90 focus:outline-none focus:ring-2 focus:ring-zurit-purple focus:ring-offset-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <span>Import CSV</span>
                </button>

                <!-- User Avatar -->
                <div v-if="user" class="h-10 w-10 rounded-full bg-gray-600 flex items-center justify-center">
                    <span class="font-body text-sm font-medium text-white">
                        {{ user.name?.charAt(0).toUpperCase() }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Avg Progress Card -->
            <div class="rounded-lg bg-purple-100 p-6 shadow-sm">
                <h3 class="font-body text-sm font-medium text-purple-800">Avg Progress</h3>
                <p class="mt-2 font-heading text-3xl font-bold text-purple-900">{{ avgProgress }}%</p>
            </div>

            <!-- Active Clients Card -->
            <div class="rounded-lg bg-pink-100 p-6 shadow-sm">
                <h3 class="font-body text-sm font-medium text-pink-800">Active Clients</h3>
                <p class="mt-2 font-heading text-3xl font-bold text-pink-900">{{ String(activeClients).padStart(2, '0')
                }}</p>
            </div>

            <!-- Total Leads Card -->
            <div class="rounded-lg bg-blue-100 p-6 shadow-sm">
                <h3 class="font-body text-sm font-medium text-blue-800">Total Leads</h3>
                <p class="mt-2 font-heading text-3xl font-bold text-blue-900">{{ String(totalLeads).padStart(2, '0')
                }}</p>
            </div>

            <!-- Completed Card -->
            <div class="rounded-lg bg-green-100 p-6 shadow-sm">
                <h3 class="font-body text-sm font-medium text-green-800">Completed</h3>
                <p class="mt-2 font-heading text-3xl font-bold text-green-900">{{ String(completed).padStart(2, '0') }}
                </p>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="flex flex-col space-y-0 sm:flex-row sm:items-center sm:justify-between sm:space-y-0 sm:space-x-4">
            <!-- Search Bar -->
            <div class="flex-1">
                <div class="relative">
                    <div
                        class="pointer-events-none absolute inset-y-0 left-0 flex items-center justify-center h-full pl-3">
                        <svg class="h-5 w-5 text-zurit-gray" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" @input="handleSearch" placeholder="Search client, companies, services..."
                        class="block w-full rounded-lg border border-gray-300 bg-white py-2 pl-12 pr-3 font-body text-sm text-light-black placeholder-zurit-gray focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple"
                        style="text-indent:1rem;" />
                </div>
            </div>

            <!-- Sort by Company Name Button -->
            <div class="flex items-center space-x-3">
                <button @click="handleCompanySort" :class="[
                    'inline-flex items-center space-x-2 rounded-lg border px-4 py-2 font-body text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-zurit-purple focus:ring-offset-2',
                    props.companySortOrder === null
                        ? 'border-gray-300 bg-white text-light-black hover:bg-light-gray'
                        : 'border-zurit-purple bg-zurit-purple/5 text-zurit-purple hover:bg-zurit-purple/10'
                ]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17V7a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v10M7 20h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2z" />
                    </svg>
                    <span>Company Name</span>
                    <!-- Sort Arrow -->
                    <div class="flex flex-col">
                        <!-- Up Arrow (Ascending) -->
                        <svg v-if="props.companySortOrder === 'asc'" class="h-3 w-3" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                        </svg>
                        <!-- Down Arrow (Descending) -->
                        <svg v-else-if="props.companySortOrder === 'desc'" class="h-3 w-3" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                        <!-- No Arrow (Default) -->
                        <svg v-else class="h-3 w-3 opacity-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                        </svg>
                    </div>
                </button>

                <!-- Date Filter -->
                <button @click="handleFilter('date')"
                    class="inline-flex items-center space-x-2 rounded-lg border border-gray-300 bg-white px-4 py-2 font-body text-sm font-medium text-light-black transition-colors hover:bg-light-gray focus:outline-none focus:ring-2 focus:ring-zurit-purple focus:ring-offset-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Date</span>
                </button>
            </div>
        </div>
    </div>
</template>
