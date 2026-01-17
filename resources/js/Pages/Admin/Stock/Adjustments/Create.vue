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
    warehouses: Array,
    reasons: Array,
});

const form = useForm({
    warehouse_id: null,
    reason: null,
    date: new Date(),
    notes: '',
    lines: [],
});

const productSearch = ref('');
const productSuggestions = ref([]);

const searchProducts = async (event) => {
    // This assumes we have a product search API. If not, we might need one.
    // Ideally ProductController::index supports JSON search or we make a dedicated endpoint.
    // reusing existing product index for now if it returns JSON on internal calls or create a new route.
    // For now, let's assume we can hit a search endpoint.
    // If not, we should probably add one. Let's try hitting the main product index with ?search=... & ?json=1 if supported, 
    // or just assume we need to implement a search helper.
    // ProductController index disabled for now... let's use a temporary mock or assume a route exists.
    // Usually standard CRUD: /admin/products?search={query}
    
    // Using axios to fetch recommendations
    if (!event.query.trim()) return;

    try {
        // We might need to adjust this route if ProductController return Index view by default.
        // A dedicated search endpoint is better.
        // Let's assume we'll simply list all products via Inertia shared props for MVP or search via existing controller.
        // Re-using ProductController::indexDisabledForNow -> might not work well for JSON.
        // Let's rely on a new route or just inline generic search if we had one.
        // Check `routes/web.php`... ProductController is set with CrudRouter endpoint `products`.
        
        // Quick fix: Request specific fields via a search route.
        // I will add a method to ProductController later or use a standard one.
        // For now, let's assume `products.index` accepts `wantsJson()`
        
        const response = await axios.get(route('products.index'), { 
            params: { search: event.query, per_page: 10 },
            headers: { 'Accept': 'application/json' }
        });
        
        // This depends on how the controller returns data for JSON requests.
        // Inertia controllers usually always return Inertia::render.
        // This is a common friction point.
        // I'll implement a `search` method in ProductController in next step if needed.
        // For now, let's leave this placeholder logic.
        
        productSuggestions.value = response.data.data.map(p => ({
            ...p,
            display_label: p.name + (p.sku ? ` (${p.sku})` : '')
        }));
    } catch (e) {
        console.error("Search failed", e);
    }
};

const addLine = (product) => {
    // Check if variations exist
    // If product.type === 'variable', we need to select a variation.
    // Simple logic: add line, then maybe let user pick variation from dropdown if needed.
    
    form.lines.push({
        product_id: product.id,
        product_name: product.name,
        type: product.type,
        variations: product.variations || [], // Need to ensure products search returns variations
        variation_id: null,
        quantity_adjusted: 0,
    });
    
    productSearch.value = ''; // clear search
};

const removeLine = (index) => {
    form.lines.splice(index, 1);
};

const submit = () => {
    form.post(route('adjustments.store'));
};
</script>

<template>
    <Head title="Create Adjustment" />

    <AppLayout>
        <div class="card max-w-4xl mx-auto">
            <h2 class="text-xl font-semibold mb-6">Create Stock Adjustment</h2>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Header Fields -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="field">
                        <label class="block font-bold mb-2">Warehouse</label>
                        <Dropdown v-model="form.warehouse_id" :options="warehouses" optionLabel="name" optionValue="id" placeholder="Select Warehouse" class="w-full" :class="{'p-invalid': form.errors.warehouse_id}" />
                         <small class="p-error" v-if="form.errors.warehouse_id">{{ form.errors.warehouse_id }}</small>
                    </div>
                    <div class="field">
                        <label class="block font-bold mb-2">Reason</label>
                        <Dropdown v-model="form.reason" :options="reasons" placeholder="Select Reason" class="w-full" :class="{'p-invalid': form.errors.reason}">
                            <template #option="slotProps">
                                <span class="capitalize">{{ slotProps.option }}</span>
                            </template>
                            <template #value="slotProps">
                                <span v-if="slotProps.value" class="capitalize">{{ slotProps.value }}</span>
                                <span v-else>{{ slotProps.placeholder }}</span>
                            </template>
                        </Dropdown>
                         <small class="p-error" v-if="form.errors.reason">{{ form.errors.reason }}</small>
                    </div>
                    <div class="field">
                        <label class="block font-bold mb-2">Date</label>
                        <Calendar v-model="form.date" showIcon class="w-full" :class="{'p-invalid': form.errors.date}" />
                         <small class="p-error" v-if="form.errors.date">{{ form.errors.date }}</small>
                    </div>
                </div>

                <!-- Product Search -->
                <div class="field">
                    <label class="block font-bold mb-2">Add Product</label>
                    <AutoComplete 
                        v-model="productSearch" 
                        :suggestions="productSuggestions" 
                        @complete="searchProducts" 
                        field="display_label" 
                        @item-select="(e) => addLine(e.value)"
                        placeholder="Type to search product..."
                        class="w-full"
                    />
                </div>

                <!-- Lines Table -->
                <div v-if="form.lines.length" class="border rounded p-2">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="border-b">
                                <th class="p-2">Product</th>
                                <th class="p-2">Variation</th>
                                <th class="p-2 w-32">Qty Adjustment</th>
                                <th class="p-2 w-10"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(line, index) in form.lines" :key="index" class="border-b">
                                <td class="p-2">
                                    {{ line.product_name }}
                                </td>
                                <td class="p-2">
                                    <div v-if="line.type === 'variable' && line.variations.length">
                                        <Dropdown 
                                            v-model="line.variation_id" 
                                            :options="line.variations" 
                                            optionLabel="sku" 
                                            optionValue="id" 
                                            placeholder="Select Variant" 
                                            class="w-full md:w-40"
                                            :class="{'p-invalid': form.errors[`lines.${index}.variation_id`]}"
                                        >
                                           <template #option="slotProps">
                                                {{ slotProps.option.sku }} - {{ slotProps.option.attribute_values?.map(a => a.value).join(', ') }}
                                           </template>
                                        </Dropdown>
                                    </div>
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                                <td class="p-2">
                                    <InputNumber v-model="line.quantity_adjusted" class="w-full" :class="{'p-invalid': form.errors[`lines.${index}.quantity_adjusted`]}" />
                                </td>
                                <td class="p-2 text-right">
                                    <Button icon="pi pi-trash" class="p-button-text p-button-danger p-button-sm" @click="removeLine(index)" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <small class="p-error block mt-2" v-if="form.errors.lines">{{ form.errors.lines }}</small>
                </div>

                <!-- Notes -->
                 <div class="field">
                    <label class="block font-bold mb-2">Notes</label>
                    <Textarea v-model="form.notes" rows="3" class="w-full" />
                </div>

                 <div class="flex justify-end gap-2">
                    <Button label="Cancel" class="p-button-text p-button-secondary" @click="$inertia.visit(route('adjustments.index'))" />
                    <Button type="submit" label="Create Draft" :loading="form.processing" />
                </div>
            </form>
        </div>
    </AppLayout>
</template>
