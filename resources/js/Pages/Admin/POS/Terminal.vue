<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Card from 'primevue/card';
import AutoComplete from 'primevue/autocomplete';
import Dialog from 'primevue/dialog';
import InputNumber from 'primevue/inputnumber';
import axios from 'axios';
import { useToast } from "primevue/usetoast";

const props = defineProps(['session', 'emi_plans', 'customers']);

const toast = useToast();

const cart = ref([]);
const customer = ref(null);
const customerSearch = ref('');
const customerSuggestions = ref([]);

const productSearch = ref('');
const productSuggestions = ref([]);

const showPaymentModal = ref(false);
const paidAmount = ref(0);
const paymentMethod = ref('cash');
const selectedEmiPlan = ref(null);

const subtotal = computed(() => cart.value.reduce((acc, item) => acc + (item.quantity * item.price), 0));
const total = computed(() => subtotal.value); // Add tax logic later
const change = computed(() => Math.max(0, paidAmount.value - total.value));

const minDownPayment = computed(() => {
    if (paymentMethod.value !== 'emi' || !selectedEmiPlan.value) return 0;
    return (total.value * selectedEmiPlan.value.down_payment_percentage / 100);
});

const searchProducts = async (event) => {
    if (!event.query.trim()) return;
    try {
        const response = await axios.get(route('products.index'), {
            params: { search: event.query, per_page: 10 },
             headers: { 'Accept': 'application/json' }
        });
        productSuggestions.value = response.data.map(p => ({
             ...p,
             display_label: p.name + ` ($${p.price || 0})`
        }));
    } catch (e) { console.error(e); }
};

const addToCart = (product) => {
    const existing = cart.value.find(item => item.id === product.id && item.variation_id === null); // Handle variants later
    if (existing) {
        existing.quantity++;
    } else {
        cart.value.push({
            id: product.id,
            name: product.name,
            variation_id: null,
            quantity: 1,
            price: parseFloat(product.price || 0), // Ensure price exists
        });
    }
    productSearch.value = '';
};

const removeFromCart = (index) => cart.value.splice(index, 1);

const searchCustomers = async (event) => {
     if (!event.query.trim()) return;
    try {
        const response = await axios.get(route('customers.index'), {
            params: { search: event.query },
             headers: { 'Accept': 'application/json' }
        });
        customerSuggestions.value = response.data;
    } catch (e) { console.error(e); }
};

const processPayment = async () => {
    if (paymentMethod.value === 'emi' && !customer.value) {
        toast.add({ severity: 'warn', summary: 'Customer Required', detail: 'EMI requires a selected customer', life: 3000 });
        return;
    }

    try {
        const response = await axios.post(route('pos.store'), {
            customer_id: customer.value?.id,
            lines: cart.value.map(item => ({
                product_id: item.id,
                variation_id: item.variation_id,
                quantity: item.quantity,
                unit_price: item.price
            })),
            payment_method: paymentMethod.value,
            paid_amount: paidAmount.value,
            emi_plan_id: selectedEmiPlan.value?.id,
        });

        if (response.data.success) {
            if (response.data.redirect_url) {
                window.location.href = response.data.redirect_url;
                return;
            }
            toast.add({ severity: 'success', summary: 'Sale Completed', detail: `Invoice #${response.data.invoice_number}`, life: 3000 });
            cart.value = [];
            customer.value = null;
            showPaymentModal.value = false;
            paidAmount.value = 0;
            selectedEmiPlan.value = null;
            paymentMethod.value = 'cash';
        }
    } catch (e) {
        const message = e.response?.data?.message || 'Transaction failed';
        toast.add({ severity: 'error', summary: 'Error', detail: message, life: 3000 });
        console.error(e);
    }
};

const openPayment = () => {
    if (!cart.value.length) return;
    paidAmount.value = total.value;
    paymentMethod.value = 'cash';
    selectedEmiPlan.value = null;
    showPaymentModal.value = true;
};
</script>

