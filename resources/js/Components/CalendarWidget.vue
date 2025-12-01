<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';

const currentDate = ref(new Date());
const selectedDate = ref(new Date());
const tasks = ref([]);
const loading = ref(true);
const showModal = ref(false);
const selectedDayTasks = ref([]);
const selectedDayNumber = ref(null);

const currentMonth = computed(() => {
    return currentDate.value.toLocaleString('default', { month: 'long' });
});

const currentYear = computed(() => {
    return currentDate.value.getFullYear();
});

const daysInMonth = computed(() => {
    const year = currentDate.value.getFullYear();
    const month = currentDate.value.getMonth();
    const days = new Date(year, month + 1, 0).getDate();
    const firstDay = new Date(year, month, 1).getDay();

    const daysArray = [];

    // Add empty slots for days before month starts
    for (let i = 0; i < firstDay; i++) {
        daysArray.push(null);
    }

    // Add actual days
    for (let i = 1; i <= days; i++) {
        daysArray.push(i);
    }

    return daysArray;
});

const isToday = (day) => {
    if (!day) return false;
    const today = new Date();
    return day === today.getDate() &&
        currentDate.value.getMonth() === today.getMonth() &&
        currentDate.value.getFullYear() === today.getFullYear();
};

const getTasksForDay = (day) => {
    if (!day) return [];

    return tasks.value.filter(task => {
        const taskDate = new Date(task.due_date);
        return taskDate.getDate() === day &&
            taskDate.getMonth() === currentDate.value.getMonth() &&
            taskDate.getFullYear() === currentDate.value.getFullYear();
    });
};

const hasTasksOnDay = (day) => {
    return getTasksForDay(day).length > 0;
};

const getTaskCountForDay = (day) => {
    return getTasksForDay(day).length;
};

const handleDayClick = (day) => {
    if (!day) return;

    selectedDayNumber.value = day;
    selectedDayTasks.value = getTasksForDay(day);
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedDayNumber.value = null;
    selectedDayTasks.value = [];
};

const getStatusColor = (status) => {
    const colors = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'in_progress': 'bg-blue-100 text-blue-800',
        'completed': 'bg-green-100 text-green-800',
        'cancelled': 'bg-gray-100 text-gray-800'
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const getPriorityColor = (priority) => {
    const colors = {
        'high': 'text-red-600',
        'medium': 'text-yellow-600',
        'low': 'text-green-600'
    };
    return colors[priority] || 'text-gray-600';
};

const fetchTasks = async () => {
    try {
        loading.value = true;

        const response = await axios.get('/api/tasks', {
            params: {
                per_page: 1000
            }
        });

        // Get all tasks (including completed ones from previous months)
        const year = currentDate.value.getFullYear();
        const month = currentDate.value.getMonth();
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);

        // Filter tasks for current month only for display
        tasks.value = response.data.data.filter(task => {
            const taskDate = new Date(task.due_date);
            return taskDate >= firstDay && taskDate <= lastDay;
        });

    } catch (error) {
        console.error('Error fetching tasks:', error);
    } finally {
        loading.value = false;
    }
};

const previousMonth = () => {
    const newDate = new Date(currentDate.value);
    newDate.setMonth(newDate.getMonth() - 1);
    currentDate.value = newDate;
};

const nextMonth = () => {
    const newDate = new Date(currentDate.value);
    newDate.setMonth(newDate.getMonth() + 1);
    currentDate.value = newDate;
};

// Watch for month changes and refetch tasks
watch(currentDate, () => {
    fetchTasks();
});

onMounted(() => {
    // Initialize with current date
    currentDate.value = new Date();
    selectedDate.value = new Date();
    fetchTasks();
});
</script>

