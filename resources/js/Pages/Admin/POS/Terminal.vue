<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed, onMounted } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Card from 'primevue/card';
import AutoComplete from 'primevue/autocomplete';
import Dialog from 'primevue/dialog';
import InputNumber from 'primevue/inputnumber';
import Select from 'primevue/select';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import axios from 'axios';
import { useToast } from "primevue/usetoast";

const props = defineProps(['session', 'emi_plans', 'customers', 'categories', 'initial_products']);

const toast = useToast();

const cart = ref([]);
const customer = ref(null);
const customerSearch = ref('');
const customerSuggestions = ref([]);

const productSearch = ref('');
const productSuggestions = ref([]);
const products = ref(props.initial_products || []);
const activeCategory = ref(null);
const loadingProducts = ref(false);

const showPaymentModal = ref(false);
const paidAmount = ref(0);
const paymentMethod = ref('cash');
const selectedEmiPlan = ref(null);

const subtotal = computed(() => cart.value.reduce((acc, item) => acc + (item.quantity * item.price), 0));
const total = computed(() => subtotal.value);
const change = computed(() => Math.max(0, paidAmount.value - total.value));

const minDownPayment = computed(() => {
    if (paymentMethod.value !== 'emi' || !selectedEmiPlan.value) return 0;
    return (total.value * selectedEmiPlan.value.down_payment_percentage / 100);
});

const searchProducts = async (event) => {
    if (!event.query.trim()) return;
    try {
        const response = await axios.get(route('products.index'), {
            params: { search: event.query },
            headers: { 'Accept': 'application/json' }
        });
        productSuggestions.value = response.data.map(p => ({
             ...p,
             display_label: p.name + ` ($${p.price || 0})`
        }));
    } catch (e) { console.error(e); }
};

const fetchByCategory = async (catId) => {
    activeCategory.value = catId;
    loadingProducts.value = true;
    try {
        const response = await axios.get(route('products.index'), {
            params: { category_id: catId, is_active: 1 },
            headers: { 'Accept': 'application/json' }
        });
        products.value = response.data;
    } catch (e) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to load products' });
    } finally {
        loadingProducts.value = false;
    }
};

