<template>
    <div>
      <h1 class="text-2xl font-bold text-gray-900 mb-6">Dashboard</h1>
      
      <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Welcome, {{ user?.name }}!</h2>
        <p class="text-gray-600">
          You are logged into the <strong>{{ tenantName }}</strong> WMS system.
        </p>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- User information card -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Your Information</h3>
          
          <div class="space-y-3">
            <div>
              <span class="text-gray-500">Name:</span>
              <span class="ml-2 text-gray-900">{{ user?.name }}</span>
            </div>
            <div>
              <span class="text-gray-500">Email:</span>
              <span class="ml-2 text-gray-900">{{ user?.email }}</span>
            </div>
            <div>
              <span class="text-gray-500">Role:</span>
              <span class="ml-2 text-gray-900">{{ userRoles.join(', ') }}</span>
            </div>
          </div>
        </div>
        
        <!-- Tenant information card -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Tenant Information</h3>
          
          <div class="space-y-3">
            <div>
              <span class="text-gray-500">Tenant Name:</span>
              <span class="ml-2 text-gray-900">{{ tenantName }}</span>
            </div>
            <div>
              <span class="text-gray-500">Tenant ID:</span>
              <span class="ml-2 text-gray-900">{{ tenant?.id }}</span>
            </div>
            <div v-if="tenant?.domains && tenant.domains.length > 0">
              <span class="text-gray-500">Domain:</span>
              <span class="ml-2 text-gray-900">{{ tenant.domains[0].domain }}</span>
            </div>
          </div>
        </div>
        
        <!-- Quick actions card -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
          
          <div class="space-y-2">
            <button 
              class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              View Inventory
            </button>
            
            <button 
              class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              Manage Products
            </button>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import { computed } from 'vue'
  import { useAuthStore } from '@/stores/auth'
  
  export default {
    name: 'DashboardView',
    
    setup() {
      const authStore = useAuthStore()
      
      // Get user and tenant information from the auth store
      const user = computed(() => authStore.user)
      const tenant = computed(() => authStore.tenant)
      
      // Get tenant name from the tenant's data property
      const tenantName = computed(() => {
        return tenant.value?.data?.name || 'Unknown Tenant'
      })
      
      // Get user roles as simple array of names
      const userRoles = computed(() => {
        return user.value?.roles?.map(role => role.name) || []
      })
      
      return {
        user,
        tenant,
        tenantName,
        userRoles
      }
    }
  }
  </script>