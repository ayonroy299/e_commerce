<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
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
    router.get(route('reports.sales'), {
        ...filters.value,
        start_date: filters.value.start_date ? filters.value.start_date.toISOString().split('T')[0] : null,
        end_date: filters.value.end_date ? filters.value.end_date.toISOString().split('T')[0] : null,
    }, { preserveState: true, replace: true });
};

const resetFilters = () => {
    filters.value = { search: '', start_date: null, end_date: null };
    applyFilters();
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-BD', { style: 'currency', currency: 'BDT' }).format(value);
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
    <Head title="Sales Report" />

    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Sales Report
                </h2>
                <div class="flex gap-2">
                    <Button icon="pi pi-file-excel" label="Export Excel" severity="success" class="p-button-sm" />
                    <Button icon="pi pi-file-pdf" label="Export PDF" severity="danger" class="p-button-sm" />
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Card class="mb-6">
                    <template #content>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium">Search</label>
                                <InputText v-model="filters.search" placeholder="Invoice # or Customer" @keyup.enter="applyFilters" />
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
                           @page="router.get(route('reports.sales'), { ...filters, page: $event.page + 1 })"
                           responsiveLayout="scroll" class="p-datatable-sm">
                            <Column field="invoice_number" header="Invoice #" sortable></Column>
                            <Column field="sold_at" header="Date">
                                <template #body="slotProps">
                                    {{ formatDate(slotProps.data.sold_at) }}
                                </template>
                            </Column>
                            <Column field="customer.name" header="Customer">
                                <template #body="slotProps">
                                    {{ slotProps.data.customer?.name || 'Walk-in' }}
                                </template>
                            </Column>
                            <Column field="branch.name" header="Branch"></Column>
                            <Column field="total_amount" header="Total">
                                <template #body="slotProps">
                                    {{ formatCurrency(slotProps.data.total_amount) }}
                                </template>
                            </Column>
                            <Column field="paid_amount" header="Paid">
                                <template #body="slotProps">
                                    <span :class="{'text-green-600': slotProps.data.payment_status === 'paid', 'text-yellow-600': slotProps.data.payment_status !== 'paid'}">
                                        {{ formatCurrency(slotProps.data.paid_amount) }}
                                    </span>
                                </template>
                            </Column>
                            <Column field="payment_status" header="Status">
                                <template #body="slotProps">
                                    <span class="capitalize px-2 py-1 rounded-full text-xs font-semibold" 
                                          :class="slotProps.data.payment_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'">
                                        {{ slotProps.data.payment_status }}
                                    </span>
                                </template>
                            </Column>
                        </DataTable>
                    </template>
                </Card>
            </div>
        </div>
    </AdminLayout>
</template>
