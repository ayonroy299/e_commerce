<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, watch } from 'vue';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import { debounce } from 'lodash';

const props = defineProps({
    orders: Object,
});

const search = ref('');

const getStatusSeverity = (status) => {
    switch (status) {
        case 'received': return 'success';
        case 'ordered': return 'info';
        case 'partially_received': return 'warning';
        case 'draft': return 'secondary';
        case 'cancelled': return 'danger';
        default: return 'info';
    }
};
</script>

<template>
    <Head title="Purchase Orders" />

    <AppLayout>
        <div class="card">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Purchase Orders</h2>
                <Link :href="route('purchase-orders.create')">
                    <Button label="New Purchase Order" icon="pi pi-plus" />
                </Link>
            </div>

            <DataTable :value="orders.data" :paginator="true" :rows="15" :total-records="orders.total" lazy>
                <Column field="date" header="Date">
                    <template #body="slotProps">
                         {{ new Date(slotProps.data.date).toLocaleDateString() }}
                    </template>
                </Column>
                <Column field="id" header="Ref #">
                    <template #body="slotProps">
                        <span class="text-xs uppercase">{{ slotProps.data.id.substring(0, 8) }}</span>
                    </template>
                </Column>
                <Column field="supplier.name" header="Supplier"></Column>
                <Column field="total_amount" header="Total">
                    <template #body="slotProps">
                        {{ Number(slotProps.data.total_amount).toFixed(2) }}
                    </template>
                </Column>
                <Column field="status" header="Status">
                     <template #body="slotProps">
                        <Tag :value="slotProps.data.status.replace('_', ' ')" :severity="getStatusSeverity(slotProps.data.status)" class="uppercase" />
                    </template>
                </Column>
                 <Column header="Actions">
                    <template #body="slotProps">
                        <Link :href="route('purchase-orders.show', slotProps.data.id)">
                            <Button icon="pi pi-eye" class="p-button-text p-button-sm" />
                        </Link>
                    </template>
                </Column>
            </DataTable>
        </div>
    </AppLayout>
</template>
