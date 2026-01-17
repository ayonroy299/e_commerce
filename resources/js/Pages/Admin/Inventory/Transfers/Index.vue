<template>
  <div>
    <CrudComponent :form="form">
      <template #columns>
        <Column
          field="transfer_no"
          header="Transfer #"
          sortable
        />
        <Column
          field="from_branch.name"
          header="From"
        />
        <Column
          field="to_branch.name"
          header="To"
        />
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
          field="created_at"
          header="Date"
          sortable
        >
          <template #body="{ data }">
            {{ $formatDate(data.created_at) }}
          </template>
        </Column>
        <Column header="Actions">
          <template #body="{ data }">
            <Button
              icon="pi pi-eye"
              outlined
              rounded
              @click="viewTransfer(data.id)"
            />
          </template>
        </Column>
      </template>

      <template #form="{ submitted }">
        <div class="flex flex-col gap-4">
          <div class="field">
            <label class="font-bold block mb-2">Destination Branch</label>
            <Select
              v-model="form.to_branch_id"
              :options="branches"
              option-label="name"
              option-value="id"
              placeholder="Select Branch"
              class="w-full"
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="field">
              <label class="font-bold block mb-2">From Warehouse</label>
              <Select
                v-model="form.from_warehouse_id"
                :options="warehouses"
                option-label="name"
                option-value="id"
                placeholder="Source"
                class="w-full"
              />
            </div>
            <div class="field">
              <label class="font-bold block mb-2">To Warehouse</label>
              <Select
                v-model="form.to_warehouse_id"
                :options="warehouses"
                option-label="name"
                option-value="id"
                placeholder="Destination"
                class="w-full"
              />
            </div>
          </div>

          <div class="field">
            <label class="font-bold block mb-2">Notes</label>
            <Textarea
              v-model="form.notes"
              rows="3"
              class="w-full"
            />
          </div>

          <!-- Line items placeholder - In a real app we'd need a dynamic list here -->
          <div class="p-3 bg-yellow-50 border border-yellow-200 rounded text-sm italic">
             Note: Use the 'Show' page to add and manage product lines for this transfer.
          </div>
        </div>
      </template>
    </CrudComponent>
  </div>
</template>

<script setup>
import CrudComponent from "@/Components/CrudComponent.vue";
import { useForm, router } from "@inertiajs/vue3";

const props = defineProps(['items', 'branches', 'warehouses']);

const form = useForm({
    to_branch_id: null,
    from_warehouse_id: null,
    to_warehouse_id: null,
    notes: "",
    lines: [], // Will be managed in detailed view or added here
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

const viewTransfer = (id) => {
    router.visit(route('stock-transfers.show', id));
};
</script>
