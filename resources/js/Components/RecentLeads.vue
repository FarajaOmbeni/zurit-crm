<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';

const recentLeads = ref([]);
const loading = ref(false);

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

onMounted(() => {
    fetchRecentLeads();
});
</script>

<template>
    <div class="bg-white rounded-lg shadow-sm p-6">
        <!-- Title -->
        <h2 class="text-lg font-bold text-gray-800 mb-4">Recent Leads</h2>
        
        <div v-if="loading" class="text-center py-8 text-gray-500">
            Loading...
        </div>
        
        <div v-else-if="recentLeads.length === 0" class="text-center py-8 text-gray-500">
            No recent leads found
        </div>
        
        <div v-else>
            <!-- Table -->
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
                                :class="['inline-flex px-3 py-1 rounded-full text-xs font-medium', statusColors[lead.status] || 'bg-gray-100 text-gray-800']"
                            >
                                {{ getStatusLabel(lead.status) }}
                            </span>
                        </td>
                        <td class="py-3 text-sm text-gray-600">{{ formatTimeAgo(lead.updated_at) }}</td>
                    </tr>
                </tbody>
            </table>
            
            <!-- View All Link -->
            <div class="mt-6 text-center">
                <Link 
                    href="/leads" 
                    class="text-purple-600 hover:text-purple-700 text-sm font-medium inline-flex items-center"
                >
                    View All Leads →
                </Link>
            </div>
        </div>
    </div>
</template>

