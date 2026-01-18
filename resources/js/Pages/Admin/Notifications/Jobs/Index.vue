<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Card from 'primevue/card';
import Tag from 'primevue/tag';

const props = defineProps({
    jobs: Object,
});

const getStatusSeverity = (status) => {
    switch (status) {
        case 'sent': return 'success';
        case 'failed': return 'danger';
        case 'pending': return 'warn';
        default: return 'info';
    }
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleString();
};
</script>

<template>
    <Head title="Notification History" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Notification History
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Card>
                    <template #content>
                        <DataTable :value="jobs.data" paginator :rows="20" class="p-datatable-sm">
                            <Column field="created_at" header="Created At">
                                <template #body="slotProps">
                                    {{ formatDate(slotProps.data.created_at) }}
                                </template>
                            </Column>
                            <Column field="template.name" header="Template"></Column>
                            <Column header="Recipient">
                                <template #body="slotProps">
                                    <div class="flex flex-col">
                                        <span class="font-bold">{{ slotProps.data.recipient?.name || 'Unknown' }}</span>
                                        <span class="text-xs text-gray-500">{{ slotProps.data.recipient?.email || slotProps.data.recipient?.phone }}</span>
                                    </div>
                                </template>
                            </Column>
                            <Column field="status" header="Status">
                                <template #body="slotProps">
                                    <Tag :severity="getStatusSeverity(slotProps.data.status)" 
                                         :value="slotProps.data.status" class="capitalize" />
                                </template>
                            </Column>
                            <Column field="sent_at" header="Sent At">
                                <template #body="slotProps">
                                    {{ formatDate(slotProps.data.sent_at) }}
                                </template>
                            </Column>
                        </DataTable>
                    </template>
                </Card>
            </div>
        </div>
    </AdminLayout>
</template>
