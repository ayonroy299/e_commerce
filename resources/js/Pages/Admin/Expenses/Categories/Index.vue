<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import { ref } from 'vue';

defineProps({
    categories: Array,
});

const showModal = ref(false);
const form = useForm({
    name: '',
    status: true,
});

const submit = () => {
    form.post(route('expense-categories.store'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        }
    });
};
</script>

<template>
    <Head title="Expense Categories" />

    <AppLayout>
        <div class="card max-w-2xl mx-auto">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Expense Categories</h2>
                <Button label="New Category" icon="pi pi-plus" @click="showModal = true" />
            </div>
            
            <DataTable :value="categories">
                <Column field="name" header="Name"></Column>
                <Column field="status" header="Status">
                     <template #body="slotProps">
                        <span :class="slotProps.data.status ? 'text-green-600' : 'text-red-600'">
                            {{ slotProps.data.status ? 'Active' : 'Inactive' }}
                        </span>
                    </template>
                </Column>
            </DataTable>
        </div>

        <Dialog v-model:visible="showModal" header="New Category" :modal="true" class="w-full max-w-sm">
             <form @submit.prevent="submit" class="space-y-4">
                 <div class="field">
                    <label class="block mb-2">Name</label>
                    <InputText v-model="form.name" class="w-full" :class="{'p-invalid': form.errors.name}" />
                    <small class="p-error" v-if="form.errors.name">{{ form.errors.name }}</small>
                </div>
                 <div class="flex justify-end pt-2">
                    <Button type="submit" label="Save" :loading="form.processing" />
                </div>
             </form>
        </Dialog>
    </AppLayout>
</template>
