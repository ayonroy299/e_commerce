<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Chart from 'primevue/chart';
import Avatar from 'primevue/avatar';
import Rating from 'primevue/rating';

const props = defineProps({
    stats: Object,
    income_stats: Object,
    traffic_stats: Array,
    recent_orders: Array,
    recent_reviews: Array,
    active_users: Object,
    sales_trend: Array,
    top_products: Array,
    low_stock: Array,
    filters: Object
});

const salesTrendData = computed(() => ({
    labels: props.sales_trend.map(t => t.date),
    datasets: [
        {
            label: 'Daily Sales',
            data: props.sales_trend.map(t => t.amount),
            fill: true,
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            tension: 0.4,
            pointRadius: 0
        }
    ]
}));

const incomeChartData = computed(() => ({
    labels: props.income_stats.labels,
    datasets: [
        {
            label: 'Monthly Income',
            backgroundColor: '#3b82f6',
            data: props.income_stats.amounts,
            borderRadius: 6
        }
    ]
}));

const trafficChartData = computed(() => ({
    labels: props.traffic_stats.map(s => s.source),
    datasets: [
        {
            data: props.traffic_stats.map(s => s.amount),
            backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
            hoverOffset: 4
        }
    ]
}));

const chartOptions = {
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false }
    },
    scales: {
        x: { display: false },
        y: { display: false }
    }
};

const barOptions = {
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false }
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: { color: 'rgba(0,0,0,0.05)' }
        },
        x: {
            grid: { display: false }
        }
    }
};

const pieOptions = {
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 10 } }
    }
};

