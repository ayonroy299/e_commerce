<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { useConfirm } from "primevue/useconfirm";
import ConfirmDialog from 'primevue/confirmdialog';

const props = defineProps({
    order: Object,
});

const confirm = useConfirm();

const approve = () => {
    confirm.require({
        message: 'Are you sure you want to approve this order? This will mark it as Ordered.',
        header: 'Confirm Approval',
        icon: 'pi pi-check-circle',
        accept: () => {
             router.post(route('purchase-orders.approve', props.order.id));
        }
    });
};
</script>

<template>
    <Head title="Purchase Order Details" />

    <AppLayout>
        <div class="card max-w-5xl mx-auto">
             <div class="flex justify-between items-start mb-6">
                <div>
                     <h2 class="text-xl font-semibold mb-1">PO Details</h2>
                     <p class="text-gray-500 text-sm">Ref: {{ order.id }}</p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('purchase-orders.index')">
                        <Button label="Back" icon="pi pi-arrow-left" class="p-button-outlined p-button-secondary" />
                    </Link>
                    <Button
                        v-if="order.status === 'draft'"
                        label="Approve Order"
                        icon="pi pi-check"
                        class="p-button-success"
                        @click="approve"
                    />
                     <Link v-if="['ordered', 'partially_received'].includes(order.status)" :href="route('purchase-orders.receive', order.id)">
                        <Button label="Receive Goods (GRN)" icon="pi pi-box" class="p-button-info" />
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 border-b pb-6">
                 <div>
                    <span class="block text-gray-500 text-sm">Status</span>
                     <Tag :value="order.status.replace('_', ' ')" class="uppercase mt-1" />
                </div>
                 <div>
                    <span class="block text-gray-500 text-sm">Supplier</span>
                    <span class="block font-medium mt-1">{{ order.supplier.name }}</span>
                </div>
                 <div>
                    <span class="block text-gray-500 text-sm">Date</span>
                    <span class="block font-medium mt-1">{{ new Date(order.date).toLocaleDateString() }}</span>
                </div>
                 <div>
                    <span class="block text-gray-500 text-sm">Total Amount</span>
                    <span class="block font-medium mt-1">{{ Number(order.total_amount).toFixed(2) }}</span>
                </div>
            </div>

            <h3 class="font-semibold mb-3">Order Items</h3>
             <DataTable :value="order.lines" responsiveLayout="scroll" class="mb-6">
                 <Column field="product.name" header="Product">
                     <template #body="slotProps">
                        <div class="font-medium">{{ slotProps.data.product?.name }}</div>
                        <div v-if="slotProps.data.variation" class="text-xs text-gray-500">
                             {{ slotProps.data.variation?.sku }}
                        </div>
                    </template>
                 </Column>
                 <Column field="quantity_ordered" header="Qty Ordered"></Column>
                 <Column field="quantity_received" header="Qty Received">
                     <template #body="slotProps">
                        <span :class="{'text-green-600 font-bold': slotProps.data.quantity_received >= slotProps.data.quantity_ordered}">
                            {{ slotProps.data.quantity_received }}
                        </span>
                     </template>
                 </Column>
                 <Column field="unit_cost" header="Unit Cost"></Column>
                 <Column field="subtotal" header="Subtotal"></Column>
             </DataTable>

             <ConfirmDialog />
        </div>
    </AppLayout>
</template>
