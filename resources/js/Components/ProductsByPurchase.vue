<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const products = ref([]);
const loading = ref(true);

const maxCount = computed(() => {
    return Math.max(...products.value.map(p => p.count), 0);
});

const totalCount = computed(() => {
    return products.value.reduce((sum, p) => sum + p.count, 0);
});

const getPercentage = (count) => {
    if (totalCount.value === 0) return 0;
    return ((count / totalCount.value) * 100).toFixed(0);
};

const fetchProductsData = async () => {
    try {
        loading.value = true;
        const response = await axios.get('/api/leads', {
            params: {
                status: 'won',
                include_clients: true,
                per_page: 1000
            }
        });

        // Group by product
        const productCounts = response.data.data.reduce((acc, lead) => {
            const product = lead.product || 'Unknown';
            acc[product] = (acc[product] || 0) + 1;
            return acc;
        }, {});

        // Convert to array and sort by count (descending)
        products.value = Object.entries(productCounts)
            .map(([name, count]) => ({ name, count }))
            .sort((a, b) => b.count - a.count)
            .slice(0, 5); // Show top 5 products

    } catch (error) {
        console.error('Error fetching products data:', error);
    } finally {
        loading.value = false;
    }
};

const getBarWidth = (count) => {
    if (totalCount.value === 0) return 0;
    return (count / totalCount.value) * 100;
};

const getBarColor = (index) => {
    const colors = [
        'bg-purple-500',
        'bg-blue-500',
        'bg-indigo-500',
        'bg-violet-500',
        'bg-blue-400'
    ];
    return colors[index % colors.length];
};

onMounted(() => {
    fetchProductsData();
});
</script>

<template>
    <div class="bg-white shadow rounded-lg p-6 h-[500px] flex flex-col">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Products by Purchase</h3>

        <div v-if="loading" class="space-y-3 flex-1 flex flex-col justify-center">
            <div v-for="i in 5" :key="i" class="animate-pulse flex items-center gap-3">
                <div class="h-4 bg-gray-200 rounded w-32"></div>
                <div class="flex-1 h-6 bg-gray-100 rounded"></div>
            </div>
        </div>

        <div v-else-if="products.length === 0"
            class="text-center py-8 text-gray-500 flex-1 flex items-center justify-center">
            No product data available
        </div>

        <div v-else class="space-y-3 flex-1 flex flex-col">
            <div v-for="(product, index) in products" :key="product.name" class="flex items-center gap-3">
                <!-- Product Name -->
                <div class="w-40 text-sm text-gray-700 truncate" :title="product.name">
                    {{ product.name }}
                </div>

                <!-- Bar Chart -->
                <div class="flex-1 flex items-center gap-2">
                    <div class="flex-1 bg-gray-200 rounded-full h-6 overflow-hidden">
                        <div :class="getBarColor(index)"
                            class="h-full rounded-full transition-all duration-500 ease-out flex items-center justify-end pr-2"
                            :style="{ width: getBarWidth(product.count) + '%' }">
                            <span v-if="getBarWidth(product.count) > 15" class="text-xs text-white font-medium">
                                {{ product.count }}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span v-if="getBarWidth(product.count) <= 15" class="text-xs text-gray-600 font-medium">
                            {{ product.count }}
                        </span>
                        <span class="text-xs text-gray-400 w-10 text-right">
                            {{ getPercentage(product.count) }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
