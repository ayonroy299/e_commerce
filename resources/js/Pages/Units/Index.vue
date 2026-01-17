<template>
  <div>
    <CrudComponent :form="form">
      <template #columns>
        <Column
          field="name"
          header="Name"
        />
        <Column
          field="created_at"
          header="Created At"
          sortable
        />
      </template>

      <template #form="{ submitted, handlePhotoUpload, photoPreview, resolveImagePath }">
        <Form
          :form="form"
          :base-units="baseUnits"
        />
      </template>
    </CrudComponent>
  </div>
</template>
<script setup>
import CrudComponent from '@/Components/CrudComponent.vue';
import Form from './Form.vue';
import { useForm } from '@inertiajs/vue3';

const { baseUnits } = defineProps({
    baseUnits: Object
});

console.log('baseUnits:', baseUnits)

const form = useForm({
    name: '',
    short_code: '',
    status: '',
    base_unit_id: '',
});

</script>
