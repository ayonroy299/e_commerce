<template>
  <div>
    <CrudComponent :form="form">
      <template #columns>
        <Column field="code" header="Account Code" sortable />
        <Column field="name" header="Account Name" sortable />
        <Column field="type" header="Type">
          <template #body="{ data }">
            <Badge :severity="getTypeSeverity(data.type)">{{ data.type.toUpperCase() }}</Badge>
          </template>
        </Column>
        <Column field="is_system" header="System Managed">
          <template #body="{ data }">
            <i v-if="data.is_system" class="pi pi-check-circle text-blue-500"></i>
            <i v-else class="pi pi-user text-gray-400"></i>
          </template>
        </Column>
        <Column header="Actions">
          <template #body="{ data }">
            <Button icon="pi pi-list" outlined rounded @click="viewLedger(data.id)" />
          </template>
        </Column>
      </template>

      <template #form="{ submitted }">
        <div class="flex flex-col gap-4">
          <div class="field">
            <label class="font-bold block mb-2">Account Name</label>
            <InputText v-model="form.name" class="w-full" :disabled="form.is_system" />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div class="field">
              <label class="font-bold block mb-2">Code</label>
              <InputText v-model="form.code" class="w-full" :disabled="form.id && form.is_system" />
            </div>
            <div class="field">
              <label class="font-bold block mb-2">Type</label>
              <Select v-model="form.type" :options="types" placeholder="Select Type" class="w-full" :disabled="form.is_system" />
            </div>
          </div>
          <div class="field flex items-center gap-2">
            <Checkbox v-model="form.is_active" :binary="true" />
            <label>Active Account</label>
          </div>
        </div>
      </template>
    </CrudComponent>
  </div>
</template>

<script setup>
import CrudComponent from "@/Components/CrudComponent.vue";
import { useForm, router } from "@inertiajs/vue3";

const props = defineProps(['items', 'types']);

const form = useForm({
    id: null,
    name: "",
    code: "",
    type: "asset",
    is_active: true,
    is_system: false,
});

const getTypeSeverity = (type) => {
    switch (type) {
        case 'asset': return 'success';
        case 'liability': return 'danger';
        case 'equity': return 'primary';
        case 'revenue': return 'info';
        case 'expense': return 'warn';
        default: return null;
    }
};

const viewLedger = (id) => {
    router.visit(route('accounts.ledger', id));
};
</script>
