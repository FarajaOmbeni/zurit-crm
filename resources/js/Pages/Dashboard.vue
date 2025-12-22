<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Snapshot from '@/Components/Snapshot.vue';
import RecentLeads from '@/Components/RecentLeads.vue';
import SalesChart from '@/Components/SalesChart.vue';
import Tasks from '@/Components/Tasks.vue';
import ProductsByPurchase from '@/Components/ProductsByPurchase.vue';
import LeadsPerSource from '@/Components/LeadsPerSource.vue';
import CalendarWidget from '@/Components/CalendarWidget.vue';
import RecentActivities from '@/Components/RecentActivities.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    snapshot: {
        type: Object,
        default: () => ({
            leadsToday: 0,
            clientsToday: 0,
            revenueToday: 0,
            revenueThisMonth: 0,
            conversionRate: 0,
        }),
    },
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Snapshot Cards -->
                <Snapshot
                    :leads-today="snapshot.leadsToday"
                    :clients-today="snapshot.clientsToday"
                    :revenue-today="snapshot.revenueToday"
                    :revenue-this-month="snapshot.revenueThisMonth"
                    :conversion-rate="snapshot.conversionRate"
                />
                
                <!-- Dashboard Sections -->
                <div class="mt-8 grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <!-- Recent Leads -->
                    <div class="lg:col-span-1">
                        <RecentLeads />
                    </div>
                    
                    <!-- Sales Chart -->
                    <div class="lg:col-span-2">
                        <SalesChart />
                    </div>
                    
                    <!-- Tasks -->
                    <div class="lg:col-span-1">
                        <Tasks />
                    </div>
                </div>

                <!-- Analytics Section -->
                <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Products by Purchase -->
                    <div class="lg:col-span-1">
                        <ProductsByPurchase />
                    </div>
                    
                    <!-- Leads per Source -->
                    <div class="lg:col-span-1">
                        <LeadsPerSource />
                    </div>
                    
                    <!-- Calendar Widget -->
                    <div class="lg:col-span-1">
                        <CalendarWidget />
                    </div>
                </div>

                <!-- Recent Activities Section -->
                <div class="mt-8">
                    <RecentActivities />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
