<template>
  <div>
    <CrudComponent :form="form">
      <template #columns>
        <Column
          field="ticket_no"
          header="Ticket #"
          sortable
        />
        <Column
          field="customer.name"
          header="Customer"
          sortable
        />
        <Column
          field="product.name"
          header="Product"
        />
        <Column
          field="priority"
          header="Priority"
        >
          <template #body="{ data }">
            <Badge :severity="getPrioritySeverity(data.priority)">
              {{ data.priority.toUpperCase() }}
            </Badge>
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
        <Column
          field="assignee.name"
          header="Assigned To"
        />
      </template>

      <template #form="{ submitted }">
        <div class="flex flex-col gap-4">
          <div class="field">
            <label class="font-bold block mb-2">Customer</label>
            <Select
              v-model="form.customer_id"
              :options="customers"
              option-label="name"
              option-value="id"
              filter
              placeholder="Select Customer"
              class="w-full"
            />
          </div>

          <div class="field">
            <label class="font-bold block mb-2">Product (Optional)</label>
            <Select
              v-model="form.product_id"
              :options="products"
              option-label="name"
              option-value="id"
              filter
              placeholder="Select Product"
              class="w-full"
            />
          </div>

          <div class="field">
            <label class="font-bold block mb-2">Serial / IMEI</label>
            <InputText
              v-model="form.serial_no"
              class="w-full"
            />
          </div>

          <div class="field">
            <label class="font-bold block mb-2">Issue Description</label>
            <Textarea
              v-model="form.issue"
              rows="4"
              class="w-full"
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="field">
              <label class="font-bold block mb-2">Priority</label>
              <Select
                v-model="form.priority"
                :options="priorities"
                option-label="label"
                option-value="value"
                class="w-full"
              />
            </div>
            <div class="field">
              <label class="font-bold block mb-2">Assign To</label>
              <Select
                v-model="form.assigned_to"
                :options="users"
                option-label="name"
                option-value="id"
                class="w-full"
              />
            </div>
          </div>
        </div>
      </template>
    </CrudComponent>
  </div>
</template>

<script setup>
import CrudComponent from "@/Components/CrudComponent.vue";
import { useForm } from "@inertiajs/vue3";

const props = defineProps(['items', 'customers', 'products', 'users']);

const form = useForm({
    customer_id: null,
    product_id: null,
    serial_no: "",
    issue: "",
    priority: "low",
    assigned_to: null,
});

const priorities = [
    { label: 'Low', value: 'low' },
    { label: 'Medium', value: 'medium' },
    { label: 'High', value: 'high' },
    { label: 'Critical', value: 'critical' }
];

const getPrioritySeverity = (priority) => {
    switch (priority) {
        case 'critical': return 'danger';
        case 'high': return 'warn';
        case 'medium': return 'info';
        case 'low': return 'success';
        default: return null;
    }
};

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
</script>
