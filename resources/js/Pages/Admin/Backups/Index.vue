<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Card from 'primevue/card';

const props = defineProps({
    backups: Object,
});

const createBackup = () => {
    router.post(route('backups.store'), {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Success message handled by flash
        }
    });
};

const formatSize = (bytes) => {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};
</script>

<template>
    <Head title="System Backups" />

    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    System Backups
                </h2>
                <Button label="Create New Backup" icon="pi pi-plus" @click="createBackup" />
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Card>
                    <template #content>
                        <DataTable :value="backups.data" paginator :rows="15" class="p-datatable-sm">
                            <Column field="created_at" header="Date">
                                <template #body="slotProps">
                                    {{ new Date(slotProps.data.created_at).toLocaleString() }}
                                </template>
                            </Column>
                            <Column field="name" header="Backup Name"></Column>
                            <Column field="size" header="Size">
                                <template #body="slotProps">
                                    {{ formatSize(slotProps.data.size) }}
                                </template>
                            </Column>
                            <Column field="status" header="Status">
                                <template #body="slotProps">
                                    <span class="capitalize px-2 py-1 rounded-full text-xs font-semibold" 
                                          :class="slotProps.data.status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                                        {{ slotProps.data.status }}
                                    </span>
                                </template>
                            </Column>
                            <Column header="Actions">
                                <template #body="slotProps">
                                    <div class="flex gap-2">
                                        <Button icon="pi pi-download" severity="secondary" v-if="slotProps.data.status === 'completed'" />
                                        <Button icon="pi pi-trash" severity="danger" />
                                    </div>
                                </template>
                            </Column>
                        </DataTable>
                    </template>
                </Card>
            </div>
        </div>
    </AdminLayout>
</template>
