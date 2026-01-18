<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Checkbox from 'primevue/checkbox';
import Button from 'primevue/button';
import Message from 'primevue/message';
import ToggleSwitch from 'primevue/toggleswitch';

const props = defineProps({
    template: {
        type: Object,
        default: () => ({
            code: '',
            name: '',
            subject: '',
            body: '',
            channels: ['email'],
            is_active: true,
        }),
    },
    isEdit: Boolean,
});

const form = useForm({
    code: props.template.code,
    name: props.template.name,
    subject: props.template.subject,
    body: props.template.body,
    channels: props.template.channels,
    is_active: props.template.is_active,
});

const submit = () => {
    if (props.isEdit) {
        form.put(route('notification-templates.update', props.template.id));
    } else {
        form.post(route('notification-templates.store'));
    }
};

const channelOptions = [
    { label: 'Email', value: 'email' },
    { label: 'SMS', value: 'sms' },
    { label: 'WhatsApp', value: 'whatsapp' },
];
</script>

<template>
    <Head :title="isEdit ? 'Edit Template' : 'Create Template'" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ isEdit ? 'Edit Notification Template' : 'Create Notification Template' }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <Card>
                    <template #content>
                        <form @submit.prevent="submit" class="flex flex-col gap-6">
                            <div class="flex flex-col gap-2">
                                <label for="code" class="font-bold">Code (Unique Identifier)</label>
                                <InputText id="code" v-model="form.code" :disabled="isEdit" placeholder="e.g. EMI_DUE_REMINDER" />
                                <Message v-if="form.errors.code" severity="error">{{ form.errors.code }}</Message>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="name" class="font-bold">Name</label>
                                <InputText id="name" v-model="form.name" placeholder="e.g. EMI Due Reminder" />
                                <Message v-if="form.errors.name" severity="error">{{ form.errors.name }}</Message>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="font-bold">Channels</label>
                                <div class="flex flex-wrap gap-4">
                                    <div v-for="option in channelOptions" :key="option.value" class="flex items-center gap-2">
                                        <Checkbox v-model="form.channels" :inputId="option.value" :name="option.value" :value="option.value" />
                                        <label :for="option.value">{{ option.label }}</label>
                                    </div>
                                </div>
                                <Message v-if="form.errors.channels" severity="error">{{ form.errors.channels }}</Message>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="subject" class="font-bold">Subject (Optional for SMS/WhatsApp)</label>
                                <InputText id="subject" v-model="form.subject" placeholder="Email Subject" />
                                <Message v-if="form.errors.subject" severity="error">{{ form.errors.subject }}</Message>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="body" class="font-bold">Message Body</label>
                                <Textarea id="body" v-model="form.body" rows="10" placeholder="Use {{ placeholder }} for dynamic data" />
                                <p class="text-xs text-gray-500 italic">Example: Dear {{ customer_name }}, your EMI of {{ amount }} is due on {{ date }}.</p>
                                <Message v-if="form.errors.body" severity="error">{{ form.errors.body }}</Message>
                            </div>

                            <div class="flex items-center gap-2">
                                <ToggleSwitch v-model="form.is_active" />
                                <label>Active</label>
                            </div>

                            <div class="flex justify-end gap-2 border-t pt-4">
                                <Link :href="route('notification-templates.index')">
                                    <Button label="Cancel" severity="secondary" text />
                                </Link>
                                <Button type="submit" :label="isEdit ? 'Update Template' : 'Create Template'" :loading="form.processing" />
                            </div>
                        </form>
                    </template>
                </Card>
            </div>
        </div>
    </AdminLayout>
</template>