const addToCart = (product) => {
    const existing = cart.value.find(item => item.id === product.id && item.variation_id === null);
    if (existing) {
        existing.quantity++;
    } else {
        cart.value.push({
            id: product.id,
            name: product.name,
            variation_id: null,
            quantity: 1,
            price: parseFloat(product.price || 0),
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
    
    <div class="h-screen flex flex-col bg-slate-50 font-sans text-slate-900">
        <!-- Header -->
        <div class="bg-white/80 backdrop-blur-md border-b border-slate-200 p-3 flex justify-between items-center sticky top-0 z-10">
            <div class="flex items-center gap-3">
                <div class="bg-blue-600 p-2 rounded-lg text-white">
                    <i class="pi pi-desktop"></i>
                </div>
                <h1 class="font-black text-lg tracking-tight">TERMINAL <span class="text-blue-600">POS</span></h1>
            </div>
            
            <div class="w-1/3 max-w-sm">
                 <AutoComplete
                    v-model="customer"
                    :suggestions="customerSuggestions"
                    @complete="searchCustomers"
                    field="name"
                    placeholder="Search Customer..."
                    class="w-full"
                    inputClass="w-full !rounded-full !bg-slate-100 !border-none !px-4"
                >
                    <template #option="slotProps">
                        <div class="flex flex-col">
                            <span class="font-bold">{{ slotProps.option.name }}</span>
                            <span class="text-xs text-slate-500">{{ slotProps.option.phone }}</span>
                        </div>
                    </template>
                </AutoComplete>
            </div>
            
            <div class="flex items-center gap-2">
                <div class="text-right hidden sm:block">
                    <p class="text-xs font-bold text-slate-400">OPERATOR</p>
                    <p class="text-sm font-black">{{ $page.props.auth.user.name }}</p>
                </div>
                <Button icon="pi pi-power-off" class="p-button-rounded p-button-text p-button-danger" />
            </div>
        </div>

        <div class="flex flex-1 overflow-hidden">
            <!-- Left: Products Browser -->
            <div class="w-2/3 flex flex-col border-r border-slate-200">
                <!-- Search & Filters -->
                <div class="p-4 bg-white border-b border-slate-100">
                     <div class="relative group mb-4">
                         <i class="pi pi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-500 transition-colors"></i>
                         <AutoComplete
                            v-model="productSearch"
                            :suggestions="productSuggestions"
                            @complete="searchProducts"
                            field="display_label"
                            @item-select="(e) => addToCart(e.value)"
                            placeholder="Scan barcode or type product name..."
                            class="w-full"
                            inputClass="w-full !pl-12 !py-4 !rounded-2xl !bg-slate-50 !border-none !text-lg !font-medium"
                            autoFocus
                        />
                    </div>

                    <!-- Category Tabs -->
                    <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
                        <button 
                            @click="products = initial_products; activeCategory = null"
                            class="px-5 py-2 rounded-full text-sm font-bold transition-all whitespace-nowrap"
                            :class="activeCategory === null ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        >
                            All Categories
                        </button>
                        <button 
                            v-for="cat in categories" 
                            :key="cat.id"
                            @click="fetchByCategory(cat.id)"
                            class="px-5 py-2 rounded-full text-sm font-bold transition-all whitespace-nowrap"
                            :class="activeCategory === cat.id ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        >
                            {{ cat.name }}
                        </button>
                    </div>
                </div>

                <!-- Product Grid -->
                <div class="flex-1 overflow-y-auto p-4 bg-slate-50/50">
                    <div v-if="loadingProducts" class="flex items-center justify-center h-full">
                        <i class="pi pi-spin pi-spinner text-4xl text-blue-500"></i>
                    </div>
                    <div v-else-if="products.length" class="grid grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                        <div 
                            v-for="product in products" 
                            :key="product.id"
                            @click="addToCart(product)"
                            class="bg-white rounded-2xl p-3 shadow-sm border border-transparent hover:border-blue-500 hover:shadow-md transition-all cursor-pointer group flex flex-col h-full"
                        >
                            <div class="aspect-square rounded-xl bg-slate-100 mb-3 overflow-hidden relative">
                                <img v-if="product.image" :src="product.image" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
                                <div v-else class="w-full h-full flex items-center justify-center text-slate-300">
                                    <i class="pi pi-image text-3xl"></i>
                                </div>
                                <div class="absolute top-2 right-2 bg-black/60 backdrop-blur-md text-white text-[10px] font-black px-2 py-1 rounded-full">
                                    STOCK: {{ product.stock }}
                                </div>
                            </div>
                            <h4 class="font-bold text-xs mb-1 line-clamp-2">{{ product.name }}</h4>
                            <div class="mt-auto pt-2 flex justify-between items-center">
                                <span class="text-blue-600 font-black text-sm">{{ $formatCurrency(product.price) }}</span>
                                <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                    <i class="pi pi-plus text-[10px]"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="flex flex-col items-center justify-center h-full text-slate-400 opacity-50">
                        <i class="pi pi-box text-6xl mb-4"></i>
                        <p class="font-black italic">No products found in this category</p>
                    </div>
                </div>
            </div>

            <!-- Right: Order Sidebar (Cart) -->
            <div class="w-1/3 bg-white shadow-2xl flex flex-col z-20">
                <div class="p-4 border-b border-slate-100 flex justify-between items-center">
                    <h2 class="font-black text-slate-900">CURRENT ORDER</h2>
                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-black">{{ cart.length }} ITEMS</span>
                </div>

                <div class="flex-1 overflow-y-auto p-4 space-y-3">
                    <TransitionGroup name="list" tag="div" class="space-y-3">
                        <div v-for="(item, index) in cart" :key="item.id" class="flex gap-3 p-3 bg-slate-50 rounded-2xl group relative overflow-hidden">
                             <div class="w-12 h-12 rounded-xl bg-white border border-slate-200 flex items-center justify-center font-black text-blue-600">
                                {{ item.quantity }}
                             </div>
                             <div class="flex-1">
                                <p class="font-bold text-sm text-slate-900 leading-tight mb-1">{{ item.name }}</p>
                                <p class="text-xs font-black text-blue-600">{{ $formatCurrency(item.price) }}</p>
                             </div>
                             <div class="text-right">
                                <p class="font-black text-sm">{{ $formatCurrency(item.quantity * item.price) }}</p>
                                <button @click="removeFromCart(index)" class="text-slate-300 hover:text-red-500 transition-colors">
                                    <i class="pi pi-trash"></i>
                                </button>
                             </div>
                        </div>
                    </TransitionGroup>

                    <div v-if="!cart.length" class="flex flex-col items-center justify-center h-full text-slate-300">
                        <i class="pi pi-shopping-bag text-5xl mb-4"></i>
                        <p class="font-bold uppercase tracking-widest text-xs">Cart is empty</p>
                    </div>
                </div>

                <!-- Totals & Checkout -->
                <div class="p-6 bg-white border-t border-slate-200 shadow-[0_-10px_20px_rgba(0,0,0,0.02)]">
                    <div class="space-y-2 mb-6">
                         <div class="flex justify-between text-slate-500 text-sm font-bold">
                            <span>Subtotal</span>
                            <span>{{ $formatCurrency(subtotal) }}</span>
                        </div>
                         <div class="flex justify-between text-slate-900 text-xl font-black pt-2">
                            <span>Total</span>
                            <span class="text-blue-600">{{ $formatCurrency(total) }}</span>
                        </div>
                    </div>
                    
                    <Button 
                        label="PROCESS PAYMENT" 
                        class="w-full !py-5 !rounded-2xl !text-lg !font-black !shadow-lg !shadow-blue-200" 
                        icon="pi pi-credit-card" 
                        @click="openPayment" 
                        :disabled="!cart.length" 
                    />
                </div>
            </div>
        </div>
        
        <!-- Payment Modal -->
        <Dialog v-model:visible="showPaymentModal" header="COMPLETE YOUR ORDER" :modal="true" class="w-full max-w-md !rounded-3xl !border-none" pt:mask:class="backdrop-blur-sm">
             <div class="p-2 space-y-6">
                <!-- Large Price Display -->
                <div class="bg-blue-600 rounded-3xl p-8 text-center text-white shadow-xl shadow-blue-200">
                    <p class="text-blue-100 text-xs font-black uppercase tracking-widest mb-2">Total Amount Due</p>
                    <h2 class="text-5xl font-black">{{ $formatCurrency(total) }}</h2>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="field">
                        <label class="block mb-2 text-xs font-black text-slate-400 uppercase">Amount Received</label>
                        <InputNumber v-model="paidAmount" mode="currency" currency="USD" locale="en-US" class="w-full" inputClass="!text-2xl !font-black !py-4 !rounded-2xl !bg-slate-50 !border-slate-200" />
                    </div>
                    <div class="field">
                        <label class="block mb-2 text-xs font-black text-slate-400 uppercase">Return Change</label>
                        <div class="text-2xl font-black py-4 px-4 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100">
                            {{ $formatCurrency(change) }}
                        </div>
                    </div>
                </div>
                
                <div>
                     <label class="block mb-3 text-xs font-black text-slate-400 uppercase">Payment Method</label>
                     <div class="grid grid-cols-2 gap-3">
                        <button 
                            v-for="method in ['cash', 'card', 'online', 'emi']" 
                            :key="method"
                            @click="paymentMethod = method; if(method !== 'emi') paidAmount = total; else paidAmount = 0;"
                            class="py-4 rounded-2xl font-black text-sm flex items-center justify-center gap-2 border-2 transition-all uppercase"
                            :class="paymentMethod === method ? 'bg-blue-600 border-blue-600 text-white shadow-lg shadow-blue-100' : 'bg-white border-slate-100 text-slate-400 hover:border-slate-200'"
                        >
                            <i :class="{
                                'pi pi-wallet': method === 'cash',
                                'pi pi-credit-card': method === 'card',
                                'pi pi-globe': method === 'online',
                                'pi pi-calendar': method === 'emi'
                            }"></i>
                            {{ method }}
                        </button>
                     </div>
                </div>

                <div v-if="paymentMethod === 'emi'" class="animate-fade-in space-y-4 p-5 bg-blue-50/50 border border-blue-100 rounded-3xl">
                    <div class="field">
                        <label class="block mb-2 text-xs font-black text-blue-600 uppercase">Installment Plan</label>
                        <Select 
                            v-model="selectedEmiPlan" 
                            :options="emi_plans" 
                            optionLabel="name" 
                            placeholder="Choose your plan" 
                            class="w-full !rounded-xl !border-blue-200"
                        />
                    </div>
                    <div v-if="selectedEmiPlan" class="grid grid-cols-2 gap-4 text-xs font-bold text-blue-800">
                        <div class="bg-white p-3 rounded-xl border border-blue-100">
                            <p class="text-blue-400 mb-1">MIN DOWN PAYMENT</p>
                            <p class="text-lg font-black">{{ $formatCurrency(minDownPayment) }}</p>
                        </div>
                        <div class="bg-white p-3 rounded-xl border border-blue-100">
                            <p class="text-blue-400 mb-1">INTEREST RATE</p>
                            <p class="text-lg font-black">{{ selectedEmiPlan.interest_rate }}%</p>
                        </div>
                    </div>
                </div>
                
                 <Button 
                    label="FINISH & PRINT RECEIPT" 
                    class="w-full !py-5 !rounded-3xl !text-xl !font-black !shadow-2xl transition-all active:scale-95" 
                    severity="success"
                    @click="processPayment" 
                    :disabled="paymentMethod === 'emi' ? (!selectedEmiPlan || paidAmount < minDownPayment) : (paidAmount < total)" 
                />
             </div>
        </Dialog>
    </div>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.list-enter-active,
.list-leave-active {
  transition: all 0.5s ease;
}
.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateX(30px);
}

:deep(.p-autocomplete-input) {
    width: 100%;
}

:deep(.p-dialog-header) {
    padding: 1.5rem 1.5rem 0.5rem;
}
:deep(.p-dialog-content) {
    padding: 1.5rem;
}
</style>
