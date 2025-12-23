<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    products: {
        type: Array,
        required: true,
    },
});

const page = usePage();
const currentUser = computed(() => page.props.auth.user);
const isAdmin = computed(() => currentUser.value?.role === 'admin');

const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const editingProduct = ref(null);
const deletingProduct = ref(null);
const searchQuery = ref('');

const createForm = useForm({
    name: '',
    price: '',
    category: '',
    description: '',
    is_active: true,
});

const editForm = useForm({
    name: '',
    price: '',
    category: '',
    description: '',
    is_active: true,
});

const filteredProducts = computed(() => {
    if (!searchQuery.value) {
        return props.products;
    }

    const query = searchQuery.value.toLowerCase();
    return props.products.filter(product =>
        product.name.toLowerCase().includes(query) ||
        (product.category && product.category.toLowerCase().includes(query)) ||
        (product.description && product.description.toLowerCase().includes(query))
    );
});

const submitCreate = () => {
    createForm.post(route('products.store'), {
        preserveScroll: true,
        onSuccess: () => {
            createForm.reset();
            showCreateModal.value = false;
        },
    });
};

const openEditModal = (product) => {
    editingProduct.value = product;
    editForm.name = product.name;
    editForm.price = product.price;
    editForm.category = product.category || '';
    editForm.description = product.description || '';
    editForm.is_active = product.is_active;
    showEditModal.value = true;
};

const submitEdit = () => {
    editForm.put(route('products.update', editingProduct.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            editForm.reset();
            showEditModal.value = false;
            editingProduct.value = null;
        },
    });
};

const openDeleteModal = (product) => {
    deletingProduct.value = product;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    router.delete(route('products.destroy', deletingProduct.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            deletingProduct.value = null;
        },
    });
};

const closeCreateModal = () => {
    createForm.reset();
    createForm.clearErrors();
    showCreateModal.value = false;
};

const closeEditModal = () => {
    editForm.reset();
    editForm.clearErrors();
    showEditModal.value = false;
    editingProduct.value = null;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    deletingProduct.value = null;
};

const formatPrice = (price) => {
    return parseFloat(price).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};
</script>

