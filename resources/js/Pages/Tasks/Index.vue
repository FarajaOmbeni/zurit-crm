<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AddTaskModal from '@/Components/AddTaskModal.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';

// State
const tasks = ref([]);
const loading = ref(false);
const searchTerm = ref('');
const sortBy = ref('due_date');
const sortOrder = ref('asc');
const activeTab = ref('all');
const showAddModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const taskToDelete = ref(null);
const taskToEdit = ref(null);
const deleting = ref(false);

// Pagination
const currentPage = ref(1);
const lastPage = ref(1);
const total = ref(0);
const perPage = ref(15);

// Stats
const stats = ref({
    total: 0,
    pending: 0,
    completed: 0,
    overdue: 0,
});

// Leads for edit modal dropdown
const leads = ref([]);

// Edit task form
const editForm = ref({
    title: '',
    description: '',
    type: 'other',
    priority: 'medium',
    due_date: '',
    lead_id: null,
});
const editFormErrors = ref({});
const updating = ref(false);

// Fetch tasks
const fetchTasks = async () => {
    loading.value = true;
    try {
        const params = {
            per_page: perPage.value,
            page: currentPage.value,
            order_by: sortBy.value,
            order: sortOrder.value,
        };

        if (searchTerm.value) {
            params.search = searchTerm.value;
        }

        if (activeTab.value !== 'all') {
            params.status = activeTab.value;
        }

        const response = await axios.get('/api/tasks', { params });
        tasks.value = response.data.data || [];
        total.value = response.data.total || 0;
        currentPage.value = response.data.current_page || 1;
        lastPage.value = response.data.last_page || 1;
        perPage.value = response.data.per_page || 15;
    } catch (error) {
        console.error('Error fetching tasks:', error);
        tasks.value = [];
    } finally {
        loading.value = false;
    }
};

// Fetch stats
const fetchStats = async () => {
    try {
        // Fetch all tasks to calculate stats
        const response = await axios.get('/api/tasks', { params: { per_page: 1000 } });
        const allTasks = response.data.data || [];

        const now = new Date();
        stats.value = {
            total: allTasks.length,
            pending: allTasks.filter(t => t.status === 'pending' || t.status === 'in_progress').length,
            completed: allTasks.filter(t => t.status === 'completed').length,
            overdue: allTasks.filter(t => {
                const dueDate = new Date(t.due_date);
                return dueDate < now && t.status !== 'completed';
            }).length,
        };
    } catch (error) {
        console.error('Error fetching stats:', error);
    }
};

// Fetch leads for the edit modal dropdown
const fetchLeads = async () => {
    try {
        const response = await axios.get('/api/leads', {
            params: {
                per_page: 1000,
                include_clients: true
            }
        });
        leads.value = response.data.data || [];
    } catch (error) {
        console.error('Error fetching leads:', error);
    }
};

// Toggle task completion
const toggleTask = async (task) => {
    try {
        if (task.status === 'completed') {
            await axios.put(`/api/tasks/${task.id}`, { status: 'pending' });
        } else {
            await axios.patch(`/api/tasks/${task.id}/complete`);
        }
        fetchTasks();
        fetchStats();
    } catch (error) {
        console.error('Error toggling task:', error);
    }
};

// Delete task
const confirmDelete = (task) => {
    taskToDelete.value = task;
    showDeleteModal.value = true;
};

const deleteTask = async () => {
    if (!taskToDelete.value) return;

    deleting.value = true;
    try {
        await axios.delete(`/api/tasks/${taskToDelete.value.id}`);
        showDeleteModal.value = false;
        taskToDelete.value = null;
        fetchTasks();
        fetchStats();
    } catch (error) {
        console.error('Error deleting task:', error);
    } finally {
        deleting.value = false;
    }
};

// Handle task added event from modal
const handleTaskAdded = (newTask) => {
    fetchTasks();
    fetchStats();
};

