<template>
  <div class="p-6 flex flex-col gap-6">
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-4 rounded-xl shadow-sm">
      <div>
        <h1 class="text-2xl font-bold">EMI Contract: {{ contract.sale.invoice_number }}</h1>
        <p class="text-gray-500">Customer: {{ contract.sale.customer.name }}</p>
      </div>
      <div class="flex gap-2">
        <Badge :severity="getStatusSeverity(contract.status)" size="large">
          {{ contract.status.toUpperCase() }}
        </Badge>
        <Button v-if="contract.status === 'active'" label="Cancel Contract" severity="danger" outlined @click="cancelContract" />
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Summary Card -->
      <div class="card p-4 bg-white rounded-xl shadow-sm col-span-1">
        <h3 class="text-lg font-bold mb-4 border-b pb-2">Financial Summary</h3>
        <div class="flex flex-col gap-3">
          <div class="flex justify-between">
            <span>Principal Amount:</span>
            <span class="font-semibold">{{ $formatCurrency(contract.principal_amount) }}</span>
          </div>
          <div class="flex justify-between text-green-600">
            <span>Down Payment:</span>
            <span class="font-semibold">{{ $formatCurrency(contract.down_payment) }}</span>
          </div>
          <div class="flex justify-between border-t pt-2">
            <span>Financed Amount:</span>
            <span class="font-semibold">{{ $formatCurrency(contract.financed_amount) }}</span>
          </div>
          <div class="flex justify-between">
            <span>Interest ({{ contract.plan.interest_rate }}% {{ contract.plan.interest_type }}):</span>
            <span class="font-semibold">{{ $formatCurrency(contract.interest_amount) }}</span>
          </div>
          <div class="flex justify-between border-t pt-2 text-xl font-bold">
            <span>Total Payable:</span>
            <span>{{ $formatCurrency(contract.total_amount) }}</span>
          </div>
        </div>
        
        <div class="mt-6">
          <Button label="Record Payment" icon="pi pi-plus" class="w-full" @click="showPaymentDialog = true" :disabled="contract.status !== 'active'" />
        </div>
      </div>

      <!-- Schedule Table -->
      <div class="card p-4 bg-white rounded-xl shadow-sm col-span-2">
        <h3 class="text-lg font-bold mb-4 border-b pb-2">Installment Schedule</h3>
        <DataTable :value="contract.schedules" class="p-datatable-sm">
          <Column field="installment_no" header="#" />
          <Column field="due_date" header="Due Date">
            <template #body="{ data }">
              {{ $formatDate(data.due_date) }}
            </template>
          </Column>
          <Column field="total_due" header="Due Amount">
            <template #body="{ data }">
              {{ $formatCurrency(data.total_due) }}
            </template>
          </Column>
          <Column field="paid_amount" header="Paid">
            <template #body="{ data }">
              <span :class="data.paid_amount >= data.total_due ? 'text-green-600 font-bold' : ''">
                {{ $formatCurrency(data.paid_amount) }}
              </span>
            </template>
          </Column>
          <Column field="status" header="Status">
            <template #body="{ data }">
              <Badge :severity="getScheduleSeverity(data.status)">{{ data.status.toUpperCase() }}</Badge>
            </template>
          </Column>
        </DataTable>
      </div>
    </div>

    <!-- Receipt Dialog -->
    <Dialog v-model:visible="showPaymentDialog" header="Record EMI Payment" modal class="w-[400px]">
      <div class="flex flex-col gap-4">
        <div>
          <label class="block font-bold mb-1">Amount</label>
          <InputNumber v-model="paymentForm.amount" mode="decimal" class="w-full" autofocus />
        </div>
        <div>
          <label class="block font-bold mb-1">Payment Method</label>
          <Select 
            v-model="paymentForm.payment_method_id" 
            :options="paymentMethods" 
            optionLabel="name" 
            optionValue="id" 
            class="w-full" 
            placeholder="Select Method"
          />
        </div>
        <div>
          <label class="block font-bold mb-1">Reference</label>
          <InputText v-model="paymentForm.reference_no" class="w-full" placeholder="Cheque #, TRX ID, etc." />
        </div>
        <div>
          <label class="block font-bold mb-1">Note</label>
          <Textarea v-model="paymentForm.note" class="w-full" rows="3" />
        </div>
        <div class="flex justify-end gap-2 mt-4">
          <Button label="Cancel" severity="secondary" outlined @click="showPaymentDialog = false" />
          <Button label="Submit Payment" @click="submitPayment" :loading="paymentForm.processing" />
        </div>
      </div>
    </Dialog>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps(['contract', 'paymentMethods']);

const showPaymentDialog = ref(false);

const paymentForm = useForm({
    emi_contract_id: props.contract.id,
    amount: 0,
    payment_method_id: null,
    reference_no: '',
    note: '',
});

const getStatusSeverity = (status) => {
    switch (status) {
        case 'active': return 'info';
        case 'completed': return 'success';
        case 'cancelled': return 'secondary';
        default: return 'danger';
    }
};

const getScheduleSeverity = (status) => {
    switch (status) {
        case 'paid': return 'success';
        case 'partially_paid': return 'warning';
        case 'overdue': return 'danger';
        default: return 'info';
    }
};

const submitPayment = () => {
    paymentForm.post(route('emi-receipts.store'), {
        onSuccess: () => {
            showPaymentDialog.value = false;
            paymentForm.reset();
        }
    });
};

const cancelContract = () => {
    if (confirm('Are you sure you want to cancel this EMI contract?')) {
        router.post(route('emi-contracts.cancel', props.contract.id));
    }
};
</script>
