<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';

const form = useForm({
    name: '',
    phone: '',
    email: '',
    opening_balance: 0,
});

const submit = () => {
    form.post(route('customers.store'));
};
</script>

<template>
    <Head title="Create Customer" />

    <AppLayout>
        <div class="card max-w-lg mx-auto">
             <h2 class="text-xl font-semibold mb-6">Create Customer</h2>

             <form @submit.prevent="submit" class="space-y-4">
                 <div class="field">
                    <label class="block font-bold mb-2">Name</label>
                    <InputText v-model="form.name" class="w-full" :class="{'p-invalid': form.errors.name}" />
                    <small class="p-error" v-if="form.errors.name">{{ form.errors.name }}</small>
                </div>
                
                <div class="field">
                    <label class="block font-bold mb-2">Phone</label>
                    <InputText v-model="form.phone" class="w-full" />
                </div>
                
                <div class="field">
                    <label class="block font-bold mb-2">Email</label>
                    <InputText v-model="form.email" class="w-full" :class="{'p-invalid': form.errors.email}" />
                    <small class="p-error" v-if="form.errors.email">{{ form.errors.email }}</small>
                </div>
                
                <div class="field">
                    <label class="block font-bold mb-2">Opening Balance</label>
                    <InputNumber v-model="form.opening_balance" mode="currency" currency="USD" locale="en-US" class="w-full" />
                </div>

                <div class="flex justify-end gap-2 pt-4">
                     <Link :href="route('customers.index')">
                         <Button label="Cancel" class="p-button-text p-button-secondary" />
                    </Link>
                    <Button type="submit" label="Create Customer" :loading="form.processing" />
                </div>
             </form>
        </div>
    </AppLayout>
</template>