// Open edit modal and populate form
const openEditModal = (task) => {
    taskToEdit.value = task;
    editForm.value = {
        title: task.title || '',
        description: task.description || '',
        type: task.type || 'other',
        priority: task.priority || 'medium',
        due_date: task.due_date ? task.due_date.split('T')[0] : '',
        lead_id: task.lead_id || null,
    };
    editFormErrors.value = {};
    fetchLeads();
    showEditModal.value = true;
};

// Update task
const updateTask = async () => {
    if (!taskToEdit.value) return;

    editFormErrors.value = {};
    updating.value = true;

    try {
        await axios.put(`/api/tasks/${taskToEdit.value.id}`, editForm.value);
        showEditModal.value = false;
        taskToEdit.value = null;
        resetEditForm();
        fetchTasks();
        fetchStats();
    } catch (error) {
        if (error.response?.data?.errors) {
            editFormErrors.value = error.response.data.errors;
        }
        console.error('Error updating task:', error);
    } finally {
        updating.value = false;
    }
};

// Reset edit form
const resetEditForm = () => {
    editForm.value = {
        title: '',
        description: '',
        type: 'other',
        priority: 'medium',
        due_date: '',
        lead_id: null,
    };
    editFormErrors.value = {};
    taskToEdit.value = null;
};

// Helpers
const getLeadDisplayName = (lead) => {
    if (!lead) return '';
    const name = lead.name || '';
    const company = lead.company || '';
    if (name && company) {
        return `${name} - ${company}`;
    }
    return name || company || 'Unknown';
};

const isOverdue = (task) => {
    if (task.status === 'completed') return false;
    const dueDate = new Date(task.due_date);
    return dueDate < new Date();
};

const getStatusIcon = (task) => {
    if (task.status === 'completed') {
        return { type: 'completed', bg: 'bg-green-100', iconBg: 'bg-green-500', border: 'border-green-500' };
    }
    if (isOverdue(task)) {
        return { type: 'overdue', bg: 'bg-red-50', iconBg: 'bg-red-500', border: 'border-red-500' };
    }
    return { type: 'pending', bg: 'bg-yellow-50', iconBg: 'bg-yellow-500', border: 'border-yellow-500' };
};

const getPriorityBadge = (task) => {
    if (task.status === 'completed') {
        return { label: 'DONE', class: 'bg-green-100 text-green-800' };
    }
    const badges = {
        high: { label: 'HIGH', class: 'bg-red-100 text-red-800' },
        medium: { label: 'MEDIUM', class: 'bg-yellow-100 text-yellow-800' },
        low: { label: 'LOW', class: 'bg-gray-100 text-gray-600' },
    };
    return badges[task.priority] || badges.medium;
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const day = date.getDate();
    const month = date.toLocaleDateString('en-US', { month: 'long' });
    const year = date.getFullYear();
    return `${day} ${month} ${year}`;
};

const formatDateWithOverdue = (task) => {
    const dateStr = formatDate(task.due_date);
    if (task.status === 'completed') {
        return `Due: ${dateStr}`;
    }
    if (isOverdue(task)) {
        return `Due: ${dateStr} (Overdue)`;
    }
    return `Due: ${dateStr}`;
};

const getInitials = (name) => {
    if (!name) return '??';
    const parts = name.split(' ');
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
};

// Search handler with debounce
let searchTimeout = null;
const handleSearch = (event) => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        searchTerm.value = event.target.value;
        currentPage.value = 1;
        fetchTasks();
    }, 300);
};

// Sort handlers
const handleSortByName = () => {
    sortBy.value = 'title';
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    fetchTasks();
};

const handleSortByDate = () => {
    sortBy.value = 'due_date';
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    fetchTasks();
};

// Tab change
const setActiveTab = (tab) => {
    activeTab.value = tab;
    currentPage.value = 1;
    fetchTasks();
};

// Page change
const handlePageChange = (page) => {
    currentPage.value = page;
    fetchTasks();
};

