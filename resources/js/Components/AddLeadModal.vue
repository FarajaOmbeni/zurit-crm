<script setup>
import { ref, watch, computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Alert from '@/Components/Alert.vue';
import axios from 'axios';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    contactType: {
        type: String,
        default: 'company', // 'company' or 'personal'
        validator: (value) => ['company', 'personal'].includes(value),
    },
});

const emit = defineEmits(['close', 'lead-added']);

// Computed labels based on contact type
const isPersonal = computed(() => props.contactType === 'personal');

const labels = computed(() => {
    if (isPersonal.value) {
        return {
            title: 'Add Personal Contact',
            sectionTitle: 'Personal Details',
            primaryField: 'Full Name',
            primaryPlaceholder: 'Enter full name',
            secondarySection: 'Additional Info',
            positionLabel: 'Occupation',
            positionPlaceholder: 'Their occupation',
            sectorLabel: 'Service Interest',
            sectorPlaceholder: 'Service they are interested in',
        };
    }
    return {
        title: 'Add Company Lead',
        sectionTitle: 'Company Details',
        primaryField: 'Company Name',
        primaryPlaceholder: 'Enter company name',
        secondarySection: 'Contact Person',
        positionLabel: 'Position',
        positionPlaceholder: "Contact person's position",
        sectorLabel: 'Sector',
        sectorPlaceholder: 'Industry sector',
    };
});

// Form fields
const form = ref({
    // Primary field (name for personal, company for company)
    primary_field: '',
    // Contact details
    contact_name: '',
    contact_position: '',
    contact_email: '',
    contact_phone: '',
    // Location
    city: '',
    country: '',
    sector: '',
    source: '',
});

const errors = ref({});
const saving = ref(false);
const notification = ref({ type: null, message: '' });

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
        primary_field: '',
        contact_name: '',
        contact_position: '',
        contact_email: '',
        contact_phone: '',
        city: '',
        country: '',
        sector: '',
        source: '',
    };
    errors.value = {};
    notification.value = { type: null, message: '' };
};

