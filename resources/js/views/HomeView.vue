<template>
    <div>
      <h1 class="text-2xl font-semibold text-gray-900 mb-6">Welcome to WMS</h1>
      
      <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900">
            Warehouse Management System
          </h3>
          <p class="mt-1 max-w-2xl text-sm text-gray-500">
            Welcome to your warehouse management dashboard.
          </p>
        </div>
        <div class="border-t border-gray-200">
          <dl>
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500">
                Navigation
              </dt>
              <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                <p>Please use the navigation menu to access available features.</p>
                
                <div class="mt-4">
                  <h4 class="text-sm font-medium text-gray-800 mb-2">Available sections:</h4>
                  <ul class="space-y-1 list-disc list-inside text-sm text-gray-600">
                    <li v-if="hasDashboardAccess">
                      <router-link to="/dashboard" class="text-blue-600 hover:underline">Dashboard</router-link> - View performance metrics and business overview
                    </li>
                    <li v-if="hasInventoryAccess">
                      <router-link to="/dashboard/inventory" class="text-blue-600 hover:underline">Inventory</router-link> - Manage your product inventory
                    </li>
                    <li v-if="hasProductsAccess">
                      <router-link to="/dashboard/products" class="text-blue-600 hover:underline">Products</router-link> - View and manage products
                    </li>
                    <li>
                      <router-link to="/dashboard/profile" class="text-blue-600 hover:underline">Your Profile</router-link> - Manage your user profile
                    </li>
                  </ul>
                </div>
              </dd>
            </div>
            
            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500">
                Access permissions
              </dt>
              <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                <p>
                  Some features may be restricted based on your user permissions. 
                  Contact your administrator if you need additional access.
                </p>
              </dd>
            </div>
          </dl>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { computed } from 'vue';
  import { useAuthStore } from '../stores/auth';
  
  const authStore = useAuthStore();
  
  // Compute which features the user has access to
  const hasDashboardAccess = computed(() => authStore.hasPermission('dashboard.view'));
  const hasInventoryAccess = computed(() => authStore.hasPermission('inventory.view'));
  const hasProductsAccess = computed(() => authStore.hasPermission('products.view'));
  </script>