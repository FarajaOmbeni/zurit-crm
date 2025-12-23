<script setup>
import { ref, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Alert from '@/Components/Alert.vue';
import LocationSelect from '@/Components/LocationSelect.vue';
import axios from 'axios';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'lead-added']);

// Form fields matching personal lead import columns
const form = ref({
    name: '',           // Full Name (required)
    email: '',          // Email
    phone: '',          // Phone
    company: '',        // Workplace/Company
    position: '',       // Occupation
    city: '',           // City
    country: '',        // Country
    source: '',         // Source
    sector: '',         // Service Interest
});

const errors = ref({});
const saving = ref(false);
const notification = ref({ type: null, message: '' });

// Source options
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
        name: '',
        email: '',
        phone: '',
        company: '',
        position: '',
        city: '',
        country: '',
        source: '',
        sector: '',
    };
    errors.value = {};
    notification.value = { type: null, message: '' };
};

const validateForm = () => {
    errors.value = {};

    // Full name is required for personal contacts
    if (!form.value.name.trim()) {
        errors.value.name = 'Full name is required';
    }

    // Email is optional, but if provided, must be valid
    if (form.value.email.trim() && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) {
        errors.value.email = 'Please enter a valid email address';
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
            contact_type: 'personal',
            name: form.value.name,
            email: form.value.email,
            phone: form.value.phone,
            company: form.value.company || null,
            position: form.value.position,
            city: form.value.city,
            country: form.value.country,
            source: form.value.source,
            sector: form.value.sector,
            status: 'new_lead',
        });

        notification.value = {
            type: 'success',
            message: 'Contact added successfully!',
        };

        emit('lead-added', response.data);

        setTimeout(() => {
            resetForm();
            emit('close');
        }, 1500);
    } catch (error) {
        console.error('Error adding contact:', error);
        if (error.response?.data?.errors) {
            const backendErrors = error.response.data.errors;
            const mappedErrors = {};

            for (const [key, value] of Object.entries(backendErrors)) {
                mappedErrors[key] = Array.isArray(value) ? value[0] : value;
            }

            errors.value = mappedErrors;

            if (error.response?.data?.message) {
                errors.value.submit = error.response.data.message;
            }
        } else {
            errors.value.submit = error.response?.data?.message || 'Failed to add contact. Please try again.';
        }
    } finally {
        saving.value = false;
    }
};

const handleClose = () => {
    resetForm();
    emit('close');
};

watch(() => props.show, (isShowing) => {
    if (!isShowing) {
        resetForm();
    }
});
</script>

<template>
    <!-- Error Message (Top of Modal, Floating Banner) -->
    <div v-if="errors.submit"
        class="fixed left-1/2 top-8 z-50 flex w-full max-w-xl -translate-x-1/2 justify-center pointer-events-none">
        <Alert type="error" :message="errors.submit" class="w-full shadow-lg pointer-events-auto" />
    </div>

    <Modal :show="show" max-width="2xl" @close="handleClose">
        <div class="p-6">
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-gray-200 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <h2 class="font-heading text-2xl font-bold text-light-black">Add Personal Contact</h2>
                </div>
                <button @click="handleClose"
                    class="rounded-lg p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="handleSubmit" class="space-y-6">
                <!-- Personal Details Section -->
                <div class="pt-6">
                    <label class="mb-4 block font-body text-md font-medium text-light-black">
                        Personal Details
                    </label>

                    <!-- Full Name (Required) -->
                    <div class="mb-4">
                        <label for="name" class="mb-2 block font-body text-sm font-medium text-light-black">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input id="name" v-model="form.name" type="text" placeholder="Enter full name"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 font-body text-sm text-light-black placeholder-zurit-gray focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple"
                            :class="{ 'border-red-500': errors.name }" />
                        <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                    </div>

                    <!-- Email -->
                    <div class="mb-4">
                        <label for="email" class="mb-2 block font-body text-sm font-medium text-light-black">
                            Email
                        </label>
                        <input id="email" v-model="form.email" type="email" placeholder="email@example.com"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 font-body text-sm text-light-black placeholder-zurit-gray focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple"
                            :class="{ 'border-red-500': errors.email }" />
                        <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
                    </div>

                    <!-- Phone -->
                    <div class="mb-4">
                        <label for="phone" class="mb-2 block font-body text-sm font-medium text-light-black">
                            Phone
                        </label>
                        <input id="phone" v-model="form.phone" type="tel" placeholder="+254 700 000 000"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 font-body text-sm text-light-black placeholder-zurit-gray focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple"
                            :class="{ 'border-red-500': errors.phone }" />
                        <p v-if="errors.phone" class="mt-1 text-sm text-red-600">{{ errors.phone }}</p>
                    </div>

                    <!-- Country & City -->
                    <LocationSelect
                        v-model:country="form.country"
                        v-model:city="form.city"
                    />

                    <!-- Source -->
                    <div class="mb-4">
                        <label for="source" class="mb-2 block font-body text-sm font-medium text-light-black">
                            Source
                        </label>
                        <div class="relative">
                            <select id="source" v-model="form.source"
                                class="block w-full appearance-none rounded-lg border border-gray-300 bg-white px-3 py-2 pr-10 font-body text-sm text-light-black focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple">
                                <option value="">Select source</option>
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
                </div>

                <!-- Additional Info Section -->
                <div>
                    <label class="mb-4 block font-body text-md font-medium text-light-black">
                        Additional Info
                    </label>

                    <!-- Workplace/Company -->
                    <div class="mb-4">
                        <label for="company" class="mb-2 block font-body text-sm font-medium text-light-black">
                            Workplace/Company
                        </label>
                        <input id="company" v-model="form.company" type="text" placeholder="Where they work (optional)"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 font-body text-sm text-light-black placeholder-zurit-gray focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple" />
                    </div>

                    <!-- Occupation -->
                    <div class="mb-4">
                        <label for="position" class="mb-2 block font-body text-sm font-medium text-light-black">
                            Occupation
                        </label>
                        <input id="position" v-model="form.position" type="text" placeholder="Their occupation"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 font-body text-sm text-light-black placeholder-zurit-gray focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple" />
                    </div>

                    <!-- Service Interest -->
                    <div>
                        <label for="sector" class="mb-2 block font-body text-sm font-medium text-light-black">
                            Service Interest
                        </label>
                        <input id="sector" v-model="form.sector" type="text" placeholder="Service they are interested in"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 font-body text-sm text-light-black placeholder-zurit-gray focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple" />
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6">
                    <button type="button" @click="handleClose"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 font-body text-sm font-medium text-light-black transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-zurit-purple focus:ring-offset-2"
                        :disabled="saving">
                        Cancel
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
                        <span v-else>Add Contact</span>
                    </PrimaryButton>
                </div>
            </form>

            <!-- Success Notification -->
            <div v-if="notification.type === 'success'" class="mt-6">
                <Alert type="success" :message="notification.message" />
            </div>
        </div>
    </Modal>
</template>
