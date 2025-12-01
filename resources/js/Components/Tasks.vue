<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const tasks = ref([]);
const allTasks = ref([]);
const loading = ref(false);
const showModal = ref(false);
const modalLoading = ref(false);

const getTaskIcon = (task) => {
    if (task.status === 'completed') {
        return { bg: 'bg-green-500', icon: '✓' };
    }
    const dueDate = new Date(task.due_date);
    const now = new Date();
    if (task.priority === 'high' || dueDate < now) {
        return { bg: 'bg-red-500', icon: '!' };
    }
    return { bg: 'bg-orange-500', icon: '⏰' };
};

const formatTime = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const hours = date.getHours();
    const minutes = date.getMinutes();
    const ampm = hours >= 12 ? 'pm' : 'am';
    const displayHours = hours % 12 || 12;
    return `${displayHours}:${minutes.toString().padStart(2, '0')} ${ampm}`;
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric',
        year: 'numeric'
    });
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
        'high': 'bg-red-100 text-red-800',
        'medium': 'bg-yellow-100 text-yellow-800',
        'low': 'bg-green-100 text-green-800'
    };
    return colors[priority] || 'bg-gray-100 text-gray-800';
};

const toggleTask = async (task) => {
    try {
        if (task.status === 'completed') {
            // Uncomplete task
            await axios.put(`/api/tasks/${task.id}`, {
                status: 'pending',
            });
        } else {
            // Complete task
            await axios.patch(`/api/tasks/${task.id}/complete`);
        }
        // Refresh tasks
        fetchTasks();
        if (showModal.value) {
            fetchAllTasks();
        }
    } catch (error) {
        console.error('Error toggling task:', error);
    }
};

const fetchTasks = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/tasks?per_page=6&order_by=due_date&order=asc');
        tasks.value = response.data.data || [];
    } catch (error) {
        console.error('Error fetching tasks:', error);
    } finally {
        loading.value = false;
    }
};

const fetchAllTasks = async () => {
    modalLoading.value = true;
    try {
        const response = await axios.get('/api/tasks', {
            params: {
                per_page: 100,
                order_by: 'due_date',
                order: 'asc'
            }
        });
        allTasks.value = response.data.data || [];
    } catch (error) {
        console.error('Error fetching all tasks:', error);
    } finally {
        modalLoading.value = false;
    }
};

const openModal = (e) => {
    e.preventDefault();
    showModal.value = true;
    fetchAllTasks();
};

const closeModal = () => {
    showModal.value = false;
};

onMounted(() => {
    fetchTasks();
});
</script>

<style scoped>
/* Custom scrollbar styling for modal */
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

<template>
    <div class="bg-white rounded-lg shadow-sm p-6 h-[500px] flex flex-col">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Tasks</h2>
            <button
                @click="openModal"
                class="text-sm text-gray-600 hover:text-gray-900"
            >
                View All
            </button>
        </div>
        
        <div v-if="loading" class="text-center py-8 text-gray-500 flex-1 flex items-center justify-center">
            Loading...
        </div>
        
        <div v-else-if="tasks.length === 0" class="text-center py-8 text-gray-500 flex-1 flex items-center justify-center">
            No tasks found
        </div>
        
        <div v-else class="space-y-3 flex-1 overflow-hidden">
            <div 
                v-for="task in tasks" 
                :key="task.id"
                class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition"
            >
                <!-- Icon -->
                <div 
                    :class="['w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-bold', getTaskIcon(task).bg]"
                >
                    {{ getTaskIcon(task).icon }}
                </div>
                
                <!-- Task Info -->
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">
                        {{ task.title }}
                    </p>
                    <p v-if="task.status !== 'completed'" class="text-xs text-gray-500">
                        Due {{ formatTime(task.due_date) }}
                    </p>
                    <p v-else class="text-xs text-green-600 font-medium">
                        Done
                    </p>
                </div>
                
                <!-- Checkbox -->
                <button
                    @click="toggleTask(task)"
                    :class="[
                        'w-5 h-5 rounded border-2 flex items-center justify-center transition',
                        task.status === 'completed' 
                            ? 'bg-red-500 border-red-500' 
                            : 'border-gray-300 hover:border-gray-400'
                    ]"
                >
                    <svg 
                        v-if="task.status === 'completed'"
                        class="w-3 h-3 text-white" 
                        fill="none" 
                        stroke="currentColor" 
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Modal -->
        <div
            v-if="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50"
            @click="closeModal"
        >
            <div
                class="bg-white rounded-lg shadow-xl max-w-5xl w-full max-h-[85vh] overflow-hidden"
                @click.stop
            >
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900">All Tasks</h3>
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
                <div class="overflow-y-auto max-h-[calc(85vh-140px)] modal-scrollbar">
                    <div v-if="modalLoading" class="p-6">
                        <div class="space-y-3">
                            <div v-for="i in 10" :key="i" class="animate-pulse flex gap-4">
                                <div class="h-12 bg-gray-200 rounded flex-1"></div>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="allTasks.length === 0" class="text-center py-12 text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <p class="text-lg font-medium">No tasks found</p>
                    </div>

                    <div v-else class="p-6 space-y-3">
                        <div
                            v-for="task in allTasks"
                            :key="task.id"
                            class="flex items-start gap-4 p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            <!-- Icon -->
                            <div 
                                :class="['w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0', getTaskIcon(task).bg]"
                            >
                                {{ getTaskIcon(task).icon }}
                            </div>
                            
                            <!-- Task Info -->
                            <div class="flex-1 min-w-0">
                                <h4 class="text-base font-semibold text-gray-900 mb-2">
                                    {{ task.title }}
                                </h4>
                                <p v-if="task.description" class="text-sm text-gray-600 mb-3">
                                    {{ task.description }}
                                </p>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        :class="['inline-flex px-2 py-1 rounded-full text-xs font-medium', getStatusColor(task.status)]"
                                    >
                                        {{ task.status.replace('_', ' ') }}
                                    </span>
                                    <span
                                        :class="['inline-flex px-2 py-1 rounded-full text-xs font-medium', getPriorityColor(task.priority)]"
                                    >
                                        {{ task.priority }} priority
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        Due: {{ formatDate(task.due_date) }} at {{ formatTime(task.due_date) }}
                                    </span>
                                    <span v-if="task.lead" class="text-xs text-gray-400">
                                        • {{ task.lead.company }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Checkbox -->
                            <button
                                @click="toggleTask(task)"
                                :class="[
                                    'w-6 h-6 rounded border-2 flex items-center justify-center transition flex-shrink-0 mt-1',
                                    task.status === 'completed' 
                                        ? 'bg-green-500 border-green-500' 
                                        : 'border-gray-300 hover:border-gray-400'
                                ]"
                            >
                                <svg 
                                    v-if="task.status === 'completed'"
                                    class="w-4 h-4 text-white" 
                                    fill="none" 
                                    stroke="currentColor" 
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </button>
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

