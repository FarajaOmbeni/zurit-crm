<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>

    <Head title="Welcome to Zurit CRM" />
    <div class="min-h-screen bg-gradient-to-br from-purple-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800">
        <div class="flex min-h-screen flex-col items-center justify-center px-6 py-12">
            <div class="w-full max-w-2xl text-center">
                <!-- Logo/Title -->
                <div class="mb-8">
                    <h1 class="text-5xl font-bold text-gray-900 dark:text-white mb-2">
                        Zurit CRM
                    </h1>
                    <p class="text-xl text-gray-600 dark:text-gray-300">
                        Customer Relationship Management System
                    </p>
                </div>

                <!-- Description -->
                <div class="mb-12 rounded-lg bg-white/80 dark:bg-gray-800/80 p-8 shadow-lg backdrop-blur-sm">
                    <p class="text-lg text-gray-700 dark:text-gray-200 leading-relaxed mb-4">
                        Zurit CRM helps you manage your leads, track client relationships, and streamline your sales
                        pipeline.
                        From initial contact to closing deals, everything you need is in one place.
                    </p>
                    <p class="text-base text-gray-600 dark:text-gray-400">
                        Track activities, schedule follow-ups, generate reports, and convert leads into clients
                        effortlessly.
                    </p>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Link v-if="$page.props.auth.user" :href="route('dashboard')"
                        class="inline-flex items-center justify-center rounded-lg bg-purple-600 px-8 py-3 text-base font-semibold text-white shadow-lg transition hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                    Go to Dashboard
                    </Link>

                    <template v-else>
                        <Link :href="route('login')"
                            class="inline-flex items-center justify-center rounded-lg bg-purple-600 px-8 py-3 text-base font-semibold text-white shadow-lg transition hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                        Log In
                        </Link>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>
