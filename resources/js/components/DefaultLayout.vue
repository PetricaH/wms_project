<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-blue-600 shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex">
            <div class="flex-shrink-0 flex items-center">
              <h1 class="text-white text-xl font-bold">WMS</h1>
            </div>
            <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
              <router-link 
                :to="{ name: 'dashboard' }" 
                class="border-transparent text-white hover:border-white hover:text-white inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                active-class="border-white"
              >
                Dashboard
              </router-link>
              
              <!-- Add more navigation links based on permissions -->
              <router-link
                v-if="authStore.hasPermission('inventory.view')"
                to="/dashboard/inventory"
                class="border-transparent text-white hover:border-white hover:text-white inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                active-class="border-white"
              >
                Inventory
              </router-link>
              
              <router-link
                v-if="authStore.hasPermission('products.view')"
                to="/dashboard/products"
                class="border-transparent text-white hover:border-white hover:text-white inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                active-class="border-white"
              >
                Products
              </router-link>

              <!-- Add other nav links as needed -->
            </div>
          </div>
          
          <div class="hidden sm:ml-6 sm:flex sm:items-center">
            <!-- Integrated UserMenu component -->
            <UserMenu />
          </div>
        </div>
      </div>
    </nav>
       
    <!-- Main content -->
    <main class="py-10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <slot />
      </div>
    </main>
  </div>
</template>
   
<script setup>
import { useAuthStore } from '../stores/auth';
import UserMenu from '../components/navigation/UserMenu.vue';
import SidebarNav from '../components/navigation/SidebarNav.vue';
// Get the auth store for permission checking
const authStore = useAuthStore();
</script>