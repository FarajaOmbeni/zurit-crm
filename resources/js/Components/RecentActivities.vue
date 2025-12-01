<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const activities = ref([]);
const allActivities = ref([]);
const loading = ref(true);
const showModal = ref(false);
const modalLoading = ref(false);

const fetchRecentActivities = async () => {
    try {
        loading.value = true;

        // Fetch both activities and tasks in parallel
        const [activitiesRes, tasksRes] = await Promise.all([
            axios.get('/api/activities', {
                params: {
                    per_page: 10
                }
            }),
            axios.get('/api/tasks', {
                params: {
                    per_page: 10
                }
            })
        ]);

        // Map activities with itemType
        const activitiesData = (activitiesRes.data.data || []).map(activity => ({
            ...activity,
            itemType: 'activity',
            date: activity.activity_date,
            displayTitle: activity.description || `${activity.type} activity`,
            companyName: activity.lead?.company || 'Unknown',
            userName: activity.user?.name || 'Unknown User'
        }));

        // Map completed tasks with itemType
        const tasksData = (tasksRes.data.data || [])
            .filter(task => task.status === 'completed')
            .map(task => ({
                ...task,
                itemType: 'task',
                date: task.completed_at || task.due_date,
                displayTitle: task.title || task.description || 'Task',
                companyName: task.lead?.company || 'Unknown',
                userName: task.created_by_user?.name || 'Unknown User'
            }));

        // Combine, sort by date, and limit to 15 items
        activities.value = [...activitiesData, ...tasksData]
            .sort((a, b) => new Date(b.date) - new Date(a.date))
            .slice(0, 15);

    } catch (error) {
        console.error('Error fetching recent activities:', error);
    } finally {
        loading.value = false;
    }
};

const fetchAllActivities = async () => {
    try {
        modalLoading.value = true;

        // Fetch all activities and tasks
        const [activitiesRes, tasksRes] = await Promise.all([
            axios.get('/api/activities', {
                params: {
                    per_page: 100
                }
            }),
            axios.get('/api/tasks', {
                params: {
                    per_page: 100
                }
            })
        ]);

        // Map activities with itemType
        const activitiesData = (activitiesRes.data.data || []).map(activity => ({
            ...activity,
            itemType: 'activity',
            date: activity.activity_date,
            displayTitle: activity.description || `${activity.type} activity`,
            companyName: activity.lead?.company || 'Unknown',
            userName: activity.user?.name || 'Unknown User'
        }));

        // Map tasks with itemType (all tasks, not just completed)
        const tasksData = (tasksRes.data.data || []).map(task => ({
            ...task,
            itemType: 'task',
            date: task.completed_at || task.due_date,
            displayTitle: task.title || task.description || 'Task',
            companyName: task.lead?.company || 'Unknown',
            userName: task.created_by_user?.name || 'Unknown User'
        }));

        // Combine and sort by date
        allActivities.value = [...activitiesData, ...tasksData]
            .sort((a, b) => new Date(b.date) - new Date(a.date));

    } catch (error) {
        console.error('Error fetching all activities:', error);
    } finally {
        modalLoading.value = false;
    }
};

const openModal = () => {
    showModal.value = true;
    fetchAllActivities();
};

const closeModal = () => {
    showModal.value = false;
};

const getTypeIcon = (item) => {
    if (item.itemType === 'task') return 'checkbox';
    
    const icons = {
        'call': 'phone',
        'email': 'envelope',
        'meeting': 'users',
        'note': 'document'
    };
    return icons[item.type] || 'document';
};

const getTypeColor = (item) => {
    if (item.itemType === 'task') return 'text-purple-600';
    
    const colors = {
        'call': 'text-blue-600',
        'email': 'text-green-600',
        'meeting': 'text-orange-600',
        'note': 'text-gray-600'
    };
    return colors[item.type] || 'text-gray-600';
};

const getTypeBgColor = (item) => {
    if (item.itemType === 'task') return 'bg-purple-100';
    
    const colors = {
        'call': 'bg-blue-100',
        'email': 'bg-green-100',
        'meeting': 'bg-orange-100',
        'note': 'bg-gray-100'
    };
    return colors[item.type] || 'bg-gray-100';
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins} min ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays === 1) return 'Yesterday';
    if (diffDays < 7) return `${diffDays}d ago`;
    
    return date.toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric',
        year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
    });
};

const formatTime = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleTimeString('en-US', { 
        hour: 'numeric', 
        minute: '2-digit',
        hour12: true 
    });
};

