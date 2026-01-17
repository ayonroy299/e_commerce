<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import InputNumber from 'primevue/inputnumber';
import Checkbox from 'primevue/checkbox';
import { ref } from 'vue';

const props = defineProps({
    settings: Object, // Key-Value pair
    taxes: Array,
    currencies: Array,
});

// --- General Settings ---
const generalForm = useForm({
    settings: {
        app_name: props.settings.app_name || '',
        app_phone: props.settings.app_phone || '',
        app_address: props.settings.app_address || '',
        footer_text: props.settings.footer_text || '',
    }
});

const saveGeneral = () => {
    generalForm.post(route('settings.update'));
};

// --- Taxes ---
const showTaxModal = ref(false);
const taxForm = useForm({ id: null, name: '', rate_type: 'percent', rate_value: 0 });
const editTax = (tax) => {
    taxForm.id = tax.id;
    taxForm.name = tax.name;
    taxForm.rate_type = tax.rate_type;
    taxForm.rate_value = parseFloat(tax.rate_value);
    showTaxModal.value = true;
};
const saveTax = () => {
    if (taxForm.id) {
        taxForm.put(route('taxes.update', taxForm.id), { onSuccess: () => showTaxModal.value = false });
    } else {
        taxForm.post(route('taxes.store'), { onSuccess: () => showTaxModal.value = false });
    }
};
const deleteTax = (id) => {
    if (confirm('Delete tax?')) useForm({}).delete(route('taxes.destroy', id));
};

// --- Currencies ---
const showCurrencyModal = ref(false);
const currencyForm = useForm({ id: null, name: '', code: '', symbol: '', exchange_rate: 1, is_default: false });
const editCurrency = (cur) => {
    currencyForm.id = cur.id;
    currencyForm.name = cur.name;
    currencyForm.code = cur.code;
    currencyForm.symbol = cur.symbol;
    currencyForm.exchange_rate = parseFloat(cur.exchange_rate);
    currencyForm.is_default = !!cur.is_default;
    showCurrencyModal.value = true;
};
const saveCurrency = () => {
     if (currencyForm.id) {
        currencyForm.put(route('currencies.update', currencyForm.id), { onSuccess: () => showCurrencyModal.value = false });
    } else {
        currencyForm.post(route('currencies.store'), { onSuccess: () => showCurrencyModal.value = false });
    }
};
const deleteCurrency = (id) => {
     if (confirm('Delete currency?')) useForm({}).delete(route('currencies.destroy', id));
};
</script>

