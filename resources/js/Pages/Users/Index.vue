<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

defineProps({
    users: {
        type: Array,
        required: true,
    },
});

const showCreateModal = ref(false);

const form = useForm({
    name: '',
    email: '',
    role: 'team_member',
    manager_id: null,
    is_active: true,
});

const submit = () => {
    // Convert email to lowercase before submitting
    form.email = form.email.toLowerCase().trim();
    
    form.post(route('users.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            showCreateModal.value = false;
        },
    });
};

const closeModal = () => {
    form.reset();
    form.clearErrors();
    showCreateModal.value = false;
};

const getRoleBadgeClass = (role) => {
    switch (role) {
        case 'admin':
            return 'bg-zurit-purple text-white';
        case 'manager':
            return 'bg-prosper text-white';
        case 'team_member':
            return 'bg-zurit-gray text-white';
        default:
            return 'bg-gray-200 text-gray-800';
    }
};

const formatRole = (role) => {
    return role.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const resendOtp = (userId) => {
    if (confirm('Are you sure you want to resend the OTP to this user?')) {
        router.post(route('users.resend-otp', userId), {}, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Manage Users" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Header Section -->
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="font-heading text-3xl font-bold text-light-black">Manage Users</h1>
                    <PrimaryButton @click="showCreateModal = true">
                        Create User
                    </PrimaryButton>
                </div>

                <!-- Success Message -->
                <div
                    v-if="form.recentlySuccessful"
                    class="mb-4 rounded-lg bg-green-50 border border-green-200 p-4"
                >
                    <p class="font-body text-sm text-green-800">
                        User created successfully.
                    </p>
                </div>

                <!-- Users Table -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div v-if="users.length === 0" class="p-12 text-center">
                        <p class="font-body text-lg text-zurit-gray">No users found.</p>
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-light-gray">
                                <tr>
                                    <th class="px-6 py-3 text-left font-heading text-xs font-medium uppercase tracking-wider text-light-black">
                                        Name
                                    </th>
                                    <th class="px-6 py-3 text-left font-heading text-xs font-medium uppercase tracking-wider text-light-black">
                                        Email
                                    </th>
                                    <th class="px-6 py-3 text-left font-heading text-xs font-medium uppercase tracking-wider text-light-black">
                                        Role
                                    </th>
                                    <th class="px-6 py-3 text-left font-heading text-xs font-medium uppercase tracking-wider text-light-black">
                                        Manager
                                    </th>
                                    <th class="px-6 py-3 text-left font-heading text-xs font-medium uppercase tracking-wider text-light-black">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left font-heading text-xs font-medium uppercase tracking-wider text-light-black">
                                        Created At
                                    </th>
                                    <th class="px-6 py-3 text-left font-heading text-xs font-medium uppercase tracking-wider text-light-black">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="user in users" :key="user.id" class="hover:bg-light-gray">
                                    <td class="whitespace-nowrap px-6 py-4 font-body text-sm text-light-black">
                                        {{ user.name }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 font-body text-sm text-zurit-gray">
                                        {{ user.email }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span
                                            :class="[
                                                'inline-flex rounded-full px-3 py-1 text-xs font-semibold font-body',
                                                getRoleBadgeClass(user.role)
                                            ]"
                                        >
                                            {{ formatRole(user.role) }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 font-body text-sm text-zurit-gray">
                                        {{ user.manager_name || '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span
                                            :class="[
                                                'inline-flex rounded-full px-3 py-1 text-xs font-semibold font-body',
                                                user.is_active
                                                    ? 'bg-green-100 text-green-800'
                                                    : 'bg-red-100 text-red-800'
                                            ]"
                                        >
                                            {{ user.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 font-body text-sm text-zurit-gray">
                                        {{ user.created_at }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 font-body text-sm">
                                        <button
                                            v-if="user.has_otp && user.otp_expired"
                                            @click="resendOtp(user.id)"
                                            class="inline-flex items-center rounded-lg bg-prosper px-3 py-1.5 text-xs font-medium text-white hover:bg-prosper/90 focus:outline-none focus:ring-2 focus:ring-prosper focus:ring-offset-2 transition-colors"
                                        >
                                            Resend OTP
                                        </button>
                                        <span v-else-if="user.has_otp && !user.otp_expired" class="text-zurit-gray text-xs">
                                            OTP Active
                                        </span>
                                        <span v-else class="text-zurit-gray text-xs">
                                            -
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Create User Modal -->
                <div
                    v-show="showCreateModal"
                    class="fixed inset-0 z-50 overflow-y-auto"
                    aria-labelledby="modal-title"
                    role="dialog"
                    aria-modal="true"
                >
                    <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                        <!-- Background overlay -->
                        <div
                            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                            @click="closeModal"
                        ></div>

                        <!-- Center the modal -->
                        <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

                        <!-- Modal panel -->
                        <div
                            class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle"
                        >
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="mb-4">
                                    <h3 class="font-heading text-2xl font-bold text-light-black" id="modal-title">
                                        Create New User
                                    </h3>
                                </div>

                                <form @submit.prevent="submit">
                                    <!-- Name -->
                                    <div class="mb-4">
                                        <InputLabel for="name" value="Name" />
                                        <TextInput
                                            id="name"
                                            v-model="form.name"
                                            type="text"
                                            class="mt-1 block w-full"
                                            required
                                            autofocus
                                        />
                                        <InputError class="mt-2" :message="form.errors.name" />
                                    </div>

                                    <!-- Email -->
                                    <div class="mb-4">
                                        <InputLabel for="email" value="Email" />
                                        <TextInput
                                            id="email"
                                            v-model="form.email"
                                            type="email"
                                            class="mt-1 block w-full"
                                            required
                                        />
                                        <InputError class="mt-2" :message="form.errors.email" />
                                    </div>

                                    <!-- Role -->
                                    <div class="mb-4">
                                        <InputLabel for="role" value="Role" />
                                        <select
                                            id="role"
                                            v-model="form.role"
                                            class="mt-1 block w-full rounded-lg border-light-gray bg-light-gray px-4 py-3 font-body text-sm text-light-black focus:border-zurit-purple focus:ring-1 focus:ring-zurit-purple focus:outline-none transition-colors"
                                            required
                                        >
                                            <option value="team_member">Team Member</option>
                                            <option value="manager">Manager</option>
                                            <option value="admin">Admin</option>
                                        </select>
                                        <InputError class="mt-2" :message="form.errors.role" />
                                    </div>

                                    <!-- Is Active -->
                                    <div class="mb-4">
                                        <label class="flex items-center">
                                            <input
                                                type="checkbox"
                                                v-model="form.is_active"
                                                class="rounded border-light-gray text-zurit-purple focus:ring-zurit-purple"
                                            />
                                            <span class="ml-2 font-body text-sm text-light-black">Active</span>
                                        </label>
                                    </div>

                                    <!-- Form Actions -->
                                    <div class="mt-6 flex justify-end space-x-3">
                                        <button
                                            type="button"
                                            @click="closeModal"
                                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 font-body text-sm font-medium text-light-black shadow-sm hover:bg-light-gray focus:outline-none focus:ring-2 focus:ring-zurit-purple focus:ring-offset-2"
                                        >
                                            Cancel
                                        </button>
                                        <PrimaryButton :disabled="form.processing">
                                            Create User
                                        </PrimaryButton>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

