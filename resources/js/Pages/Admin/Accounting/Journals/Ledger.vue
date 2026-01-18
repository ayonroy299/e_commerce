<script setup>
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps(['account', 'lines']);

const onPage = (event) => {
    router.get(route('accounts.ledger', props.account.id), { page: event.page + 1 }, { preserveState: true });
};
</script>

<template>
  <Head :title="`Ledger: ${account.name}`" />

  <AuthenticatedLayout>
    <div class="p-6 flex flex-col gap-6">
      <div class="flex justify-between items-center mb-4">
        <div>
           <h1 class="text-2xl font-bold">Ledger: {{ account.name }}</h1>
           <p class="text-gray-500">Account Code: {{ account.code }} | Type: {{ account.type.toUpperCase() }}</p>
        </div>
        <div class="text-right">
           <div class="text-sm text-gray-500 uppercase">Current Balance</div>
           <div class="text-2xl font-bold" :class="account.balance < 0 ? 'text-red-600' : 'text-green-600'">
              {{ $formatCurrency(account.balance) }}
           </div>
        </div>
      </div>

      <div class="card bg-white rounded-xl shadow-sm p-4">
        <DataTable :value="lines.data" class="p-datatable-sm">
          <Column field="journal.date" header="Date">
             <template #body="{ data }">
                {{ $formatDate(data.journal.date) }}
             </template>
          </Column>
          <Column field="journal.journal_no" header="Journal #" />
          <Column field="journal.notes" header="Description" />
          <Column field="debit" header="Debit" class="text-right">
             <template #body="{ data }">
                <span v-if="data.debit > 0" class="text-blue-600">{{ $formatCurrency(data.debit) }}</span>
                <span v-else>-</span>
             </template>
          </Column>
          <Column field="credit" header="Credit" class="text-right">
             <template #body="{ data }">
                <span v-if="data.credit > 0" class="text-orange-600">{{ $formatCurrency(data.credit) }}</span>
                <span v-else>-</span>
             </template>
          </Column>
        </DataTable>
        <Paginator :rows="lines.per_page" :totalRecords="lines.total" @page="onPage" />
      </div>
    </div>
  </AuthenticatedLayout>
</template>
