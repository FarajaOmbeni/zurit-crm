<script setup>
import { ref, watch, computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import axios from 'axios';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'lead-added']);

// Form fields
const form = ref({
    company_name: '',
    company_email: '',
    physical_location: '',
    contact_name: '',
    contact_email: '',
    phone_number: '',
    source: '',
    selected_products: [], // Array of product IDs
});

const errors = ref({});
const saving = ref(false);
const notification = ref({ type: null, message: '' });

// Fetch products for services field
const products = ref([]);
const loadingProducts = ref(false);

const fetchProducts = async () => {
    loadingProducts.value = true;
    try {
        const response = await axios.get('/api/products', {
            params: {
                per_page: 100,
            },
        });
        products.value = response.data.data || [];
    } catch (error) {
        console.error('Error fetching products:', error);
    } finally {
        loadingProducts.value = false;
    }
};

// Source options (common lead sources)
const sourceOptions = [
    'Website',
    'Referral',
    'Social Media',
    'Email Campaign',
    'Cold Call',
    'Trade Show',
    'Partner',
    'Other',
];

const resetForm = () => {
    form.value = {
        company_name: '',
        company_email: '',
        physical_location: '',
        contact_name: '',
        contact_email: '',
        phone_number: '',
        source: '',
        selected_products: [],
    };
    errors.value = {};
    notification.value = { type: null, message: '' };
};

const validateForm = () => {
    errors.value = {};

    if (!form.value.company_name.trim()) {
        errors.value.company_name = 'Company name is required';
    }

    if (!form.value.company_email.trim()) {
        errors.value.company_email = 'Company email is required';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.company_email)) {
        errors.value.company_email = 'Please enter a valid email address';
    }

    if (!form.value.contact_email.trim()) {
        errors.value.contact_email = 'Contact email is required';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.contact_email)) {
        errors.value.contact_email = 'Please enter a valid email address';
    }

    if (!form.value.phone_number.trim()) {
        errors.value.phone_number = 'Phone number is required';
    }

    return Object.keys(errors.value).length === 0;
};

const handleSubmit = async () => {
    if (!validateForm()) {
        return;
    }

    saving.value = true;
    errors.value = {};
    notification.value = { type: null, message: '' };

    try {
        const response = await axios.post('/api/leads', {
            company: form.value.company_name,
            email: form.value.contact_email,
            phone: form.value.phone_number,
            name: form.value.contact_name,
            city: form.value.physical_location,
            source: form.value.source,
            product_ids: form.value.selected_products, // Send selected product IDs
            status: 'new_lead',
        });

        // Show success notification
        notification.value = {
            type: 'success',
            message: 'Lead added successfully!',
        };

        // Emit success event
        emit('lead-added', response.data);

        // Close modal after 1.5 seconds
        setTimeout(() => {
            resetForm();
            emit('close');
        }, 1500);
    } catch (error) {
        console.error('Error adding lead:', error);
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        } else {
            errors.value.submit = error.response?.data?.message || 'Failed to add lead. Please try again.';
        }
    } finally {
        saving.value = false;
    }
};

const handleClose = () => {
    resetForm();
    emit('close');
};

// Watch for modal show/hide
watch(() => props.show, (isShowing) => {
    if (isShowing) {
        fetchProducts();
    } else {
        resetForm();
    }
});
</script>

