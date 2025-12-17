<script setup>
const props = defineProps({
    clients: {
        type: Array,
        default: () => [],
    },
    total: {
        type: Number,
        default: 0,
    },
    currentPage: {
        type: Number,
        default: 1,
    },
    lastPage: {
        type: Number,
        default: 1,
    },
    perPage: {
        type: Number,
        default: 15,
    },
    loading: {
        type: Boolean,
        default: false,
    },
    showEmptyState: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['view', 'edit', 'pageChange']);

const handlePageChange = (page) => {
    emit('pageChange', page);
};

const getPageRange = () => {
    const start = (props.currentPage - 1) * props.perPage + 1;
    const end = Math.min(props.currentPage * props.perPage, props.total);
    return { start, end };
};

const getPageNumbers = () => {
    const pages = [];
    const maxVisible = 5;
    let startPage = Math.max(1, props.currentPage - Math.floor(maxVisible / 2));
    let endPage = Math.min(props.lastPage, startPage + maxVisible - 1);

    // Adjust start page if we're near the end
    if (endPage - startPage < maxVisible - 1) {
        startPage = Math.max(1, endPage - maxVisible + 1);
    }

    for (let i = startPage; i <= endPage; i++) {
        pages.push(i);
    }

    return pages;
};

const getInitials = (name) => {
    if (!name) return '??';
    const parts = name.split(' ');
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
};

const getStatusBadgeClass = (status) => {
    switch (status?.toLowerCase()) {
        case 'active':
        case 'won':
            return 'bg-green-100 text-green-800';
        case 'completed':
            return 'bg-blue-100 text-blue-800';
        case 'paused':
        case 'lost':
            return 'bg-yellow-100 text-yellow-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const formatStatus = (status) => {
    if (!status) return 'Unknown';
    const statusMap = {
        'won': 'Active',
        'lost': 'Paused',
        'new_lead': 'New',
        'initial_outreach': 'Active',
        'follow_ups': 'Active',
        'negotiations': 'Active',
    };
    return statusMap[status.toLowerCase()] || status;
};

const formatDate = (date) => {
    if (!date) return '-';
    const d = new Date(date);
    const months = ['January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'];
    return `${d.getDate()} ${months[d.getMonth()]}, ${d.getFullYear()}`;
};

const getServiceIconColor = (index) => {
    const colors = [
        'text-blue-600',
        'text-yellow-600',
        'text-red-600',
        'text-green-600',
    ];
    return colors[index % colors.length];
};

const getServices = (client) => {
    // Use products relationship if available, otherwise fallback to product string
    if (client.products && Array.isArray(client.products) && client.products.length > 0) {
        // Extract ALL product names from the relationship
        const services = client.products.map(p => {
            // Use product name from pivot if available, otherwise use product name
            return p.pivot?.product_name || p.name || '';
        }).filter(name => name);
        // Return all services - no limit
        return services;
    }

    // Fallback to old product string field for backward compatibility
    if (client.product) {
        const services = client.product.split(',').map(s => s.trim()).filter(s => s);
        return services; // Return all services
    }

    return [];
};

const getRemainingServicesCount = (client) => {
    // This function is no longer needed since we show all services
    // But keeping it for backward compatibility in case template uses it
    return 0;
};

const handleView = (client) => {
    emit('view', client);
};

const handleEdit = (client) => {
    emit('edit', client);
};
</script>

<template>
    <div class="mt-8">
        <!-- Header -->
        <div v-if="!loading || showEmptyState" class="mb-4">
            <p class="font-body text-sm text-zurit-gray">
                <template v-if="total > 0">
                    Showing {{ getPageRange().start }} to {{ getPageRange().end }} of {{ total }} leads
                </template>
                <template v-else>
                    No leads found
                </template>
            </p>
        </div>

        <!-- Client Table -->
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <!-- Loading State -->
            <div v-if="loading && !showEmptyState" class="p-12 text-center">
                <div class="flex flex-col items-center justify-center space-y-4">
                    <svg class="animate-spin h-8 w-8 text-zurit-purple" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <p class="font-body text-sm text-zurit-gray">Loading</p>
                </div>
            </div>

            <!-- Table (show when not loading or when there's data) -->
            <template v-else>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-light-gray">
                            <tr>
                                <th
                                    class="px-6 py-4 text-left font-heading text-xs font-medium uppercase tracking-wider text-light-black">
                                    CLIENT
                                </th>
                                <th
                                    class="px-6 py-4 text-left font-heading text-xs font-medium uppercase tracking-wider text-light-black">
                                    TYPE
                                </th>
                                <th
                                    class="px-6 py-4 text-left font-heading text-xs font-medium uppercase tracking-wider text-light-black">
                                    SERVICES
                                </th>
                                <th
                                    class="px-6 py-4 text-left font-heading text-xs font-medium uppercase tracking-wider text-light-black">
                                    STATUS
                                </th>
                                <th
                                    class="px-6 py-4 text-left font-heading text-xs font-medium uppercase tracking-wider text-light-black">
                                    NEXT SESSION
                                </th>
                                <th
                                    class="px-6 py-4 text-left font-heading text-xs font-medium uppercase tracking-wider text-light-black">
                                    ACTION
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="client in clients" :key="client.id"
                                class="hover:bg-light-gray/50 transition-colors">
                                <!-- CLIENT Column -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-4">
                                        <!-- Avatar -->
                                        <div
                                            class="h-12 w-12 rounded-full bg-zurit-purple flex items-center justify-center flex-shrink-0">
                                            <span class="font-body text-sm font-medium text-white">
                                                {{ getInitials(client.company || client.name) }}
                                            </span>
                                        </div>
                                        <!-- Client Info -->
                                        <div>
                                            <p class="font-body text-sm font-semibold text-light-black">
                                                {{ client.company || client.name }}
                                            </p>
                                            <p class="font-body text-xs text-zurit-gray mt-1">
                                                {{ client.company ? client.name : (client.position || 'Individual client')
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- TYPE Column -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="[
                                        'inline-flex rounded-full px-3 py-1 text-xs font-semibold font-body',
                                        client.is_client 
                                            ? 'bg-green-100 text-green-800' 
                                            : 'bg-blue-100 text-blue-800'
                                    ]">
                                        {{ client.is_client ? 'Client' : 'Lead' }}
                                    </span>
                                </td>

                                <!-- SERVICES Column -->
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <template
                                            v-if="(client.products && client.products.length > 0) || client.product">
                                            <!-- Service items with icons -->
                                            <div v-for="(service, index) in getServices(client)" :key="index"
                                                class="flex items-center space-x-1">
                                                <!-- Service Icon -->
                                                <svg :class="['h-4 w-4', getServiceIconColor(index)]" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path v-if="index % 4 === 0" stroke-linecap="round"
                                                        stroke-linejoin="round" stroke-width="2"
                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                    <path v-else-if="index % 4 === 1" stroke-linecap="round"
                                                        stroke-linejoin="round" stroke-width="2"
                                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                    <path v-else-if="index % 4 === 2" stroke-linecap="round"
                                                        stroke-linejoin="round" stroke-width="2"
                                                        d="M8.684 13.342c-.353 0-.654.105-.881.26l-.154.12c-.554.43-1.26.43-1.814 0a1.723 1.723 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.723 1.723 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.723 1.723 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.43.263.96.263 1.39 0l.154-.12c.554-.43 1.26-.43 1.814 0a1.723 1.723 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.723 1.723 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.723 1.723 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37-.43-.263-.96-.263-1.39 0l-.154.12c-.554.43-1.26.43-1.814 0a1.723 1.723 0 00-2.573-1.066z" />
                                                    <path v-else stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                                </svg>
                                                <span class="font-body text-xs text-light-black">{{ service }}</span>
                                            </div>
                                        </template>
                                        <template v-else>
                                            <span class="font-body text-xs text-zurit-gray">No services</span>
                                        </template>
                                    </div>
                                </td>

                                <!-- STATUS Column -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="[
                                        'inline-flex rounded-full px-3 py-1 text-xs font-semibold font-body',
                                        getStatusBadgeClass(client.status)
                                    ]">
                                        {{ formatStatus(client.status) }}
                                    </span>
                                </td>

                                <!-- NEXT SESSION Column -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <p class="font-body text-sm text-light-black">
                                        {{ formatDate(client.expected_close_date) }}
                                    </p>
                                </td>

                                <!-- ACTION Column -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-2">
                                        <!-- View Button -->
                                        <button @click="handleView(client)"
                                            class="h-8 w-8 rounded-full bg-zurit-purple flex items-center justify-center text-white hover:bg-zurit-purple/90 transition-colors focus:outline-none focus:ring-2 focus:ring-zurit-purple focus:ring-offset-2">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        <!-- Edit Button -->
                                        <button @click="handleEdit(client)"
                                            class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-300 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-if="clients.length === 0 && showEmptyState" class="p-12 text-center">
                    <p class="font-body text-lg text-zurit-gray">No leads found.</p>
                </div>

                <!-- Pagination -->
                <div v-if="clients.length > 0 && lastPage > 1" class="px-6 py-4 border-t border-gray-200 bg-white">
                    <div class="flex items-center justify-between">
                        <!-- Previous Button -->
                        <button @click="handlePageChange(currentPage - 1)" :disabled="currentPage === 1" :class="[
                            'inline-flex items-center px-4 py-2 text-sm font-body font-medium rounded-lg border transition-colors',
                            currentPage === 1
                                ? 'border-gray-300 text-gray-400 cursor-not-allowed bg-gray-50'
                                : 'border-gray-300 text-light-black bg-white hover:bg-light-gray'
                        ]">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 19l-7-7 7-7" />
                            </svg>
                            Previous
                        </button>

                        <!-- Page Numbers -->
                        <div class="flex items-center space-x-2">
                            <!-- First page -->
                            <button v-if="getPageNumbers()[0] > 1" @click="handlePageChange(1)"
                                class="px-3 py-2 text-sm font-body font-medium text-light-black hover:bg-light-gray rounded-lg transition-colors">
                                1
                            </button>
                            <span v-if="getPageNumbers()[0] > 2" class="px-2 text-zurit-gray">...</span>

                            <!-- Page numbers -->
                            <button v-for="page in getPageNumbers()" :key="page" @click="handlePageChange(page)" :class="[
                                'px-3 py-2 text-sm font-body font-medium rounded-lg transition-colors',
                                page === currentPage
                                    ? 'bg-zurit-purple text-white'
                                    : 'text-light-black hover:bg-light-gray'
                            ]">
                                {{ page }}
                            </button>

                            <!-- Last page -->
                            <span v-if="getPageNumbers()[getPageNumbers().length - 1] < lastPage - 1"
                                class="px-2 text-zurit-gray">...</span>
                            <button v-if="getPageNumbers()[getPageNumbers().length - 1] < lastPage"
                                @click="handlePageChange(lastPage)"
                                class="px-3 py-2 text-sm font-body font-medium text-light-black hover:bg-light-gray rounded-lg transition-colors">
                                {{ lastPage }}
                            </button>
                        </div>

                        <!-- Next Button -->
                        <button @click="handlePageChange(currentPage + 1)" :disabled="currentPage === lastPage" :class="[
                            'inline-flex items-center px-4 py-2 text-sm font-body font-medium rounded-lg border transition-colors',
                            currentPage === lastPage
                                ? 'border-gray-300 text-gray-400 cursor-not-allowed bg-gray-50'
                                : 'border-gray-300 text-light-black bg-white hover:bg-light-gray'
                        ]">
                            Next
                            <svg class="h-4 w-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>
