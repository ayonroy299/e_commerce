<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
import InputNumber from 'primevue/inputnumber';
import Dropdown from 'primevue/dropdown';
import Textarea from 'primevue/textarea';
import Calendar from 'primevue/calendar';

const props = defineProps({
    order: Object,
    warehouses: Array,
});

const form = useForm({
    warehouse_id: null,
    received_date: new Date(),
    notes: '',
    lines: props.order.lines.map(line => ({
        product_id: line.product_id,
        variation_id: line.variation_id,
        product_name: line.product?.name,
        sku: line.variation?.sku,
        quantity_ordered: parseFloat(line.quantity_ordered),
        quantity_received: Math.max(0, parseFloat(line.quantity_ordered) - parseFloat(line.quantity_received)), // Default to remaining
        quantity_already_received: parseFloat(line.quantity_received),
    })),
});

const submit = () => {
    form.post(route('purchase-orders.receive.store', props.order.id));
};
</script>

<template>
    <Head title="Receive Goods (GRN)" />

    <AppLayout>
        <div class="card max-w-4xl mx-auto">
             <h2 class="text-xl font-semibold mb-6">Receive Goods (GRN) - PO #{{ order.id.substring(0,8) }}</h2>

             <form @submit.prevent="submit" class="space-y-6">
                 <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                     <div class="field">
                        <label class="block font-bold mb-2">Warehouse</label>
                        <Dropdown v-model="form.warehouse_id" :options="warehouses" optionLabel="name" optionValue="id" placeholder="Select Warehouse" class="w-full" :class="{'p-invalid': form.errors.warehouse_id}" />
                        <small class="p-error" v-if="form.errors.warehouse_id">{{ form.errors.warehouse_id }}</small>
                    </div>
                    <div class="field">
                        <label class="block font-bold mb-2">Received Date</label>
                        <Calendar v-model="form.received_date" showIcon class="w-full" :class="{'p-invalid': form.errors.received_date}" />
                    </div>
                 </div>

                 <div class="border rounded p-2">
                     <table class="w-full text-sm text-left">
                         <thead>
                             <tr class="border-b">
                                 <th class="p-2">Product</th>
                                 <th class="p-2">Ordered</th>
                                 <th class="p-2">Prev. Received</th>
                                 <th class="p-2 w-32">Receive Now</th>
                             </tr>
                         </thead>
                         <tbody>
                            <tr v-for="(line, index) in form.lines" :key="index" class="border-b">
                                <td class="p-2">
                                    {{ line.product_name }}
                                    <div v-if="line.sku" class="text-xs text-gray-500">{{ line.sku }}</div>
                                </td>
                                <td class="p-2">{{ line.quantity_ordered }}</td>
                                <td class="p-2">{{ line.quantity_already_received }}</td>
                                <td class="p-2">
                                    <InputNumber v-model="line.quantity_received" :min="0" :max="line.quantity_ordered - line.quantity_already_received + 1000" class="w-full" />
                                </td>
                            </tr>
                         </tbody>
                     </table>
                     <small class="p-error" v-if="form.errors.lines">{{ form.errors.lines }}</small>
                 </div>

                 <div class="field">
                    <label class="block font-bold mb-2">Notes</label>
                    <Textarea v-model="form.notes" rows="3" class="w-full" />
                </div>

                <div class="flex justify-end gap-2">
                     <Link :href="route('purchase-orders.show', order.id)">
                         <Button label="Cancel" class="p-button-text p-button-secondary" />
                    </Link>
                    <Button type="submit" label="Create GRN" icon="pi pi-check" :loading="form.processing" />
                </div>
             </form>
        </div>
    </AppLayout>
</template>
