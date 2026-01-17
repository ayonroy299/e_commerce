<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

defineProps({
    sale: Object,
});
</script>

<template>
    <Head title="Sale Details" />

    <AppLayout>
        <div class="card max-w-4xl mx-auto">
             <div class="flex justify-between items-start mb-6">
                <div>
                     <h2 class="text-xl font-semibold mb-1">Invoice {{ sale.invoice_number }}</h2>
                     <p class="text-gray-500 text-sm">
                         {{ new Date(sale.sold_at).toLocaleString() }}
                     </p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('sales.index')">
                        <Button label="Back" icon="pi pi-arrow-left" class="p-button-outlined p-button-secondary" />
                    </Link>
                    <Button label="Print Invoice" icon="pi pi-print" class="p-button-help" onclick="window.print()" />
                </div>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 border-b pb-6">
                 <div>
                    <span class="block text-gray-500 text-sm">Customer</span>
                    <span class="block font-medium mt-1">{{ sale.customer?.name || 'Walk-in' }}</span>
                </div>
                 <div>
                    <span class="block text-gray-500 text-sm">Status</span>
                     <Tag :value="sale.status" class="uppercase mt-1" />
                </div>
                <div>
                    <span class="block text-gray-500 text-sm">Total</span>
                    <span class="block font-medium mt-1">{{ Number(sale.total_amount).toFixed(2) }}</span>
                </div>
                 <div>
                    <span class="block text-gray-500 text-sm">Cashier</span>
                    <span class="block font-medium mt-1">{{ sale.cashier?.name }}</span>
                </div>
            </div>

            <DataTable :value="sale.lines" responsiveLayout="scroll" class="mb-6">
                 <Column field="product.name" header="Product"></Column>
                 <Column field="quantity" header="Qty"></Column>
                 <Column field="unit_price" header="Price"></Column>
                 <Column field="subtotal" header="Subtotal"></Column>
             </DataTable>
             
             <div class="flex justify-end">
                 <div class="w-64 space-y-2">
                     <div class="flex justify-between">
                         <span>Subtotal</span>
                         <span>{{ sale.total_amount }}</span>
                     </div>
                     <div class="flex justify-between font-bold text-lg border-t pt-2">
                         <span>Total</span>
                         <span>{{ sale.total_amount }}</span>
                     </div>
                 </div>
             </div>
        </div>
    </AppLayout>
</template>
