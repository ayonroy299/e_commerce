<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';

const props = defineProps({
    sale: Object,
});

const form = useForm({
    reason: '',
    lines: props.sale.lines.map(line => ({
        sale_line_id: line.id,
        product_name: line.product?.name,
        quantity_sold: parseFloat(line.quantity),
        quantity_returned: 0,
        unit_price: parseFloat(line.unit_price),
    })),
});

const submit = () => {
    // Filter out 0 qty lines
    const payload = {
        ...form,
        lines: form.lines.filter(l => l.quantity_returned > 0)
    };
    form.post(route('sales.return.store', props.sale.id), {
        data: payload
    });
};
</script>

<template>
    <Head title="Create Return" />

    <AppLayout>
        <div class="card max-w-4xl mx-auto">
            <h2 class="text-xl font-semibold mb-6">Return Items - Invoice {{ sale.invoice_number }}</h2>
            
            <form @submit.prevent="submit">
                <div class="border rounded p-2 mb-6">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="border-b">
                                <th class="p-2">Product</th>
                                <th class="p-2">Sold Qty</th>
                                <th class="p-2 w-32">Return Qty</th>
                                <th class="p-2 w-32 text-right">Refund Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                             <tr v-for="(line, index) in form.lines" :key="index" class="border-b">
                                <td class="p-2">{{ line.product_name }}</td>
                                <td class="p-2">{{ line.quantity_sold }}</td>
                                <td class="p-2">
                                     <InputNumber v-model="line.quantity_returned" :min="0" :max="line.quantity_sold" class="w-full" />
                                </td>
                                <td class="p-2 text-right">
                                    {{ (line.quantity_returned * line.unit_price).toFixed(2) }}
                                </td>
                             </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="field mb-6">
                    <label class="block font-bold mb-2">Reason for Return</label>
                    <Textarea v-model="form.reason" rows="3" class="w-full" placeholder="e.g. Damaged, Wrong Item" />
                    <small class="p-error" v-if="form.errors.reason">{{ form.errors.reason }}</small>
                </div>
                
                <div class="flex justify-end gap-2">
                    <Link :href="route('sales.index')">
                        <Button label="Cancel" class="p-button-text p-button-secondary" />
                    </Link>
                    <Button type="submit" label="Process Return & Refund" icon="pi pi-check" :loading="form.processing" />
                </div>
            </form>
        </div>
    </AppLayout>
</template>
