<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, watch } from 'vue';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import { debounce } from 'lodash';

const props = defineProps({
    customers: Object,
    filters: Object,
});

const search = ref(props.filters.search);

watch(search, debounce((value) => {
    router.get(route('customers.index'), { search: value }, { preserveState: true, replace: true });
}, 300));
</script>

<template>
    <Head title="Customers" />

    <AppLayout>
        <div class="card">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Customers</h2>
                <Link :href="route('customers.create')">
                    <Button label="New Customer" icon="pi pi-plus" />
                </Link>
            </div>
            
            <div class="mb-4">
                <InputText v-model="search" placeholder="Search customers..." class="w-full md:w-64" />
            </div>

            <DataTable :value="customers.data" :paginator="true" :rows="15" :total-records="customers.total" lazy>
                <Column field="name" header="Name"></Column>
                <Column field="phone" header="Phone"></Column>
                <Column field="email" header="Email"></Column>
                <Column field="opening_balance" header="Balance">
                    <template #body="slotProps">
                        {{ Number(slotProps.data.opening_balance).toFixed(2) }}
                    </template>
                </Column>
                 <Column header="Actions">
                    <template #body="slotProps">
                        <Link :href="route('customers.show', slotProps.data.id)">
                            <Button icon="pi pi-eye" class="p-button-text p-button-sm" />
                        </Link>
                    </template>
                </Column>
            </DataTable>
        </div>
    </AppLayout>
</template>
