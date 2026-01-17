<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'; // Or AppLayout
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag'; // Import Tag component

defineProps({
    stats: Object,
    low_stock: Array,
    recent_activity: Array,
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <!-- Daily Sales -->
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                <div class="text-gray-500 text-sm font-semibold uppercase">Today's Sales</div>
                <div class="text-3xl font-bold mt-2 text-gray-800">
                    {{ stats.currency }}{{ Number(stats.daily_sales).toFixed(2) }}
                </div>
            </div>

            <!-- Daily Orders -->
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                <div class="text-gray-500 text-sm font-semibold uppercase">Today's Orders</div>
                <div class="text-3xl font-bold mt-2 text-gray-800">
                    {{ stats.daily_orders }}
                </div>
            </div>

            <!-- Total Products -->
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-indigo-500">
                <div class="text-gray-500 text-sm font-semibold uppercase">Total Products</div>
                <div class="text-3xl font-bold mt-2 text-gray-800">
                    {{ stats.total_products }}
                </div>
            </div>
            
            <!-- Quick Actions -->
             <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500 flex flex-col justify-center gap-2">
                <div class="text-gray-500 text-sm font-semibold uppercase mb-1">Quick Actions</div>
                <div class="flex gap-2">
                     <Link :href="route('pos.index')" class="w-full">
                        <Button label="POS Terminal" icon="pi pi-desktop" class="w-full p-button-sm" />
                     </Link>
                     <Link :href="route('products.create')" class="w-full">
                        <Button label="Add Product" icon="pi pi-plus" class="w-full p-button-outlined p-button-sm" />
                     </Link>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Low Stock Alerts -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-4 border-b flex justify-between items-center bg-red-50 rounded-t-lg">
                    <h3 class="font-semibold text-red-700">Low Stock Alerts</h3>
                     <Link :href="route('products.index')">
                        <span class="text-xs text-blue-600 hover:underline">View Inventory</span>
                    </Link>
                </div>
                <div class="p-2">
                    <DataTable :value="low_stock" responsiveLayout="scroll" v-if="low_stock.length">
                        <Column field="name" header="Product"></Column>
                        <Column field="stock" header="Current"></Column>
                        <Column field="threshold" header="Min"></Column>
                        <Column header="Status">
                            <template #body>
                                <Tag value="Low Stock" severity="danger" />
                            </template>
                        </Column>
                    </DataTable>
                    <div v-else class="p-4 text-center text-gray-500">
                        All stock levels look good!
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
             <div class="bg-white rounded-lg shadow">
                <div class="p-4 border-b bg-gray-50 rounded-t-lg">
                    <h3 class="font-semibold text-gray-700">Recent Activity</h3>
                </div>
                <div class="divide-y">
                    <div v-for="activity in recent_activity" :key="activity.id" class="p-4 flex justify-between items-center hover:bg-gray-50">
                        <div>
                             <p class="font-medium text-gray-800">{{ activity.description }}</p>
                             <p class="text-xs text-gray-500">{{ activity.time }}</p>
                        </div>
                        <div class="font-bold text-gray-700">
                            {{ stats.currency }}{{ Number(activity.amount).toFixed(2) }}
                        </div>
                    </div>
                     <div v-if="recent_activity.length === 0" class="p-4 text-center text-gray-500">
                        No recent activity.
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
