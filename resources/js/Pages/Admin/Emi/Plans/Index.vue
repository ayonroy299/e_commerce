<template>
  <div>
    <CrudComponent :form="form">
      <template #columns>
        <Column
          field="name"
          header="Plan Name"
          sortable
        />
        <Column
          field="tenor_months"
          header="Tenor (Months)"
          sortable
        />
        <Column
          field="interest_rate"
          header="Interest Rate (%)"
          sortable
        />
        <Column
          field="interest_type"
          header="Type"
          sortable
        >
          <template #body="{ data }">
            <Badge :severity="data.interest_type === 'flat' ? 'info' : 'warn'">
              {{ data.interest_type.toUpperCase() }}
            </Badge>
          </template>
        </Column>
        <Column
          field="down_payment_percentage"
          header="Min Down Payment (%)"
          sortable
        />
        <Column
          field="is_active"
          header="Status"
        >
          <template #body="{ data }">
            <Badge :severity="data.is_active ? 'success' : 'danger'">
              {{ data.is_active ? "Active" : "Inactive" }}
            </Badge>
          </template>
        </Column>
      </template>

      <template #form="{ submitted, statuses }">
        <div class="flex flex-col gap-4">
          <div class="field">
            <label
              for="name"
              class="font-bold block mb-2"
            >Plan Name</label>
            <InputText
              id="name"
              v-model="form.name"
              required
              autofocus
              :invalid="submitted && !form.name"
              class="w-full"
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="field">
              <label
                for="tenor_months"
                class="font-bold block mb-2"
              >Tenor (Months)</label>
              <InputNumber
                id="tenor_months"
                v-model="form.tenor_months"
                show-buttons
                min="1"
                class="w-full"
              />
            </div>
            <div class="field">
              <label
                for="interest_rate"
                class="font-bold block mb-2"
              >Interest Rate (%)</label>
              <InputNumber
                id="interest_rate"
                v-model="form.interest_rate"
                show-buttons
                min="0"
                mode="decimal"
                :min-fraction-digits="2"
                class="w-full"
              />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="field">
              <label
                for="interest_type"
                class="font-bold block mb-2"
              >Interest Type</label>
              <Select
                v-model="form.interest_type"
                :options="interestTypes"
                option-label="label"
                option-value="value"
                class="w-full"
              />
            </div>
            <div class="field">
              <label
                for="down_payment_percentage"
                class="font-bold block mb-2"
              >Min Down Payment (%)</label>
              <InputNumber
                id="down_payment_percentage"
                v-model="form.down_payment_percentage"
                show-buttons
                min="0"
                max="100"
                mode="decimal"
                class="w-full"
              />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="field">
              <label
                for="late_fee_type"
                class="font-bold block mb-2"
              >Late Fee Type</label>
              <Select
                v-model="form.late_fee_type"
                :options="lateFeeTypes"
                option-label="label"
                option-value="value"
                class="w-full"
              />
            </div>
            <div class="field">
              <label
                for="late_fee_value"
                class="font-bold block mb-2"
              >Late Fee Value</label>
              <InputNumber
                id="late_fee_value"
                v-model="form.late_fee_value"
                show-buttons
                min="0"
                mode="decimal"
                class="w-full"
              />
            </div>
          </div>

          <div class="field">
            <label
              for="is_active"
              class="font-bold block mb-2"
            >Status</label>
            <Select
              v-model="form.is_active"
              :options="statuses"
              option-label="label"
              option-value="value"
              class="w-full"
            />
          </div>
        </div>
      </template>
    </CrudComponent>
  </div>
</template>

<script setup>
import CrudComponent from "@/Components/CrudComponent.vue";
import { useForm } from "@inertiajs/vue3";

const props = defineProps(['items', 'config']);

const form = useForm({
    name: "",
    tenor_months: 6,
    interest_rate: 0,
    interest_type: "flat",
    down_payment_percentage: 0,
    late_fee_type: "fixed",
    late_fee_value: 0,
    is_active: 1,
});

const interestTypes = [
    { label: 'Flat', value: 'flat' },
    { label: 'Declining Balance', value: 'declining' }
];

const lateFeeTypes = [
    { label: 'Fixed Amount', value: 'fixed' },
    { label: 'Percentage of Due', value: 'percentage' }
];
</script>
