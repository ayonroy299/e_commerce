<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Tag from 'primevue/tag';

const props = defineProps({
    templates: Object,
});

const getChannelIcon = (channel) => {
    switch (channel) {
        case 'email': return 'pi pi-envelope';
        case 'sms': return 'pi pi-mobile';
        case 'whatsapp': return 'pi pi-whatsapp';
        default: return 'pi pi-bell';
    }
};
</script>

<template>
    <Head title="Notification Templates" />

    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Notification Templates
                </h2>
                <Link :href="route('notification-templates.create')">
                    <Button label="New Template" icon="pi pi-plus" />
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Card>
                    <template #content>
                        <DataTable :value="templates.data" paginator :rows="15" class="p-datatable-sm">
                            <Column field="code" header="Code" class="font-mono font-bold"></Column>
                            <Column field="name" header="Name"></Column>
                            <Column field="channels" header="Channels">
                                <template #body="slotProps">
                                    <div class="flex gap-2">
                                        <i v-for="channel in slotProps.data.channels" :key="channel" 
                                           :class="getChannelIcon(channel)" class="text-gray-600" :title="channel"></i>
                                    </div>
                                </template>
                            </Column>
                            <Column field="is_active" header="Status">
                                <template #body="slotProps">
                                    <Tag :severity="slotProps.data.is_active ? 'success' : 'danger'" 
                                         :value="slotProps.data.is_active ? 'Active' : 'Inactive'" />
                                </template>
                            </Column>
                            <Column header="Actions">
                                <template #body="slotProps">
                                    <Link :href="route('notification-templates.edit', slotProps.data.id)">
                                        <Button icon="pi pi-pencil" severity="secondary" rounded text />
                                    </Link>
                                </template>
                            </Column>
                        </DataTable>
                    </template>
                </Card>
            </div>
        </div>
    </AdminLayout>
</template>