<template>
    <Head title="Settings" />

    <AppLayout>
        <div class="card">
            <h2 class="text-xl font-semibold mb-4">Configurations</h2>
            
            <TabView>
                <!-- General Settings -->
                <TabPanel header="General">
                    <form @submit.prevent="saveGeneral" class="max-w-xl space-y-4">
                        <div class="field">
                            <label class="block mb-2 font-bold">App Name</label>
                            <InputText v-model="generalForm.settings.app_name" class="w-full" />
                        </div>
                        <div class="field">
                            <label class="block mb-2 font-bold">Phone</label>
                            <InputText v-model="generalForm.settings.app_phone" class="w-full" />
                        </div>
                         <div class="field">
                            <label class="block mb-2 font-bold">Address</label>
                            <Textarea v-model="generalForm.settings.app_address" rows="3" class="w-full" />
                        </div>
                         <div class="field">
                            <label class="block mb-2 font-bold">Footer Text</label>
                            <InputText v-model="generalForm.settings.footer_text" class="w-full" />
                        </div>
                        <Button type="submit" label="Save Changes" :loading="generalForm.processing" />
                    </form>
                </TabPanel>

                <!-- Taxes -->
                <TabPanel header="Taxes">
                    <div class="flex justify-end mb-2">
                        <Button label="Add Tax" icon="pi pi-plus" size="small" @click="() => { taxForm.reset(); taxForm.id=null; showTaxModal = true; }" />
                    </div>
                    <DataTable :value="taxes">
                        <Column field="name" header="Name"></Column>
                        <Column field="rate_value" header="Rate">
                            <template #body="slotProps">
                                {{ slotProps.data.rate_value }} {{ slotProps.data.rate_type === 'percent' ? '%' : '' }}
                            </template>
                        </Column>
                         <Column header="Actions">
                            <template #body="slotProps">
                                <Button icon="pi pi-pencil" class="p-button-text p-button-sm" @click="editTax(slotProps.data)" />
                                <Button icon="pi pi-trash" class="p-button-text p-button-danger p-button-sm" @click="deleteTax(slotProps.data.id)" />
                            </template>
                        </Column>
                    </DataTable>
                </TabPanel>

                <!-- Currencies -->
                <TabPanel header="Currencies">
                     <div class="flex justify-end mb-2">
                        <Button label="Add Currency" icon="pi pi-plus" size="small" @click="() => { currencyForm.reset(); currencyForm.id=null; showCurrencyModal = true; }" />
                    </div>
                    <DataTable :value="currencies">
                         <Column field="code" header="Code"></Column>
                         <Column field="name" header="Name"></Column>
                         <Column field="symbol" header="Symbol"></Column>
                         <Column field="exchange_rate" header="Rate"></Column>
                         <Column header="Default">
                             <template #body="slotProps">
                                 <i v-if="slotProps.data.is_default" class="pi pi-check text-green-600"></i>
                             </template>
                         </Column>
                         <Column header="Actions">
                            <template #body="slotProps">
                                <Button icon="pi pi-pencil" class="p-button-text p-button-sm" @click="editCurrency(slotProps.data)" />
                                <Button icon="pi pi-trash" class="p-button-text p-button-danger p-button-sm" @click="deleteCurrency(slotProps.data.id)" />
                            </template>
                        </Column>
                    </DataTable>
                </TabPanel>
            </TabView>
        </div>

        <!-- Tax Modal -->
        <Dialog v-model:visible="showTaxModal" header="Tax Rule" :modal="true" class="w-full max-w-sm">
            <div class="space-y-4">
                <div class="field">
                    <label class="block mb-2">Name</label>
                    <InputText v-model="taxForm.name" class="w-full" />
                </div>
                <div class="field">
                    <label class="block mb-2">Type</label>
                    <Dropdown v-model="taxForm.rate_type" :options="['percent', 'flat']" class="w-full" />
                </div>
                 <div class="field">
                    <label class="block mb-2">Rate</label>
                    <InputNumber v-model="taxForm.rate_value" :min="0" class="w-full" />
                </div>
                <Button label="Save" class="w-full" @click="saveTax" :loading="taxForm.processing" />
            </div>
        </Dialog>

        <!-- Currency Modal -->
        <Dialog v-model:visible="showCurrencyModal" header="Currency" :modal="true" class="w-full max-w-sm">
             <div class="space-y-4">
                <div class="field">
                    <label class="block mb-2">Name</label>
                    <InputText v-model="currencyForm.name" class="w-full" />
                </div>
                 <div class="grid grid-cols-2 gap-4">
                    <div class="field">
                        <label class="block mb-2">Code (e.g. USD)</label>
                        <InputText v-model="currencyForm.code" class="w-full" maxlength="3" />
                    </div>
                     <div class="field">
                        <label class="block mb-2">Symbol (e.g. $)</label>
                        <InputText v-model="currencyForm.symbol" class="w-full" />
                    </div>
                </div>
                 <div class="field">
                    <label class="block mb-2">Exchange Rate</label>
                    <InputNumber v-model="currencyForm.exchange_rate" :min="0" :maxFractionDigits="4" class="w-full" />
                </div>
                <div class="field flex items-center gap-2">
                    <Checkbox v-model="currencyForm.is_default" :binary="true" inputId="is_def" />
                    <label for="is_def">Default Currency</label>
                </div>
                <Button label="Save" class="w-full" @click="saveCurrency" :loading="currencyForm.processing" />
            </div>
        </Dialog>
    </AppLayout>
</template>
