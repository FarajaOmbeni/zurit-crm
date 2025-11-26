<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>

    <Head title="Forgot Password" />

    <div class="flex min-h-screen">
        <!-- Left Panel - Promotional Content -->
        <div
            class="hidden lg:flex lg:w-1/2 flex-col justify-between bg-gradient-to-b from-[#A78BFA] via-[#7639C2] to-[#FF5B5D] px-12 py-16">
            <!-- Logo -->
            <div class="flex items-center space-x-3">
                <img src="/images/zurit_logo.png" alt="ZuritCRM Logo" class="h-20 w-auto mx-auto" />
            </div>

            <!-- Welcome Content -->
            <div class="space-y-8 mx-auto text-justified">
                <h2 class="font-heading text-5xl text-white mb-4 text-center">
                    Forgot Password?
                </h2>

                <p class="font-body text-lg text-white/90 max-w-md leading-relaxed mx-auto text-justify">
                    Enter your email address and we will send you a link to reset your password.
                </p>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-3 gap-8 mx-auto">
            </div>
        </div>

        <!-- Right Panel - Forgot Password Form -->
        <div class="flex-1 flex flex-col justify-center px-6 py-12 lg:px-16 bg-white">
            <div class="mx-auto w-full max-w-md">
                <!-- Logo for mobile -->
                <div class="lg:hidden flex items-center justify-center mb-8">
                    <div class="flex items-center space-x-3">
                        <img src="/images/zurit_logo.png" alt="ZuritCRM Logo" class="h-20 w-auto mx-auto" />
                    </div>
                </div>

                <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
                    {{ status }}
                </div>

                <div class="mb-8">
                    <h1 class="font-heading text-3xl font-bold text-light-black mb-2">Forgot Password</h1>
                    <p class="font-body text-sm text-zurit-gray">
                        No problem. Just let us know your email address and we will email you a password reset link that
                        will allow you to choose a new one.
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Email Field -->
                    <div>
                        <InputLabel for="email" value="Email" />
                        <TextInput id="email" type="email" class="mt-2" v-model="form.email"
                            placeholder="example@zuritconsulting.com" required autofocus autocomplete="username" />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <PrimaryButton
                            class="w-full justify-center bg-zurit-purple hover:bg-zurit-purple/90 focus:ring-zurit-purple"
                            :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Email Password Reset Link
                        </PrimaryButton>
                    </div>
                </form>

                <!-- Back to Login Link -->
                <div class="mt-8 text-center">
                    <Link :href="route('login')"
                        class="font-body text-sm text-zurit-purple hover:text-zurit-purple/80 font-medium">
                    ← Back to Sign in
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
