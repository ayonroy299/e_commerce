<script setup>
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from 'primevue/card';
import Calendar from 'primevue/calendar';
import { ref, watch } from 'vue';

const props = defineProps({
    stats: Object,
    filters: Object,
});

const dateFilter = ref([new Date(props.filters.start_date), new Date(props.filters.end_date)]);

watch(dateFilter, (val) => {
    if (val && val[1]) {
        router.get(route('reports.financial'), {
            start_date: val[0].toISOString().split('T')[0],
            end_date: val[1].toISOString().split('T')[0]
        }, { preserveState: true, replace: true });
    }
});
</script>

<template>
    <Head title="Financial Dashboard" />

    <AppLayout>
        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">Financial Overview</h2>
            <div class="bg-white p-2 rounded shadow">
                 <Calendar v-model="dateFilter" selectionMode="range" :showIcon="true" placeholder="Filter Date Range" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Revenue -->
            <div class="bg-white rounded p-6 shadow border-l-4 border-blue-500">
                <div class="text-gray-500 text-sm uppercase">Total Revenue</div>
                <div class="text-3xl font-bold mt-2 text-gray-800">{{ Number(stats.revenue).toFixed(2) }}</div>
            </div>
            
            <!-- Returns -->
            <div class="bg-white rounded p-6 shadow border-l-4 border-orange-500">
                 <div class="text-gray-500 text-sm uppercase">Returns & Refunds</div>
                <div class="text-3xl font-bold mt-2 text-gray-800">{{ Number(stats.returns).toFixed(2) }}</div>
            </div>
            
            <!-- Expenses -->
             <div class="bg-white rounded p-6 shadow border-l-4 border-red-500">
                 <div class="text-gray-500 text-sm uppercase">Expenses</div>
                <div class="text-3xl font-bold mt-2 text-gray-800">{{ Number(stats.expenses).toFixed(2) }}</div>
            </div>
            
            <!-- Net Profit -->
            <div class="bg-white rounded p-6 shadow border-l-4" :class="stats.net_profit >= 0 ? 'border-green-500' : 'border-red-600'">
                 <div class="text-gray-500 text-sm uppercase">Net Profit</div>
                <div class="text-3xl font-bold mt-2" :class="stats.net_profit >= 0 ? 'text-green-600' : 'text-red-600'">
                    {{ Number(stats.net_profit).toFixed(2) }}
                </div>
            </div>
        </div>
        
        <div class="card h-64 flex items-center justify-center text-gray-400">
            <p>Chart Placeholder (Revenue vs Expenses)</p>
        </div>
    </AppLayout>
</template>
