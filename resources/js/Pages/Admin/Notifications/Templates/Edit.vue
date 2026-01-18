<script setup>
import CreateEdit from './CreateEdit.vue';
defineProps({ template: Object });
</script>

<template>
    <CreateEdit :template="template" :is-edit="true" />
</template>
