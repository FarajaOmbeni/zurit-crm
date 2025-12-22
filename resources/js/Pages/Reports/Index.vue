<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';

// State
const activeTab = ref('eod');
const selectedDate = ref(new Date().toISOString().split('T')[0]);
const selectedWeek = ref('current');
const loading = ref(false);
const generating = ref(false);
const downloading = ref(false);
const sending = ref(false);

// Report data
const eodReportData = ref(null);
const eowReportData = ref(null);

// Toast notifications
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success'); // 'success' or 'error'

// Helpers
const formatDate = (dateString) => {
    const date = new Date(dateString);
    const day = date.getDate();
    const month = date.toLocaleDateString('en-US', { month: 'long' });
    const year = date.getFullYear();
    return `${day} ${month} ${year}`;
};

const formatCurrency = (value) => {
    if (!value) return 'Ksh 0';
    return `Ksh ${parseFloat(value).toLocaleString('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
};

// Monday-Friday week calculation
const getMondayOfWeek = (date) => {
    const d = new Date(date);
    const day = d.getDay();
    const diff = d.getDate() - day + (day === 0 ? -6 : 1); // Adjust when day is Sunday
    return new Date(d.setDate(diff));
};

const getFridayOfWeek = (date) => {
    const monday = getMondayOfWeek(date);
    const friday = new Date(monday);
    friday.setDate(monday.getDate() + 4);
    return friday;
};

const getCurrentWeek = () => {
    const now = new Date();
    const monday = getMondayOfWeek(now);
    const friday = getFridayOfWeek(now);
    return `${formatDate(monday)} - ${formatDate(friday)}`;
};

const getLastWeek = () => {
    const now = new Date();
    now.setDate(now.getDate() - 7);
    const monday = getMondayOfWeek(now);
    const friday = getFridayOfWeek(now);
    return `${formatDate(monday)} - ${formatDate(friday)}`;
};

const weekRange = computed(() => {
    if (selectedWeek.value === 'current') {
        const monday = getMondayOfWeek(new Date());
        const friday = getFridayOfWeek(new Date());
        return { start: monday, end: friday };
    } else if (selectedWeek.value === 'last') {
        const now = new Date();
        now.setDate(now.getDate() - 7);
        const monday = getMondayOfWeek(now);
        const friday = getFridayOfWeek(now);
        return { start: monday, end: friday };
    }
    return null;
});

// API Functions
const generateEodReport = async () => {
    try {
        generating.value = true;
        const response = await axios.post('/api/reports/eod', {
            date: selectedDate.value,
            highlights: '',
            challenges: ''
        });

        eodReportData.value = response.data;
        displayToast('EOD report generated successfully', 'success');
    } catch (error) {
        console.error('Error generating EOD report:', error);
        displayToast(error.response?.data?.message || 'Failed to generate EOD report', 'error');
    } finally {
        generating.value = false;
    }
};

const generateEowReport = async () => {
    try {
        generating.value = true;
        const range = weekRange.value;
        if (!range) {
            displayToast('Please select a valid week', 'error');
            return;
        }

        const response = await axios.post('/api/reports/custom', {
            start_date: range.start.toISOString().split('T')[0],
            end_date: range.end.toISOString().split('T')[0],
            highlights: '',
            challenges: ''
        });

        eowReportData.value = response.data;
        displayToast('EOW report generated successfully', 'success');
    } catch (error) {
        console.error('Error generating EOW report:', error);
        displayToast(error.response?.data?.message || 'Failed to generate EOW report', 'error');
    } finally {
        generating.value = false;
    }
};

const downloadPdf = async (reportId) => {
    try {
        downloading.value = true;
        const response = await axios.get(`/api/reports/${reportId}/download`, {
            responseType: 'blob'
        });

        // Create download link
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `report-${selectedDate.value}.pdf`);
        document.body.appendChild(link);
        link.click();
        link.remove();

        displayToast('Report downloaded successfully', 'success');
    } catch (error) {
        console.error('Error downloading PDF:', error);
        displayToast('Failed to download report', 'error');
    } finally {
        downloading.value = false;
    }
};

const sendToSupervisor = async (reportId) => {
    try {
        sending.value = true;
        const response = await axios.post(`/api/reports/${reportId}/send-email`, {
            highlights: '',
            challenges: ''
        });

        displayToast(response.data.message || 'Report sent successfully', 'success');
    } catch (error) {
        console.error('Error sending report:', error);
        displayToast(error.response?.data?.message || 'Failed to send report', 'error');
    } finally {
        sending.value = false;
    }
};

const displayToast = (message, type = 'success') => {
    toastMessage.value = message;
    toastType.value = type;
    showToast.value = true;
    setTimeout(() => {
        showToast.value = false;
    }, 3000);
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
                                        :max="new Date().toISOString().split('T')[0]"
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 pr-10 font-body text-sm text-light-black focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple"
                                    />
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </div>
                                <button
                                    @click="generateEodReport"
                                    :disabled="generating"
                                    class="inline-flex items-center gap-2 rounded-lg bg-zurit-purple px-4 py-2.5 font-body text-sm font-medium text-white hover:bg-zurit-purple/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <svg v-if="!generating" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <svg v-else class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ generating ? 'Generating...' : 'Generate Report' }}
                                </button>
                            </div>
                        </div>

                        <!-- Report Content -->
                        <div v-if="eodReportData" class="space-y-8">
                            <!-- Report Title -->
                            <div class="text-center border-b pb-6">
                                <h2 class="font-heading text-2xl font-bold text-light-black">
                                    Zurit Consulting - {{ eodReportData.salesperson_name }} Report
                                </h2>
                                <p class="mt-2 font-body text-lg text-zurit-gray">{{ formatDate(eodReportData.date) }}</p>
                            </div>

                            <!-- 1. Outreach Summary Section -->
                            <div class="rounded-xl border-2 border-gray-300 bg-gray-50 p-8">
                                <h3 class="font-heading text-xl font-semibold text-light-black mb-4">Outreach Summary</h3>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="font-body text-base text-light-black font-medium">Leads Contacted:</span>
                                        <span class="font-heading text-2xl font-bold text-zurit-purple">
                                            {{ eodReportData.data.outreach_summary?.total_contacted || 0 }}
                                        </span>
                                    </div>
                                    <div v-if="eodReportData.data.outreach_summary?.contacted_leads?.length > 0" class="ml-6 mt-3 space-y-1">
                                        <div
                                            v-for="(lead, index) in eodReportData.data.outreach_summary.contacted_leads"
                                            :key="index"
                                            class="text-sm"
                                        >
                                            <span class="font-body text-zurit-gray">- {{ lead.display_name }}</span>
                                        </div>
                                    </div>
                                    <div v-else class="ml-6 text-sm font-body text-zurit-gray italic">
                                        No leads contacted
                                    </div>
                                </div>
                            </div>

                            <!-- 2. Scheme Engagement Updates (Table) -->
                            <div class="rounded-xl border-2 border-gray-300 bg-gray-50 p-8">
                                <h3 class="font-heading text-xl font-semibold text-light-black mb-4">Scheme Engagement Updates</h3>
                                <div v-if="eodReportData.data.schemes_engagement?.length > 0" class="overflow-x-auto">
                                    <table class="w-full border-collapse">
                                        <thead>
                                            <tr class="border-b-2 border-gray-300">
                                                <th class="text-left py-3 px-4 font-body text-sm font-semibold text-light-black">Contact Person</th>
                                                <th class="text-left py-3 px-4 font-body text-sm font-semibold text-light-black">Phone Number</th>
                                                <th class="text-left py-3 px-4 font-body text-sm font-semibold text-light-black">Feedback</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="(engagement, index) in eodReportData.data.schemes_engagement"
                                                :key="index"
                                                class="border-b border-gray-200 hover:bg-white transition-colors"
                                            >
                                                <td class="py-3 px-4 font-body text-sm text-light-black">{{ engagement.contact_person }}</td>
                                                <td class="py-3 px-4 font-body text-sm text-light-black">{{ engagement.phone }}</td>
                                                <td class="py-3 px-4 font-body text-sm text-zurit-gray">{{ engagement.feedback }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div v-else class="py-8 text-center">
                                    <p class="font-body text-sm text-zurit-gray">No engagement updates for this period</p>
                                </div>
                            </div>

                            <!-- 3. Program Sales Update (Table) -->
                            <div class="rounded-xl border-2 border-gray-300 bg-gray-50 p-8">
                                <h3 class="font-heading text-xl font-semibold text-light-black mb-4">Program Sales Update</h3>
                                <div v-if="eodReportData.data.won_deals?.length > 0" class="overflow-x-auto">
                                    <table class="w-full border-collapse">
                                        <thead>
                                            <tr class="border-b-2 border-gray-300">
                                                <th class="text-left py-3 px-4 font-body text-sm font-semibold text-light-black">Client/Lead Name</th>
                                                <th class="text-left py-3 px-4 font-body text-sm font-semibold text-light-black">Product</th>
                                                <th class="text-right py-3 px-4 font-body text-sm font-semibold text-light-black">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="(deal, index) in eodReportData.data.won_deals"
                                                :key="index"
                                                class="border-b border-gray-200 hover:bg-white transition-colors"
                                            >
                                                <td class="py-3 px-4 font-body text-sm text-light-black">{{ deal.client_name }}</td>
                                                <td class="py-3 px-4 font-body text-sm text-light-black">{{ deal.product }}</td>
                                                <td class="py-3 px-4 font-body text-sm font-semibold text-prosper text-right">{{ formatCurrency(deal.amount) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div v-else class="py-8 text-center">
                                    <p class="font-body text-sm text-zurit-gray">No sales recorded for this period</p>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center gap-3 pt-4">
                                <button
                                    @click="downloadPdf(eodReportData.report.id)"
                                    :disabled="downloading"
                                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 font-body text-sm font-medium text-light-black hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <svg v-if="!downloading" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <svg v-else class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ downloading ? 'Downloading...' : 'Download PDF' }}
                                </button>
                                <button
                                    @click="sendToSupervisor(eodReportData.report.id)"
                                    :disabled="sending"
                                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 font-body text-sm font-medium text-light-black hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <svg v-if="!sending" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <svg v-else class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ sending ? 'Sending...' : 'Send to Supervisor' }}
                                </button>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 font-body text-sm font-medium text-light-black">No report generated</h3>
                            <p class="mt-1 font-body text-sm text-zurit-gray">Select a date and click "Generate Report" to view your EOD report.</p>
                        </div>
                    </div>

                    <!-- EOW Report Content -->
                    <div v-if="activeTab === 'eow'" class="p-8">
                        <!-- Week Selector -->
                        <div class="mb-8">
                            <label class="block font-body text-sm font-medium text-light-black mb-2">Select Week (Monday - Friday)</label>
                            <div class="flex items-center gap-4">
                                <select
                                    v-model="selectedWeek"
                                    class="max-w-xs rounded-lg border border-gray-300 px-4 py-2.5 font-body text-sm text-light-black focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple"
                                >
                                    <option value="current">Current Week ({{ getCurrentWeek() }})</option>
                                    <option value="last">Last Week ({{ getLastWeek() }})</option>
                                </select>
                                <button
                                    @click="generateEowReport"
                                    :disabled="generating"
                                    class="inline-flex items-center gap-2 rounded-lg bg-zurit-purple px-4 py-2.5 font-body text-sm font-medium text-white hover:bg-zurit-purple/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <svg v-if="!generating" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <svg v-else class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ generating ? 'Generating...' : 'Generate Report' }}
                                </button>
                            </div>
                        </div>

                        <!-- Report Content -->
                        <div v-if="eowReportData" class="space-y-8">
                            <!-- Report Title -->
                            <div class="text-center border-b pb-6">
                                <h2 class="font-heading text-2xl font-bold text-light-black">
                                    Zurit Consulting - {{ eowReportData.salesperson_name }} Report
                                </h2>
                                <p class="mt-2 font-body text-lg text-zurit-gray">
                                    {{ formatDate(eowReportData.start_date) }} - {{ formatDate(eowReportData.end_date) }}
                                </p>
                            </div>

                            <!-- 1. Outreach Summary Section -->
                            <div class="rounded-xl border-2 border-gray-300 bg-gray-50 p-8">
                                <h3 class="font-heading text-xl font-semibold text-light-black mb-4">Outreach Summary</h3>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="font-body text-base text-light-black font-medium">Leads Contacted:</span>
                                        <span class="font-heading text-2xl font-bold text-zurit-purple">
                                            {{ eowReportData.data.outreach_summary?.total_contacted || 0 }}
                                        </span>
                                    </div>
                                    <div v-if="eowReportData.data.outreach_summary?.contacted_leads?.length > 0" class="ml-6 mt-3 space-y-1">
                                        <div
                                            v-for="(lead, index) in eowReportData.data.outreach_summary.contacted_leads"
                                            :key="index"
                                            class="text-sm"
                                        >
                                            <span class="font-body text-zurit-gray">- {{ lead.display_name }}</span>
                                        </div>
                                    </div>
                                    <div v-else class="ml-6 text-sm font-body text-zurit-gray italic">
                                        No leads contacted
                                    </div>
                                </div>
                            </div>

                            <!-- 2. Scheme Engagement Updates (Table) -->
                            <div class="rounded-xl border-2 border-gray-300 bg-gray-50 p-8">
                                <h3 class="font-heading text-xl font-semibold text-light-black mb-4">Scheme Engagement Updates</h3>
                                <div v-if="eowReportData.data.schemes_engagement?.length > 0" class="overflow-x-auto">
                                    <table class="w-full border-collapse">
                                        <thead>
                                            <tr class="border-b-2 border-gray-300">
                                                <th class="text-left py-3 px-4 font-body text-sm font-semibold text-light-black">Contact Person</th>
                                                <th class="text-left py-3 px-4 font-body text-sm font-semibold text-light-black">Phone Number</th>
                                                <th class="text-left py-3 px-4 font-body text-sm font-semibold text-light-black">Feedback</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="(engagement, index) in eowReportData.data.schemes_engagement"
                                                :key="index"
                                                class="border-b border-gray-200 hover:bg-white transition-colors"
                                            >
                                                <td class="py-3 px-4 font-body text-sm text-light-black">{{ engagement.contact_person }}</td>
                                                <td class="py-3 px-4 font-body text-sm text-light-black">{{ engagement.phone }}</td>
                                                <td class="py-3 px-4 font-body text-sm text-zurit-gray">{{ engagement.feedback }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div v-else class="py-8 text-center">
                                    <p class="font-body text-sm text-zurit-gray">No engagement updates for this period</p>
                                </div>
                            </div>

                            <!-- 3. Program Sales Update (Table) -->
                            <div class="rounded-xl border-2 border-gray-300 bg-gray-50 p-8">
                                <h3 class="font-heading text-xl font-semibold text-light-black mb-4">Program Sales Update</h3>
                                <div v-if="eowReportData.data.won_deals?.length > 0" class="overflow-x-auto">
                                    <table class="w-full border-collapse">
                                        <thead>
                                            <tr class="border-b-2 border-gray-300">
                                                <th class="text-left py-3 px-4 font-body text-sm font-semibold text-light-black">Client/Lead Name</th>
                                                <th class="text-left py-3 px-4 font-body text-sm font-semibold text-light-black">Product</th>
                                                <th class="text-right py-3 px-4 font-body text-sm font-semibold text-light-black">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="(deal, index) in eowReportData.data.won_deals"
                                                :key="index"
                                                class="border-b border-gray-200 hover:bg-white transition-colors"
                                            >
                                                <td class="py-3 px-4 font-body text-sm text-light-black">{{ deal.client_name }}</td>
                                                <td class="py-3 px-4 font-body text-sm text-light-black">{{ deal.product }}</td>
                                                <td class="py-3 px-4 font-body text-sm font-semibold text-prosper text-right">{{ formatCurrency(deal.amount) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div v-else class="py-8 text-center">
                                    <p class="font-body text-sm text-zurit-gray">No sales recorded for this period</p>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center gap-3 pt-4">
                                <button
                                    @click="downloadPdf(eowReportData.report.id)"
                                    :disabled="downloading"
                                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 font-body text-sm font-medium text-light-black hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <svg v-if="!downloading" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <svg v-else class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ downloading ? 'Downloading...' : 'Download PDF' }}
                                </button>
                                <button
                                    @click="sendToSupervisor(eowReportData.report.id)"
                                    :disabled="sending"
                                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 font-body text-sm font-medium text-light-black hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <svg v-if="!sending" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <svg v-else class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ sending ? 'Sending...' : 'Send to Supervisor' }}
                                </button>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 font-body text-sm font-medium text-light-black">No report generated</h3>
                            <p class="mt-1 font-body text-sm text-zurit-gray">Select a week and click "Generate Report" to view your EOW report.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast Notification -->
        <div
            v-if="showToast"
            class="fixed bottom-4 right-4 z-50 animate-slide-up"
        >
            <div
                :class="[
                    'rounded-lg px-6 py-4 shadow-lg',
                    toastType === 'success' ? 'bg-green-500' : 'bg-red-500'
                ]"
            >
                <div class="flex items-center gap-3">
                    <svg v-if="toastType === 'success'" class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <svg v-else class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <p class="font-body text-sm font-medium text-white">{{ toastMessage }}</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@keyframes slide-up {
    from {
        transform: translateY(100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.animate-slide-up {
    animation: slide-up 0.3s ease-out;
}
</style>
