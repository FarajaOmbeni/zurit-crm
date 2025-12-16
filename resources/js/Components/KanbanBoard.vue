<script setup>
import { ref, onMounted, computed, watch, nextTick } from 'vue';
import axios from 'axios';
import LeadCard from './LeadCard.vue';
import LeadNotesModal from './LeadNotesModal.vue';

const props = defineProps({
    searchQuery: {
        type: String,
        default: '',
    },
    productId: {
        type: Number,
        required: true,
    },
});

const emit = defineEmits(['addLead', 'viewLead']);

const leads = ref({});
const loading = ref(false);
const draggedLead = ref(null);
const draggedFromColumn = ref(null);
const headerScrollRef = ref(null);
const contentScrollRef = ref(null);
const showNotesModal = ref(false);
const selectedLeadForNotes = ref(null);

// Pipeline stages configuration
const stages = [
    {
        slug: 'new_lead',
        name: 'New',
        color: '#7639C2', // Purple
        dotColor: 'bg-purple-500',
    },
    {
        slug: 'initial_outreach',
        name: 'Initial Outreach',
        color: '#3B82F6', // Blue
        dotColor: 'bg-blue-500',
    },
    {
        slug: 'follow_ups',
        name: 'Follow-ups',
        color: '#FF5B5D', // Pink/Red
        dotColor: 'bg-pink-500',
    },
    {
        slug: 'negotiations',
        name: 'Negotiation',
        color: '#F97316', // Orange
        dotColor: 'bg-orange-500',
    },
    {
        slug: 'won',
        name: 'Closing',
        color: '#10B981', // Green
        dotColor: 'bg-green-500',
    },
];

const fetchLeads = async () => {
    if (!props.productId) {
        console.warn('Product ID is required to fetch leads');
        return;
    }

    loading.value = true;
    try {
        const response = await axios.get('/api/leads/kanban', {
            params: {
                product_id: props.productId,
            },
        });
        console.log('Kanban API Response:', response.data);
        console.log('Product ID:', props.productId);
        leads.value = response.data.leads || {};
        console.log('Leads loaded:', leads.value);
        console.log('Leads keys:', Object.keys(leads.value));
        console.log('Total leads count:', Object.values(leads.value).flat().length);
    } catch (error) {
        console.error('Error fetching leads:', error);
        if (error.response) {
            console.error('Response status:', error.response.status);
            console.error('Response data:', error.response.data);
        }
    } finally {
        loading.value = false;
    }
};

const getLeadsForStage = (stageSlug) => {
    const stageLeads = leads.value[stageSlug] || [];

    // Debug logging
    if (stageLeads.length > 0) {
        console.log(`Stage ${stageSlug}: ${stageLeads.length} leads`);
    }

    // Filter by search query if provided
    if (props.searchQuery) {
        const query = props.searchQuery.toLowerCase();
        return stageLeads.filter(lead =>
            lead.name?.toLowerCase().includes(query) ||
            lead.company?.toLowerCase().includes(query) ||
            lead.product?.toLowerCase().includes(query) ||
            lead.product_pivot?.status?.toLowerCase().includes(query)
        );
    }

    return stageLeads;
};

const getLeadCount = (stageSlug) => {
    return getLeadsForStage(stageSlug).length;
};

const getStageBackgroundColor = (color) => {
    // Convert hex to rgba with 10% opacity
    const hex = color.replace('#', '');
    const r = parseInt(hex.substring(0, 2), 16);
    const g = parseInt(hex.substring(2, 4), 16);
    const b = parseInt(hex.substring(4, 6), 16);
    return `rgba(${r}, ${g}, ${b}, 0.1)`;
};

const handleDragStart = (event, lead, stageSlug) => {
    draggedLead.value = lead;
    draggedFromColumn.value = stageSlug;
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/html', event.target);
    event.target.style.opacity = '0.5';
};

const handleDragEnd = (event) => {
    event.target.style.opacity = '1';
    draggedLead.value = null;
    draggedFromColumn.value = null;
};

const handleDragOver = (event) => {
    event.preventDefault();
    event.dataTransfer.dropEffect = 'move';
};

const handleDrop = async (event, targetStageSlug) => {
    event.preventDefault();

    if (!draggedLead.value || draggedFromColumn.value === targetStageSlug) {
        return;
    }

    const leadId = draggedLead.value.id;
    const oldStage = draggedFromColumn.value;

    // Optimistically update UI
    if (leads.value[oldStage]) {
        leads.value[oldStage] = leads.value[oldStage].filter(l => l.id !== leadId);
    }

    if (!leads.value[targetStageSlug]) {
        leads.value[targetStageSlug] = [];
    }

    const updatedLead = { ...draggedLead.value, status: targetStageSlug };
    leads.value[targetStageSlug].push(updatedLead);

    // Update on server
    try {
        await axios.patch(`/api/leads/${leadId}/status`, {
            status: targetStageSlug,
            product_id: props.productId,
        });
    } catch (error) {
        console.error('Error updating lead status:', error);
        // Revert on error
        if (leads.value[targetStageSlug]) {
            leads.value[targetStageSlug] = leads.value[targetStageSlug].filter(l => l.id !== leadId);
        }
        if (leads.value[oldStage]) {
            leads.value[oldStage].push(draggedLead.value);
        }
    }
};

const handleAddLead = (stageSlug) => {
    emit('addLead', stageSlug);
};

const handleViewLead = (lead) => {
    emit('viewLead', lead);
};

const handleNotes = (lead) => {
    selectedLeadForNotes.value = lead;
    showNotesModal.value = true;
};

const handleCloseNotesModal = () => {
    showNotesModal.value = false;
    selectedLeadForNotes.value = null;
};