<template>
    <div class="bg-white shadow rounded-lg p-6">
        <!-- Calendar Header -->
        <div class="flex items-center justify-between mb-4">
            <button @click="previousMonth" class="p-1 hover:bg-gray-100 rounded transition-colors"
                aria-label="Previous month">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <div class="text-center">
                <div class="text-lg font-semibold text-gray-900">{{ currentMonth }}</div>
                <div class="text-sm text-gray-500">{{ currentYear }}</div>
            </div>

            <button @click="nextMonth" class="p-1 hover:bg-gray-100 rounded transition-colors" aria-label="Next month">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>

        <!-- Weekday Headers -->
        <div class="grid grid-cols-7 gap-1 mb-2">
            <div v-for="day in ['S', 'M', 'T', 'W', 'T', 'F', 'S']" :key="day"
                class="text-center text-xs font-medium text-gray-500 py-2">
                {{ day }}
            </div>
        </div>

        <!-- Calendar Days -->
        <div class="grid grid-cols-7 gap-1">
            <div v-for="(day, index) in daysInMonth" :key="index"
                class="aspect-square flex items-center justify-center relative">
                <button v-if="day" @click="handleDayClick(day)" :class="[
                    'w-full h-full flex flex-col items-center justify-center rounded-lg text-sm transition-colors relative',
                    isToday(day)
                        ? 'bg-purple-600 text-white font-semibold hover:bg-purple-700'
                        : hasTasksOnDay(day)
                            ? 'text-gray-700 hover:bg-gray-100 ring-2 ring-purple-400'
                            : 'text-gray-700 hover:bg-gray-100'
                ]">
                    <span>{{ day }}</span>
                    <!-- Task indicator dot -->
                    <div v-if="hasTasksOnDay(day) && !isToday(day)" class="flex gap-0.5 mt-1">
                        <div v-for="i in Math.min(getTaskCountForDay(day), 3)" :key="i"
                            class="w-1 h-1 rounded-full bg-purple-500"></div>
                    </div>
                    <div v-else-if="hasTasksOnDay(day) && isToday(day)" class="flex gap-0.5 mt-1">
                        <div v-for="i in Math.min(getTaskCountForDay(day), 3)" :key="i"
                            class="w-1 h-1 rounded-full bg-white"></div>
                    </div>
                </button>
            </div>
        </div>

        <!-- Today Button -->
        <div class="mt-4 pt-4 border-t border-gray-200">
            <button @click="currentDate = new Date()"
                class="w-full py-2 text-sm text-purple-600 hover:bg-purple-50 rounded-lg transition-colors font-medium">
                Today
            </button>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50"
            @click="closeModal">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[80vh] overflow-hidden" @click.stop>
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ currentMonth }} {{ selectedDayNumber }}, {{ currentYear }}
                    </h3>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-4 overflow-y-auto max-h-[60vh]">
                    <div v-if="selectedDayTasks.length === 0" class="text-center py-8 text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <p class="text-lg font-medium">No tasks on this day</p>
                    </div>

                    <div v-else class="space-y-3">
                        <div v-for="task in selectedDayTasks" :key="task.id"
                            class="p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="flex items-start justify-between mb-2">
                                <h4 class="font-medium text-gray-900 flex-1">{{ task.title }}</h4>
                                <span :class="[
                                    'text-xs px-2 py-1 rounded-full font-medium',
                                    getStatusColor(task.status)
                                ]">
                                    {{ task.status.replace('_', ' ') }}
                                </span>
                            </div>

                            <p v-if="task.description" class="text-sm text-gray-600 mb-2">
                                {{ task.description }}
                            </p>

                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <div class="flex items-center gap-2">
                                    <span :class="['font-medium', getPriorityColor(task.priority)]">
                                        {{ task.priority }} priority
                                    </span>
                                    <span>•</span>
                                    <span>{{ task.type }}</span>
                                </div>
                                <span v-if="task.lead" class="text-gray-400">
                                    {{ task.lead.company }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-4 border-t border-gray-200 bg-gray-50">
                    <button @click="closeModal"
                        class="w-full py-2 px-4 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-medium">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
