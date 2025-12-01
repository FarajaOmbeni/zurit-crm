<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const sourcesData = ref([]);
const loading = ref(true);

const sourceColors = {
    'Website': '#6366F1', // Indigo
    'Referral': '#F59E0B', // Amber/Orange
    'Cold Call': '#14B8A6', // Teal
    'Social Media': '#EC4899', // Pink
    'Email Campaign': '#8B5CF6', // Purple
    'LinkedIn': '#06B6D4', // Cyan
    'Trade Show': '#F97316', // Orange
    'Unknown': '#6B7280' // Gray
};

const totalLeads = computed(() => {
    return sourcesData.value.reduce((sum, source) => sum + source.count, 0);
});

const chartData = computed(() => {
    const total = totalLeads.value;
    if (total === 0) return [];

    let cumulativePercentage = 0;
    return sourcesData.value.map(source => {
        const percentage = (source.count / total) * 100;
        const startAngle = (cumulativePercentage * 360) / 100;
        const endAngle = ((cumulativePercentage + percentage) * 360) / 100;
        cumulativePercentage += percentage;

        return {
            ...source,
            percentage: percentage.toFixed(1),
            startAngle,
            endAngle
        };
    });
});

const fetchLeadsData = async () => {
    try {
        loading.value = true;
        const response = await axios.get('/api/leads', {
            params: {
                per_page: 1000
            }
        });

        // Filter leads from last 30 days
        const thirtyDaysAgo = new Date();
        thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);

        const recentLeads = response.data.data.filter(lead => {
            const createdAt = new Date(lead.created_at);
            return createdAt >= thirtyDaysAgo;
        });

        // Group by source
        const sourceCounts = recentLeads.reduce((acc, lead) => {
            const source = lead.source || 'Unknown';
            acc[source] = (acc[source] || 0) + 1;
            return acc;
        }, {});

        // Convert to array with colors
        sourcesData.value = Object.entries(sourceCounts)
            .map(([name, count]) => ({
                name,
                count,
                color: sourceColors[name] || sourceColors['Unknown']
            }))
            .sort((a, b) => b.count - a.count);

    } catch (error) {
        console.error('Error fetching leads data:', error);
    } finally {
        loading.value = false;
    }
};

const getDonutPath = (startAngle, endAngle) => {
    const centerX = 80;
    const centerY = 80;
    const radius = 65;
    const innerRadius = 32;

    const startRad = (startAngle - 90) * (Math.PI / 180);
    const endRad = (endAngle - 90) * (Math.PI / 180);

    const x1 = centerX + radius * Math.cos(startRad);
    const y1 = centerY + radius * Math.sin(startRad);
    const x2 = centerX + radius * Math.cos(endRad);
    const y2 = centerY + radius * Math.sin(endRad);
    const x3 = centerX + innerRadius * Math.cos(endRad);
    const y3 = centerY + innerRadius * Math.sin(endRad);
    const x4 = centerX + innerRadius * Math.cos(startRad);
    const y4 = centerY + innerRadius * Math.sin(startRad);

    const largeArc = endAngle - startAngle > 180 ? 1 : 0;

    return `
        M ${x1} ${y1}
        A ${radius} ${radius} 0 ${largeArc} 1 ${x2} ${y2}
        L ${x3} ${y3}
        A ${innerRadius} ${innerRadius} 0 ${largeArc} 0 ${x4} ${y4}
        Z
    `;
};

onMounted(() => {
    fetchLeadsData();
});
</script>

<template>
    <div class="bg-white shadow rounded-lg p-6 h-[500px] flex flex-col">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Leads per Source (Last 30 days)</h3>

        <div v-if="loading" class="flex flex-col items-center flex-1 justify-center">
            <div class="animate-pulse">
                <div class="w-40 h-40 bg-gray-200 rounded-full"></div>
            </div>
            <div class="mt-4 space-y-2 w-full">
                <div v-for="i in 3" :key="i" class="h-4 bg-gray-200 rounded w-3/4 mx-auto animate-pulse"></div>
            </div>
        </div>

        <div v-else-if="sourcesData.length === 0"
            class="text-center py-8 text-gray-500 flex-1 flex items-center justify-center">
            No leads data available
        </div>

        <div v-else class="flex flex-col items-center flex-1 justify-center">
            <!-- Donut Chart -->
            <div class="relative w-40 h-40">
                <svg viewBox="0 0 160 160" class="w-full h-full transform -rotate-90">
                    <path v-for="segment in chartData" :key="segment.name"
                        :d="getDonutPath(segment.startAngle, segment.endAngle)" :fill="segment.color"
                        class="transition-all duration-300 hover:opacity-80" />
                </svg>

                <!-- Center Text -->
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-900">{{ totalLeads }}</div>
                        <div class="text-xs text-gray-500">Total</div>
                    </div>
                </div>
            </div>

            <!-- Legend -->
            <div class="mt-6 w-full space-y-2">
                <div v-for="source in sourcesData" :key="source.name" class="flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: source.color }"></div>
                        <span class="text-gray-700">{{ source.name }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-gray-600">{{ source.count }}</span>
                        <span class="text-gray-400 text-xs w-12 text-right">
                            {{ ((source.count / totalLeads) * 100).toFixed(0) }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
