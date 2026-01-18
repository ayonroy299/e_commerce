<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
</script>

<template>
    <AuthenticatedLayout>
        <div v-if="$slots.header" class="mb-6">
            <slot name="header" />
        </div>
        <slot />
    </AuthenticatedLayout>
</template>