const validateForm = () => {
    errors.value = {};

    // Primary field is required
    if (!form.value.primary_field.trim()) {
        errors.value.primary_field = isPersonal.value
            ? 'Full name is required'
            : 'Company name is required';
    }

    // Contact email is optional, but if provided, must be valid
    if (form.value.contact_email.trim() && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.contact_email)) {
        errors.value.contact_email = 'Please enter a valid email address';
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
        // Build payload based on contact type
        const payload = {
            contact_type: props.contactType,
            email: form.value.contact_email,
            phone: form.value.contact_phone,
            city: form.value.city,
            country: form.value.country,
            sector: form.value.sector,
            source: form.value.source,
            status: 'new_lead',
        };

        if (isPersonal.value) {
            // For personal contacts: name is primary, company is secondary (optional)
            payload.name = form.value.primary_field;
            payload.company = form.value.contact_name || null; // Use contact_name field for workplace
            payload.position = form.value.contact_position;
        } else {
            // For company contacts: company is primary, name is contact person
            payload.company = form.value.primary_field;
            payload.name = form.value.contact_name;
            payload.position = form.value.contact_position;
        }

        const response = await axios.post('/api/leads', payload);

        // Show success notification
        notification.value = {
            type: 'success',
            message: isPersonal.value ? 'Contact added successfully!' : 'Lead added successfully!',
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
            // Map backend field names to frontend field names
            const backendErrors = error.response.data.errors;
            const mappedErrors = {};

            for (const [key, value] of Object.entries(backendErrors)) {
                if (key === 'email') {
                    mappedErrors.contact_email = Array.isArray(value) ? value[0] : value;
                } else if (key === 'phone') {
                    mappedErrors.contact_phone = Array.isArray(value) ? value[0] : value;
                } else if (key === 'company' || key === 'name') {
                    mappedErrors.primary_field = Array.isArray(value) ? value[0] : value;
                } else {
                    mappedErrors[key] = Array.isArray(value) ? value[0] : value;
                }
            }

            errors.value = mappedErrors;

            // Also show a general error message for duplicates
            if (error.response?.data?.message) {
                errors.value.submit = error.response.data.message;
            }
        } else {
            errors.value.submit = error.response?.data?.message || 'Failed to add. Please try again.';
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
                    <!-- Type indicator icon -->
                    <div :class="isPersonal ? 'bg-green-100' : 'bg-blue-100'" class="w-10 h-10 rounded-full flex items-center justify-center">
                        <svg v-if="isPersonal" class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <svg v-else class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h2 class="font-heading text-2xl font-bold text-light-black">{{ labels.title }}</h2>
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
                <!-- Primary Details Section -->
                <div class="pt-6">
                    <label class="mb-4 block font-body text-md font-medium text-light-black">
                        {{ labels.sectionTitle }}
                    </label>

                    <!-- Primary Field (Company Name or Full Name) -->
                    <div class="mb-4">
                        <label for="primary_field" class="mb-2 block font-body text-sm font-medium text-light-black">
                            {{ labels.primaryField }} <span class="text-red-500">*</span>
                        </label>
                        <input id="primary_field" v-model="form.primary_field" type="text" :placeholder="labels.primaryPlaceholder"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 font-body text-sm text-light-black placeholder-zurit-gray focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple"
                            :class="{ 'border-red-500': errors.primary_field }" />
                        <p v-if="errors.primary_field" class="mt-1 text-sm text-red-600">{{ errors.primary_field }}</p>
                    </div>

                    <!-- Country -->
                    <div class="mb-4">
                        <label for="country" class="mb-2 block font-body text-sm font-medium text-light-black">
                            Country
                        </label>
                        <input id="country" v-model="form.country" type="text" placeholder="Country"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 font-body text-sm text-light-black placeholder-zurit-gray focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple" />
                    </div>

                    <!-- City -->
                    <div class="mb-4">
                        <label for="city" class="mb-2 block font-body text-sm font-medium text-light-black">
                            City
                        </label>
                        <input id="city" v-model="form.city" type="text" placeholder="City"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 font-body text-sm text-light-black placeholder-zurit-gray focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple" />
                    </div>

                    <!-- Sector / Interest -->
                    <div class="mb-4">
                        <label for="sector" class="mb-2 block font-body text-sm font-medium text-light-black">
                            {{ labels.sectorLabel }}
                        </label>
                        <input id="sector" v-model="form.sector" type="text" :placeholder="labels.sectorPlaceholder"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 font-body text-sm text-light-black placeholder-zurit-gray focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple" />
                    </div>

                    <!-- Source -->
                    <div>
                        <label for="source" class="mb-2 block font-body text-sm font-medium text-light-black">
                            Source
                        </label>
                        <div class="relative">
                            <select id="source" v-model="form.source"
                                class="block w-full appearance-none mb-8 rounded-lg border border-gray-300 bg-white px-3 py-2 pr-10 font-body text-sm text-light-black focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple">
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

                    <!-- Secondary Section (Contact Person for Company / Additional Info for Personal) -->
                    <div>
                        <label class="mb-4 block font-body text-sm font-medium text-light-black">
                            {{ labels.secondarySection }}
                        </label>

                        <!-- Secondary Name Field -->
                        <div class="mb-4">
                            <label for="contact_name" class="mb-2 block font-body text-sm font-medium text-light-black">
                                {{ isPersonal ? 'Workplace/Company' : 'Name' }}
                            </label>
                            <input id="contact_name" v-model="form.contact_name" type="text"
                                :placeholder="isPersonal ? 'Where they work (optional)' : 'Contact person\'s name'"
                                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 font-body text-sm text-light-black placeholder-zurit-gray focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple" />
                        </div>

                        <!-- Position / Occupation -->
                        <div class="mb-4">
                            <label for="contact_position"
                                class="mb-2 block font-body text-sm font-medium text-light-black">
                                {{ labels.positionLabel }}
                            </label>
                            <input id="contact_position" v-model="form.contact_position" type="text"
                                :placeholder="labels.positionPlaceholder"
                                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 font-body text-sm text-light-black placeholder-zurit-gray focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple" />
                        </div>

                        <!-- Email -->
                        <div class="mb-4">
                            <label for="contact_email"
                                class="mb-2 block font-body text-sm font-medium text-light-black">
                                Email
                            </label>
                            <input id="contact_email" v-model="form.contact_email" type="email"
                                placeholder="email@example.com"
                                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 font-body text-sm text-light-black placeholder-zurit-gray focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple"
                                :class="{ 'border-red-500': errors.contact_email }" />
                            <p v-if="errors.contact_email" class="mt-1 text-sm text-red-600">{{ errors.contact_email }}
                            </p>
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="contact_phone"
                                class="mb-2 block font-body text-sm font-medium text-light-black">
                                Phone
                            </label>
                            <input id="contact_phone" v-model="form.contact_phone" type="tel"
                                placeholder="+254 700 000 000"
                                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 font-body text-sm text-light-black placeholder-zurit-gray focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple"
                                :class="{ 'border-red-500': errors.contact_phone }" />
                            <p v-if="errors.contact_phone" class="mt-1 text-sm text-red-600">{{ errors.contact_phone }}
                            </p>
                        </div>
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
                        <span v-else>{{ isPersonal ? 'Add Contact' : 'Add Lead' }}</span>
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
