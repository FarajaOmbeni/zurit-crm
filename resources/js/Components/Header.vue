<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const isAdmin = computed(() => user.value?.role === 'admin');

const navigation = [
    { name: 'Dashboard', route: 'dashboard' },
    { name: 'Pipeline', route: 'pipeline' },
    { name: 'Lead Profile', route: 'leads.index' },
    { name: 'Client Database', route: 'clients.index' },
    { name: 'Reports', route: 'reports.index' },
    { name: 'Tasks', route: 'tasks.index' },
];

const isActive = (routeName) => {
    return route().current(routeName);
};
</script>

<template>
    <header class="bg-white">
        <!-- Thin blue bar at top -->
        <div class="h-1 bg-zurit-purple"></div>

        <!-- Main header content -->
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <!-- Left: Logo and Brand -->
                <div class="flex items-center space-x-3">
                    <Link :href="route('dashboard')" class="flex items-center space-x-3">
                    <img src="/images/zurit_logo.png" alt="ZuritCRM Logo" class="h-8 w-auto" />
                    </Link>
                </div>

                <!-- Center: Navigation Links -->
                <nav class="hidden md:flex items-center space-x-8">
                    <Link v-for="item in navigation" :key="item.name" :href="route(item.route)" :class="[
                        'font-body text-sm font-medium transition-colors',
                        isActive(item.route)
                            ? 'text-light-black border-b-2 border-prosper pb-1'
                            : 'text-zurit-gray hover:text-light-black'
                    ]">
                    {{ item.name }}
                    </Link>
                </nav>

                <!-- Right: User Profile -->
                <div class="flex items-center space-x-3" v-if="user">
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button class="flex items-center space-x-3 focus:outline-none">
                                <div
                                    class="h-10 w-10 rounded-full border-2 border-prosper overflow-hidden bg-light-gray flex items-center justify-center">
                                    <span class="font-body text-sm font-medium text-light-black">
                                        {{ user.name?.charAt(0).toUpperCase() }}
                                    </span>
                                </div>
                                <span class="font-body text-sm font-medium text-prosper">
                                    {{ user.name }}
                                </span>
                            </button>
                        </template>

                        <template #content>
                            <DropdownLink v-if="isAdmin" :href="route('users.index')">
                                Manage Users
                            </DropdownLink>
                            <DropdownLink :href="route('profile.edit')">
                                Settings
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </div>
        </div>
    </header>
</template>