<template>
    <Head title="Manage Products" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Header Section -->
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="font-heading text-3xl font-bold text-light-black">
                        Manage Products
                    </h1>
                    <PrimaryButton @click="showCreateModal = true">
                        Create Product
                    </PrimaryButton>
                </div>

                <!-- Search Bar -->
                <div class="mb-6">
                    <TextInput
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search products by name, category, or description..."
                        class="w-full"
                    />
                </div>

                <!-- Products Table -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div v-if="filteredProducts.length === 0" class="p-12 text-center">
                        <p class="font-body text-lg text-zurit-gray">
                            {{ searchQuery ? 'No products found matching your search.' : 'No products found.' }}
                        </p>
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-light-gray">
                                <tr>
                                    <th class="px-6 py-3 text-left font-heading text-xs font-medium uppercase tracking-wider text-light-black">
                                        Name
                                    </th>
                                    <th class="px-6 py-3 text-left font-heading text-xs font-medium uppercase tracking-wider text-light-black">
                                        Category
                                    </th>
                                    <th class="px-6 py-3 text-left font-heading text-xs font-medium uppercase tracking-wider text-light-black">
                                        Price
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
                                <tr v-for="product in filteredProducts" :key="product.id" class="hover:bg-light-gray">
                                    <td class="px-6 py-4">
                                        <div class="font-body text-sm font-medium text-light-black">
                                            {{ product.name }}
                                        </div>
                                        <div v-if="product.description" class="font-body text-xs text-zurit-gray mt-1 max-w-md truncate">
                                            {{ product.description }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 font-body text-sm text-zurit-gray">
                                        {{ product.category || '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 font-body text-sm font-medium text-light-black">
                                        {{ formatPrice(product.price) }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span
                                            :class="[
                                                'inline-flex rounded-full px-3 py-1 text-xs font-semibold font-body',
                                                product.is_active
                                                    ? 'bg-green-100 text-green-800'
                                                    : 'bg-red-100 text-red-800'
                                            ]"
                                        >
                                            {{ product.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 font-body text-sm text-zurit-gray">
                                        {{ product.created_at }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 font-body text-sm space-x-2">
                                        <button
                                            @click="openEditModal(product)"
                                            class="inline-flex items-center rounded-lg bg-zurit-purple px-3 py-1.5 text-xs font-medium text-white hover:bg-zurit-purple/90 focus:outline-none focus:ring-2 focus:ring-zurit-purple focus:ring-offset-2 transition-colors"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            @click="openDeleteModal(product)"
                                            class="inline-flex items-center rounded-lg bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Create Product Modal -->
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
                            @click="closeCreateModal"
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
                                        Create New Product
                                    </h3>
                                </div>

                                <form @submit.prevent="submitCreate">
                                    <!-- Name -->
                                    <div class="mb-4">
                                        <InputLabel for="create_name" value="Product Name" />
                                        <TextInput
                                            id="create_name"
                                            v-model="createForm.name"
                                            type="text"
                                            class="mt-1 block w-full"
                                            required
                                            autofocus
                                        />
                                        <InputError class="mt-2" :message="createForm.errors.name" />
                                    </div>

                                    <!-- Price -->
                                    <div class="mb-4">
                                        <InputLabel for="create_price" value="Price" />
                                        <TextInput
                                            id="create_price"
                                            v-model="createForm.price"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="mt-1 block w-full"
                                            required
                                        />
                                        <InputError class="mt-2" :message="createForm.errors.price" />
                                    </div>

                                    <!-- Category -->
                                    <div class="mb-4">
                                        <InputLabel for="create_category" value="Category (Optional)" />
                                        <TextInput
                                            id="create_category"
                                            v-model="createForm.category"
                                            type="text"
                                            class="mt-1 block w-full"
                                        />
                                        <InputError class="mt-2" :message="createForm.errors.category" />
                                    </div>

                                    <!-- Description -->
                                    <div class="mb-4">
                                        <InputLabel for="create_description" value="Description (Optional)" />
                                        <textarea
                                            id="create_description"
                                            v-model="createForm.description"
                                            rows="3"
                                            class="mt-1 block w-full rounded-lg border-light-gray bg-light-gray px-4 py-3 font-body text-sm text-light-black focus:border-zurit-purple focus:ring-1 focus:ring-zurit-purple focus:outline-none transition-colors"
                                        ></textarea>
                                        <InputError class="mt-2" :message="createForm.errors.description" />
                                    </div>

                                    <!-- Is Active -->
                                    <div class="mb-4">
                                        <label class="flex items-center">
                                            <input
                                                type="checkbox"
                                                v-model="createForm.is_active"
                                                class="rounded border-light-gray text-zurit-purple focus:ring-zurit-purple"
                                            />
                                            <span class="ml-2 font-body text-sm text-light-black">Active</span>
                                        </label>
                                    </div>

                                    <!-- Form Actions -->
                                    <div class="mt-6 flex justify-end space-x-3">
                                        <button
                                            type="button"
                                            @click="closeCreateModal"
                                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 font-body text-sm font-medium text-light-black shadow-sm hover:bg-light-gray focus:outline-none focus:ring-2 focus:ring-zurit-purple focus:ring-offset-2"
                                        >
                                            Cancel
                                        </button>
                                        <PrimaryButton :disabled="createForm.processing">
                                            Create Product
                                        </PrimaryButton>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Product Modal -->
                <div
                    v-show="showEditModal"
                    class="fixed inset-0 z-50 overflow-y-auto"
                    aria-labelledby="edit-modal-title"
                    role="dialog"
                    aria-modal="true"
                >
                    <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                        <!-- Background overlay -->
                        <div
                            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                            @click="closeEditModal"
                        ></div>

                        <!-- Center the modal -->
                        <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

                        <!-- Modal panel -->
                        <div
                            class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle"
                        >
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="mb-4">
                                    <h3 class="font-heading text-2xl font-bold text-light-black" id="edit-modal-title">
                                        Edit Product
                                    </h3>
                                </div>

                                <form @submit.prevent="submitEdit">
                                    <!-- Name -->
                                    <div class="mb-4">
                                        <InputLabel for="edit_name" value="Product Name" />
                                        <TextInput
                                            id="edit_name"
                                            v-model="editForm.name"
                                            type="text"
                                            class="mt-1 block w-full"
                                            required
                                            autofocus
                                        />
                                        <InputError class="mt-2" :message="editForm.errors.name" />
                                    </div>

                                    <!-- Price -->
                                    <div class="mb-4">
                                        <InputLabel for="edit_price" value="Price" />
                                        <TextInput
                                            id="edit_price"
                                            v-model="editForm.price"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="mt-1 block w-full"
                                            required
                                        />
                                        <InputError class="mt-2" :message="editForm.errors.price" />
                                    </div>

                                    <!-- Category -->
                                    <div class="mb-4">
                                        <InputLabel for="edit_category" value="Category (Optional)" />
                                        <TextInput
                                            id="edit_category"
                                            v-model="editForm.category"
                                            type="text"
                                            class="mt-1 block w-full"
                                        />
                                        <InputError class="mt-2" :message="editForm.errors.category" />
                                    </div>

                                    <!-- Description -->
                                    <div class="mb-4">
                                        <InputLabel for="edit_description" value="Description (Optional)" />
                                        <textarea
                                            id="edit_description"
                                            v-model="editForm.description"
                                            rows="3"
                                            class="mt-1 block w-full rounded-lg border-light-gray bg-light-gray px-4 py-3 font-body text-sm text-light-black focus:border-zurit-purple focus:ring-1 focus:ring-zurit-purple focus:outline-none transition-colors"
                                        ></textarea>
                                        <InputError class="mt-2" :message="editForm.errors.description" />
                                    </div>

                                    <!-- Is Active -->
                                    <div class="mb-4">
                                        <label class="flex items-center">
                                            <input
                                                type="checkbox"
                                                v-model="editForm.is_active"
                                                class="rounded border-light-gray text-zurit-purple focus:ring-zurit-purple"
                                            />
                                            <span class="ml-2 font-body text-sm text-light-black">Active</span>
                                        </label>
                                    </div>

                                    <!-- Form Actions -->
                                    <div class="mt-6 flex justify-end space-x-3">
                                        <button
                                            type="button"
                                            @click="closeEditModal"
                                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 font-body text-sm font-medium text-light-black shadow-sm hover:bg-light-gray focus:outline-none focus:ring-2 focus:ring-zurit-purple focus:ring-offset-2"
                                        >
                                            Cancel
                                        </button>
                                        <PrimaryButton :disabled="editForm.processing">
                                            Update Product
                                        </PrimaryButton>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delete Confirmation Modal -->
                <div
                    v-show="showDeleteModal"
                    class="fixed inset-0 z-50 overflow-y-auto"
                    aria-labelledby="delete-modal-title"
                    role="dialog"
                    aria-modal="true"
                >
                    <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                        <!-- Background overlay -->
                        <div
                            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                            @click="closeDeleteModal"
                        ></div>

                        <!-- Center the modal -->
                        <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

                        <!-- Modal panel -->
                        <div
                            class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle"
                        >
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                        </svg>
                                    </div>
                                    <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                        <h3 class="font-heading text-lg font-bold text-light-black" id="delete-modal-title">
                                            Delete Product
                                        </h3>
                                        <div class="mt-2">
                                            <p class="font-body text-sm text-zurit-gray">
                                                Are you sure you want to delete <strong>{{ deletingProduct?.name }}</strong>? This action cannot be undone.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                <button
                                    type="button"
                                    @click="confirmDelete"
                                    class="inline-flex w-full justify-center rounded-lg bg-red-600 px-4 py-2 font-body text-sm font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 sm:ml-3 sm:w-auto"
                                >
                                    Delete
                                </button>
                                <button
                                    type="button"
                                    @click="closeDeleteModal"
                                    class="mt-3 inline-flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 font-body text-sm font-medium text-light-black shadow-sm hover:bg-light-gray focus:outline-none focus:ring-2 focus:ring-zurit-purple focus:ring-offset-2 sm:mt-0 sm:w-auto"
                                >
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
