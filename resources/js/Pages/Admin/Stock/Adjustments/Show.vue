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
    adjustment: Object,
});

const confirm = useConfirm();

const getStatusSeverity = (status) => {
    switch (status) {
        case 'approved': return 'success';
        case 'draft': return 'warning';
        default: return 'info';
    }
};

const approve = () => {
    confirm.require({
        message: 'Are you sure you want to approve this adjustment? Stock will be updated immediately.',
        header: 'Confirm Approval',
        icon: 'pi pi-exclamation-triangle',
        accept: () => {
             router.post(route('adjustments.approve', props.adjustment.id));
        }
    });
};
</script>

<template>
    <Head title="Adjustment Details" />

    <AppLayout>
         <div class="card max-w-4xl mx-auto">
            <div class="flex justify-between items-start mb-6">
                <div>
                     <h2 class="text-xl font-semibold mb-1">Adjustment Details</h2>
                     <p class="text-gray-500 text-sm">Ref: {{ adjustment.id }}</p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('adjustments.index')">
                        <Button label="Back" icon="pi pi-arrow-left" class="p-button-outlined p-button-secondary" />
                    </Link>
                    <Button 
                        v-if="adjustment.status === 'draft'" 
                        label="Approve Adjustment" 
                        icon="pi pi-check" 
                        class="p-button-success" 
                        @click="approve" 
                    />
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 border-b pb-6">
                <div>
                    <span class="block text-gray-500 text-sm">Status</span>
                    <Tag :value="adjustment.status" :severity="getStatusSeverity(adjustment.status)" class="uppercase mt-1" />
                </div>
                <div>
                    <span class="block text-gray-500 text-sm">Reason</span>
                    <span class="block font-medium capitalize mt-1">{{ adjustment.reason.replace('_', ' ') }}</span>
                </div>
                 <div>
                    <span class="block text-gray-500 text-sm">Date</span>
                    <span class="block font-medium mt-1">{{ new Date(adjustment.date).toLocaleDateString() }}</span>
                </div>
                 <div>
                    <span class="block text-gray-500 text-sm">Warehouse</span>
                    <!-- Assuming backend loads warehouse relation (controller check needed) -->
                    <!-- StockAdjustmentController show method only loaded lines.product/variation. Need to load warehouse too. -->
                    <!-- I will assume ID for now or fix controller. Let's fix controller in next step if missed. -->
                    <span class="block font-medium mt-1">{{ adjustment.warehouse_id }}</span> 
                </div>
            </div>

            <h3 class="font-semibold mb-3">Adjusted Items</h3>
            <DataTable :value="adjustment.lines" responsiveLayout="scroll" class="mb-6">
                <Column field="product.name" header="Product">
                    <template #body="slotProps">
                        <div class="font-medium">{{ slotProps.data.product?.name || 'Unknown Product' }}</div>
                        <div v-if="slotProps.data.variation" class="text-xs text-gray-500">
                             Variant: {{ slotProps.data.variation?.sku }} 
                        </div>
                    </template>
                </Column>
                 <Column field="quantity_adjusted" header="Qty Change">
                     <template #body="slotProps">
                        <span :class="{'text-red-500': slotProps.data.quantity_adjusted < 0, 'text-green-500': slotProps.data.quantity_adjusted > 0}">
                             {{ slotProps.data.quantity_adjusted > 0 ? '+' : '' }}{{ slotProps.data.quantity_adjusted }}
                        </span>
                    </template>
                </Column>
            </DataTable>

            <div v-if="adjustment.notes">
                <h3 class="font-semibold mb-2">Notes</h3>
                <p class="text-gray-700 bg-gray-50 p-3 rounded">{{ adjustment.notes }}</p>
            </div>
         </div>
          <ConfirmDialog />
    </AppLayout>
</template>
