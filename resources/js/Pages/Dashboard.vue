<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Snapshot from '@/Components/Snapshot.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    snapshot: {
        type: Object,
        default: () => ({
            leadsToday: 0,
            clientsToday: 0,
            totalRevenue: 0,
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
                <Snapshot
                    :leads-today="snapshot.leadsToday"
                    :clients-today="snapshot.clientsToday"
                    :total-revenue="snapshot.totalRevenue"
                    :conversion-rate="snapshot.conversionRate"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
