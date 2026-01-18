<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import Card from 'primevue/card';

const reports = [
    {
        name: 'Sales Report',
        description: 'Detailed view of all completed sales, including customer and cashier info.',
        icon: 'pi pi-shopping-cart',
        route: 'reports.sales',
        color: 'text-blue-500',
        bg: 'bg-blue-100'
    },
    {
        name: 'Stock Report',
        description: 'Track all stock movements, additions, and adjustments.',
        icon: 'pi pi-box',
        route: 'reports.stock',
        color: 'text-green-500',
        bg: 'bg-green-100'
    },
    {
        name: 'Purchase Report',
        description: 'Summary of all purchase orders and stock receipts from suppliers.',
        icon: 'pi pi-truck',
        route: 'reports.purchases',
        color: 'text-orange-500',
        bg: 'bg-orange-100'
    },
    {
        name: 'Financial Report',
        description: 'Link to accounting ledgers and cashflow analysis.',
        icon: 'pi pi-chart-line',
        route: 'reports.financial',
        color: 'text-purple-500',
        bg: 'bg-purple-100'
    }
];
</script>

<template>
    <Head title="Reports Central" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Reports Central
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <Link 
                        v-for="report in reports" 
                        :key="report.name" 
                        :href="route(report.route)"
                        class="no-underline"
                    >
                        <Card class="hover:shadow-lg transition-shadow duration-300 cursor-pointer h-full">
                            <template #content>
                                <div class="flex flex-col items-center text-center">
                                    <div :class="['w-16 h-16 rounded-full flex items-center justify-center mb-4', report.bg]">
                                        <i :class="[report.icon, report.color, 'text-2xl']"></i>
                                    </div>
                                    <h3 class="text-lg font-bold mb-2">{{ report.name }}</h3>
                                    <p class="text-gray-600 text-sm italic">{{ report.description }}</p>
                                </div>
                            </template>
                        </Card>
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
