<template>
  <div class="p-6 flex flex-col gap-6">
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-4 rounded-xl shadow-sm">
      <div>
        <h1 class="text-2xl font-bold">Ticket: {{ ticket.ticket_no }}</h1>
        <p class="text-gray-500">Customer: {{ ticket.customer.name }} | Priority: {{ ticket.priority.toUpperCase() }}</p>
      </div>
      <div class="flex gap-2">
        <Badge :severity="getStatusSeverity(ticket.status)" size="large">
          {{ ticket.status.toUpperCase() }}
        </Badge>
        <Select 
          v-model="ticket.status" 
          :options="statuses" 
          optionLabel="label" 
          optionValue="value" 
          @change="updateStatus"
          placeholder="Change Status"
          class="w-48"
        />
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Info Card -->
      <div class="card p-4 bg-white rounded-xl shadow-sm col-span-1">
        <h3 class="text-lg font-bold mb-4 border-b pb-2">Ticket Info</h3>
        <div class="flex flex-col gap-3">
          <div>
            <label class="text-sm text-gray-500">Product</label>
            <p class="font-semibold">{{ ticket.product?.name ?? 'N/A' }}</p>
          </div>
          <div>
            <label class="text-sm text-gray-500">Serial/IMEI</label>
            <p class="font-semibold">{{ ticket.serial_no ?? 'N/A' }}</p>
          </div>
          <div>
            <label class="text-sm text-gray-500">Issue Reported</label>
            <p class="p-2 bg-gray-50 rounded italic">{{ ticket.issue }}</p>
          </div>
          <div>
            <label class="text-sm text-gray-500">Assignee</label>
            <p class="font-semibold">{{ ticket.assignee?.name ?? 'Unassigned' }}</p>
          </div>
        </div>
      </div>

      <!-- Actions Timeline -->
      <div class="card p-4 bg-white rounded-xl shadow-sm col-span-2">
        <h3 class="text-lg font-bold mb-4 border-b pb-2">Service History</h3>
        
        <div class="flex flex-col gap-4 max-h-[500px] overflow-y-auto mb-4">
          <div v-for="action in ticket.actions" :key="action.id" class="p-3 border-l-4 border-blue-500 bg-blue-50/50 rounded-r-lg">
            <div class="flex justify-between items-center mb-1">
              <span class="font-bold">{{ action.technician.name }}</span>
              <span class="text-xs text-gray-500">{{ $formatDate(action.created_at) }}</span>
            </div>
            <p class="text-sm">{{ action.notes }}</p>
            <p v-if="action.internal_notes" class="text-xs text-red-600 mt-1 bg-red-50 p-1 rounded">
              <i class="pi pi-lock text-[10px]"></i> {{ action.internal_notes }}
            </p>
          </div>
          <div v-if="!ticket.actions.length" class="text-center py-10 text-gray-400">
             No actions recorded yet.
          </div>
        </div>

        <div class="border-t pt-4">
          <h4 class="font-bold mb-2">Add New Action</h4>
          <div class="flex flex-col gap-3">
             <Textarea v-model="actionForm.notes" placeholder="Notes for customer..." rows="3" class="w-full" />
             <Textarea v-model="actionForm.internal_notes" placeholder="Internal/Technician notes..." rows="2" class="w-full bg-red-50/20" />
             <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                   <label>Cost Est.</label>
                   <InputNumber v-model="actionForm.cost_estimate" mode="decimal" class="w-32" />
                </div>
                <Button label="Record Action" @click="submitAction" :loading="actionForm.processing" />
             </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps(['ticket', 'technicians']);

const actionForm = useForm({
    notes: '',
    internal_notes: '',
    cost_estimate: 0,
});

const statuses = [
    { label: 'Open', value: 'open' },
    { label: 'Diagnosing', value: 'diagnosing' },
    { label: 'Waiting Parts', value: 'waiting_parts' },
    { label: 'Repaired', value: 'repaired' },
    { label: 'Delivered', value: 'delivered' },
    { label: 'Closed', value: 'closed' },
];

const getStatusSeverity = (status) => {
    switch (status) {
        case 'open': return 'info';
        case 'diagnosing': return 'warn';
        case 'waiting_parts': return 'help';
        case 'repaired': return 'success';
        case 'delivered': return 'primary';
        case 'closed': return 'secondary';
        default: return null;
    }
};

const updateStatus = () => {
    router.post(route('service-tickets.update-status', props.ticket.id), {
        status: props.ticket.status
    });
};

const submitAction = () => {
    actionForm.post(route('service-actions.store', props.ticket.id), {
        onSuccess: () => actionForm.reset()
    });
};
</script>
