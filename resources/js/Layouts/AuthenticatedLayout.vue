<script setup>
import Header from '@/Components/Header.vue';
</script>

<template>
    <div>
        <div class="min-h-screen bg-light-gray">
            <!-- Header Navigation -->
            <Header />

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
