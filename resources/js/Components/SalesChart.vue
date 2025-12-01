<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const salesData = ref([]);
const loading = ref(false);
const hoveredPoint = ref(null);
const showModal = ref(false);
const reportData = ref(null);
const modalLoading = ref(false);
const reportError = ref(null);
const selectedDate = ref(null);
const availableDates = ref([]);
const loadingDates = ref(false);
const downloading = ref(false);
const sending = ref(false);
const emailSuccess = ref(false);
const userHighlights = ref('');
const userChallenges = ref('');

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
        const response = await axios.get('/api/leads', {
            params: {
                status: 'won',
                include_clients: true, // Include clients in the response
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
                // Create date string in local timezone to match
                const wonDateString = new Date(wonDate.getFullYear(), wonDate.getMonth(), wonDate.getDate())
                    .toISOString().split('T')[0];
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
    // Fixed scale: 0-10 on Y-axis, chart height is 160
    return 160 - (sales / 10) * 160;
};

const showTooltip = (index) => {
    hoveredPoint.value = index;
};

const hideTooltip = () => {
    hoveredPoint.value = null;
};

const fetchReport = async (date = null) => {
    modalLoading.value = true;
    reportError.value = null;
    try {
        let response;
        if (date) {
            // Fetch report for specific date
            response = await axios.get('/api/reports/by-date', {
                params: { date: date }
            });
        } else {
            // Fetch the latest pre-generated report
            response = await axios.get('/api/reports/latest');
        }
        reportData.value = response.data.report;
        selectedDate.value = reportData.value.report_date;
    } catch (error) {
        console.error('Error fetching report:', error);
        if (error.response && error.response.status === 404) {
            if (date) {
                reportError.value = `No report available for ${formatDate(date)}. Reports are generated at the end of each day.`;
            } else {
                reportError.value = 'No report available yet. Reports are generated at the end of each day.';
            }
        } else {
            reportError.value = 'Failed to load report. Please try again later.';
        }
    } finally {
        modalLoading.value = false;
    }
};

const fetchAvailableDates = async () => {
    loadingDates.value = true;
    try {
        // Fetch all reports to get available dates
        const response = await axios.get('/api/reports', {
            params: {
                per_page: 100,
                type: 'eod'
            }
        });
        availableDates.value = response.data.data.map(report => report.report_date);
    } catch (error) {
        console.error('Error fetching available dates:', error);
        availableDates.value = [];
    } finally {
        loadingDates.value = false;
    }
};

const onDateChange = (event) => {
    const newDate = event.target.value;
    if (newDate) {
        fetchReport(newDate);
    }
};

const openModal = () => {
    showModal.value = true;
    fetchReport();
    fetchAvailableDates();
};

const closeModal = () => {
    showModal.value = false;
    reportData.value = null;
    reportError.value = null;
    selectedDate.value = null;
    availableDates.value = [];
    emailSuccess.value = false;
    userHighlights.value = '';
    userChallenges.value = '';
};

const downloadReport = async () => {
    if (!reportData.value || !reportData.value.id) return;

    downloading.value = true;
    try {
        const response = await axios.get(`/api/reports/${reportData.value.id}/download`, {
            params: {
                highlights: userHighlights.value,
                challenges: userChallenges.value
            },
            responseType: 'blob'
        });

        // Extract filename from Content-Disposition header
        const contentDisposition = response.headers['content-disposition'];
        let filename = `sales-report-${reportData.value.report_date}.pdf`;

        if (contentDisposition) {
            const filenameMatch = contentDisposition.match(/filename="?(.+)"?/i);
            if (filenameMatch && filenameMatch[1]) {
                filename = filenameMatch[1].replace(/"/g, '');
            }
        }

        // Create download link
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', filename);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error('Error downloading report:', error);
        alert('Failed to download report. Please try again.');
    } finally {
        downloading.value = false;
    }
};

const sendReportEmail = async () => {
    if (!reportData.value || !reportData.value.id) return;

    sending.value = true;
    emailSuccess.value = false;
    try {
        const response = await axios.post(`/api/reports/${reportData.value.id}/send-email`, {
            recipient: 'ombenifaraja@gmail.com',
            highlights: userHighlights.value,
            challenges: userChallenges.value
        });

        if (response.data.success) {
            emailSuccess.value = true;
            setTimeout(() => {
                emailSuccess.value = false;
            }, 3000);
        }
    } catch (error) {
        console.error('Error sending report:', error);
        alert('Failed to send report. Please try again.');
    } finally {
        sending.value = false;
    }
};

const formatTimestamp = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
        minimumFractionDigits: 0
    }).format(value);
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
};

