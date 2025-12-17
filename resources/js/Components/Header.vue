<script setup>
import { Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import axios from 'axios';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const isAdmin = computed(() => user.value?.role === 'admin');

const navigation = [
    { name: 'Client Database', route: 'clients.index' },
    { name: 'Reports', route: 'reports.index' },
    { name: 'Tasks', route: 'tasks.index' },
];

const isActive = (routeName) => {
    return route().current(routeName);
};

// Pipeline dropdown state
const pipelineDropdownOpen = ref(false);
const products = ref([]);
const loadingProducts = ref(false);

const fetchProducts = async () => {
    if (products.value.length > 0) return; // Already fetched

    loadingProducts.value = true;
    try {
        const response = await axios.get('/api/products', {
            params: {
                per_page: 50, // Get all products
            },
        });
        products.value = response.data.data || [];
    } catch (error) {
        console.error('Error fetching products:', error);
    } finally {
        loadingProducts.value = false;
    }
};

const handlePipelineClick = () => {
    // Navigate to product selection page
    router.visit(route('pipeline.select-product'));
};

const handleProductClick = (productId) => {
    // Select product and redirect to pipeline
    router.post('/pipeline/select-product', {
        product_id: productId,
    }, {
        preserveState: false,
        preserveScroll: false,
    });
    pipelineDropdownOpen.value = false;
};

const handlePipelineMouseEnter = () => {
    pipelineDropdownOpen.value = true;
    fetchProducts();
};

const handlePipelineMouseLeave = () => {
    pipelineDropdownOpen.value = false;
};
</script>

<template>
    <header class="bg-white">
        <!-- Thin blue bar at top -->
        <div class="h-1 bg-zurit-purple"></div>

        <!-- Main header content -->
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <!-- Left: Logo and Brand -->
                <div class="flex items-center space-x-3">
                    <Link :href="route('dashboard')" class="flex items-center space-x-3">
                        <img src="/images/zurit_logo.png" alt="ZuritCRM Logo" class="h-8 w-auto" />
                    </Link>
                </div>

                <!-- Center: Navigation Links -->
                <nav class="hidden md:flex items-center space-x-8">
                    <!-- Dashboard Link (First) -->
                    <Link :href="route('dashboard')" :class="[
                        'font-body text-sm font-medium transition-colors',
                        isActive('dashboard')
                            ? 'text-light-black border-b-2 border-prosper pb-1'
                            : 'text-zurit-gray hover:text-light-black'
                    ]">
                        Dashboard
                    </Link>

                    <!-- Pipeline Dropdown -->
                    <div class="relative" @mouseenter="handlePipelineMouseEnter" @mouseleave="handlePipelineMouseLeave">
                        <button @click="handlePipelineClick" :class="[
                            'font-body text-sm font-medium transition-colors flex items-center gap-1',
                            isActive('pipeline') || isActive('pipeline.select-product')
                                ? 'text-light-black border-b-2 border-prosper pb-1'
                                : 'text-zurit-gray hover:text-light-black'
                        ]">
                            Pipeline
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Products Dropdown -->
                        <Transition enter-active-class="transition ease-out duration-200"
                            enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100"
                            leave-active-class="transition ease-in duration-75" leave-from-class="opacity-100 scale-100"
                            leave-to-class="opacity-0 scale-95">
                            <div v-show="pipelineDropdownOpen"
                                class="absolute z-50 mt-2 w-64 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5"
                                @mouseenter="handlePipelineMouseEnter" @mouseleave="handlePipelineMouseLeave"
                                @click.stop>
                                <div class="py-1">
                                    <!-- Loading State -->
                                    <div v-if="loadingProducts" class="px-4 py-2 text-sm text-zurit-gray">
                                        Loading products...
                                    </div>

                                    <!-- Products List -->
                                    <template v-else-if="products.length > 0">
                                        <button v-for="product in products" :key="product.id"
                                            @click="handleProductClick(product.id)"
                                            class="block w-full px-4 py-2 text-start text-sm leading-5 font-body text-light-black transition duration-150 ease-in-out hover:bg-light-gray focus:bg-light-gray focus:outline-none">
                                            <div class="font-medium">{{ product.name }}</div>
                                            <div v-if="product.category" class="text-xs text-zurit-gray">{{
                                                product.category }}</div>
                                        </button>
                                    </template>

                                    <!-- Empty State -->
                                    <div v-else class="px-4 py-2 text-sm text-zurit-gray">
                                        No products available
                                    </div>

                                    <!-- Divider -->
                                    <div class="border-t border-gray-200 my-1"></div>

                                    <!-- View All Products Link -->
                                    <button @click="handlePipelineClick"
                                        class="block w-full px-4 py-2 text-start text-sm leading-5 font-body text-zurit-purple transition duration-150 ease-in-out hover:bg-light-gray focus:bg-light-gray focus:outline-none font-medium">
                                        View All Products →
                                    </button>
                                </div>
                            </div>
                        </Transition>
                    </div>

                    <!-- Other Navigation Links -->
                    <Link v-for="item in navigation" :key="item.name" :href="route(item.route)" :class="[
                        'font-body text-sm font-medium transition-colors',
                        isActive(item.route)
                            ? 'text-light-black border-b-2 border-prosper pb-1'
                            : 'text-zurit-gray hover:text-light-black'
                    ]">
                        {{ item.name }}
                    </Link>
                </nav>

                <!-- Right: User Profile -->
                <div class="flex items-center space-x-3" v-if="user">
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button class="flex items-center space-x-3 focus:outline-none">
                                <div
                                    class="h-10 w-10 rounded-full border-2 border-prosper overflow-hidden bg-light-gray flex items-center justify-center">
                                    <span class="font-body text-sm font-medium text-light-black">
                                        {{ user.name?.charAt(0).toUpperCase() }}
                                    </span>
                                </div>
                                <span class="font-body text-sm font-medium text-prosper">
                                    {{ user.name }}
                                </span>
                            </button>
                        </template>

                        <template #content>
                            <DropdownLink v-if="isAdmin" :href="route('users.index')">
                                Manage Users
                            </DropdownLink>
                            <DropdownLink :href="route('profile.edit')">
                                Settings
                            </DropdownLink>
                            <div class="border-t border-gray-200"></div>
                            <Link :href="route('logout')" method="post" as="button"
                                class="block w-full px-4 py-2 text-start text-sm leading-5 font-body text-light-black transition duration-150 ease-in-out hover:bg-light-gray focus:bg-light-gray focus:outline-none">
                                Logout
                            </Link>
                        </template>
                    </Dropdown>
                </div>
            </div>
        </div>
    </header>
</template>
