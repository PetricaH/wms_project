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
                  to="/dashboard" 
                  class="border-transparent text-white hover:border-white hover:text-white inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                  active-class="border-white"
                >
                  Dashboard
                </router-link>
                <!-- Add more navigation links here -->
              </div>
            </div>
            <div class="hidden sm:ml-6 sm:flex sm:items-center">
              <!-- Profile dropdown -->
              <div class="ml-3 relative">
                <div class="flex items-center">
                  <span class="text-white text-sm">{{ user?.name }}</span>
                  <button 
                    @click="logout" 
                    class="ml-4 px-3 py-1 border border-white text-white rounded-md text-sm hover:bg-white hover:text-blue-600"
                  >
                    Logout
                  </button>
                </div>
              </div>
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
  
  <script>
  import { computed } from 'vue'
  import { useRouter } from 'vue-router'
  import { useAuthStore } from '@/stores/auth'
  
  export default {
    name: 'DefaultLayout',
    
    setup() {
      const router = useRouter()
      const authStore = useAuthStore()
      
      // Get current user from store
      const user = computed(() => authStore.user)
      
      // Logout method
      const logout = async () => {
        await authStore.logout()
        router.push('/login')
      }
      
      return {
        user,
        logout
      }
    }
  }
  </script>