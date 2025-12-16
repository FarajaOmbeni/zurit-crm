<script setup>
import { computed } from 'vue';

const props = defineProps({
    type: {
        type: String,
        default: 'info', // 'success', 'error', 'warning', 'info'
        validator: (value) => ['success', 'error', 'warning', 'info'].includes(value),
    },
    message: {
        type: String,
        required: true,
    },
    show: {
        type: Boolean,
        default: true,
    },
});

const alertClasses = computed(() => {
    const baseClasses = 'rounded-lg border p-4 font-body text-sm';
    const typeClasses = {
        success: 'bg-green-50 border-green-200',
        error: 'bg-red-50 border-red-200',
        warning: 'bg-yellow-50 border-yellow-200',
        info: 'bg-blue-50 border-blue-200',
    };
    return `${baseClasses} ${typeClasses[props.type] || typeClasses.info}`;
});

const iconClasses = computed(() => {
    const typeClasses = {
        success: 'text-green-600',
        error: 'text-red-600',
        warning: 'text-yellow-600',
        info: 'text-blue-600',
    };
    return typeClasses[props.type] || typeClasses.info;
});

const messageClasses = computed(() => {
    const baseClasses = 'font-medium';
    const typeClasses = {
        success: 'text-green-800',
        error: 'text-red-800',
        warning: 'text-yellow-800',
        info: 'text-blue-800',
    };
    return `${baseClasses} ${typeClasses[props.type] || typeClasses.info}`;
});
</script>

<template>
    <Transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0 translate-y-[-10px]"
        enter-to-class="opacity-100 translate-y-0" leave-active-class="ease-in duration-200"
        leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-[-10px]">
        <div v-if="show" :class="alertClasses">
            <div class="flex items-center gap-2">
                <!-- Success Icon -->
                <svg v-if="type === 'success'" class="h-5 w-5 flex-shrink-0" :class="iconClasses" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>

                <!-- Error Icon -->
                <svg v-else-if="type === 'error'" class="h-5 w-5 flex-shrink-0" :class="iconClasses" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>

                <!-- Warning Icon -->
                <svg v-else-if="type === 'warning'" class="h-5 w-5 flex-shrink-0" :class="iconClasses" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>

                <!-- Info Icon -->
                <svg v-else class="h-5 w-5 flex-shrink-0" :class="iconClasses" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>

                <p :class="messageClasses">{{ message }}</p>
            </div>
        </div>
    </Transition>
</template>
