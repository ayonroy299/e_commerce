<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';

defineProps({
    sales: Object,
});
</script>

<template>
    <Head title="Sales History" />

    <AppLayout>
        <div class="card">
            <h2 class="text-xl font-semibold mb-4">Sales History</h2>
            
            <DataTable :value="sales.data" :paginator="true" :rows="15" :total-records="sales.total" lazy>
                <Column field="invoice_number" header="Invoice"></Column>
                <Column field="sold_at" header="Date">
                    <template #body="slotProps">
                        {{ new Date(slotProps.data.sold_at).toLocaleDateString() }}
                    </template>
                </Column>
                <Column field="customer.name" header="Customer">
                    <template #body="slotProps">
                        {{ slotProps.data.customer?.name || 'Walk-in' }}
                    </template>
                </Column>
                <Column field="total_amount" header="Total">
                     <template #body="slotProps">
                        {{ Number(slotProps.data.total_amount).toFixed(2) }}
                    </template>
                </Column>
                 <Column field="status" header="Status">
                     <template #body="slotProps">
                        <Tag :value="slotProps.data.status" class="uppercase" :severity="slotProps.data.status === 'completed' ? 'success' : 'warning'" />
                    </template>
                </Column>
                <Column header="Actions">
                    <template #body="slotProps">
                         <div class="flex gap-2">
                            <Link :href="route('sales.show', slotProps.data.id)">
                                <Button icon="pi pi-eye" class="p-button-text p-button-sm" />
                            </Link>
                            <Link v-if="slotProps.data.status === 'completed'" :href="route('sales.return', slotProps.data.id)">
                                <Button icon="pi pi-replay" class="p-button-text p-button-danger p-button-sm" v-tooltip="'Return Items'" />
                            </Link>
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>
    </AppLayout>
</template>
