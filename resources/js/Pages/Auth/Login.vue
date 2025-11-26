<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>

    <Head title="Sign in" />

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
                    Welcome to
                </h2>

                <h3 class="font-heading font-bold text-5xl text-white mb-4 text-center">
                    ZuritCRM
                </h3>

                <p class="font-body text-lg text-white/90 max-w-md leading-relaxed mx-auto text-justify">
                    Your work drives meaningful relationships and sustainable growth. Zurit CRM gives you the tools to
                    manage leads, track interactions, and follow through with confidence, all in a clean, focused
                    workspace built for impact.
                </p>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-3 gap-8 mx-auto">
                <div>
                    <div class="font-heading text-4xl font-bold text-white mb-1">500+</div>
                    <div class="font-body text-sm text-white/80">Team Members</div>
                </div>
                <div>
                    <div class="font-heading text-4xl font-bold text-white mb-1">10K+</div>
                    <div class="font-body text-sm text-white/80">Clients Managed</div>
                </div>
                <div>
                    <div class="font-heading text-4xl font-bold text-white mb-1">97%</div>
                    <div class="font-body text-sm text-white/80">Satisfaction</div>
                </div>
            </div>
        </div>

        <!-- Right Panel - Login Form -->
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
                    <h1 class="font-heading text-3xl font-bold text-light-black mb-2">Sign in</h1>
                    <p class="font-body text-sm text-zurit-gray">Welcome back! Please enter your credentials.</p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Email Field -->
                    <div>
                        <InputLabel for="email" value="Email" />
                        <TextInput id="email" type="email" class="mt-2" v-model="form.email"
                            placeholder="example@zurit.com" required autofocus autocomplete="username" />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <!-- Password Field -->
                    <div>
                        <InputLabel for="password" value="Password" />
                        <TextInput id="password" type="password" class="mt-2" v-model="form.password" required
                            autocomplete="current-password" />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <Checkbox name="remember" v-model:checked="form.remember" />
                            <span class="ms-2 font-body text-sm text-zurit-gray">Remember me</span>
                        </label>

                        <Link v-if="canResetPassword" :href="route('password.request')"
                            class="font-body text-sm text-zurit-purple hover:text-zurit-purple/80 focus:outline-none focus:ring-2 focus:ring-zurit-purple focus:ring-offset-2 rounded">
                        Forgot password?
                        </Link>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <PrimaryButton
                            class="w-full justify-center bg-zurit-purple hover:bg-zurit-purple/90 focus:ring-zurit-purple"
                            :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Continue
                        </PrimaryButton>
                    </div>
                </form>

                <!-- Footer Text -->
                <div class="mt-8 text-center">
                    <p class="font-body text-sm text-zurit-gray">
                        Don't have access?
                        <Link href="#" class="text-zurit-purple hover:text-zurit-purple/80 font-medium">
                        Contact your Administrator
                        </Link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
