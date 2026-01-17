<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

const page = usePage();

// branches passed from Laravel (shared)
const branches = computed(() => page.props.branches || []);
const currentBranchId = computed(() => page.props.auth.user.branch_id);

const changeBranch = (e) => {
    router.post(
        route("branch.switch"),
        { branch_id: e.value },
        {
            preserveScroll: true,
            preserveState: true,
        }
    );
};
</script>

<template>
  <div class="flex items-center gap-2">
    <label class="text-sm text-slate-600 font-medium hidden md:block">
      Branch
    </label>

    <Select
      v-model="currentBranchId"
      :options="branches"
      option-label="name"
      option-value="id"
      placeholder="Select Branch"
      class="w-48"
      @change="changeBranch"
    />
  </div>
</template>
