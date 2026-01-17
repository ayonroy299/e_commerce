<template>
  <div class="p-6 flex flex-col gap-6">
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-2xl font-bold">General Journal</h1>
    </div>

    <div class="card bg-white rounded-xl shadow-sm p-4">
      <DataTable :value="journals.data" class="p-datatable-sm">
        <Column field="date" header="Date">
           <template #body="{ data }">
              {{ $formatDate(data.date) }}
           </template>
        </Column>
        <Column field="journal_no" header="Journal #" />
        <Column field="reference_type" header="Ref Type" />
        <Column field="notes" header="Description" />
        <Column field="creator.name" header="Posted By" />
        <Column header="Total" class="text-right">
           <template #body="{ data }">
              <div class="font-bold">
                 {{ $formatCurrency(data.lines.reduce((acc, l) => acc + parseFloat(l.debit), 0)) }}
              </div>
           </template>
        </Column>
        <Column header="Actions">
           <template #body="{ data }">
              <Button icon="pi pi-search-plus" text rounded @click="selectedJournal = data" />
           </template>
        </Column>
      </DataTable>
      <Paginator :rows="journals.per_page" :totalRecords="journals.total" @page="onPage" />
    </div>

    <!-- Details Dialog -->
    <Dialog v-model:visible="!!selectedJournal" header="Journal Entry Details" modal class="w-[700px]">
       <div v-if="selectedJournal" class="flex flex-col gap-4">
          <div class="grid grid-cols-2 text-sm text-gray-500 mb-4 p-3 bg-gray-50 rounded">
             <div>Journal #: <span class="font-bold text-black">{{ selectedJournal.journal_no }}</span></div>
             <div>Date: <span class="font-bold text-black">{{ $formatDate(selectedJournal.date) }}</span></div>
             <div>Ref: <span class="font-bold text-black">{{ selectedJournal.reference_type }} ({{ selectedJournal.reference_id }})</span></div>
             <div>Posted By: <span class="font-bold text-black">{{ selectedJournal.creator.name }}</span></div>
          </div>
          
          <DataTable :value="selectedJournal.lines" size="small">
             <Column field="account.name" header="Account" />
             <Column field="debit" header="Debit" class="text-right">
                <template #body="{ data }">
                   {{ data.debit > 0 ? $formatCurrency(data.debit) : '-' }}
                </template>
             </Column>
             <Column field="credit" header="Credit" class="text-right">
                <template #body="{ data }">
                   {{ data.credit > 0 ? $formatCurrency(data.credit) : '-' }}
                </template>
             </Column>
          </DataTable>
          
          <div class="p-2 italic text-gray-500 text-sm">Notes: {{ selectedJournal.notes ?? 'None' }}</div>
       </div>
    </Dialog>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps(['journals']);
const selectedJournal = ref(null);

const onPage = (event) => {
    router.get(route('journals.index'), { page: event.page + 1 }, { preserveState: true });
};
</script>
