<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

// State
const activeTab = ref('eod');
const selectedDate = ref(new Date().toISOString().split('T')[0]);
const selectedWeek = ref('current');

// Helpers
const formatDate = (dateString) => {
    const date = new Date(dateString);
    const day = date.getDate();
    const month = date.toLocaleDateString('en-US', { month: 'long' });
    const year = date.getFullYear();
    return `${day} ${month} ${year}`;
};

const getCurrentWeek = () => {
    const now = new Date();
    const startOfWeek = new Date(now);
    startOfWeek.setDate(now.getDate() - now.getDay()); // Start on Sunday
    const endOfWeek = new Date(startOfWeek);
    endOfWeek.setDate(startOfWeek.getDate() + 6);

    return `${formatDate(startOfWeek)} - ${formatDate(endOfWeek)}`;
};
</script>

<template>
    <Head title="Reports" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Header Section -->
                <div class="mb-8">
                    <h1 class="font-heading text-3xl font-bold text-light-black">Sales Reports</h1>
                    <p class="mt-2 font-body text-sm text-zurit-gray">Track your daily and weekly performance metrics</p>
                </div>

                <!-- Main Content Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <!-- Tabs -->
                    <div class="border-b border-gray-200 px-6 pt-6">
                        <div class="flex items-center gap-6">
                            <button
                                @click="activeTab = 'eod'"
                                :class="[
                                    'pb-4 font-body text-sm font-medium transition-colors border-b-2',
                                    activeTab === 'eod'
                                        ? 'text-prosper border-prosper'
                                        : 'text-zurit-gray border-transparent hover:text-light-black'
                                ]"
                            >
                                End of Day Reports
                            </button>
                            <button
                                @click="activeTab = 'eow'"
                                :class="[
                                    'pb-4 font-body text-sm font-medium transition-colors border-b-2',
                                    activeTab === 'eow'
                                        ? 'text-prosper border-prosper'
                                        : 'text-zurit-gray border-transparent hover:text-light-black'
                                ]"
                            >
                                End of Week Reports
                            </button>
                        </div>
                    </div>

                    <!-- EOD Report Content -->
                    <div v-if="activeTab === 'eod'" class="p-8">
                        <!-- Date Selector -->
                        <div class="mb-8">
                            <label class="block font-body text-sm font-medium text-light-black mb-2">Select Date</label>
                            <div class="flex items-center gap-4">
                                <div class="relative max-w-xs">
                                    <input
                                        v-model="selectedDate"
                                        type="date"
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 pr-10 font-body text-sm text-light-black focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple"
                                    />
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </div>
                                <button
                                    class="inline-flex items-center gap-2 rounded-lg bg-zurit-purple px-4 py-2.5 font-body text-sm font-medium text-white hover:bg-zurit-purple/90 transition-colors"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Generate Report
                                </button>
                            </div>
                        </div>

                        <!-- Placeholder Content -->
                        <div class="space-y-6">
                            <!-- Activity Summary Section -->
                            <div class="rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-8">
                                <div class="flex items-start gap-4">
                                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg bg-zurit-purple/10">
                                        <svg class="h-6 w-6 text-zurit-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-heading text-lg font-semibold text-light-black">Activity Summary</h3>
                                        <p class="mt-1 font-body text-sm text-zurit-gray">Calls made, emails sent, meetings held, notes added</p>
                                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                            <div class="text-center">
                                                <p class="font-heading text-3xl font-bold text-zurit-purple">--</p>
                                                <p class="mt-1 font-body text-xs text-zurit-gray">Calls</p>
                                            </div>
                                            <div class="text-center">
                                                <p class="font-heading text-3xl font-bold text-prosper">--</p>
                                                <p class="mt-1 font-body text-xs text-zurit-gray">Emails</p>
                                            </div>
                                            <div class="text-center">
                                                <p class="font-heading text-3xl font-bold text-zurit-purple">--</p>
                                                <p class="mt-1 font-body text-xs text-zurit-gray">Meetings</p>
                                            </div>
                                            <div class="text-center">
                                                <p class="font-heading text-3xl font-bold text-prosper">--</p>
                                                <p class="mt-1 font-body text-xs text-zurit-gray">Notes</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Lead Progress Section -->
                            <div class="rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-8">
                                <div class="flex items-start gap-4">
                                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg bg-prosper/10">
                                        <svg class="h-6 w-6 text-prosper" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-heading text-lg font-semibold text-light-black">Lead Progress</h3>
                                        <p class="mt-1 font-body text-sm text-zurit-gray">New leads, pipeline movements, won/lost deals</p>
                                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
                                            <div class="text-center">
                                                <p class="font-heading text-3xl font-bold text-prosper">--</p>
                                                <p class="mt-1 font-body text-xs text-zurit-gray">New Leads</p>
                                            </div>
                                            <div class="text-center">
                                                <p class="font-heading text-3xl font-bold text-green-600">--</p>
                                                <p class="mt-1 font-body text-xs text-zurit-gray">Won</p>
                                            </div>
                                            <div class="text-center">
                                                <p class="font-heading text-3xl font-bold text-red-600">--</p>
                                                <p class="mt-1 font-body text-xs text-zurit-gray">Lost</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Task Completion Section -->
                            <div class="rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-8">
                                <div class="flex items-start gap-4">
                                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg bg-zurit-purple/10">
                                        <svg class="h-6 w-6 text-zurit-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-heading text-lg font-semibold text-light-black">Task Completion</h3>
                                        <p class="mt-1 font-body text-sm text-zurit-gray">Tasks completed, overdue, and created</p>
                                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
                                            <div class="text-center">
                                                <p class="font-heading text-3xl font-bold text-green-600">--</p>
                                                <p class="mt-1 font-body text-xs text-zurit-gray">Completed</p>
                                            </div>
                                            <div class="text-center">
                                                <p class="font-heading text-3xl font-bold text-red-600">--</p>
                                                <p class="mt-1 font-body text-xs text-zurit-gray">Overdue</p>
                                            </div>
                                            <div class="text-center">
                                                <p class="font-heading text-3xl font-bold text-zurit-purple">--</p>
                                                <p class="mt-1 font-body text-xs text-zurit-gray">Created</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Revenue Impact Section -->
                            <div class="rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-8">
                                <div class="flex items-start gap-4">
                                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg bg-prosper/10">
                                        <svg class="h-6 w-6 text-prosper" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-heading text-lg font-semibold text-light-black">Revenue Impact</h3>
                                        <p class="mt-1 font-body text-sm text-zurit-gray">Deals won and in negotiation</p>
                                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div class="text-center">
                                                <p class="font-heading text-3xl font-bold text-prosper">Ksh --</p>
                                                <p class="mt-1 font-body text-xs text-zurit-gray">Deals Won</p>
                                            </div>
                                            <div class="text-center">
                                                <p class="font-heading text-3xl font-bold text-zurit-purple">Ksh --</p>
                                                <p class="mt-1 font-body text-xs text-zurit-gray">In Negotiation</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center gap-3 pt-4">
                                <button
                                    disabled
                                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 font-body text-sm font-medium text-gray-400 cursor-not-allowed"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Download PDF
                                </button>
                                <button
                                    disabled
                                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 font-body text-sm font-medium text-gray-400 cursor-not-allowed"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    Send to Supervisor
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- EOW Report Content -->
                    <div v-if="activeTab === 'eow'" class="p-8">
                        <!-- Week Selector -->
                        <div class="mb-8">
                            <label class="block font-body text-sm font-medium text-light-black mb-2">Select Week</label>
                            <div class="flex items-center gap-4">
                                <select
                                    v-model="selectedWeek"
                                    class="max-w-xs rounded-lg border border-gray-300 px-4 py-2.5 font-body text-sm text-light-black focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple"
                                >
                                    <option value="current">Current Week ({{ getCurrentWeek() }})</option>
                                    <option value="last">Last Week</option>
                                    <option value="custom">Custom Week...</option>
                                </select>
                                <button
                                    class="inline-flex items-center gap-2 rounded-lg bg-zurit-purple px-4 py-2.5 font-body text-sm font-medium text-white hover:bg-zurit-purple/90 transition-colors"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Generate Report
                                </button>
                            </div>
                        </div>

                        <!-- Placeholder Content -->
                        <div class="space-y-6">
                            <!-- Weekly Summary Section -->
                            <div class="rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-8">
                                <div class="flex items-start gap-4">
                                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-zurit-purple to-prosper">
                                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-heading text-lg font-semibold text-light-black">Weekly Summary</h3>
                                        <p class="mt-1 font-body text-sm text-zurit-gray">Aggregated metrics for the entire week</p>
                                        <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-4">
                                            <div class="text-center">
                                                <p class="font-heading text-2xl font-bold text-zurit-purple">--</p>
                                                <p class="mt-1 font-body text-xs text-zurit-gray">Total Activities</p>
                                            </div>
                                            <div class="text-center">
                                                <p class="font-heading text-2xl font-bold text-prosper">--</p>
                                                <p class="mt-1 font-body text-xs text-zurit-gray">New Leads</p>
                                            </div>
                                            <div class="text-center">
                                                <p class="font-heading text-2xl font-bold text-green-600">--</p>
                                                <p class="mt-1 font-body text-xs text-zurit-gray">Deals Won</p>
                                            </div>
                                            <div class="text-center">
                                                <p class="font-heading text-2xl font-bold text-prosper">Ksh --</p>
                                                <p class="mt-1 font-body text-xs text-zurit-gray">Revenue</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Week-over-Week Comparison -->
                            <div class="rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-8">
                                <div class="flex items-start gap-4">
                                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg bg-zurit-purple/10">
                                        <svg class="h-6 w-6 text-zurit-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-heading text-lg font-semibold text-light-black">Week-over-Week Comparison</h3>
                                        <p class="mt-1 font-body text-sm text-zurit-gray">Performance trends compared to previous week</p>
                                        <div class="mt-4 h-48 flex items-center justify-center border-2 border-dashed border-gray-300 rounded-lg bg-white">
                                            <p class="font-body text-sm text-zurit-gray">Chart will appear here</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pipeline Health -->
                            <div class="rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-8">
                                <div class="flex items-start gap-4">
                                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg bg-prosper/10">
                                        <svg class="h-6 w-6 text-prosper" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-heading text-lg font-semibold text-light-black">Pipeline Health</h3>
                                        <p class="mt-1 font-body text-sm text-zurit-gray">Lead distribution across pipeline stages</p>
                                        <div class="mt-4 grid grid-cols-2 sm:grid-cols-5 gap-3">
                                            <div class="rounded-lg bg-white p-4 text-center border border-gray-200">
                                                <p class="font-heading text-xl font-bold text-gray-400">--</p>
                                                <p class="mt-1 font-body text-xs text-zurit-gray">New</p>
                                            </div>
                                            <div class="rounded-lg bg-white p-4 text-center border border-gray-200">
                                                <p class="font-heading text-xl font-bold text-gray-400">--</p>
                                                <p class="mt-1 font-body text-xs text-zurit-gray">Outreach</p>
                                            </div>
                                            <div class="rounded-lg bg-white p-4 text-center border border-gray-200">
                                                <p class="font-heading text-xl font-bold text-gray-400">--</p>
                                                <p class="mt-1 font-body text-xs text-zurit-gray">Follow-up</p>
                                            </div>
                                            <div class="rounded-lg bg-white p-4 text-center border border-gray-200">
                                                <p class="font-heading text-xl font-bold text-gray-400">--</p>
                                                <p class="mt-1 font-body text-xs text-zurit-gray">Negotiation</p>
                                            </div>
                                            <div class="rounded-lg bg-white p-4 text-center border border-gray-200">
                                                <p class="font-heading text-xl font-bold text-green-600">--</p>
                                                <p class="mt-1 font-body text-xs text-zurit-gray">Won</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Top Performing Products -->
                            <div class="rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-8">
                                <div class="flex items-start gap-4">
                                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg bg-zurit-purple/10">
                                        <svg class="h-6 w-6 text-zurit-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-heading text-lg font-semibold text-light-black">Top Performing Products</h3>
                                        <p class="mt-1 font-body text-sm text-zurit-gray">Products with highest engagement and sales</p>
                                        <div class="mt-4 space-y-3">
                                            <div class="flex items-center justify-between rounded-lg bg-white p-4 border border-gray-200">
                                                <span class="font-body text-sm text-zurit-gray">Product data will appear here</span>
                                                <span class="font-body text-sm font-semibold text-gray-400">--</span>
                                            </div>
                                            <div class="flex items-center justify-between rounded-lg bg-white p-4 border border-gray-200">
                                                <span class="font-body text-sm text-zurit-gray">Product data will appear here</span>
                                                <span class="font-body text-sm font-semibold text-gray-400">--</span>
                                            </div>
                                            <div class="flex items-center justify-between rounded-lg bg-white p-4 border border-gray-200">
                                                <span class="font-body text-sm text-zurit-gray">Product data will appear here</span>
                                                <span class="font-body text-sm font-semibold text-gray-400">--</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center gap-3 pt-4">
                                <button
                                    disabled
                                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 font-body text-sm font-medium text-gray-400 cursor-not-allowed"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Download PDF
                                </button>
                                <button
                                    disabled
                                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 font-body text-sm font-medium text-gray-400 cursor-not-allowed"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    Send to Supervisor
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
