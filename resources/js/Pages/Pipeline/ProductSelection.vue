<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    products: {
        type: Array,
        default: () => [],
    },
});

const selecting = ref(null);

const selectProduct = (productId) => {
    selecting.value = productId;

    // Use Inertia router to post and follow redirect
    router.post('/pipeline/select-product', {
        product_id: productId,
    }, {
        preserveState: false,
        preserveScroll: false,
        onError: (errors) => {
            console.error('Error selecting product:', errors);
            selecting.value = null;
        },
    });
};
</script>

<template>

    <Head title="Select Product" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="font-heading text-3xl font-bold text-light-black">Select Product</h1>
                    <p class="mt-2 font-body text-sm text-zurit-gray">
                        Choose a product to view its pipeline and manage leads
                    </p>
                </div>

                <!-- Empty State -->
                <div v-if="products.length === 0" class="flex items-center justify-center py-12">
                    <div class="text-center">
                        <p class="text-gray-500 mb-2">No products available</p>
                        <p class="text-sm text-gray-400">Please contact an administrator to add products</p>
                    </div>
                </div>

                <!-- Products Grid -->
                <div v-else class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="product in products" :key="product.id"
                        class="group relative rounded-lg bg-white p-6 shadow-sm transition-all hover:shadow-md border border-gray-200 hover:border-zurit-purple">
                        <!-- Product Header -->
                        <div class="mb-4">
                            <h3 class="font-heading text-xl font-semibold text-light-black mb-2">
                                {{ product.name }}
                            </h3>
                            <p v-if="product.description" class="font-body text-sm text-zurit-gray line-clamp-2">
                                {{ product.description }}
                            </p>
                            <p v-if="product.category" class="mt-2 font-body text-xs text-zurit-gray">
                                Category: {{ product.category }}
                            </p>
                        </div>

                        <!-- Stats -->
                        <div class="mb-4 flex items-center gap-4">
                            <div class="flex items-center gap-2">
                                <div class="h-2 w-2 rounded-full bg-pink-500"></div>
                                <span class="font-body text-sm text-zurit-gray">
                                    <span class="font-semibold text-light-black">{{ product.active_leads_count }}</span>
                                    Active Leads
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="h-2 w-2 rounded-full bg-gray-400"></div>
                                <span class="font-body text-sm text-zurit-gray">
                                    <span class="font-semibold text-light-black">{{ product.total_leads_count }}</span>
                                    Total
                                </span>
                            </div>
                        </div>

                        <!-- Price (if available) -->
                        <div v-if="product.price" class="mb-4">
                            <span class="font-heading text-lg font-bold text-zurit-purple">
                                Ksh {{ Number(product.price).toLocaleString() }}
                            </span>
                        </div>

                        <!-- Select Button -->
                        <button @click="selectProduct(product.id)" :disabled="selecting === product.id"
                            class="w-full rounded-lg bg-zurit-purple px-4 py-2 font-body text-sm font-medium text-white transition-colors hover:bg-zurit-purple/90 focus:outline-none focus:ring-2 focus:ring-zurit-purple focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span v-if="selecting === product.id">Selecting...</span>
                            <span v-else>Select Product</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
