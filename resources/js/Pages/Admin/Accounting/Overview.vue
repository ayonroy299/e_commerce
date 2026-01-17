<template>
  <div class="p-6 flex flex-col gap-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <Card class="bg-blue-50 border-blue-100">
        <template #title><span class="text-blue-600">Total Revenue</span></template>
        <template #content><h2 class="text-3xl font-bold">{{ $formatCurrency(overview.total_revenue) }}</h2></template>
      </Card>
      <Card class="bg-red-50 border-red-100">
        <template #title><span class="text-red-600">Total Expenses</span></template>
        <template #content><h2 class="text-3xl font-bold">{{ $formatCurrency(overview.total_expenses) }}</h2></template>
      </Card>
      <Card class="bg-green-50 border-green-100">
        <template #title><span class="text-green-600">Net Profit</span></template>
        <template #content><h2 class="text-3xl font-bold">{{ $formatCurrency(overview.net_profit) }}</h2></template>
      </Card>
      <Card class="bg-orange-50 border-orange-100">
        <template #title><span class="text-orange-600">Cash Balance</span></template>
        <template #content><h2 class="text-3xl font-bold">{{ $formatCurrency(overview.cash_balance) }}</h2></template>
      </Card>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
       <!-- Charts/Visuals could go here -->
       <div class="card p-4 bg-white rounded-xl shadow-sm">
          <h3 class="font-bold mb-4 border-b pb-2">Top Expense Categories</h3>
          <p class="text-gray-400 italic">Expense breakdown visualization pending...</p>
       </div>
       <div class="card p-4 bg-white rounded-xl shadow-sm">
          <h3 class="font-bold mb-4 border-b pb-2">Revenue vs Expense (Trail)</h3>
          <p class="text-gray-400 italic">Financial trend visualization pending...</p>
       </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps(['overview']);
</script>