const handleNoteAdded = (eventData) => {
    // Update the lead's product_pivot.notes in the local leads state
    // This will cause the notesCount computed property in LeadCard to update
    if (eventData && eventData.leadId && eventData.notes !== undefined) {
        // Find the lead in all stages and update its product_pivot.notes
        Object.keys(leads.value).forEach((stageSlug) => {
            const stageLeads = leads.value[stageSlug];
            if (Array.isArray(stageLeads)) {
                const leadIndex = stageLeads.findIndex(l => l.id === eventData.leadId);
                if (leadIndex !== -1) {
                    // Create a new lead object with updated product_pivot to ensure reactivity
                    const existingPivot = stageLeads[leadIndex].product_pivot || {};
                    const updatedLead = {
                        ...stageLeads[leadIndex],
                        product_pivot: {
                            ...existingPivot,
                            notes: eventData.notes,
                        },
                    };
                    // Create a new array with the updated lead to trigger reactivity
                    const updatedLeads = [...stageLeads];
                    updatedLeads[leadIndex] = updatedLead;
                    leads.value[stageSlug] = updatedLeads;
                }
            }
        });
    }
};

const syncScroll = (sourceElement, targetElement) => {
    if (sourceElement && targetElement) {
        targetElement.scrollLeft = sourceElement.scrollLeft;
    }
};

const handleHeaderScroll = (event) => {
    syncScroll(event.target, contentScrollRef.value);
};

const handleContentScroll = (event) => {
    syncScroll(event.target, headerScrollRef.value);
};

// Watch for productId changes and refetch leads
watch(() => props.productId, (newProductId) => {
    if (newProductId) {
        fetchLeads();
    }
}, { immediate: false });

onMounted(() => {
    if (props.productId) {
        fetchLeads();
    }
});
</script>

<template>
    <div v-if="loading" class="flex items-center justify-center py-12">
        <p class="text-gray-500">Loading leads...</p>
    </div>

    <div v-else-if="Object.keys(leads).length === 0" class="flex items-center justify-center py-12">
        <div class="text-center">
            <p class="text-gray-500 mb-2">No leads found</p>
            <p class="text-sm text-gray-400">Leads will appear here once they are added to the system</p>
        </div>
    </div>

    <div v-else class="mt-6">
        <!-- Fixed Stage Headers -->
        <div class="sticky top-0 z-10 py-2 pl-2 mb-4 border-b border-gray-200" :style="{ backgroundColor: '#ffffff' }">
            <div ref="headerScrollRef" @scroll="handleHeaderScroll" class="flex overflow-x-auto kanban-scrollbar">
                <div v-for="(stage, index) in stages" :key="stage.slug"
                    :class="['flex-shrink-0 w-80', index > 0 ? 'border-l border-gray-200 pl-4' : 'pl-0']"
                    :style="{ backgroundColor: getStageBackgroundColor(stage.color) }">
                    <div class="flex items-center gap-2 pr-4">
                        <div :class="['h-3 w-3 rounded-full', stage.dotColor]"></div>
                        <h3 class="font-heading text-lg font-semibold text-light-black">
                            {{ stage.name }}
                        </h3>
                        <span class="ml-auto mr-14 font-body text-sm text-zurit-gray">
                            {{ getLeadCount(stage.slug) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scrollable Content -->
        <div ref="contentScrollRef" @scroll="handleContentScroll" class="flex overflow-x-auto pb-4 kanban-scrollbar">
            <div v-for="(stage, index) in stages" :key="stage.slug"
                :class="['flex-shrink-0 w-80', index > 0 ? 'border-l border-gray-200 pl-4' : 'pl-0']">
                <!-- Column Content -->
                <div class="min-h-[400px] rounded-lg p-4 pr-4 space-y-3" @dragover="handleDragOver"
                    @drop="handleDrop($event, stage.slug)"
                    :style="{ backgroundColor: getStageBackgroundColor(stage.color) }">
                    <!-- Lead Cards -->
                    <LeadCard v-for="lead in getLeadsForStage(stage.slug)" :key="lead.id" :lead="lead"
                        :product-id="productId" @drag-start="handleDragStart($event, lead, stage.slug)"
                        @drag-end="handleDragEnd" @view="handleViewLead" @notes="handleNotes" />

                    <!-- Add Client Button -->
                    <button v-if="stage.slug === 'new_lead'" @click="handleAddLead(stage.slug)"
                        class="w-full rounded-lg border-2 border-dashed border-gray-300 bg-white p-4 text-center font-body text-sm text-zurit-gray transition-colors hover:border-zurit-purple hover:text-zurit-purple">
                        <div class="flex items-center justify-center gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            <span>+ Add Client</span>
                        </div>
                    </button>

                    <!-- Drag & Drop Button -->
                    <button v-else @click="handleAddLead(stage.slug)"
                        class="w-full rounded-lg border-2 border-dashed border-gray-300 bg-white p-4 text-center font-body text-sm text-zurit-gray transition-colors hover:border-zurit-purple hover:text-zurit-purple">
                        <div class="flex items-center justify-center gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            <span>+ Drag & Drop a Company</span>
                        </div>
                    </button>
                </div>
            </div>
        </div>

        <!-- Notes Modal -->
        <LeadNotesModal :show="showNotesModal" :lead="selectedLeadForNotes" :product-id="productId"
            @close="handleCloseNotesModal" @note-added="handleNoteAdded" />
    </div>
</template>

<style scoped>
.kanban-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f1f5f9;
}

.kanban-scrollbar::-webkit-scrollbar {
    height: 8px;
}

.kanban-scrollbar::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

.kanban-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.kanban-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
