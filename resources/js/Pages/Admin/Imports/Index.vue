<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Card from 'primevue/card';
import FileUpload from 'primevue/fileupload';
import Select from 'primevue/select';
import Message from 'primevue/message';

const props = defineProps({
    imports: Object,
});

const form = useForm({
    type: 'products',
    file: null,
});

const types = [
    { label: 'Products', value: 'products' },
    { label: 'Customers', value: 'customers' },
    { label: 'Suppliers', value: 'suppliers' },
];

const onUpload = (event) => {
    form.file = event.files[0];
};

const submit = () => {
    form.post(route('imports.store'), {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
        },
    });
};

const getStatusSeverity = (status) => {
    switch (status) {
        case 'completed': return 'success';
        case 'processing': return 'info';
        case 'failed': return 'danger';
        default: return 'warn';
    }
};
</script>

<template>
    <Head title="Data Imports" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Data Imports
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <Card>
                    <template #title>New Import</template>
                    <template #content>
                        <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium">Import Type</label>
                                <Select v-model="form.type" :options="types" optionLabel="label" optionValue="value" placeholder="Select Type" />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium">CSV File</label>
                                <FileUpload mode="basic" name="file" accept=".csv" :auto="true" @select="onUpload" class="w-full" />
                            </div>
                            <div>
                                <Button type="submit" label="Start Import" icon="pi pi-upload" :loading="form.processing" />
                            </div>
                        </form>
                        <div v-if="form.errors.file" class="mt-2">
                             <Message severity="error">{{ form.errors.file }}</Message>
                        </div>
                    </template>
                </Card>

                <Card>
                    <template #title>Import History</template>
                    <template #content>
                        <DataTable :value="imports.data" paginator :rows="15" class="p-datatable-sm">
                            <Column field="created_at" header="Date">
                                <template #body="slotProps">
                                    {{ new Date(slotProps.data.created_at).toLocaleString() }}
                                </template>
                            </Column>
                            <Column field="type" header="Type" class="capitalize"></Column>
                            <Column field="status" header="Status">
                                <template #body="slotProps">
                                    <span class="capitalize px-2 py-1 rounded-full text-xs font-semibold" 
                                          :class="`bg-${getStatusSeverity(slotProps.data.status)}-100 text-${getStatusSeverity(slotProps.data.status)}-700`">
                                        {{ slotProps.data.status }}
                                    </span>
                                </template>
                            </Column>
                            <Column field="processed_rows" header="Progress">
                                <template #body="slotProps">
                                    {{ slotProps.data.processed_rows }} / {{ slotProps.data.total_rows }}
                                </template>
                            </Column>
                            <Column field="user.name" header="Imported By"></Column>
                        </DataTable>
                    </template>
                </Card>
            </div>
        </div>
    </AdminLayout>
</template>
