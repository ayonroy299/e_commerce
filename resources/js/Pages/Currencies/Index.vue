<template>
  <div>
    <CrudComponent :form="form">
      <template #columns>
        <Column
          field="title"
          header="Title"
        />
        <Column
          field="short_code"
          header="Short Code"
        />
        <Column
          field="date"
          header="Date"
        />
        <Column
          field="symbol"
          header="Symbol"
        />
        <Column
          field="exchange_rate"
          header="Exchange Rate"
        />
        <Column
          field="created_at"
          header="Created At"
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
    title: '',
    short_code: '',
    date: '',
    symbol: '',
    exchange_rate: '',
    status: '',
});

</script>
