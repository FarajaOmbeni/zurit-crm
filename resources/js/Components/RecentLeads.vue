<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';

const recentLeads = ref([]);
const allLeads = ref([]);
const loading = ref(false);
const showModal = ref(false);
const modalLoading = ref(false);

const statusColors = {
    'new_lead': 'bg-purple-100 text-purple-800',
    'initial_outreach': 'bg-blue-100 text-blue-800',
    'follow_ups': 'bg-pink-100 text-pink-800',
    'negotiations': 'bg-yellow-100 text-yellow-800',
    'won': 'bg-green-100 text-green-800',
    'lost': 'bg-red-100 text-red-800',
};

const getStatusLabel = (status) => {
    const labels = {
        'new_lead': 'New',
        'initial_outreach': 'Qualified',
        'follow_ups': 'Proposal',
        'negotiations': 'Negotiation',
        'won': 'Won',
        'lost': 'Lost',
    };
    return labels[status] || status;
};

const formatTimeAgo = (date) => {
    if (!date) return 'N/A';
    const now = new Date();
    const past = new Date(date);
    const diffInHours = Math.floor((now - past) / (1000 * 60 * 60));

    if (diffInHours < 1) return 'Just now';
    if (diffInHours === 1) return '1 hour ago';
    if (diffInHours < 24) return `${diffInHours} hours ago`;
    const diffInDays = Math.floor(diffInHours / 24);
    if (diffInDays === 1) return '1 day ago';
    return `${diffInDays} days ago`;
};

const fetchRecentLeads = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/leads?per_page=4&order_by=created_at&order=desc');
        recentLeads.value = response.data.data || [];
    } catch (error) {
        console.error('Error fetching recent leads:', error);
    } finally {
        loading.value = false;
    }
};

const fetchAllLeads = async () => {
    modalLoading.value = true;
    try {
        const response = await axios.get('/api/leads', {
            params: {
                per_page: 100,
                order_by: 'created_at',
                order: 'desc'
            }
        });
        allLeads.value = response.data.data || [];
    } catch (error) {
        console.error('Error fetching all leads:', error);
    } finally {
        modalLoading.value = false;
    }
};

const openModal = (e) => {
    e.preventDefault();
    showModal.value = true;
    fetchAllLeads();
};

const closeModal = () => {
    showModal.value = false;
};

onMounted(() => {
    fetchRecentLeads();
});
</script>

<template>
    <div class="bg-white rounded-lg shadow-sm p-6 h-[500px] flex flex-col">
        <!-- Title -->
        <h2 class="text-lg font-bold text-gray-800 mb-4">Recent Leads</h2>

        <div v-if="loading" class="text-center py-8 text-gray-500 flex-1 flex items-center justify-center">
            Loading...
        </div>

        <div v-else-if="recentLeads.length === 0"
            class="text-center py-8 text-gray-500 flex-1 flex items-center justify-center">
            No recent leads found
        </div>

        <div v-else class="flex-1 flex flex-col overflow-hidden">
            <!-- Table -->
            <div class="flex-1 overflow-hidden">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="text-left py-3 text-sm font-normal text-gray-500">COMPANY</th>
                            <th class="text-left py-3 text-sm font-normal text-gray-500">STATUS</th>
                            <th class="text-left py-3 text-sm font-normal text-gray-500">LAST CONTACT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="lead in recentLeads" :key="lead.id" class="border-b border-gray-100 last:border-b-0">
                            <td class="py-3 text-sm text-gray-900 font-medium">{{ lead.company }}</td>
                            <td class="py-3">
                                <span
                                    :class="['inline-flex px-3 py-1 rounded-full text-xs font-medium', statusColors[lead.status] || 'bg-gray-100 text-gray-800']">
                                    {{ getStatusLabel(lead.status) }}
                                </span>
                            </td>
                            <td class="py-3 text-sm text-gray-600">{{ formatTimeAgo(lead.updated_at) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- View All Link -->
            <div class="mt-auto pt-4 text-center">
                <button @click="openModal"
                    class="text-purple-600 hover:text-purple-700 text-sm font-medium inline-flex items-center">
                    View All Leads →
                </button>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50"
            @click="closeModal">
            <div class="bg-white rounded-lg shadow-xl max-w-5xl w-full max-h-[85vh] overflow-hidden" @click.stop>
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900">All Leads</h3>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="overflow-y-auto max-h-[calc(85vh-140px)]">
                    <div v-if="modalLoading" class="p-6">
                        <div class="space-y-3">
                            <div v-for="i in 10" :key="i" class="animate-pulse flex gap-4">
                                <div class="h-4 bg-gray-200 rounded flex-1"></div>
                                <div class="h-4 bg-gray-200 rounded w-24"></div>
                                <div class="h-4 bg-gray-200 rounded w-32"></div>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="allLeads.length === 0" class="text-center py-12 text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-lg font-medium">No leads found</p>
                    </div>

                    <table v-else class="w-full">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">COMPANY</th>
                                <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">STATUS</th>
                                <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">LAST CONTACT</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="lead in allLeads" :key="lead.id" class="hover:bg-gray-50 transition-colors">
                                <td class="py-4 px-6 text-sm text-gray-900 font-medium">
                                    {{ lead.company }}
                                </td>
                                <td class="py-4 px-6">
                                    <span
                                        :class="['inline-flex px-3 py-1 rounded-full text-xs font-medium', statusColors[lead.status] || 'bg-gray-100 text-gray-800']">
                                        {{ getStatusLabel(lead.status) }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-sm text-gray-600">
                                    {{ formatTimeAgo(lead.updated_at) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Modal Footer -->
                <div class="p-6 border-t border-gray-200 bg-gray-50">
                    <button @click="closeModal"
                        class="w-full py-2 px-4 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-medium">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Custom scrollbar for modal */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f3f4f6;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #9333ea;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #7c3aed;
}

/* Firefox scrollbar */
.overflow-y-auto {
    scrollbar-width: thin;
    scrollbar-color: #9333ea #f3f4f6;
}
</style>
