<template>
    <div class="min-h-screen bg-gray-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
      <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Create a new account
        </h2>
      </div>
  
      <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
          <!-- Alert for errors -->
          <div v-if="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ errorMessage }}
          </div>
  
          <!-- Registration Form -->
          <form class="space-y-6" @submit.prevent="register">
            <!-- Personal Information -->
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700">
                Full name
              </label>
              <div class="mt-1">
                <input 
                  v-model="form.name" 
                  id="name" 
                  name="name" 
                  type="text" 
                  autocomplete="name" 
                  required 
                  class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                />
              </div>
            </div>
  
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700">
                Email address
              </label>
              <div class="mt-1">
                <input 
                  v-model="form.email" 
                  id="email" 
                  name="email" 
                  type="email" 
                  autocomplete="email" 
                  required 
                  class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                />
              </div>
            </div>
  
            <!-- Business Information -->
            <div>
              <label for="business_name" class="block text-sm font-medium text-gray-700">
                Business name
              </label>
              <div class="mt-1">
                <input 
                  v-model="form.business_name" 
                  id="business_name" 
                  name="business_name" 
                  type="text" 
                  required 
                  class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                />
              </div>
            </div>
  
            <div>
              <label for="subdomain" class="block text-sm font-medium text-gray-700">
                Subdomain
              </label>
              <div class="mt-1 flex rounded-md shadow-sm">
                <input 
                  v-model="form.subdomain" 
                  id="subdomain" 
                  name="subdomain" 
                  type="text" 
                  required 
                  class="appearance-none flex-1 block w-full px-3 py-2 border border-gray-300 rounded-l-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                />
                <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                  .example.com
                </span>
              </div>
              <p class="mt-1 text-sm text-gray-500">
                Letters, numbers, and dashes only
              </p>
            </div>
  
            <!-- Password -->
            <div>
              <label for="password" class="block text-sm font-medium text-gray-700">
                Password
              </label>
              <div class="mt-1">
                <input 
                  v-model="form.password" 
                  id="password" 
                  name="password" 
                  type="password" 
                  autocomplete="new-password" 
                  required 
                  class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                />
              </div>
            </div>
  
            <div>
              <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                Confirm password
              </label>
              <div class="mt-1">
                <input 
                  v-model="form.password_confirmation" 
                  id="password_confirmation" 
                  name="password_confirmation" 
                  type="password" 
                  autocomplete="new-password" 
                  required 
                  class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                />
              </div>
            </div>
  
            <div>
              <button 
                type="submit" 
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                :disabled="loading"
              >
                <span v-if="loading">Creating account...</span>
                <span v-else>Register</span>
              </button>
            </div>
          </form>
  
          <div class="mt-6">
            <div class="relative">
              <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300"></div>
              </div>
              <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white text-gray-500">
                  Or
                </span>
              </div>
            </div>
  
            <div class="mt-6">
              <router-link 
                to="/login" 
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-blue-600 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                Sign in to existing account
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import { ref, reactive } from 'vue'
  import { useRouter } from 'vue-router'
  import { useAuthStore } from '@/stores/auth'
  
  export default {
    name: 'RegisterView',
    
    setup() {
      const router = useRouter()
      const authStore = useAuthStore()
      
      // Form data
      const form = reactive({
        name: '',
        email: '',
        business_name: '',
        subdomain: '',
        password: '',
        password_confirmation: ''
      })
      
      // UI state
      const loading = ref(false)
      const errorMessage = ref('')
      
      // Methods
      const register = async () => {
        loading.value = true
        errorMessage.value = ''
        
        try {
          // Call the register action from the auth store
          await authStore.register(form)
          
          // Redirect to dashboard on success
          router.push('/dashboard')
        } catch (error) {
          // Display error message
          if (error.response?.data?.errors) {
            // Format validation errors
            const errors = error.response.data.errors
            errorMessage.value = Object.keys(errors)
              .map(key => errors[key].join(' '))
              .join('\n')
          } else {
            errorMessage.value = error.response?.data?.error || 'An error occurred during registration'
          }
        } finally {
          loading.value = false
        }
      }
      
      return {
        form,
        loading,
        errorMessage,
        register
      }
    }
  }
  </script>