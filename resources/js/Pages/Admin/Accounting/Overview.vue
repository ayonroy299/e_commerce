<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps(['overview']);

const trendData = computed(() => ({
    labels: props.overview.trends.map(t => t.month),
    datasets: [
        {
            label: 'Revenue',
            data: props.overview.trends.map(t => t.revenue),
            fill: false,
            borderColor: '#10b981',
            tension: 0.4
        },
        {
            label: 'Expenses',
            data: props.overview.trends.map(t => t.expenses),
            fill: false,
            borderColor: '#ef4444',
            tension: 0.4
        }
    ]
}));

const expenseData = computed(() => ({
    labels: props.overview.top_expenses.map(e => e.name),
    datasets: [
        {
            data: props.overview.top_expenses.map(e => e.value),
            backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#06b6d4', '#8b5cf6'],
        }
    ]
}));

const lineOptions = {
    maintainAspectRatio: false,
    aspectRatio: 0.6,
    plugins: {
        legend: {
            position: 'bottom'
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                callback: (value) => '$' + value.toLocaleString()
            }
        }
    }
};

const pieOptions = {
    maintainAspectRatio: false,
    aspectRatio: 0.8,
    plugins: {
        legend: {
            position: 'bottom'
        }
    }
};
</script>

<template>
  <Head title="Accounting Overview" />
  
  <AuthenticatedLayout>
    <div class="p-6 flex flex-col gap-6">
      <!-- KPI Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <Card class="bg-blue-50 border-blue-100 hover:shadow-md transition-all duration-300">
          <template #title><span class="text-blue-600">Total Revenue</span></template>
          <template #content><h2 class="text-3xl font-bold">{{ $formatCurrency(overview.total_revenue) }}</h2></template>
        </Card>
        <Card class="bg-red-50 border-red-100 hover:shadow-md transition-all duration-300">
          <template #title><span class="text-red-600">Total Expenses</span></template>
          <template #content><h2 class="text-3xl font-bold">{{ $formatCurrency(overview.total_expenses) }}</h2></template>
        </Card>
        <Card class="bg-green-50 border-green-100 hover:shadow-md transition-all duration-300">
          <template #title><span class="text-green-600">Net Profit</span></template>
          <template #content><h2 class="text-3xl font-bold">{{ $formatCurrency(overview.net_profit) }}</h2></template>
        </Card>
        <Card class="bg-orange-50 border-orange-100 hover:shadow-md transition-all duration-300">
          <template #title><span class="text-orange-600">Cash Balance</span></template>
          <template #content><h2 class="text-3xl font-bold">{{ $formatCurrency(overview.cash_balance) }}</h2></template>
        </Card>
      </div>

      <!-- Charts Section -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
         <!-- Trend Chart -->
         <div class="card p-4 bg-white rounded-xl shadow-sm md:col-span-2">
            <h3 class="font-bold mb-4 border-b pb-2 flex items-center gap-2">
                <i class="pi pi-chart-line text-blue-500"></i>
                Revenue vs Expense Trend
            </h3>
            <div class="h-[300px]">
                <Chart type="line" :data="trendData" :options="lineOptions" class="h-full" />
            </div>
         </div>

         <!-- Pie Chart -->
         <div class="card p-4 bg-white rounded-xl shadow-sm">
            <h3 class="font-bold mb-4 border-b pb-2 flex items-center gap-2">
                <i class="pi pi-chart-pie text-orange-500"></i>
                Expense Breakdown
            </h3>
            <div class="h-[300px]">
                <Chart type="pie" :data="expenseData" :options="pieOptions" class="h-full" />
            </div>
         </div>
      </div>

      <!-- Recent Journals Preview or other lists could go here -->
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
.card {
    border: 1px solid #e5e7eb;
}
</style>
