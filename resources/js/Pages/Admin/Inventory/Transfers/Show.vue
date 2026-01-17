<template>
  <div class="p-6 flex flex-col gap-6">
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-4 rounded-xl shadow-sm">
      <div>
        <h1 class="text-2xl font-bold">Transfer: {{ transfer.transfer_no }}</h1>
        <p class="text-gray-500">From: {{ transfer.from_branch.name }} -> To: {{ transfer.to_branch.name }}</p>
      </div>
      <div class="flex gap-2">
        <Badge :severity="getStatusSeverity(transfer.status)" size="large">
          {{ transfer.status.toUpperCase() }}
        </Badge>
        
        <Button v-if="transfer.status === 'pending' || transfer.status === 'draft'" label="Ship Transfer" icon="pi pi-send" severity="warn" @click="sendTransfer" />
        <Button v-if="transfer.status === 'sent'" label="Receive Transfer" icon="pi pi-download" severity="success" @click="showReceiveDialog = true" />
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Summary Card -->
      <div class="card p-4 bg-white rounded-xl shadow-sm col-span-1">
        <h3 class="text-lg font-bold mb-4 border-b pb-2">Information</h3>
        <div class="flex flex-col gap-3">
          <div>
            <label class="text-sm text-gray-500">Source Warehouse</label>
            <p class="font-semibold">{{ transfer.from_warehouse.name }}</p>
          </div>
          <div>
            <label class="text-sm text-gray-500">Destination Warehouse</label>
            <p class="font-semibold">{{ transfer.to_warehouse.name }}</p>
          </div>
          <div v-if="transfer.sent_at">
            <label class="text-sm text-gray-500">Sent At</label>
            <p class="font-semibold">{{ $formatDate(transfer.sent_at) }}</p>
          </div>
          <div v-if="transfer.received_at">
            <label class="text-sm text-gray-500">Received At</label>
            <p class="font-semibold">{{ $formatDate(transfer.received_at) }}</p>
          </div>
          <div v-if="transfer.notes">
            <label class="text-sm text-gray-500">Notes</label>
            <p class="p-2 bg-gray-50 rounded italic">{{ transfer.notes }}</p>
          </div>
        </div>
      </div>

      <!-- Lines Table -->
      <div class="card p-4 bg-white rounded-xl shadow-sm col-span-2">
        <h3 class="text-lg font-bold mb-4 border-b pb-2">Stock Items</h3>
        <DataTable :value="transfer.lines" class="p-datatable-sm">
          <Column field="product.name" header="Product" />
          <Column field="variation.name" header="Variant">
             <template #body="{ data }">
                {{ data.variation?.name ?? 'N/A' }}
             </template>
          </Column>
          <Column field="quantity" header="Quantity" class="text-right" />
          <Column v-if="transfer.status === 'received'" field="received_quantity" header="Received" class="text-right text-green-600 font-bold" />
        </DataTable>
      </div>
    </div>

    <!-- Receive Dialog -->
    <Dialog v-model:visible="showReceiveDialog" header="Receive Stock Transfer" modal class="w-[500px]">
      <div class="flex flex-col gap-4">
        <div v-for="line in transfer.lines" :key="line.id" class="flex justify-between items-center border-b pb-2">
           <div>
              <div class="font-bold">{{ line.product.name }}</div>
              <div class="text-xs text-gray-500">Ordered: {{ line.quantity }}</div>
           </div>
           <div class="flex items-center gap-2">
              <label>Received:</label>
              <InputNumber v-model="receiveForm.items[line.id]" mode="decimal" class="w-24" />
           </div>
        </div>
        
        <div class="flex justify-end gap-2 mt-4">
          <Button label="Cancel" severity="secondary" outlined @click="showReceiveDialog = false" />
          <Button label="Confirm Receipt" severity="success" @click="submitReceive" :loading="processing" />
        </div>
      </div>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps(['transfer']);

const showReceiveDialog = ref(false);
const processing = ref(false);

const receiveForm = reactive({
    items: props.transfer.lines.reduce((acc, line) => {
        acc[line.id] = line.quantity;
        return acc;
    }, {})
});

const getStatusSeverity = (status) => {
    switch (status) {
        case 'draft': return 'secondary';
        case 'pending': return 'info';
        case 'sent': return 'warn';
        case 'received': return 'success';
        case 'cancelled': return 'danger';
        default: return null;
    }
};

const sendTransfer = () => {
    if (confirm('Mark this transfer as shipped? Stock will be deducted from current branch.')) {
        router.post(route('stock-transfers.send', props.transfer.id));
    }
};

const submitReceive = () => {
    processing.value = true;
    const data = {
        items: Object.keys(receiveForm.items).map(id => ({
            id: id,
            received_quantity: receiveForm.items[id]
        }))
    };
    
    router.post(route('stock-transfers.receive', props.transfer.id), data, {
        onSuccess: () => {
            showReceiveDialog.value = false;
            processing.value = false;
        },
        onError: () => processing.value = false
    });
};
</script>