onMounted(() => {
    fetchSalesData();
});
</script>

<template>
    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-100 h-[500px] flex flex-col">
        <!-- Title -->
        <h2 class="text-lg font-semibold text-gray-900 mb-6">Sales (Last 5 Days)</h2>

        <div v-if="loading" class="text-center py-8 text-gray-500 flex-1 flex items-center justify-center">
            Loading...
        </div>

        <div v-else class="flex-1 flex flex-col">
            <!-- Chart Container -->
            <div class="relative flex-1 min-h-0">
                <!-- Y-axis label -->
                <div
                    class="absolute -left-6 top-1/2 -translate-y-1/2 -rotate-90 text-xs text-gray-600 font-normal whitespace-nowrap">
                    No. of Sales
                </div>

                <!-- Y-axis labels (0, 1, 2, 4, 6, 8, 10) -->
                <div class="absolute left-8 top-0 bottom-10 flex flex-col justify-between text-xs text-gray-600">
                    <span>10</span>
                    <span>8</span>
                    <span>6</span>
                    <span>4</span>
                    <span>2</span>
                    <span>1</span>
                    <span>0</span>
                </div>

                <!-- Chart Area -->
                <div class="ml-14 h-full pb-10 relative">
                    <svg class="w-full h-full" viewBox="0 0 400 160" preserveAspectRatio="none">
                        <!-- Grid lines (horizontal) -->
                        <line v-for="i in 7" :key="i" :x1="0" :y1="(i - 1) * 26.67" :x2="400" :y2="(i - 1) * 26.67"
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
                            fill="#8B5CF6" class="cursor-pointer hover:r-6 transition-all"
                            @mouseenter="showTooltip(index)" @mouseleave="hideTooltip" />
                    </svg>

                    <!-- Tooltips -->
                    <div v-for="(item, index) in salesData" :key="`tooltip-${index}`" v-show="hoveredPoint === index"
                        class="absolute bg-gray-900 text-white text-xs px-2 py-1 rounded shadow-lg pointer-events-none z-10"
                        :style="{
                            left: `${((index / (salesData.length - 1)) * 100)}%`,
                            top: `${((getYPosition(item.sales) / 160) * 100) + 5}%`,
                            transform: 'translate(-50%, 0)'
                        }">
                        {{ item.sales }} {{ item.sales === 1 ? 'sale' : 'sales' }}
                        <div class="absolute left-1/2 -top-1 w-2 h-2 bg-gray-900 transform rotate-45 -translate-x-1/2">
                        </div>
                    </div>
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
            <div class="mt-6 pt-4 border-t border-gray-100 flex justify-end">
                <!-- View Report Button -->
                <button @click="openModal"
                    class="bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition shadow-sm">
                    View Report
                </button>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50"
            @click="closeModal">
            <div class="bg-white rounded-lg shadow-xl max-w-6xl w-full max-h-[90vh] overflow-hidden" @click.stop>
                <!-- Modal Header -->
                <div class="flex flex-col p-6 border-b border-gray-200 bg-gradient-to-r from-purple-600 to-purple-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <h3 class="text-xl font-semibold text-white">
                                Sales Report
                            </h3>
                            <!-- Date Picker -->
                            <div class="flex items-center gap-2">
                                <label for="report-date" class="text-white text-sm">Date:</label>
                                <input type="date" id="report-date" :value="selectedDate" @change="onDateChange"
                                    :max="new Date().toISOString().split('T')[0]"
                                    class="px-3 py-1.5 text-sm rounded-lg border border-purple-400 bg-white text-gray-900 focus:ring-2 focus:ring-purple-300 focus:border-transparent transition" />
                            </div>
                        </div>
                        <button @click="closeModal" class="text-white hover:text-gray-200 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <!-- Timestamp Subtitle -->
                    <div v-if="reportData && reportData.created_at" class="mt-2 text-sm text-purple-100 opacity-90">
                        Generated: {{ formatTimestamp(reportData.created_at) }}
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="overflow-y-auto max-h-[calc(90vh-180px)] modal-scrollbar">
                    <!-- Loading State -->
                    <div v-if="modalLoading" class="p-12">
                        <div class="flex flex-col items-center justify-center">
                            <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-purple-600 mb-4"></div>
                            <p class="text-gray-600">Loading report...</p>
                        </div>
                    </div>

                    <!-- Error State -->
                    <div v-else-if="reportError" class="p-12 text-center">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-lg font-medium text-gray-900 mb-2">{{ reportError }}</p>
                        <p class="text-sm text-gray-500">Try again later or contact support if the issue persists.</p>
                    </div>

                    <!-- Report Data -->
                    <div v-else-if="reportData" class="p-6 space-y-8">
                        <!-- Outreach Summary -->
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Outreach Summary</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div
                                    class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
                                    <div class="text-sm text-blue-600 font-medium mb-1">Schemes Contacted</div>
                                    <div class="text-3xl font-bold text-blue-700">{{
                                        reportData.outreach_summary.schemes_contacted || 0 }}</div>
                                </div>
                                <div
                                    class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                                    <div class="text-sm text-green-600 font-medium mb-1">Newly Engaged</div>
                                    <div class="text-3xl font-bold text-green-700">{{
                                        reportData.outreach_summary.schemes_newly_engaged || 0 }}</div>
                                </div>
                                <div
                                    class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-4 border border-orange-200">
                                    <div class="text-sm text-orange-600 font-medium mb-1">Follow-ups</div>
                                    <div class="text-3xl font-bold text-orange-700">{{
                                        reportData.outreach_summary.follow_ups_conducted || 0 }}</div>
                                </div>
                                <div
                                    class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200">
                                    <div class="text-sm text-purple-600 font-medium mb-1">Active Pipeline</div>
                                    <div class="text-3xl font-bold text-purple-700">{{
                                        reportData.outreach_summary.active_pipeline || 0 }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Won Deals -->
                        <div v-if="reportData.won_deals && reportData.won_deals.length > 0">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Won Deals</h4>
                            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                                <table class="w-full">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Company
                                            </th>
                                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Product
                                            </th>
                                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Value
                                            </th>
                                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Date
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <tr v-for="deal in reportData.won_deals" :key="deal.id"
                                            class="hover:bg-gray-50">
                                            <td class="py-3 px-4 text-sm font-medium text-gray-900">{{ deal.company }}
                                            </td>
                                            <td class="py-3 px-4 text-sm text-gray-600">{{ deal.product || 'N/A' }}</td>
                                            <td class="py-3 px-4 text-sm font-semibold text-green-600">{{
                                                formatCurrency(deal.value) }}</td>
                                            <td class="py-3 px-4 text-sm text-gray-600">{{
                                                formatDate(deal.actual_close_date) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- New Leads -->
                        <div v-if="reportData.new_leads && reportData.new_leads.length > 0">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">New Leads</h4>
                            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                                <table class="w-full">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Company
                                            </th>
                                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Source
                                            </th>
                                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Status
                                            </th>
                                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Expected
                                                Close</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <tr v-for="lead in reportData.new_leads" :key="lead.id"
                                            class="hover:bg-gray-50">
                                            <td class="py-3 px-4 text-sm font-medium text-gray-900">{{ lead.company }}
                                            </td>
                                            <td class="py-3 px-4 text-sm text-gray-600">{{ lead.source || 'N/A' }}</td>
                                            <td class="py-3 px-4">
                                                <span
                                                    class="inline-flex px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ lead.status?.replace('_', ' ') || 'new' }}
                                                </span>
                                            </td>
                                            <td class="py-3 px-4 text-sm text-gray-600">{{
                                                formatDate(lead.expected_close_date) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Lost Deals -->
                        <div v-if="reportData.lost_deals && reportData.lost_deals.length > 0">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Lost Deals</h4>
                            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                                <table class="w-full">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Company
                                            </th>
                                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Value
                                            </th>
                                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Reason
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <tr v-for="deal in reportData.lost_deals" :key="deal.id"
                                            class="hover:bg-gray-50">
                                            <td class="py-3 px-4 text-sm font-medium text-gray-900">{{ deal.company }}
                                            </td>
                                            <td class="py-3 px-4 text-sm text-gray-600">{{ formatCurrency(deal.value) }}
                                            </td>
                                            <td class="py-3 px-4 text-sm text-gray-600">{{ deal.lost_reason || 'No reason provided' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Key Reminders -->
                        <div
                            v-if="reportData.key_reminders && (reportData.key_reminders.upcoming_tasks?.length > 0 || reportData.key_reminders.overdue_tasks?.length > 0)">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Key Reminders</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div v-if="reportData.key_reminders.upcoming_tasks?.length > 0"
                                    class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <h5 class="font-semibold text-blue-900 mb-2">Upcoming Tasks ({{
                                        reportData.key_reminders.upcoming_tasks.length }})</h5>
                                    <ul class="space-y-2">
                                        <li v-for="task in reportData.key_reminders.upcoming_tasks.slice(0, 5)"
                                            :key="task.id" class="text-sm text-blue-800">
                                            • {{ task.title }}
                                        </li>
                                    </ul>
                                </div>
                                <div v-if="reportData.key_reminders.overdue_tasks?.length > 0"
                                    class="bg-red-50 border border-red-200 rounded-lg p-4">
                                    <h5 class="font-semibold text-red-900 mb-2">Overdue Tasks ({{
                                        reportData.key_reminders.overdue_tasks.length }})</h5>
                                    <ul class="space-y-2">
                                        <li v-for="task in reportData.key_reminders.overdue_tasks.slice(0, 5)"
                                            :key="task.id" class="text-sm text-red-800">
                                            • {{ task.title }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Highlights & Challenges -->
                        <div v-if="reportData.highlights || reportData.challenges"
                            class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-if="reportData.highlights"
                                class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <h5 class="font-semibold text-green-900 mb-2">Highlights</h5>
                                <p class="text-sm text-green-800">{{ reportData.highlights }}</p>
                            </div>
                            <div v-if="reportData.challenges"
                                class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <h5 class="font-semibold text-yellow-900 mb-2">Challenges</h5>
                                <p class="text-sm text-yellow-800">{{ reportData.challenges }}</p>
                            </div>
                        </div>

                        <!-- User Input Section -->
                        <div class="mt-8 p-6 bg-purple-50 border-2 border-purple-200 rounded-lg">
                            <h4 class="text-lg font-semibold text-purple-900 mb-4">
                                📝 Add Your Input (Optional)
                            </h4>
                            <p class="text-sm text-gray-600 mb-4">
                                Add highlights and challenges to personalize the downloaded/emailed report
                            </p>

                            <div class="space-y-4">
                                <!-- Highlights Input -->
                                <div>
                                    <label for="user-highlights" class="block text-sm font-medium text-gray-700 mb-2">
                                        Highlights
                                    </label>
                                    <textarea id="user-highlights" v-model="userHighlights" rows="3"
                                        placeholder="Enter key highlights from this period..."
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-none"></textarea>
                                </div>

                                <!-- Challenges Input -->
                                <div>
                                    <label for="user-challenges" class="block text-sm font-medium text-gray-700 mb-2">
                                        Challenges
                                    </label>
                                    <textarea id="user-challenges" v-model="userChallenges" rows="3"
                                        placeholder="Enter challenges faced during this period..."
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-none"></textarea>
                                </div>
                            </div>

                            <p class="text-xs text-gray-500 mt-3">
                                💡 Your input will override any existing highlights/challenges when downloading or
                                emailing
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-6 border-t border-gray-200 bg-gray-50 flex items-center justify-between gap-4">
                    <div class="flex gap-3">
                        <!-- Download Report Button -->
                        <button @click="downloadReport" :disabled="downloading || !reportData"
                            class="py-2 px-6 border-2 border-purple-600 text-purple-600 rounded-lg hover:bg-purple-50 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                            <svg v-if="downloading" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            {{ downloading ? 'Downloading...' : 'Download Report' }}
                        </button>

                        <!-- Send to Liz Button -->
                        <button @click="sendReportEmail" :disabled="sending || !reportData"
                            class="py-2 px-6 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                            <svg v-if="sending" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <svg v-else-if="emailSuccess" class="w-4 h-4" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            {{ sending ? 'Sending...' : emailSuccess ? 'Sent!' : 'Send to Liz' }}
                        </button>
                    </div>

                    <!-- Close Button -->
                    <button @click="closeModal"
                        class="py-2 px-6 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Custom scrollbar for modal */
.modal-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.modal-scrollbar::-webkit-scrollbar-track {
    background: #f3f4f6;
    border-radius: 3px;
}

.modal-scrollbar::-webkit-scrollbar-thumb {
    background: #9333ea;
    border-radius: 3px;
}

.modal-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #7c3aed;
}

/* Firefox scrollbar */
.modal-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: #9333ea #f3f4f6;
}
</style>