const getPageNumbers = () => {
    const pages = [];
    const maxVisible = 5;
    let startPage = Math.max(1, currentPage.value - Math.floor(maxVisible / 2));
    let endPage = Math.min(lastPage.value, startPage + maxVisible - 1);

    if (endPage - startPage < maxVisible - 1) {
        startPage = Math.max(1, endPage - maxVisible + 1);
    }

    for (let i = startPage; i <= endPage; i++) {
        pages.push(i);
    }

    return pages;
};

// Open add modal
const openAddModal = () => {
    showAddModal.value = true;
};

// Lock body scroll when modal is open
watch([showAddModal, showEditModal, showDeleteModal], ([addOpen, editOpen, deleteOpen]) => {
    if (addOpen || editOpen || deleteOpen) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});

onMounted(() => {
    fetchTasks();
    fetchStats();
});
</script>

<template>

    <Head title="Tasks" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Header Section -->
                <div class="mb-8">
                    <h1 class="font-heading text-3xl font-bold text-light-black">Task Management</h1>
                    <p class="mt-2 font-body text-sm text-zurit-gray">Organize and track all client-related tasks</p>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                    <!-- Total Tasks Card -->
                    <div class="rounded-2xl p-6 shadow-sm" style="background-color: #E8DFF5;">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full"
                                style="background-color: #7639C2;">
                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                            </div>
                            <span class="font-body text-sm font-medium" style="color: #7639C2;">Total Tasks</span>
                        </div>
                        <p class="mt-4 font-heading text-4xl font-bold" style="color: #7639C2;">{{ stats.total }}</p>
                    </div>

                    <!-- Pending Card -->
                    <div class="rounded-2xl p-6 shadow-sm" style="background-color: #FFF4E0;">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full"
                                style="background-color: #F59E0B;">
                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span class="font-body text-sm font-medium" style="color: #D97706;">Pending</span>
                        </div>
                        <p class="mt-4 font-heading text-4xl font-bold" style="color: #D97706;">{{ stats.pending }}</p>
                    </div>

                    <!-- Completed Card -->
                    <div class="rounded-2xl p-6 shadow-sm" style="background-color: #D9F5F2;">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full"
                                style="background-color: #10B981;">
                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="font-body text-sm font-medium" style="color: #059669;">Completed</span>
                        </div>
                        <p class="mt-4 font-heading text-4xl font-bold" style="color: #059669;">{{ stats.completed }}
                        </p>
                    </div>

                    <!-- Overdue Card -->
                    <div class="rounded-2xl p-6 shadow-sm" style="background-color: #FFE8E8;">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full"
                                style="background-color: #EF4444;">
                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <span class="font-body text-sm font-medium" style="color: #DC2626;">Overdue</span>
                        </div>
                        <p class="mt-4 font-heading text-4xl font-bold" style="color: #DC2626;">{{ stats.overdue }}</p>
                    </div>
                </div>

                <!-- Main Content Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <!-- Tabs -->
                    <div class="border-b border-gray-200 px-6 pt-6">
                        <div class="flex items-center gap-6">
                            <button @click="setActiveTab('all')" :class="[
                                'pb-4 font-body text-sm font-medium transition-colors border-b-2',
                                activeTab === 'all'
                                    ? 'text-prosper border-prosper'
                                    : 'text-zurit-gray border-transparent hover:text-light-black'
                            ]">
                                All Activities ({{ stats.total }})
                            </button>
                            <button @click="openAddModal"
                                class="pb-4 font-body text-sm font-medium text-zurit-gray hover:text-light-black transition-colors border-b-2 border-transparent">
                                <div class="flex items-center gap-2">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                    Add Task
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Search and Filter Section -->
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex items-center gap-4">
                            <!-- Search Bar -->
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                    <input type="text" @input="handleSearch"
                                        placeholder="Search tasks by title or client..."
                                        class="block w-full rounded-lg border border-gray-200 bg-white py-2 pl-11 pr-4 font-body text-sm text-light-black placeholder-gray-400 focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple" />
                                </div>
                            </div>

                            <!-- Sort Buttons -->
                            <div class="flex items-center gap-2">
                                <button @click="handleSortByName"
                                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 font-body text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                                    </svg>
                                    Name
                                </button>

                                <button @click="handleSortByDate"
                                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 font-body text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Date
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Loading State -->
                    <div v-if="loading" class="p-12 text-center">
                        <div class="flex flex-col items-center justify-center space-y-4">
                            <svg class="animate-spin h-8 w-8 text-zurit-purple" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <p class="font-body text-sm text-zurit-gray">Loading tasks...</p>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else-if="tasks.length === 0" class="p-12 text-center">
                        <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <p class="mt-4 font-body text-lg font-medium text-zurit-gray">No tasks found</p>
                        <p class="mt-2 font-body text-sm text-zurit-gray">Get started by adding a new task</p>
                        <button @click="openAddModal"
                            class="mt-6 inline-flex items-center gap-2 rounded-lg bg-zurit-purple px-4 py-2 font-body text-sm font-medium text-white hover:bg-zurit-purple/90 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Add Task
                        </button>
                    </div>

                    <!-- Tasks List -->
                    <div v-else class="divide-y divide-gray-100">
                        <div v-for="task in tasks" :key="task.id" :class="[
                            'p-6 transition-colors hover:bg-gray-50',
                            getStatusIcon(task).bg
                        ]">
                            <div class="flex items-start gap-4">
                                <!-- Status Icon -->
                                <div :class="[
                                    'flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full border-2',
                                    getStatusIcon(task).border,
                                    getStatusIcon(task).type === 'completed' ? 'bg-green-100' : 'bg-white'
                                ]">
                                    <!-- Completed Icon -->
                                    <svg v-if="getStatusIcon(task).type === 'completed'" class="h-5 w-5 text-green-600"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    <!-- Overdue Icon -->
                                    <svg v-else-if="getStatusIcon(task).type === 'overdue'" class="h-5 w-5 text-red-600"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <!-- Pending Icon -->
                                    <svg v-else class="h-5 w-5 text-yellow-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>

                                <!-- Task Content -->
                                <div class="flex-1 min-w-0">
                                    <h3 :class="[
                                        'font-body text-base font-semibold',
                                        task.status === 'completed' ? 'text-gray-500 line-through' : 'text-light-black'
                                    ]">
                                        {{ task.title }}
                                    </h3>
                                    <p v-if="task.description"
                                        class="mt-1 font-body text-sm text-zurit-gray line-clamp-2">
                                        {{ task.description }}
                                    </p>

                                    <!-- Client Info -->
                                    <div class="mt-3 flex items-center gap-2" v-if="task.lead">
                                        <div
                                            class="flex h-6 w-6 items-center justify-center rounded-full bg-zurit-purple text-white text-xs font-medium">
                                            {{ getInitials(task.lead.name || task.lead.company) }}
                                        </div>
                                        <span class="font-body text-sm text-zurit-gray">
                                            {{ task.lead.name }}<span v-if="task.lead.company">- {{ task.lead.company
                                                }}</span>
                                        </span>
                                    </div>
                                    <div class="mt-3 flex items-center gap-2" v-else>
                                        <div
                                            class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-400 text-white text-xs font-medium">
                                            IN
                                        </div>
                                        <span class="font-body text-sm text-zurit-gray">Internal</span>
                                    </div>

                                    <!-- Priority Badge and Due Date -->
                                    <div class="mt-3 flex flex-wrap items-center gap-3">
                                        <span :class="[
                                            'inline-flex rounded-md px-2.5 py-1 text-xs font-bold uppercase',
                                            getPriorityBadge(task).class
                                        ]">
                                            {{ getPriorityBadge(task).label }}
                                        </span>
                                        <span :class="[
                                            'inline-flex items-center gap-1.5 font-body text-xs',
                                            isOverdue(task) && task.status !== 'completed' ? 'text-red-600 font-medium' : 'text-zurit-gray'
                                        ]">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            {{ formatDateWithOverdue(task) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex items-center gap-2">
                                    <!-- Toggle Complete Button -->
                                    <button @click="toggleTask(task)" :class="[
                                        'flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg border transition-colors',
                                        task.status === 'completed'
                                            ? 'border-yellow-200 bg-white text-yellow-600 hover:bg-yellow-50 hover:border-yellow-300'
                                            : 'border-green-200 bg-white text-green-600 hover:bg-green-50 hover:border-green-300'
                                    ]"
                                        :title="task.status === 'completed' ? 'Mark as pending' : 'Mark as completed'">
                                        <!-- Undo icon for completed tasks -->
                                        <svg v-if="task.status === 'completed'" class="h-5 w-5" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                        </svg>
                                        <!-- Check icon for pending tasks -->
                                        <svg v-else class="h-5 w-5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>

                                    <!-- Edit Button -->
                                    <button @click="openEditModal(task)"
                                        class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg border border-blue-200 bg-white text-blue-600 hover:bg-blue-50 hover:border-blue-300 transition-colors"
                                        title="Edit task">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>

                                    <!-- Delete Button -->
                                    <button @click="confirmDelete(task)"
                                        class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg border border-red-200 bg-white text-red-500 hover:bg-red-50 hover:border-red-300 transition-colors"
                                        title="Delete task">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="tasks.length > 0 && lastPage > 1" class="px-6 py-4 border-t border-gray-200 bg-white">
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
                                <button v-if="getPageNumbers()[0] > 1" @click="handlePageChange(1)"
                                    class="px-3 py-2 text-sm font-body font-medium text-light-black hover:bg-light-gray rounded-lg transition-colors">
                                    1
                                </button>
                                <span v-if="getPageNumbers()[0] > 2" class="px-2 text-zurit-gray">...</span>

                                <button v-for="page in getPageNumbers()" :key="page" @click="handlePageChange(page)"
                                    :class="[
                                        'px-3 py-2 text-sm font-body font-medium rounded-lg transition-colors',
                                        page === currentPage
                                            ? 'bg-zurit-purple text-white'
                                            : 'text-light-black hover:bg-light-gray'
                                    ]">
                                    {{ page }}
                                </button>

                                <span v-if="getPageNumbers()[getPageNumbers().length - 1] < lastPage - 1"
                                    class="px-2 text-zurit-gray">...</span>
                                <button v-if="getPageNumbers()[getPageNumbers().length - 1] < lastPage"
                                    @click="handlePageChange(lastPage)"
                                    class="px-3 py-2 text-sm font-body font-medium text-light-black hover:bg-light-gray rounded-lg transition-colors">
                                    {{ lastPage }}
                                </button>
                            </div>

                            <!-- Next Button -->
                            <button @click="handlePageChange(currentPage + 1)" :disabled="currentPage === lastPage"
                                :class="[
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
                </div>
            </div>
        </div>

        <!-- Add Task Modal -->
        <AddTaskModal :show="showAddModal" @close="showAddModal = false" @task-added="handleTaskAdded" />

        <!-- Edit Task Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50"
            @click="showEditModal = false">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full overflow-hidden" @click.stop>
                <!-- Modal Header -->
                <div class="p-6 border-b border-gray-200">
                    <h3 class="font-heading text-2xl font-semibold text-light-black">Edit Task</h3>
                </div>

                <!-- Modal Body -->
                <form @submit.prevent="updateTask" class="p-6 space-y-5">
                    <!-- Task Title -->
                    <div>
                        <label class="block font-body text-sm font-medium text-light-black mb-2">Task Title *</label>
                        <input v-model="editForm.title" type="text" placeholder="What needs to be done?"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 font-body text-sm text-light-black placeholder-gray-400 focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple" />
                        <p v-if="editFormErrors.title" class="mt-1 text-sm text-red-500">{{ editFormErrors.title[0] }}
                        </p>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block font-body text-sm font-medium text-light-black mb-2">Description</label>
                        <textarea v-model="editForm.description" rows="4"
                            placeholder="Provide more details about this task..."
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 font-body text-sm text-light-black placeholder-gray-400 focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple resize-none"></textarea>
                    </div>

                    <!-- Lead/Client Selection -->
                    <div>
                        <label class="block font-body text-sm font-medium text-light-black mb-2">
                            Lead/Client
                            <span class="text-xs text-zurit-gray font-normal ml-1">(Optional - leave empty for internal
                                tasks)</span>
                        </label>
                        <select v-model="editForm.lead_id"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 font-body text-sm text-light-black focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple">
                            <option :value="null">Select a lead/client (or leave as internal task)</option>
                            <option v-for="lead in leads" :key="lead.id" :value="lead.id">
                                {{ getLeadDisplayName(lead) }}
                            </option>
                        </select>
                        <p v-if="editFormErrors.lead_id" class="mt-1 text-sm text-red-500">{{ editFormErrors.lead_id[0]
                            }}</p>
                    </div>

                    <!-- Due Date -->
                    <div>
                        <label class="block font-body text-sm font-medium text-light-black mb-2">Due Date *</label>
                        <div class="relative">
                            <input v-model="editForm.due_date" type="date" placeholder="dd/mm/yyyy"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 pr-10 font-body text-sm text-light-black placeholder-gray-400 focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple" />
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                        <p v-if="editFormErrors.due_date" class="mt-1 text-sm text-red-500">{{
                            editFormErrors.due_date[0] }}</p>
                    </div>

                    <!-- Priority -->
                    <div>
                        <label class="block font-body text-sm font-medium text-light-black mb-2">Priority</label>
                        <select v-model="editForm.priority"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 font-body text-sm text-light-black focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                        <p v-if="editFormErrors.priority" class="mt-1 text-sm text-red-500">{{
                            editFormErrors.priority[0] }}</p>
                    </div>

                    <!-- Type -->
                    <div>
                        <label class="block font-body text-sm font-medium text-light-black mb-2">Type</label>
                        <select v-model="editForm.type"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 font-body text-sm text-light-black focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple">
                            <option value="call">Call</option>
                            <option value="email">Email</option>
                            <option value="meeting">Meeting</option>
                            <option value="follow_up">Follow Up</option>
                            <option value="other">Other</option>
                        </select>
                        <p v-if="editFormErrors.type" class="mt-1 text-sm text-red-500">{{ editFormErrors.type[0] }}</p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-3 pt-4">
                        <button type="submit" :disabled="updating"
                            class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-zurit-purple px-4 py-3 font-body text-sm font-medium text-white hover:bg-zurit-purple/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            <span v-if="updating">Updating...</span>
                            <span v-else>Update Task</span>
                        </button>
                        <button type="button" @click="showEditModal = false"
                            class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-3 font-body text-sm font-medium text-light-black hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50"
            @click="showDeleteModal = false">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full overflow-hidden" @click.stop>
                <!-- Modal Header -->
                <div class="p-6 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-100 mb-4">
                        <svg class="h-7 w-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <h3 class="font-heading text-xl font-semibold text-light-black">Delete Task</h3>
                    <p class="mt-2 font-body text-sm text-zurit-gray">
                        Are you sure you want to delete "{{ taskToDelete?.title }}"? This action cannot be undone.
                    </p>
                </div>

                <!-- Modal Footer -->
                <div class="flex gap-3 p-6 pt-0">
                    <button @click="showDeleteModal = false"
                        class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2.5 font-body text-sm font-medium text-light-black hover:bg-light-gray transition-colors">
                        Cancel
                    </button>
                    <button @click="deleteTask" :disabled="deleting"
                        class="flex-1 rounded-lg bg-red-600 px-4 py-2.5 font-body text-sm font-medium text-white hover:bg-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <span v-if="deleting">Deleting...</span>
                        <span v-else>Delete</span>
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
