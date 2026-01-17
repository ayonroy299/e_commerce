<template>
  <div>
    <CrudComponent :form="form">
      <template #columns>
        <Column
          field="name"
          header="Name"
        />
        <Column
          field="address"
          header="Address"
        />
        <Column
          field="code"
          header="Code"
        />
        <Column
          field="phone"
          header="Phone"
        />
        <Column
          field="is_active"
          header="Status"
        >
          <template #body="{ data }">
            <Badge :severity="data.is_active ? 'success' : 'danger'">
              {{ data.is_active ? 'Active' : 'Inactive' }}
            </Badge>
          </template>
        </Column>
        <Column
          field="created_at"
          header="Created At"
          sortable
        />
        <Column
          field="updated_at"
          header="Updated At"
          sortable
        />
      </template>

      <template #form="{ submitted, handlePhotoUpload, photoPreview, resolveImagePath }">
        <Form :form="form" />
      </template>
    </CrudComponent>
  </div>
</template>
<script setup>
import CrudComponent from '@/Components/CrudComponent.vue';
import Form from './Form.vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    address: '',
    code: '',
    phone: '',
    is_active: '',
});

</script>
