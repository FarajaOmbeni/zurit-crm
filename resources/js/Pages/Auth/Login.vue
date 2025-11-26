<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

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

const showPassword = ref(false);

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};

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
                            placeholder="example@zuritconsulting.com" required autofocus autocomplete="username" />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <!-- Password Field -->
                    <div>
                        <InputLabel for="password" value="Password" />
                        <div class="relative mt-2">
                            <TextInput id="password" :type="showPassword ? 'text' : 'password'" class="pr-12"
                                v-model="form.password" required autocomplete="current-password" />
                            <button type="button" @click="togglePasswordVisibility"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-zurit-gray hover:text-zurit-purple focus:outline-none focus:text-zurit-purple transition-colors"
                                :aria-label="showPassword ? 'Hide password' : 'Show password'">
                                <svg v-if="showPassword" class="w-5 h-5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.736m0 0L21 21" />
                                </svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
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
