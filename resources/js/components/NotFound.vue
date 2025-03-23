<template>
    <div class="min-h-screen flex flex-col justify-center items-center">
      <div class="text-center">
        <h1 class="text-9xl font-bold text-blue-600">404</h1>
        <h2 class="text-3xl font-semibold mt-4 text-gray-800">Page Not Found</h2>
        <p class="text-gray-600 mt-2">The page you are looking for doesn't exist or has been moved.</p>
        
        <div class="mt-8">
          <router-link 
            to="/dashboard" 
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Go to Dashboard
          </router-link>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    name: 'NotFoundView'
  }
  </script>