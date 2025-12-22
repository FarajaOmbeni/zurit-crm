<script setup>
defineProps({
    leadsToday: {
        type: Number,
        default: 0,
    },
    clientsToday: {
        type: Number,
        default: 0,
    },
    revenueToday: {
        type: Number,
        default: 0,
    },
    revenueThisMonth: {
        type: Number,
        default: 0,
    },
    conversionRate: {
        type: Number,
        default: 0,
    },
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount).replace('KES', 'Ksh');
};
</script>

<template>
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Leads Today Card -->
        <div class="relative overflow-hidden rounded-lg bg-zurit-purple p-6 shadow-lg">
            <div class="flex items-start justify-between">
                <div class="flex items-center space-x-2">
                    <svg
                        class="h-6 w-6 text-white opacity-80"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                        />
                    </svg>
                    <h3 class="font-body text-sm font-medium text-white">Leads Today</h3>
                </div>
            </div>
            <div class="mt-2">
                <span class="inline-flex rounded-md bg-white/20 px-2 py-1 text-xs font-medium text-white">
                    Today
                </span>
            </div>
            <div class="mt-4">
                <p class="font-heading text-4xl font-bold text-white">
                    {{ leadsToday }}
                </p>
            </div>
        </div>

        <!-- Clients Today Card -->
        <div class="relative overflow-hidden rounded-lg bg-prosper p-6 shadow-lg">
            <div class="flex items-start justify-between">
                <div class="flex items-center space-x-2">
                    <svg
                        class="h-6 w-6 text-white opacity-80"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                        />
                    </svg>
                    <h3 class="font-body text-sm font-medium text-white">Clients Today</h3>
                </div>
            </div>
            <div class="mt-2">
                <span class="inline-flex rounded-md bg-white/20 px-2 py-1 text-xs font-medium text-white">
                    Today
                </span>
            </div>
            <div class="mt-4">
                <p class="font-heading text-4xl font-bold text-white">
                    {{ clientsToday }}
                </p>
            </div>
        </div>

        <!-- Revenue Today Card -->
        <div class="relative overflow-hidden rounded-lg bg-zurit-purple p-6 shadow-lg">
            <div class="flex items-start justify-between">
                <div class="flex items-center space-x-2">
                    <svg
                        class="h-6 w-6 text-white opacity-80"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                    <h3 class="font-body text-sm font-medium text-white">Revenue Today</h3>
                </div>
            </div>
            <div class="mt-2">
                <span class="inline-flex rounded-md bg-white/20 px-2 py-1 text-xs font-medium text-white">
                    Today
                </span>
            </div>
            <div class="mt-4">
                <p class="font-heading text-4xl font-bold text-white">
                    {{ formatCurrency(revenueToday) }}
                </p>
            </div>
        </div>

        <!-- Revenue This Month Card -->
        <div class="relative overflow-hidden rounded-lg bg-gradient-to-r from-prosper to-zurit-purple p-6 shadow-lg">
            <div class="flex items-start justify-between">
                <div class="flex items-center space-x-2">
                    <svg
                        class="h-6 w-6 text-white opacity-80"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                        />
                    </svg>
                    <h3 class="font-body text-sm font-medium text-white">Revenue This Month</h3>
                </div>
            </div>
            <div class="mt-2">
                <span class="inline-flex rounded-md bg-white/20 px-2 py-1 text-xs font-medium text-white">
                    This Month
                </span>
            </div>
            <div class="mt-4">
                <p class="font-heading text-4xl font-bold text-white">
                    {{ formatCurrency(revenueThisMonth) }}
                </p>
            </div>
        </div>
    </div>
</template>

