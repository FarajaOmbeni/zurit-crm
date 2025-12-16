<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

defineProps({
    title: {
        type: String,
        default: 'Leads Pipeline',
    },
    subtitle: {
        type: String,
        default: 'Help clients build sustainable wealth through strategic financial guidance',
    },
});

const emit = defineEmits(['export', 'addLead', 'search', 'filter']);

const totalPipeline = ref('Ksh 0');
const totalLeads = ref(0);
const closedThisMonth = ref(0);
const totalThisMonth = ref('Ksh 0');
const loading = ref(true);

const fetchStats = async () => {
    try {
        const response = await axios.get('/api/leads/pipeline-stats');
        totalPipeline.value = `Ksh ${response.data.totalPipeline}`;
        totalLeads.value = response.data.totalLeads;
        closedThisMonth.value = response.data.closedThisMonth;
        totalThisMonth.value = `Ksh ${response.data.totalThisMonth}`;
    } catch (error) {
        console.error('Error fetching pipeline stats:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchStats();
});

const handleExport = () => {
    emit('export');
};

const handleAddLead = () => {
    emit('addLead');
};

const handleSearch = (event) => {
    emit('search', event.target.value);
};

const handleFilter = (filterType) => {
    emit('filter', filterType);
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

            <!-- Action Buttons -->
            <div class="flex items-center space-x-4">
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
                    class="inline-flex items-center space-x-2 rounded-lg bg-zurit-purple px-4 py-2 font-body text-sm font-medium text-white transition-colors hover:bg-zurit-purple/90 focus:outline-none focus:ring-2 focus:ring-zurit-purple focus:ring-offset-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Add Lead</span>
                </button>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Pipeline Card -->
            <div class="rounded-lg bg-purple-100 p-6 shadow-sm">
                <h3 class="font-body text-sm font-medium text-purple-800">Total Pipeline</h3>
                <p v-if="loading" class="mt-2 font-heading text-3xl font-bold text-purple-900">...</p>
                <p v-else class="mt-2 font-heading text-3xl font-bold text-purple-900">{{ totalPipeline }}</p>
            </div>

            <!-- Total Leads Card -->
            <div class="rounded-lg bg-pink-100 p-6 shadow-sm">
                <h3 class="font-body text-sm font-medium text-pink-800">Total Leads</h3>
                <p v-if="loading" class="mt-2 font-heading text-3xl font-bold text-pink-900">...</p>
                <p v-else class="mt-2 font-heading text-3xl font-bold text-pink-900">{{ String(totalLeads).padStart(2,
                    '0') }}
                </p>
            </div>

            <!-- Closed this month Card -->
            <div class="rounded-lg bg-green-100 p-6 shadow-sm">
                <h3 class="font-body text-sm font-medium text-green-800">Closed this month</h3>
                <p v-if="loading" class="mt-2 font-heading text-3xl font-bold text-green-900">...</p>
                <p v-else class="mt-2 font-heading text-3xl font-bold text-green-900">{{
                    String(closedThisMonth).padStart(2,
                        '0') }}</p>
            </div>

            <!-- Total this month Card -->
            <div class="rounded-lg bg-blue-100 p-6 shadow-sm">
                <h3 class="font-body text-sm font-medium text-blue-800">Total this month</h3>
                <p v-if="loading" class="mt-2 font-heading text-3xl font-bold text-blue-900">...</p>
                <p v-else class="mt-2 font-heading text-3xl font-bold text-blue-900">{{ totalThisMonth }}</p>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="flex flex-col space-y-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
            <!-- Search Bar -->
            <div class="flex-1">
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-zurit-gray" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" @input="handleSearch" placeholder="Search client, companies, services..."
                        class="block w-full rounded-lg border border-gray-300 bg-white py-2 pl-10 pr-3 font-body text-sm text-light-black placeholder-zurit-gray focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple" />
                </div>
            </div>

            <!-- Filter Buttons -->
            <div class="flex items-center space-x-3">
                <!-- Name Filter -->
                <button @click="handleFilter('name')"
                    class="inline-flex items-center space-x-2 rounded-lg border border-gray-300 bg-white px-4 py-2 font-body text-sm font-medium text-light-black transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-zurit-purple focus:ring-offset-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <span>Name</span>
                </button>

                <!-- Date Filter -->
                <button @click="handleFilter('date')"
                    class="inline-flex items-center space-x-2 rounded-lg border border-gray-300 bg-white px-4 py-2 font-body text-sm font-medium text-light-black transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-zurit-purple focus:ring-offset-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Date</span>
                </button>

                <!-- Filter Icon Button -->
                <button @click="handleFilter('filter')"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white p-2 font-body text-sm font-medium text-light-black transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-zurit-purple focus:ring-offset-2">
                    <svg class="h-4 w-4 text-zurit-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>
