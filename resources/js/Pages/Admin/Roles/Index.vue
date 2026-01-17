<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import Checkbox from 'primevue/checkbox';
import { ref } from 'vue';

const props = defineProps({
    roles: Array,
    permissions: Array,
});

const showModal = ref(false);
const isEditing = ref(false);
const form = useForm({
    id: null,
    name: '',
    permissions: [],
});

const openCreate = () => {
    isEditing.value = false;
    form.reset();
    form.permissions = [];
    showModal.value = true;
};

const openEdit = (role) => {
    isEditing.value = true;
    form.id = role.id;
    form.name = role.name;
    // Map permissions to names
    form.permissions = role.permissions.map(p => p.name);
    showModal.value = true;
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('roles.update', form.id), {
            onSuccess: () => showModal.value = false
        });
    } else {
        form.post(route('roles.store'), {
            onSuccess: () => showModal.value = false
        });
    }
};

const deleteRole = (id) => {
    if (confirm('Are you sure?')) {
        form.delete(route('roles.destroy', id));
    }
};
</script>

<template>
    <Head title="Role Management" />

    <AppLayout>
        <div class="card">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Roles & Permissions</h2>
                <Button label="New Role" icon="pi pi-plus" @click="openCreate" />
            </div>

            <DataTable :value="roles" responsiveLayout="scroll">
                <Column field="name" header="Role"></Column>
                <Column header="Permissions">
                    <template #body="slotProps">
                        <div class="flex flex-wrap gap-1">
                             <span v-for="perm in slotProps.data.permissions" :key="perm.id" class="px-2 py-1 bg-gray-100 text-xs rounded">
                                 {{ perm.name }}
                             </span>
                        </div>
                    </template>
                </Column>
                <Column header="Actions" class="w-32">
                    <template #body="slotProps">
                         <div class="flex gap-2">
                            <Button icon="pi pi-pencil" class="p-button-text p-button-sm" @click="openEdit(slotProps.data)" />
                            <Button v-if="slotProps.data.name !== 'super-admin'" icon="pi pi-trash" class="p-button-text p-button-danger p-button-sm" @click="deleteRole(slotProps.data.id)" />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <Dialog v-model:visible="showModal" :header="isEditing ? 'Edit Role' : 'New Role'" :modal="true" class="w-full max-w-2xl">
             <form @submit.prevent="submit" class="space-y-6">
                 <div class="field">
                    <label class="block mb-2 font-bold">Role Name</label>
                    <InputText v-model="form.name" class="w-full" :class="{'p-invalid': form.errors.name}" />
                    <small class="p-error" v-if="form.errors.name">{{ form.errors.name }}</small>
                </div>
                
                <div class="field">
                    <label class="block mb-2 font-bold">Permissions</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <div v-for="perm in permissions" :key="perm.id" class="flex items-center">
                            <Checkbox v-model="form.permissions" :inputId="perm.id" :name="perm.name" :value="perm.name" />
                            <label :for="perm.id" class="ml-2 cursor-pointer">{{ perm.name }}</label>
                        </div>
                    </div>
                </div>

                 <div class="flex justify-end pt-4 border-t">
                    <Button type="button" label="Cancel" class="p-button-text p-button-secondary mr-2" @click="showModal = false" />
                    <Button type="submit" label="Save Role" :loading="form.processing" />
                </div>
             </form>
        </Dialog>
    </AppLayout>
</template>
