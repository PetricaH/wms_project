<template>
    <div class="min-h-screen bg-gray-100">
      <!-- Simple header for auth pages -->
      <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
          <h1 class="text-xl font-bold text-blue-600">Warehouse Management System</h1>
        </div>
      </header>
      
      <!-- Main content - just passes through the slot -->
      <main>
        <slot />
      </main>
      
      <!-- Footer -->
      <footer class="bg-white mt-8 py-4 border-t">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <p class="text-center text-sm text-gray-500">
            &copy; {{ new Date().getFullYear() }} WMS Project. All rights reserved.
          </p>
        </div>
      </footer>
    </div>
  </template>
  
  <script>
  export default {
    name: 'AuthLayout'
  }
  </script>