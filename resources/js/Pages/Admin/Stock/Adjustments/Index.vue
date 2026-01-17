<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, watch } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import { debounce } from 'lodash';

const props = defineProps({
    adjustments: Object,
});

const search = ref('');

watch(search, debounce((value) => {
    router.get(route('adjustments.index'), { search: value }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}, 300));

const getStatusSeverity = (status) => {
    switch (status) {
        case 'approved': return 'success';
        case 'draft': return 'warning';
        default: return 'info';
    }
};
</script>

<template>
    <Head title="Stock Adjustments" />

    <AppLayout>
        <div class="card">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Stock Adjustments</h2>
                <Link :href="route('adjustments.create')">
                    <Button label="New Adjustment" icon="pi pi-plus" />
                </Link>
            </div>

            <DataTable :value="adjustments.data" :paginator="true" :rows="15" :total-records="adjustments.total" lazy>
                <Column field="date" header="Date">
                    <template #body="slotProps">
                        {{ new Date(slotProps.data.date).toLocaleDateString() }}
                    </template>
                </Column>
                <Column field="id" header="Ref #">
                    <template #body="slotProps">
                        <span class="text-xs uppercase">{{ slotProps.data.id.substring(0, 8) }}</span>
                    </template>
                </Column>
                <Column field="branch.name" header="Branch"></Column> <!-- Assumes branch relation is loaded -->
                <Column field="reason" header="Reason">
                     <template #body="slotProps">
                        <span class="capitalize">{{ slotProps.data.reason.replace('_', ' ') }}</span>
                    </template>
                </Column>
                <Column field="status" header="Status">
                    <template #body="slotProps">
                        <Tag :value="slotProps.data.status" :severity="getStatusSeverity(slotProps.data.status)" class="uppercase" />
                    </template>
                </Column>
                <Column header="Actions">
                    <template #body="slotProps">
                        <Link :href="route('adjustments.show', slotProps.data.id)">
                            <Button icon="pi pi-eye" class="p-button-text p-button-sm" />
                        </Link>
                    </template>
                </Column>
            </DataTable>
        </div>
    </AppLayout>
</template>
