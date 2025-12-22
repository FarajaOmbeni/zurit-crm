<script setup>
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'What type of lead would you like to add?',
    },
});

const emit = defineEmits(['close', 'select']);

const selectType = (type) => {
    emit('select', type);
};

const handleClose = () => {
    emit('close');
};
</script>

<template>
    <Modal :show="show" max-width="lg" @close="handleClose">
        <div class="p-6">
            <!-- Header -->
            <div class="text-center mb-6">
                <h2 class="font-heading text-2xl font-bold text-light-black">{{ title }}</h2>
                <p class="mt-2 font-body text-sm text-zurit-gray">
                    Choose the type of contact you want to add
                </p>
            </div>

            <!-- Options -->
            <div class="grid grid-cols-2 gap-4">
                <!-- Company Option -->
                <button
                    @click="selectType('company')"
                    class="flex flex-col items-center p-6 border-2 border-gray-200 rounded-xl hover:border-zurit-purple hover:bg-purple-50 transition-all duration-200 group"
                >
                    <div class="w-16 h-16 mb-4 rounded-full bg-blue-100 flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="font-heading text-lg font-semibold text-light-black mb-1">Company</h3>
                    <p class="font-body text-xs text-zurit-gray text-center">
                        Business or organization contact
                    </p>
                </button>

                <!-- Personal Option -->
                <button
                    @click="selectType('personal')"
                    class="flex flex-col items-center p-6 border-2 border-gray-200 rounded-xl hover:border-zurit-purple hover:bg-purple-50 transition-all duration-200 group"
                >
                    <div class="w-16 h-16 mb-4 rounded-full bg-green-100 flex items-center justify-center group-hover:bg-green-200 transition-colors">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <h3 class="font-heading text-lg font-semibold text-light-black mb-1">Personal</h3>
                    <p class="font-body text-xs text-zurit-gray text-center">
                        Individual person contact
                    </p>
                </button>
            </div>

            <!-- Cancel Button -->
            <div class="mt-6 text-center">
                <button
                    @click="handleClose"
                    class="font-body text-sm text-zurit-gray hover:text-light-black transition-colors"
                >
                    Cancel
                </button>
            </div>
        </div>
    </Modal>
</template>
