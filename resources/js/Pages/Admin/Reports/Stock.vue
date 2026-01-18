<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import Calendar from 'primevue/calendar';
import Button from 'primevue/button';
import Card from 'primevue/card';

const props = defineProps({
    reports: Object,
    filters: Object,
});

const filters = ref({
    search: props.filters.search || '',
    start_date: props.filters.start_date ? new Date(props.filters.start_date) : null,
    end_date: props.filters.end_date ? new Date(props.filters.end_date) : null,
});

const applyFilters = () => {
    router.get(route('reports.stock'), {
        ...filters.value,
        start_date: filters.value.start_date ? filters.value.start_date.toISOString().split('T')[0] : null,
        end_date: filters.value.end_date ? filters.value.end_date.toISOString().split('T')[0] : null,
    }, { preserveState: true, replace: true });
};

const resetFilters = () => {
    filters.value = { search: '', start_date: null, end_date: null };
    applyFilters();
};

const formatDate = (value) => {
    return new Date(value).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Stock Movement Report" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Stock Movement Report
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Card class="mb-6">
                    <template #content>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium">Search Product</label>
                                <InputText v-model="filters.search" placeholder="Product Name or SKU" @keyup.enter="applyFilters" />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium">Start Date</label>
                                <Calendar v-model="filters.start_date" dateFormat="yy-mm-dd" showIcon />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium">End Date</label>
                                <Calendar v-model="filters.end_date" dateFormat="yy-mm-dd" showIcon />
                            </div>
                            <div class="flex gap-2">
                                <Button icon="pi pi-filter" label="Filter" @click="applyFilters" />
                                <Button icon="pi pi-refresh" severity="secondary" @click="resetFilters" />
                            </div>
                        </div>
                    </template>
                </Card>

                <Card>
                    <template #content>
                        <DataTable :value="reports.data" paginator :rows="15" :totalRecords="reports.total" lazy 
                           @page="router.get(route('reports.stock'), { ...filters, page: $event.page + 1 })"
                           responsiveLayout="scroll" class="p-datatable-sm">
                            <Column field="occurred_at" header="Date">
                                <template #body="slotProps">
                                    {{ formatDate(slotProps.data.occurred_at) }}
                                </template>
                            </Column>
                            <Column field="product_variant.product.name" header="Product"></Column>
                            <Column field="branch.name" header="Branch"></Column>
                            <Column field="qty_change" header="Qty Change">
                                <template #body="slotProps">
                                    <span :class="slotProps.data.qty_change > 0 ? 'text-green-600 font-bold' : 'text-red-600 font-bold'">
                                        {{ slotProps.data.qty_change > 0 ? '+' : '' }}{{ slotProps.data.qty_change }}
                                    </span>
                                </template>
                            </Column>
                            <Column field="ref_type" header="Reference Type"></Column>
                            <Column field="remarks" header="Remarks"></Column>
                        </DataTable>
                    </template>
                </Card>
            </div>
        </div>
    </AdminLayout>
</template>
