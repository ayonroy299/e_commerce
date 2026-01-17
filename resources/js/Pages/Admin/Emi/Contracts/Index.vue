<template>
  <div class="card p-4">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-bold">EMI Contracts</h2>
    </div>
    <DataTable
      :value="items.data"
      paginator
      :rows="items.per_page"
      :total-records="items.total"
      lazy
      class="p-datatable-sm"
    >
      <Column
        field="sale.invoice_number"
        header="Invoice #"
      />
      <Column
        field="sale.customer.name"
        header="Customer"
      />
      <Column
        field="plan.name"
        header="Plan"
      />
      <Column
        field="total_amount"
        header="Total Amount"
      >
        <template #body="{ data }">
          {{ $formatCurrency(data.total_amount) }}
        </template>
      </Column>
      <Column
        field="status"
        header="Status"
      >
        <template #body="{ data }">
          <Badge :severity="getStatusSeverity(data.status)">
            {{ data.status.toUpperCase() }}
          </Badge>
        </template>
      </Column>
      <Column header="Actions">
        <template #body="{ data }">
          <Button
            icon="pi pi-eye"
            outlined
            rounded
            @click="viewContract(data.id)"
          />
        </template>
      </Column>
    </DataTable>
  </div>
</template>

<script setup>
import { router } from "@inertiajs/vue3";

const props = defineProps(['items']);

const getStatusSeverity = (status) => {
    switch (status) {
        case 'active': return 'info';
        case 'completed': return 'success';
        case 'defaulted': return 'danger';
        case 'cancelled': return 'secondary';
        default: return null;
    }
};

const viewContract = (id) => {
    router.visit(route('emi-contracts.show', id));
};
</script>
