<script setup>
import { ref, onMounted, computed } from 'vue';

const salesData = ref([]);
const loading = ref(false);

const fetchSalesData = async () => {
    loading.value = true;
    try {
        // Get the last 5 days (including today)
        const today = new Date();
        const last5Days = [];

        // Generate array of last 5 days
        for (let i = 4; i >= 0; i--) {
            const date = new Date(today);
            date.setDate(date.getDate() - i);
            date.setHours(0, 0, 0, 0);
            last5Days.push(date);
        }

        // Fetch won leads
        const response = await window.axios.get('/api/leads', {
            params: {
                status: 'won',
                per_page: 1000, // Get enough to cover last 5 days
            }
        });

        const wonLeads = response.data.data || [];

        // Count sales for each of the last 5 days
        const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        salesData.value = last5Days.map(date => {
            const dateString = date.toISOString().split('T')[0];

            // Count leads won on this specific date
            const count = wonLeads.filter(lead => {
                if (!lead.won_at) return false;
                const wonDate = new Date(lead.won_at);
                const wonDateString = wonDate.toISOString().split('T')[0];
                return wonDateString === dateString;
            }).length;

            return {
                day: dayNames[date.getDay()],
                sales: count,
            };
        });

    } catch (error) {
        console.error('Error fetching sales data:', error);
        // If API fails, show empty data instead of random data
        const today = new Date();
        const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        salesData.value = [];
        for (let i = 4; i >= 0; i--) {
            const date = new Date(today);
            date.setDate(date.getDate() - i);
            salesData.value.push({
                day: dayNames[date.getDay()],
                sales: 0,
            });
        }
    } finally {
        loading.value = false;
    }
};

const getYPosition = (sales) => {
    // Fixed scale: 0-20 on Y-axis, chart height is 160
    return 160 - (sales / 20) * 160;
};

onMounted(() => {
    fetchSalesData();
});
</script>

<template>
    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-100">
        <!-- Title -->
        <h2 class="text-lg font-semibold text-gray-900 mb-6">Sales (Last 5 Days)</h2>

        <div v-if="loading" class="text-center py-8 text-gray-500">
            Loading...
        </div>

        <div v-else>
            <!-- Chart Container -->
            <div class="relative h-56">
                <!-- Y-axis label -->
                <div
                    class="absolute -left-6 top-1/2 -translate-y-1/2 -rotate-90 text-xs text-gray-600 font-normal whitespace-nowrap">
                    No. of Sales
                </div>

                <!-- Y-axis labels (0, 4, 8, 12, 16, 20) -->
                <div class="absolute left-8 top-0 bottom-10 flex flex-col justify-between text-xs text-gray-600">
                    <span>20</span>
                    <span>16</span>
                    <span>12</span>
                    <span>8</span>
                    <span>4</span>
                    <span>0</span>
                </div>

                <!-- Chart Area -->
                <div class="ml-14 h-full pb-10">
                    <svg class="w-full h-full" viewBox="0 0 400 160" preserveAspectRatio="none">
                        <!-- Grid lines (horizontal) -->
                        <line v-for="i in 6" :key="i" :x1="0" :y1="(i - 1) * 32" :x2="400" :y2="(i - 1) * 32"
                            stroke="#f3f4f6" stroke-width="1" />

                        <!-- Line graph -->
                        <polyline v-if="salesData.length > 1" :points="salesData.map((item, index) => {
                            const x = (index / (salesData.length - 1)) * 400;
                            const y = getYPosition(item.sales);
                            return `${x},${y}`;
                        }).join(' ')" fill="none" stroke="#8B5CF6" stroke-width="2.5" stroke-linecap="round"
                            stroke-linejoin="round" />

                        <!-- Data points -->
                        <circle v-for="(item, index) in salesData" :key="index"
                            :cx="(index / (salesData.length - 1)) * 400" :cy="getYPosition(item.sales)" r="4"
                            fill="#8B5CF6" />
                    </svg>
                </div>

                <!-- X-axis labels (days) -->
                <div class="ml-14 flex justify-between text-xs text-gray-600 px-1">
                    <span v-for="(item, index) in salesData" :key="index" class="flex-1 text-center">
                        {{ item.day }}
                    </span>
                </div>

                <!-- X-axis label -->
                <div class="ml-14 mt-3 text-center text-xs text-gray-600 font-normal">
                    Days of the Week
                </div>
            </div>

            <!-- Legend and Button Row -->
            <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between">
                <!-- Legend -->
                <div class="flex items-center gap-2">
                    <svg width="20" height="4" class="flex-shrink-0">
                        <line x1="0" y1="2" x2="20" y2="2" stroke="#8B5CF6" stroke-width="2" />
                    </svg>
                    <span class="text-sm text-gray-600">5</span>
                </div>

                <!-- View Report Button -->
                <button
                    class="bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition shadow-sm">
                    View Report
                </button>
            </div>
        </div>
    </div>
</template>
