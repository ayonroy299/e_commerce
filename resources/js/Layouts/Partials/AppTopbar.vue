<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import BranchSwitcher from "@/Components/BranchSwitcher.vue";
import useAuth from "@/Composables/useAuth";
import { Link, router } from "@inertiajs/vue3";
import { computed } from "vue";
import { useLayout } from "./LayoutComposable";

const { toggleMenu } = useLayout();
const { user, roles, permissions, can } = useAuth();

// detect active route
const current = computed(() => route().current());

// highlight helper
const isActive = (name) => current.value.startsWith(name);
</script>

<template>
  <div
    class="layout-topbar flex items-center justify-between px-4 bg-zinc-900 border-b border-zinc-800 shadow-md"
  >
    <!-- LEFT SIDE: Menu Toggle + Logo -->
    <div class="flex items-center gap-3">
      <button
        class="layout-menu-button layout-topbar-action text-zinc-400 hover:text-white transition-colors"
        @click="toggleMenu"
      >
        <i class="pi pi-bars text-xl" />
      </button>

      <Link
        href="/"
        class="layout-topbar-logo flex items-center gap-2"
      >
        <ApplicationLogo class="h-8 w-auto" />
      </Link>
    </div>

    <!-- RIGHT SIDE: ACTIONS -->
    <div class="flex items-center gap-4">
      <BranchSwitcher />
      <!-- POS BUTTON (TOPBAR BUTTON — NOT DROPDOWN) -->
      <button
        class="flex items-center gap-2 px-3 py-2 rounded-lg transition"
        :class="
          isActive('pos.index')
            ? 'bg-indigo-600 text-white shadow-sm'
            : 'hover:bg-zinc-800 text-zinc-300'
        "
        @click="router.visit(route('pos.index'))"
      >
        <i class="pi pi-desktop text-lg" />
        <span class="text-sm font-medium">POS</span>
      </button>

      <!-- ORDERS BUTTON -->
      <button
        class="flex items-center gap-2 px-3 py-2 rounded-lg transition"
        :class="
          isActive('pos.orders.index')
            ? 'bg-emerald-600 text-white shadow-sm'
            : 'hover:bg-zinc-800 text-zinc-300'
        "
        @click="router.visit(route('pos.orders.index'))"
      >
        <i class="pi pi-receipt text-lg" />
        <span class="text-sm font-medium">Orders</span>
      </button>

      <!-- MESSAGES BUTTON -->
      <!-- <button
                type="button"
                class="layout-topbar-action flex items-center gap-2 hover:text-primary"
            >
                <i class="pi pi-inbox text-lg"></i>
                <span class="hidden md:block text-sm">Messages</span>
            </button> -->

      <!-- PROFILE DROPDOWN -->
      <Menu
        id="top_profile_menu"
        ref="menu"
        :popup="true"
        :model="[
          {
            label: 'Settings',
            icon: 'pi pi-cog',
            command: () => router.visit(route('profile.edit')),
          },
          {
            label: 'Logout',
            icon: 'pi pi-sign-out',
            command: () => router.post(route('logout')),
          },
        ]"
      />

      <Button
        type="button"
        :label="user.name"
        icon="pi pi-user"
        class="layout-topbar-action1"
        variant="outlined"
        @click="$refs.menu.toggle($event)"
      />
    </div>
  </div>
</template>
