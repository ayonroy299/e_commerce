<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Calendar from 'primevue/calendar';
import Dropdown from 'primevue/dropdown';
import Textarea from 'primevue/textarea';
import Dialog from 'primevue/dialog';
import { ref, watch } from 'vue';

const props = defineProps({
    expenses: Object,
    categories: Array,
    filters: Object,
});

const showModal = ref(false);
const form = useForm({
    title: '',
    amount: null,
    date: new Date(),
    expense_category_id: null,
    details: '',
});

const dateFilter = ref(props.filters.start_date ? [new Date(props.filters.start_date), new Date(props.filters.end_date)] : null);

watch(dateFilter, (val) => {
    if (val && val[1]) {
        router.get(route('expenses.index'), {
            start_date: val[0].toISOString().split('T')[0],
            end_date: val[1].toISOString().split('T')[0]
        }, { preserveState: true, replace: true });
    }
});

const submit = () => {
    form.post(route('expenses.store'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        }
    });
};
</script>

<template>
    <Head title="Expenses" />

    <AppLayout>
        <div class="card">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Expenses</h2>
                <div class="flex gap-2">
                     <Calendar v-model="dateFilter" selectionMode="range" placeholder="Filter by Date" :showIcon="true" />
                     <Button label="New Expense" icon="pi pi-plus" @click="showModal = true" />
                </div>
            </div>
            
            <DataTable :value="expenses.data" :paginator="true" :rows="15" :total-records="expenses.total" lazy>
                <Column field="date" header="Date">
                     <template #body="slotProps">
                        {{ new Date(slotProps.data.date).toLocaleDateString() }}
                    </template>
                </Column>
                <Column field="title" header="Title"></Column>
                <Column field="category.name" header="Category"></Column>
                <Column field="amount" header="Amount">
                    <template #body="slotProps">
                        {{ Number(slotProps.data.amount).toFixed(2) }}
                    </template>
                </Column>
                <Column field="user.name" header="User"></Column>
            </DataTable>
        </div>

        <Dialog v-model:visible="showModal" header="Record Expense" :modal="true" class="w-full max-w-md">
             <form @submit.prevent="submit" class="space-y-4">
                 <div class="field">
                    <label class="block mb-2">Title</label>
                    <InputText v-model="form.title" class="w-full" />
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                     <div class="field">
                        <label class="block mb-2">Date</label>
                        <Calendar v-model="form.date" dateFormat="yy-mm-dd" class="w-full" />
                    </div>
                     <div class="field">
                        <label class="block mb-2">Amount</label>
                        <InputNumber v-model="form.amount" mode="currency" currency="USD" locale="en-US" class="w-full" />
                    </div>
                </div>

                <div class="field">
                    <label class="block mb-2">Category</label>
                    <Dropdown v-model="form.expense_category_id" :options="categories" optionLabel="name" optionValue="id" placeholder="Select" class="w-full" />
                </div>
                
                <div class="field">
                    <label class="block mb-2">Details</label>
                    <Textarea v-model="form.details" rows="2" class="w-full" />
                </div>

                 <div class="flex justify-end pt-2">
                    <Button type="submit" label="Save" :loading="form.processing" />
                </div>
             </form>
        </Dialog>
    </AppLayout>
</template>
