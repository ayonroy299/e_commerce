<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Dropdown from 'primevue/dropdown';
import Textarea from 'primevue/textarea';
import Calendar from 'primevue/calendar';
import AutoComplete from 'primevue/autocomplete';
import axios from 'axios';

const props = defineProps({
    suppliers: Array,
});

const form = useForm({
    supplier_id: null,
    date: new Date(),
    expected_date: null,
    notes: '',
    lines: [],
});

const productSearch = ref('');
const productSuggestions = ref([]);

const searchProducts = async (event) => {
    if (!event.query.trim()) return;
    try {
        const response = await axios.get(route('products.index'), {
            params: { search: event.query, per_page: 10 },
            headers: { 'Accept': 'application/json' }
        });
        productSuggestions.value = response.data.map(p => ({
            ...p,
           display_label: p.name + (p.sku ? ` (${p.sku})` : '')
        }));
    } catch (e) {
        console.error("Search failed", e);
    }
};

const addLine = (product) => {
    form.lines.push({
        product_id: product.id,
        product_name: product.name,
        type: product.type,
        variations: product.variations || [],
        variation_id: null,
        quantity_ordered: 1,
        unit_cost: 0, 
    });
    productSearch.value = '';
};

const removeLine = (index) => form.lines.splice(index, 1);

const submit = () => form.post(route('purchase-orders.store'));
</script>

<template>
    <Head title="Create Purchase Order" />

    <AppLayout>
        <div class="card max-w-5xl mx-auto">
            <h2 class="text-xl font-semibold mb-6">Create Purchase Order</h2>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                     <div class="field">
                        <label class="block font-bold mb-2">Supplier</label>
                        <Dropdown v-model="form.supplier_id" :options="suppliers" optionLabel="name" optionValue="id" placeholder="Select Supplier" class="w-full" :class="{'p-invalid': form.errors.supplier_id}" />
                         <small class="p-error" v-if="form.errors.supplier_id">{{ form.errors.supplier_id }}</small>
                    </div>
                     <div class="field">
                        <label class="block font-bold mb-2">Order Date</label>
                        <Calendar v-model="form.date" showIcon class="w-full" :class="{'p-invalid': form.errors.date}" />
                    </div>
                    <div class="field">
                        <label class="block font-bold mb-2">Expected Date</label>
                        <Calendar v-model="form.expected_date" showIcon class="w-full" />
                    </div>
                </div>

                <div class="field">
                    <label class="block font-bold mb-2">Add Product</label>
                    <AutoComplete
                        v-model="productSearch"
                        :suggestions="productSuggestions"
                        @complete="searchProducts"
                        field="display_label"
                        @item-select="(e) => addLine(e.value)"
                        placeholder="Search product..."
                        class="w-full"
                    />
                </div>

                 <div v-if="form.lines.length" class="border rounded p-2">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="border-b">
                                <th class="p-2">Product</th>
                                <th class="p-2">Variant</th>
                                <th class="p-2 w-32">Qty</th>
                                <th class="p-2 w-32">Unit Cost</th>
                                <th class="p-2 w-32">Subtotal</th>
                                <th class="p-2 w-10"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(line, index) in form.lines" :key="index" class="border-b">
                                <td class="p-2">{{ line.product_name }}</td>
                                <td class="p-2">
                                     <Dropdown
                                        v-if="line.type === 'variable' && line.variations.length"
                                        v-model="line.variation_id"
                                        :options="line.variations"
                                        optionLabel="sku"
                                        optionValue="id"
                                        placeholder="Select Variant"
                                        class="w-full md:w-36"
                                        :class="{'p-invalid': form.errors[`lines.${index}.variation_id`]}"
                                    />
                                    <span v-else>-</span>
                                </td>
                                <td class="p-2">
                                    <InputNumber v-model="line.quantity_ordered" :min="0.01" mode="decimal" :minFractionDigits="0" :maxFractionDigits="2" class="w-full" />
                                </td>
                                <td class="p-2">
                                    <InputNumber v-model="line.unit_cost" mode="currency" currency="USD" locale="en-US" class="w-full" />
                                </td>
                                <td class="p-2 text-right">
                                    {{ (line.quantity_ordered * line.unit_cost).toFixed(2) }}
                                </td>
                                <td class="p-2 text-right">
                                    <Button icon="pi pi-trash" class="p-button-text p-button-danger p-button-sm" @click="removeLine(index)" />
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-right font-bold p-2">Total:</td>
                                <td class="text-right font-bold p-2">
                                    {{ form.lines.reduce((acc, line) => acc + (line.quantity_ordered * line.unit_cost), 0).toFixed(2) }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                     <small class="p-error" v-if="form.errors.lines">{{ form.errors.lines }}</small>
                </div>

                 <div class="field">
                    <label class="block font-bold mb-2">Notes</label>
                    <Textarea v-model="form.notes" rows="3" class="w-full" />
                </div>

                <div class="flex justify-end gap-2">
                    <Link :href="route('purchase-orders.index')">
                         <Button label="Cancel" class="p-button-text p-button-secondary" />
                    </Link>
                    <Button type="submit" label="Create Order" :loading="form.processing" />
                </div>
            </form>
        </div>
    </AppLayout>
</template>
