<template>
  <div class="min-h-screen bg-white">
    <!-- Navbar -->
    <header class="sticky top-0 z-50 bg-slate-50 border-b">
      <div
        class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between"
      >
        <!-- Logo -->
        <div class="flex items-center">
          <span class="text-xl font-semibold text-green-600">
            SmartPOS
          </span>
        </div>

        <!-- Login -->
        <Link href="/login">
          <Button
            severity="primary"
            rounded
          >
            Login
          </Button>
        </Link>
      </div>
    </header>

    <!-- Hero Section -->
    <section class="relative overflow-hidden">
      <div
        class="max-w-7xl mx-auto px-6 py-24 grid grid-cols-1 md:grid-cols-2 gap-16 items-center"
      >
        <!-- Content -->
        <div>
          <h1 class="text-5xl font-bold text-slate-900 leading-tight">
            Modern POS System <br>
            for
            <span class="text-primary-600">Retail & Business</span>
          </h1>

          <p class="mt-6 text-lg text-slate-600">
            Manage sales, inventory, customers, and reports in one
            powerful point-of-sale platform built for growing
            businesses.
          </p>

          <div class="mt-10 flex gap-4">
            <Link :href="route('dashboard')">
              <Button
                size="large"
                severity="primary"
              >
                Access POS
              </Button>
            </Link>

            <Button
              size="large"
              severity="secondary"
              outlined
              @click="scrollToFeatures"
            >
              View Features
            </Button>
          </div>
        </div>

        <!-- Banner Image -->
        <div class="relative">
          <div
            class="absolute -inset-4 bg-primary-100 rounded-3xl blur-2xl opacity-60"
          />

          <img
            src="../../../assets/images/pos-banner.jpg"
            alt="POS Dashboard Preview"
            class="relative"
          >
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-24 bg-slate-50">
      <div class="max-w-7xl mx-auto px-6 text-center">
        <h2 class="text-3xl font-bold text-slate-900 mb-12">Powerful Features</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-left">
          <div class="bg-white p-8 rounded-xl shadow-sm">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center text-green-600 mb-6">
              <i class="pi pi-shopping-cart text-xl"></i>
            </div>
            <h3 class="text-xl font-bold mb-4">Fast Checkout</h3>
            <p class="text-slate-600">Complete sales in seconds with our optimized POS terminal.</p>
          </div>
          <div class="bg-white p-8 rounded-xl shadow-sm">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600 mb-6">
              <i class="pi pi-box text-xl"></i>
            </div>
            <h3 class="text-xl font-bold mb-4">Inventory Tracking</h3>
            <p class="text-slate-600">Monitor stock levels in real-time across multiple branches.</p>
          </div>
          <div class="bg-white p-8 rounded-xl shadow-sm">
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center text-purple-600 mb-6">
              <i class="pi pi-chart-bar text-xl"></i>
            </div>
            <h3 class="text-xl font-bold mb-4">Detailed Analytics</h3>
            <p class="text-slate-600">Gain insights with comprehensive sales and stock reports.</p>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import Button from "primevue/button";

const scrollToFeatures = () => {
  document.getElementById('features')?.scrollIntoView({ behavior: 'smooth' });
};
</script>
