<script setup>
import { ref, watch, computed } from 'vue';
import Modal from './Modal.vue';
import PrimaryButton from './PrimaryButton.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    lead: {
        type: Object,
        default: null,
    },
    defaultValue: {
        type: Number,
        default: 0,
    },
    productName: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['close', 'confirm']);

const quantity = ref(1);
const unitPrice = ref(0);
const saving = ref(false);

// Computed total deal value
const totalDealValue = computed(() => {
    const qty = parseInt(quantity.value) || 1;
    const price = parseFloat(unitPrice.value) || 0;
    return qty * price;
});

const resetForm = () => {
    quantity.value = 1;
    unitPrice.value = props.defaultValue || 0;
    saving.value = false;
};

// Watch for modal opening and reset form
watch(() => props.show, (isShowing) => {
    if (isShowing) {
        quantity.value = 1;
        unitPrice.value = props.defaultValue || 0;
    }
    if (!isShowing) {
        resetForm();
    }
});

const handleSubmit = () => {
    saving.value = true;
    emit('confirm', {
        leadId: props.lead?.id,
        value: totalDealValue.value,
        quantity: parseInt(quantity.value) || 1,
    });
};

const handleClose = () => {
    resetForm();
    emit('close');
};

const handleSkip = () => {
    saving.value = true;
    emit('confirm', {
        leadId: props.lead?.id,
        value: 0,
    });
};

const formatCurrency = (value) => {
    if (!value) return '';
    return new Intl.NumberFormat('en-KE').format(value);
};
</script>

<template>
    <Modal :show="show" max-width="md" @close="handleClose">
        <div class="p-6">
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-heading text-xl font-bold text-light-black">Deal Won!</h2>
                        <p class="font-body text-xs text-zurit-gray">Enter the quantity sold to complete this deal</p>
                    </div>
                </div>
                <button @click="handleClose"
                    class="rounded-lg p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Lead Info -->
            <div v-if="lead" class="mb-6 p-4 bg-gray-50 rounded-lg">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="font-body text-sm text-zurit-gray">Client</p>
                        <p class="font-heading text-lg font-semibold text-light-black">
                            {{ lead.contact_type === 'personal' ? lead.name : lead.company }}
                        </p>
                        <p v-if="lead.name && lead.contact_type !== 'personal'" class="font-body text-sm text-zurit-gray">
                            {{ lead.name }}
                        </p>
                    </div>
                    <div v-if="productName" class="text-right">
                        <p class="font-body text-sm text-zurit-gray">Product</p>
                        <p class="font-heading text-base font-semibold text-zurit-purple">
                            {{ productName }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="handleSubmit" class="space-y-6">
                <!-- Unit Price Input -->
                <div>
                    <label for="unitPrice" class="mb-2 block font-body text-sm font-medium text-light-black">
                        Unit Price (Ksh)
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <span class="text-zurit-gray font-body text-sm">Ksh</span>
                        </div>
                        <input
                            id="unitPrice"
                            v-model="unitPrice"
                            type="number"
                            min="0"
                            step="0.01"
                            placeholder="0.00"
                            class="block w-full rounded-lg border border-gray-300 bg-white pl-12 pr-4 py-3 font-body text-lg text-light-black placeholder-zurit-gray focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple"
                        />
                    </div>
                    <p v-if="defaultValue > 0" class="mt-1 font-body text-xs text-zurit-gray">
                        Pre-filled with product price. Adjust if needed.
                    </p>
                </div>

                <!-- Quantity Input -->
                <div>
                    <label for="quantity" class="mb-2 block font-body text-sm font-medium text-light-black">
                        Quantity
                    </label>
                    <input
                        id="quantity"
                        v-model="quantity"
                        type="number"
                        min="1"
                        step="1"
                        placeholder="1"
                        class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 font-body text-lg text-light-black placeholder-zurit-gray focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple"
                    />
                    <p class="mt-1 font-body text-xs text-zurit-gray">
                        Enter the number of units sold
                    </p>
                </div>

                <!-- Total Deal Value Display -->
                <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex justify-between items-center">
                        <span class="font-body text-sm font-medium text-green-700">Total Deal Value</span>
                        <span class="font-heading text-2xl font-bold text-green-700">
                            Ksh {{ formatCurrency(totalDealValue) }}
                        </span>
                    </div>
                    <p v-if="unitPrice > 0" class="mt-1 font-body text-xs text-green-600">
                        {{ quantity }} × Ksh {{ formatCurrency(unitPrice) }}
                    </p>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-between gap-3 border-t border-gray-200 pt-6">
                    <button
                        type="button"
                        @click="handleSkip"
                        class="font-body text-sm text-zurit-gray hover:text-zurit-purple transition-colors"
                        :disabled="saving"
                    >
                        Skip for now
                    </button>
                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            @click="handleClose"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-2 font-body text-sm font-medium text-light-black transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-zurit-purple focus:ring-offset-2"
                            :disabled="saving"
                        >
                            Cancel
                        </button>
                        <PrimaryButton type="submit" :disabled="saving || quantity < 1 || unitPrice <= 0" class="inline-flex items-center gap-2">
                            <span v-if="saving" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Saving...
                            </span>
                            <span v-else>Confirm Deal</span>
                        </PrimaryButton>
                    </div>
                </div>
            </form>
        </div>
    </Modal>
</template>