const getStatusSeverity = (status) => {
    switch (status) {
        case 'completed': return 'success';
        case 'pending': return 'warn';
        case 'cancelled': return 'danger';
        default: return 'info';
    }
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="px-2">
            <!-- HEADER METRICS SECTIONS -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <!-- Lifetime Sales -->
                <div class="card p-4 flex flex-col justify-between bg-zinc-900 border-none shadow-xl">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-zinc-400 text-xs font-bold uppercase tracking-wider">Total Sales</span>
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-500">
                             <i class="pi pi-chart-line"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-white">
                        {{ stats.currency }}{{ Number(stats.lifetime_sales).toLocaleString() }}
                    </div>
                    <div class="text-[10px] text-emerald-400 font-medium mt-1">Lifetime accumulation</div>
                </div>

                <!-- Average Order Value -->
                <div class="card p-4 flex flex-col justify-between bg-zinc-900 border-none shadow-xl">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-zinc-400 text-xs font-bold uppercase tracking-wider">Avg Order Value</span>
                        <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-500">
                             <i class="pi pi-percentage"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-white">
                        {{ stats.currency }}{{ Number(stats.avg_order_value).toFixed(2) }}
                    </div>
                    <div class="text-[10px] text-blue-400 font-medium mt-1">Based on {{ stats.order_count }} orders</div>
                </div>

                <!-- Total Orders -->
                <div class="card p-4 flex flex-col justify-between bg-zinc-900 border-none shadow-xl">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-zinc-400 text-xs font-bold uppercase tracking-wider">Total Orders</span>
                        <div class="w-8 h-8 rounded-lg bg-indigo-500/10 flex items-center justify-center text-indigo-500">
                             <i class="pi pi-shopping-bag"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-white">
                        {{ Number(stats.order_count).toLocaleString() }}
                    </div>
                    <div class="text-[10px] text-indigo-400 font-medium mt-1">In selected period</div>
                </div>

                <!-- Active Users -->
                <div class="card p-4 flex flex-col justify-between bg-zinc-900 border-none shadow-xl relative overflow-hidden">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-zinc-400 text-xs font-bold uppercase tracking-wider">Active Users</span>
                        <div class="w-8 h-8 rounded-lg bg-purple-500/10 flex items-center justify-center text-purple-500">
                             <i class="pi pi-users"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-white">
                        {{ active_users.count }}
                    </div>
                    <div class="text-[10px] text-purple-400 font-medium mt-1">Across all branches</div>
                </div>
            </div>

            <!-- SECOND ROW: Income Stats & Traffic Sources -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <div class="card lg:col-span-2 shadow-sm border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-sm font-bold text-gray-800">Income Statistics</h3>
                        <div class="text-xs text-gray-400">Monthly trend (USD)</div>
                    </div>
                    <div class="h-[300px]">
                        <Chart type="bar" :data="incomeChartData" :options="barOptions" class="h-full" />
                    </div>
                </div>

                <div class="card shadow-sm border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-sm font-bold text-gray-800">Sales by Traffic Source</h3>
                        <i class="pi pi-compass text-gray-400"></i>
                    </div>
                    <div class="h-[250px]">
                        <Chart type="pie" :data="trafficChartData" :options="pieOptions" class="h-full" />
                    </div>
                    <!-- Source Legend with Data -->
                    <div class="mt-4 space-y-2">
                        <div v-for="source in traffic_stats" :key="source.source" class="flex justify-between items-center text-xs">
                            <span class="text-gray-500">{{ source.source }}</span>
                            <span class="font-bold font-mono">{{ stats.currency }}{{ source.amount.toLocaleString() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- THIRD ROW: Recent Orders & Active Users List -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
                <!-- Recent Orders Table -->
                <div class="card lg:col-span-3 border-gray-100 p-0 overflow-hidden shadow-sm">
                    <div class="p-4 border-b flex justify-between items-center bg-gray-50/50">
                        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-tight">Recent Orders</h3>
                        <Link :href="route('pos.orders.index')" class="text-xs text-indigo-600 font-bold hover:underline">View All Orders</Link>
                    </div>
                    <DataTable :value="recent_orders" class="p-datatable-sm text-xs font-medium" :rows="10">
                        <Column field="invoice_number" header="No."></Column>
                        <Column field="status" header="Status">
                            <template #body="slotProps">
                                <Tag :value="slotProps.data.status" :severity="getStatusSeverity(slotProps.data.status)" />
                            </template>
                        </Column>
                        <Column field="country" header="Country"></Column>
                        <Column field="customer" header="Customer"></Column>
                        <Column field="date" header="Date"></Column>
                        <Column field="total_amount" header="Total">
                             <template #body="slotProps">
                                <span class="font-bold">{{ stats.currency }}{{ slotProps.data.total_amount }}</span>
                            </template>
                        </Column>
                    </DataTable>
                </div>

                <!-- Active Users List -->
                <div class="card border-gray-100 p-0 shadow-sm flex flex-col">
                    <div class="p-4 border-b flex justify-between items-center bg-gray-50/50">
                        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-tight">User Activity</h3>
                    </div>
                    <div class="flex-1 overflow-y-auto max-h-[450px]">
                        <div v-for="user in active_users.list" :key="user.email" class="p-4 border-b last:border-0 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <Avatar :label="user.name.charAt(0)" shape="circle" class="bg-indigo-100 text-indigo-700" />
                                <div>
                                    <p class="text-sm font-bold text-gray-900 leading-none">{{ user.name }}</p>
                                    <p class="text-[10px] text-gray-500 mt-1">{{ user.branch }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FOURTH ROW: Recent Reviews (From Landing) -->
            <div class="card border-gray-100 shadow-sm mb-10">
                <div class="p-4 border-b bg-gray-50/50">
                     <h3 class="text-sm font-bold text-gray-800 uppercase tracking-tight">Recent Customer Reviews</h3>
                </div>
                <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="review in recent_reviews" :key="review.id" class="p-4 rounded-xl border border-gray-100 hover:border-indigo-200 transition-all">
                        <div class="flex justify-between items-center mb-2">
                             <span class="font-bold text-sm">{{ review.customer_name }}</span>
                             <Rating :modelValue="review.rating" readonly :cancel="false" />
                        </div>
                        <p class="text-xs text-gray-600 italic line-clamp-3">"{{ review.comment }}"</p>
                        <div class="mt-3 flex items-center justify-between">
                            <span class="text-[10px] text-gray-400">{{ new Date(review.created_at).toLocaleDateString() }}</span>
                            <Tag :value="review.source || 'Web Site'" severity="info" class="text-[8px]" />
                        </div>
                    </div>
                    <div v-if="recent_reviews.length === 0" class="col-span-full py-10 text-center text-gray-400">
                        No customer reviews found.
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.card {
  @apply mb-0;
}
:deep(.p-datatable-header) {
    background: transparent;
    padding: 0;
}
:deep(.p-rating-item .p-rating-icon) {
    width: 0.8rem;
    height: 0.8rem;
}
</style>

