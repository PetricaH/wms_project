<template>
    <div class="min-h-screen bg-gray-100 flex flex-col justify-center">
      <!-- Simple header for error pages -->
      <main class="flex-grow flex items-center justify-center">
        <slot />
      </main>
      
      <!-- Footer -->
      <footer class="bg-white py-4 border-t">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex justify-center">
            <router-link 
              to="/" 
              class="text-blue-600 hover:text-blue-800"
            >
              Return to home
            </router-link>
          </div>
        </div>
      </footer>
    </div>
  </template>
  
  <script>
  export default {
    name: 'ErrorLayout'
  }
  </script>