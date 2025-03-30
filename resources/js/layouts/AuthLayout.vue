<!-- resources/js/layouts/AuthLayout.vue -->

<template>
    <div class="min-h-screen bg-gray-100 flex flex-col justify-center">
      <!-- Logo section -->
      <div class="sm:mx-auto sm:w-full sm:max-w-md mb-6">
        <h1 class="text-center text-3xl font-bold text-gray-800">WMS System</h1>
        <h2 class="text-center text-md font-medium text-gray-600">Warehouse Management System</h2>
      </div>
  
      <!-- Main content -->
      <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <!-- Display any global alerts/notifications -->
        <AlertMessage 
          v-if="alert.show" 
          :type="alert.type" 
          :message="alert.message"
          @close="closeAlert" 
          class="mx-4 sm:mx-0"
        />
        
        <!-- Router view for auth pages -->
        <div class="bg-white py-8 px-6 shadow-md rounded-lg sm:px-10">
          <router-view v-slot="{ Component }">
            <transition name="fade" mode="out-in">
              <component :is="Component" />
            </transition>
          </router-view>
        </div>
      </div>
  
      <!-- Footer -->
      <div class="sm:mx-auto sm:w-full sm:max-w-md mt-8 text-center text-gray-500 text-sm">
        WMS System &copy; {{ currentYear }}
      </div>
    </div>
  </template>
  
  <script setup>
  import { computed } from 'vue';
  import { useAlertStore } from '../stores/alert';
  import AlertMessage from '../components/shared/AlertMessage.vue';
  
  // Get the current year for the footer copyright
  const currentYear = new Date().getFullYear();
  
  // Alert state from store for displaying notifications
  const alertStore = useAlertStore();
  const alert = computed(() => alertStore.alert);
  
  /**
   * Close the alert notification
   */
  function closeAlert() {
    alertStore.clearAlert();
  }
  </script>
  
  <style scoped>
  /* Fade transition for page content */
  .fade-enter-active,
  .fade-leave-active {
    transition: opacity 0.2s;
  }
  
  .fade-enter-from,
  .fade-leave-to {
    opacity: 0;
  }
  </style>