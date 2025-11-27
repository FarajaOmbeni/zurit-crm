<script setup>
import { ref, onMounted } from 'vue';

const tasks = ref([]);
const loading = ref(false);

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

const toggleTask = async (task) => {
    try {
        if (task.status === 'completed') {
            // Uncomplete task
            await window.axios.put(`/api/tasks/${task.id}`, {
                status: 'pending',
            });
        } else {
            // Complete task
            await window.axios.patch(`/api/tasks/${task.id}/complete`);
        }
        // Refresh tasks
        fetchTasks();
    } catch (error) {
        console.error('Error toggling task:', error);
    }
};

const fetchTasks = async () => {
    loading.value = true;
    try {
        const response = await window.axios.get('/api/tasks?per_page=6&order_by=due_date&order=asc');
        tasks.value = response.data.data || [];
    } catch (error) {
        console.error('Error fetching tasks:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchTasks();
});
</script>

<template>
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Tasks</h2>
            <a href="/tasks" class="text-sm text-gray-600 hover:text-gray-900">View All</a>
        </div>
        
        <div v-if="loading" class="text-center py-8 text-gray-500">
            Loading...
        </div>
        
        <div v-else-if="tasks.length === 0" class="text-center py-8 text-gray-500">
            No tasks found
        </div>
        
        <div v-else class="space-y-3">
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
    </div>
</template>