<template>
    <Modal :show="show" max-width="2xl" @close="handleClose">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between border-b border-gray-200 pb-4">
                <h2 class="font-heading text-2xl font-bold text-light-black">Add New Lead</h2>
                <button @click="handleClose"
                    class="rounded-lg p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Error Message -->
            <div v-if="errors.submit" class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="font-body text-sm text-red-800">{{ errors.submit }}</p>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="handleSubmit" class="space-y-6">
                <!-- Company Name -->
                <div>
                    <label for="company_name" class="mb-2 block font-body text-sm font-medium text-light-black">
                        Company Name <span class="text-red-500">*</span>
                    </label>
                    <input id="company_name" v-model="form.company_name" type="text"
                        placeholder="What is the name of the company or client?"
                        class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 font-body text-sm text-light-black placeholder-zurit-gray focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple"
                        :class="{ 'border-red-500': errors.company_name }" />
                    <p v-if="errors.company_name" class="mt-1 text-sm text-red-600">{{ errors.company_name }}</p>
                </div>

                <!-- Company Email -->
                <div>
                    <label for="company_email" class="mb-2 block font-body text-sm font-medium text-light-black">
                        Company's Email Address <span class="text-red-500">*</span>
                    </label>
                    <input id="company_email" v-model="form.company_email" type="email"
                        placeholder="company@example.com"
                        class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 font-body text-sm text-light-black placeholder-zurit-gray focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple"
                        :class="{ 'border-red-500': errors.company_email }" />
                    <p v-if="errors.company_email" class="mt-1 text-sm text-red-600">{{ errors.company_email }}</p>
                </div>

                <!-- Physical Location -->
                <div>
                    <label for="physical_location" class="mb-2 block font-body text-sm font-medium text-light-black">
                        Physical Location
                    </label>
                    <input id="physical_location" v-model="form.physical_location" type="text"
                        placeholder="City, Country"
                        class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 font-body text-sm text-light-black placeholder-zurit-gray focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple" />
                </div>

                <!-- Contact Person Section -->
                <div class="border-t border-gray-200 pt-4">
                    <label class="mb-4 block font-body text-sm font-medium text-light-black">
                        <span class="text-red-500">*</span> Contact Person
                    </label>

                    <!-- Contact Name -->
                    <div class="mb-4">
                        <label for="contact_name" class="mb-2 block font-body text-sm font-medium text-light-black">
                            Name
                        </label>
                        <input id="contact_name" v-model="form.contact_name" type="text"
                            placeholder="What is the name of the contact person?"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 font-body text-sm text-light-black placeholder-zurit-gray focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple" />
                    </div>

                    <!-- Contact Email -->
                    <div class="mb-4">
                        <label for="contact_email" class="mb-2 block font-body text-sm font-medium text-light-black">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input id="contact_email" v-model="form.contact_email" type="email"
                            placeholder="contact@example.com"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 font-body text-sm text-light-black placeholder-zurit-gray focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple"
                            :class="{ 'border-red-500': errors.contact_email }" />
                        <p v-if="errors.contact_email" class="mt-1 text-sm text-red-600">{{ errors.contact_email }}</p>
                    </div>

                    <!-- Phone Number -->
                    <div>
                        <label for="phone_number" class="mb-2 block font-body text-sm font-medium text-light-black">
                            Phone Number <span class="text-red-500">*</span>
                        </label>
                        <input id="phone_number" v-model="form.phone_number" type="tel" placeholder="+254 700 000 000"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 font-body text-sm text-light-black placeholder-zurit-gray focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple"
                            :class="{ 'border-red-500': errors.phone_number }" />
                        <p v-if="errors.phone_number" class="mt-1 text-sm text-red-600">{{ errors.phone_number }}</p>
                    </div>
                </div>

                <!-- Services Field -->
                <div>
                    <label class="mb-2 block font-body text-sm font-medium text-light-black">
                        Services
                    </label>
                    <div v-if="loadingProducts" class="text-sm text-zurit-gray">Loading products...</div>
                    <div v-else class="space-y-2 max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-3">
                        <label v-for="product in products" :key="product.id"
                            class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                            <input type="checkbox" :value="product.id" v-model="form.selected_products"
                                class="rounded border-gray-300 text-zurit-purple focus:ring-zurit-purple" />
                            <span class="font-body text-sm text-light-black">{{ product.name }}</span>
                            <span v-if="product.category" class="font-body text-xs text-zurit-gray">({{ product.category
                            }})</span>
                        </label>
                        <p v-if="products.length === 0" class="text-sm text-zurit-gray">No products available</p>
                    </div>
                    <p class="mt-1 text-xs text-zurit-gray">Select the services this lead is interested in</p>
                </div>

                <!-- Source of Lead -->
                <div>
                    <label for="source" class="mb-2 block font-body text-sm font-medium text-light-black">
                        Source of Lead
                    </label>
                    <div class="relative">
                        <select id="source" v-model="form.source"
                            class="block w-full appearance-none rounded-lg border border-gray-300 bg-white px-3 py-2 pr-10 font-body text-sm text-light-black focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple">
                            <option value="">What is the source of the lead?</option>
                            <option v-for="source in sourceOptions" :key="source" :value="source">
                                {{ source }}
                            </option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg class="h-5 w-5 text-zurit-purple" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6">
                    <button type="button" @click="handleClose"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 font-body text-sm font-medium text-light-black transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-zurit-purple focus:ring-offset-2"
                        :disabled="saving">
                        Clear
                    </button>
                    <PrimaryButton type="submit" :disabled="saving" class="inline-flex items-center gap-2">
                        <svg v-if="!saving" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span v-if="saving" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Adding...
                        </span>
                        <span v-else>Add New Lead</span>
                    </PrimaryButton>
                </div>
            </form>

            <!-- Success Notification -->
            <div v-if="notification.type === 'success'"
                class="fixed bottom-4 left-1/2 transform -translate-x-1/2 z-50 pointer-events-none">
                <div
                    class="bg-green-50 border border-green-200 rounded-lg shadow-lg px-6 py-4 flex items-center space-x-3">
                    <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="font-body text-sm font-medium text-green-800">{{ notification.message }}</span>
                </div>
            </div>
        </div>
    </Modal>
</template>