const getInitials = (name) => {
    if (!name) return '?';
    const parts = name.split(' ');
    if (parts.length >= 2) {
        return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
};

onMounted(() => {
    fetchRecentActivities();
});
</script>

<template>
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Recent Activities</h3>
            <button
                @click="openModal"
                class="text-sm text-purple-600 hover:text-purple-700 font-medium"
            >
                View All
            </button>
        </div>

        <div v-if="loading" class="space-y-3">
            <div v-for="i in 5" :key="i" class="animate-pulse flex items-center gap-4 py-3">
                <div class="w-10 h-10 bg-gray-200 rounded-lg"></div>
                <div class="flex-1 space-y-2">
                    <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                    <div class="h-3 bg-gray-100 rounded w-1/2"></div>
                </div>
                <div class="w-20 h-4 bg-gray-200 rounded"></div>
                <div class="w-8 h-8 bg-gray-200 rounded-full"></div>
            </div>
        </div>

        <div v-else-if="activities.length === 0" class="text-center py-8 text-gray-500">
            <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <p class="text-lg font-medium">No recent activities</p>
        </div>

        <div v-else class="divide-y divide-gray-100">
            <div
                v-for="activity in activities"
                :key="`${activity.itemType}-${activity.id}`"
                class="py-3 flex items-center gap-4 hover:bg-gray-50 -mx-2 px-2 rounded-lg transition-colors"
            >
                <!-- Icon -->
                <div :class="['w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0', getTypeBgColor(activity)]">
                    <!-- Phone Icon -->
                    <svg v-if="getTypeIcon(activity) === 'phone'" :class="['w-5 h-5', getTypeColor(activity)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    <!-- Envelope Icon -->
                    <svg v-else-if="getTypeIcon(activity) === 'envelope'" :class="['w-5 h-5', getTypeColor(activity)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <!-- Users Icon -->
                    <svg v-else-if="getTypeIcon(activity) === 'users'" :class="['w-5 h-5', getTypeColor(activity)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <!-- Checkbox Icon -->
                    <svg v-else-if="getTypeIcon(activity) === 'checkbox'" :class="['w-5 h-5', getTypeColor(activity)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <!-- Document Icon (default) -->
                    <svg v-else :class="['w-5 h-5', getTypeColor(activity)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">
                        {{ activity.displayTitle }}
                    </p>
                    <p class="text-xs text-gray-500 truncate">
                        {{ activity.companyName }}
                    </p>
                </div>

                <!-- Date/Time -->
                <div class="text-right flex-shrink-0 hidden sm:block">
                    <p class="text-xs text-gray-900">{{ formatDate(activity.date) }}</p>
                    <p class="text-xs text-gray-500">{{ formatTime(activity.date) }}</p>
                </div>

                <!-- User Avatar -->
                <div class="flex items-center gap-2 flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-600 rounded-full flex items-center justify-center text-white text-xs font-medium">
                        {{ getInitials(activity.userName) }}
                    </div>
                    <span class="text-sm text-gray-700 hidden md:block">{{ activity.userName }}</span>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div
            v-if="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50"
            @click="closeModal"
        >
            <div
                class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[85vh] overflow-hidden"
                @click.stop
            >
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900">All Activities</h3>
                    <button
                        @click="closeModal"
                        class="text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 overflow-y-auto max-h-[calc(85vh-140px)]">
                    <div v-if="modalLoading" class="space-y-3">
                        <div v-for="i in 10" :key="i" class="animate-pulse flex items-center gap-4 py-3">
                            <div class="w-10 h-10 bg-gray-200 rounded-lg"></div>
                            <div class="flex-1 space-y-2">
                                <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                <div class="h-3 bg-gray-100 rounded w-1/2"></div>
                            </div>
                            <div class="w-20 h-4 bg-gray-200 rounded"></div>
                            <div class="w-8 h-8 bg-gray-200 rounded-full"></div>
                        </div>
                    </div>

                    <div v-else-if="allActivities.length === 0" class="text-center py-12 text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <p class="text-lg font-medium">No activities found</p>
                    </div>

                    <div v-else class="divide-y divide-gray-100">
                        <div
                            v-for="activity in allActivities"
                            :key="`modal-${activity.itemType}-${activity.id}`"
                            class="py-4 flex items-center gap-4 hover:bg-gray-50 -mx-2 px-2 rounded-lg transition-colors"
                        >
                            <!-- Icon -->
                            <div :class="['w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0', getTypeBgColor(activity)]">
                                <!-- Phone Icon -->
                                <svg v-if="getTypeIcon(activity) === 'phone'" :class="['w-5 h-5', getTypeColor(activity)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <!-- Envelope Icon -->
                                <svg v-else-if="getTypeIcon(activity) === 'envelope'" :class="['w-5 h-5', getTypeColor(activity)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <!-- Users Icon -->
                                <svg v-else-if="getTypeIcon(activity) === 'users'" :class="['w-5 h-5', getTypeColor(activity)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <!-- Checkbox Icon -->
                                <svg v-else-if="getTypeIcon(activity) === 'checkbox'" :class="['w-5 h-5', getTypeColor(activity)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                                <!-- Document Icon (default) -->
                                <svg v-else :class="['w-5 h-5', getTypeColor(activity)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ activity.displayTitle }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ activity.companyName }}
                                </p>
                            </div>

                            <!-- Date/Time -->
                            <div class="text-right flex-shrink-0">
                                <p class="text-xs text-gray-900">{{ formatDate(activity.date) }}</p>
                                <p class="text-xs text-gray-500">{{ formatTime(activity.date) }}</p>
                            </div>

                            <!-- User Avatar -->
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-600 rounded-full flex items-center justify-center text-white text-xs font-medium">
                                    {{ getInitials(activity.userName) }}
                                </div>
                                <span class="text-sm text-gray-700 hidden lg:block">{{ activity.userName }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-6 border-t border-gray-200 bg-gray-50">
                    <button
                        @click="closeModal"
                        class="w-full py-2 px-4 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-medium"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