<template>
    <Head title="POS Terminal" />
    
    <div class="h-screen flex flex-col bg-gray-100">
        <!-- Header -->
        <div class="bg-white shadow p-4 flex justify-between items-center">
            <h1 class="font-bold text-xl">POS Terminal</h1>
            <div class="w-1/3">
                 <AutoComplete
                    v-model="customer"
                    :suggestions="customerSuggestions"
                    @complete="searchCustomers"
                    field="name"
                    placeholder="Select Customer (Optional)"
                    class="w-full"
                    inputClass="w-full"
                />
            </div>
            <Button label="Close Register" class="p-button-danger p-button-outlined p-button-sm" />
        </div>

        <div class="flex flex-1 overflow-hidden">
            <!-- Left: Products -->
            <div class="w-2/3 p-4 overflow-y-auto">
                 <div class="mb-4">
                     <AutoComplete
                        v-model="productSearch"
                        :suggestions="productSuggestions"
                        @complete="searchProducts"
                        field="display_label"
                        @item-select="(e) => addToCart(e.value)"
                        placeholder="Scan or Search Product..."
                        class="w-full"
                        inputClass="w-full p-inputtext-lg"
                        autoFocus
                    />
                </div>

                <!-- Product Grid (Placeholder for full category browser) -->
                <div class="grid grid-cols-4 gap-4">
                     <!-- Quick Access Items could go here -->
                </div>
                 
                 <div class="text-center text-gray-500 mt-20">
                     <i class="pi pi-search text-4xl mb-2"></i>
                     <p>Search for product to begin</p>
                 </div>
            </div>

            <!-- Right: Cart -->
            <div class="w-1/3 bg-white shadow-lg flex flex-col">
                <div class="flex-1 overflow-y-auto p-4">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b text-gray-500 text-sm">
                                <th class="pb-2">Item</th>
                                <th class="pb-2 w-16">Qty</th>
                                <th class="pb-2 w-20 text-right">Price</th>
                                <th class="pb-2 w-20 text-right">Total</th>
                                <th class="w-8"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in cart" :key="index" class="border-b">
                                <td class="py-2">
                                    <div class="font-medium">{{ item.name }}</div>
                                </td>
                                <td class="py-2">
                                     <input v-model.number="item.quantity" type="number" min="1" class="w-12 border rounded p-1 text-center" />
                                </td>
                                <td class="py-2 text-right">{{ item.price.toFixed(2) }}</td>
                                <td class="py-2 text-right font-bold">{{ (item.quantity * item.price).toFixed(2) }}</td>
                                <td class="py-2 text-right">
                                    <button @click="removeFromCart(index)" class="text-red-500 hover:text-red-700">
                                        <i class="pi pi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Totals -->
                <div class="p-4 bg-gray-50 border-t">
                    <div class="flex justify-between text-lg font-bold mb-4">
                        <span>Total To Pay</span>
                        <span>{{ total.toFixed(2) }}</span>
                    </div>
                    
                    <Button label="Pay Now" class="w-full p-button-lg p-button-success" icon="pi pi-credit-card" @click="openPayment" :disabled="!cart.length" />
                </div>
            </div>
        </div>
        
        <!-- Payment Modal -->
        <Dialog v-model:visible="showPaymentModal" header="Payment" :modal="true" class="w-full max-w-sm">
             <div class="space-y-4">
                <div class="text-center text-2xl font-bold mb-4">{{ total.toFixed(2) }}</div>
                
                <div class="field">
                    <label class="block mb-1">Amount Tendered</label>
                    <InputNumber v-model="paidAmount" mode="currency" currency="USD" locale="en-US" class="w-full p-inputtext-lg" :min="0" />
                </div>
                
                <div class="flex justify-between font-bold text-lg" :class="change >= 0 ? 'text-green-600' : 'text-red-600'">
                    <span>Change:</span>
                    <span>{{ change.toFixed(2) }}</span>
                </div>
                
                <div class="flex gap-2 justify-center flex-wrap">
                    <Button label="Cash" :class="{'p-button-outlined': paymentMethod !== 'cash'}" @click="paymentMethod = 'cash'; paidAmount = total" />
                    <Button label="Card" :class="{'p-button-outlined': paymentMethod !== 'card'}" @click="paymentMethod = 'card'; paidAmount = total" />
                    <Button label="Online" :class="{'p-button-outlined': paymentMethod !== 'online'}" @click="paymentMethod = 'online'; paidAmount = total" />
                    <Button label="EMI" :class="{'p-button-outlined': paymentMethod !== 'emi'}" @click="paymentMethod = 'emi'; paidAmount = 0" />
                </div>

                <div v-if="paymentMethod === 'emi'" class="animate-fade-in space-y-3 p-3 bg-blue-50 rounded-lg">
                    <div class="field">
                        <label class="block mb-1 text-sm font-bold">Select EMI Plan</label>
                        <Select 
                            v-model="selectedEmiPlan" 
                            :options="emi_plans" 
                            optionLabel="name" 
                            placeholder="Choose Plan" 
                            class="w-full"
                        />
                    </div>
                    <div v-if="selectedEmiPlan" class="text-sm">
                        <div class="flex justify-between">
                            <span>Min Down Payment:</span>
                            <span class="font-bold">{{ minDownPayment.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Interest Rate:</span>
                            <span>{{ selectedEmiPlan.interest_rate }}%</span>
                        </div>
                    </div>
                </div>
                
                 <Button 
                    label="Complete Sale" 
                    class="w-full mt-4" 
                    @click="processPayment" 
                    :disabled="paymentMethod === 'emi' ? (!selectedEmiPlan || paidAmount < minDownPayment) : (paidAmount < total)" 
                />
             </div>
        </Dialog>
    </div>
</template>
